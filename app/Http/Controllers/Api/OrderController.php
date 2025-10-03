<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders.
     */
    public function index(): JsonResponse
    {
        try {
            $orders = Order::with(['user', 'orderItems.menu'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => OrderResource::collection($orders)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): JsonResponse
    {
        try {
            $order->load(['user', 'orderItems.menu']);

            return response()->json([
                'success' => true,
                'data' => new OrderResource($order)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve order',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'table_number' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|exists:menus,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        
        try {
            $order = Order::create([
                'user_id' => $request->user_id,
                'table_number' => $request->table_number,
                'status' => Order::STATUS_PENDING,
                'total_price' => 0, // Will be calculated after items are added
            ]);

            $totalPrice = 0;

            foreach ($request->items as $item) {
                $menu = Menu::findOrFail($item['menu_id']);
                
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'menu_id' => $item['menu_id'],
                    'quantity' => $item['quantity'],
                    'price' => $menu->price, // Use the current menu price
                ]);

                $totalPrice += $menu->price * $item['quantity'];
            }

            // Update the total price after all items are added
            $order->update(['total_price' => $totalPrice]);

            // Reload the order with relationships
            $order->load(['user', 'orderItems.menu']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => new OrderResource($order)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified order status to paid.
     */
    public function markAsPaid(Order $order): JsonResponse
    {
        try {
            if ($order->status === Order::STATUS_COMPLETED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order is already completed'
                ], 400);
            }

            $order->update(['status' => Order::STATUS_PAID]);

            // Reload the order with relationships
            $order->load(['user', 'orderItems.menu']);

            return response()->json([
                'success' => true,
                'message' => 'Order marked as paid successfully',
                'data' => new OrderResource($order)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified order status to completed.
     */
    public function markAsCompleted(Order $order): JsonResponse
    {
        try {
            if ($order->status !== Order::STATUS_PAID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order must be paid before marking as completed'
                ], 400);
            }

            $order->update(['status' => Order::STATUS_COMPLETED]);

            // Reload the order with relationships
            $order->load(['user', 'orderItems.menu']);

            return response()->json([
                'success' => true,
                'message' => 'Order marked as completed successfully',
                'data' => new OrderResource($order)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified order status (generic update).
     */
    public function update(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,paid,completed'
        ]);

        try {
            $order->update(['status' => $request->status]);

            // Reload the order with relationships
            $order->load(['user', 'orderItems.menu']);

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'data' => new OrderResource($order)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}