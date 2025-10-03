<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    /**
     * Display a listing of menus.
     * Access: All authenticated users can view
     */
    public function index()
    {
        return Menu::with('category')->get();
    }

    /**
     * Store a newly created menu.
     * Access: Admin only
     */
    public function store(Request $request)
    {
        // Check if user has admin role
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized - Admin access required'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
        ]);

        $menu = Menu::create($request->all());

        return response()->json($menu, 201);
    }

    /**
     * Display the specified menu.
     * Access: All authenticated users can view
     */
    public function show($id)
    {
        return Menu::with('category')->findOrFail($id);
    }

    /**
     * Update the specified menu.
     * Access: Admin only
     */
    public function update(Request $request, $id)
    {
        // Check if user has admin role
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized - Admin access required'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
        ]);

        $menu = Menu::findOrFail($id);
        $menu->update($request->all());

        return response()->json($menu);
    }

    /**
     * Remove the specified menu.
     * Access: Admin only
     */
    public function destroy($id)
    {
        // Check if user has admin role
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized - Admin access required'], 403);
        }

        $menu = Menu::findOrFail($id);
        $menu->delete();
        
        return response()->json(['message' => 'Menu deleted']);
    }
}
