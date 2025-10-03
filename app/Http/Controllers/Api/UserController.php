<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of cashier users.
     * Access: Admin only
     */
    public function index()
    {
        $user = request()->user();
        
        if (!$user->hasRole('admin')) {
            return response()->json(['message' => 'Admin access required'], 403);
        }

        $cashiers = User::whereHas('roles', function ($query) {
            $query->where('name', 'cashier');
        })->with('roles')->get();

        return response()->json([
            'success' => true,
            'data' => $cashiers
        ]);
    }

    /**
     * Store a newly created cashier in storage.
     * Access: Admin only
     */
    public function store(Request $request)
    {
        $user = request()->user();
        
        if (!$user->hasRole('admin')) {
            return response()->json(['message' => 'Admin access required'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cashier = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign cashier role to the new cashier
        $cashierRole = Role::where('name', 'cashier')->first();
        if ($cashierRole) {
            $cashier->assignRole('cashier');
        }

        return response()->json([
            'success' => true,
            'message' => 'Cashier created successfully',
            'data' => $cashier
        ], 201);
    }

    /**
     * Display the specified cashier.
     * Access: Admin only
     */
    public function show(User $user)
    {
        $admin = request()->user();
        
        if (!$admin->hasRole('admin')) {
            return response()->json(['message' => 'Admin access required'], 403);
        }

        // Only allow viewing cashier users
        if (!$user->hasRole('cashier')) {
            return response()->json(['message' => 'User not found or is not a cashier'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Update the specified cashier in storage.
     * Access: Admin only
     */
    public function update(Request $request, User $user)
    {
        $admin = request()->user();
        
        if (!$admin->hasRole('admin')) {
            return response()->json(['message' => 'Admin access required'], 403);
        }

        // Only allow updating cashier users
        if (!$user->hasRole('cashier')) {
            return response()->json(['message' => 'User not found or is not a cashier'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updateData = $request->only(['name', 'email']);
        
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Cashier updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Remove the specified cashier from storage.
     * Access: Admin only
     */
    public function destroy(User $user)
    {
        $admin = request()->user();
        
        if (!$admin->hasRole('admin')) {
            return response()->json(['message' => 'Admin access required'], 403);
        }

        // Only allow deleting cashier users
        if (!$user->hasRole('cashier')) {
            return response()->json(['message' => 'User not found or is not a cashier'], 404);
        }

        // Don't allow deleting the admin user themselves
        if ($user->id === $admin->id) {
            return response()->json(['message' => 'Cannot delete yourself'], 400);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cashier deleted successfully'
        ]);
    }
}
