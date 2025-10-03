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
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders.
     * Access: Admin can view all orders, Cashier can view all orders, User can only view their own orders
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        
        if ($user->hasRole('admin') || $user->hasRole('cashier')) {
            // Admin and Cashier can see all orders
            $orders = Order::with(['user', 'orderItems.menu'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Regular user can only see their own orders
            $orders = Order::with(['user', 'orderItems.menu'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => OrderResource::collection($orders)
        ]);
    }

    /**
     * Display the specified order.
     * Access: Admin can view any order, Cashier can view any order, User can only view their own order
     */
    public function show(Order $order): JsonResponse
    {
        $user = Auth::user();
        
        // Check if user can access this order
        if (!$user->hasRole('admin') && !$user->hasRole('cashier') && $order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this order'
            ], 403);
        }

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
     * Access: All authenticated users can create orders
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

        // Ensure the user creating the order is the authenticated user (or they have admin privileges)
        $userId = $request->user_id ?? Auth::user()->id;
        $user = Auth::user();
        
        if ($userId !== $user->id && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create order for another user'
            ], 403);
        }

        DB::beginTransaction();
        
        try {
            $order = Order::create([
                'user_id' => $userId,
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
     * Access: Only Cashier can mark orders as paid
     */
    public function markAsPaid(Order $order): JsonResponse
    {
        $user = Auth::user();
        
        // Only Cashier can mark orders as paid (admin can only view orders)
        if (!$user->hasRole('cashier')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to mark order as paid'
            ], 403);
        }

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
     * Access: Only Cashier can mark orders as completed
     */
    public function markAsCompleted(Order $order): JsonResponse
    {
        $user = Auth::user();
        
        // Only Cashier can mark orders as completed (admin can only view orders)
        if (!$user->hasRole('cashier')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to mark order as completed'
            ], 403);
        }

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
     * Update the specified order (for admin only, but excludes status updates).
     * Access: Only Admin can update orders (but not status - only other fields like table_number)
     */
    public function update(Request $request, Order $order): JsonResponse
    {
        $user = Auth::user();
        
        // Only Admin can update orders
        if (!$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update order. Only admin can update order details (except status).'
            ], 403);
        }

        // Validate that status is not being updated (status can only be changed by cashier-specific endpoints)
        $validatedData = $request->validate([
            'table_number' => 'sometimes|required|string|max:255',
            'user_id' => 'sometimes|required|exists:users,id',
            // Note: 'status' is intentionally excluded from validation to prevent updates
        ]);

        // If status was provided in the request, reject the request
        if ($request->has('status')) {
            return response()->json([
                'success' => false,
                'message' => 'Status updates are restricted. Use cashier-specific endpoints to update status.'
            ], 403);
        }

        try {
            $order->update($validatedData);

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