<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     * Access: All authenticated users can view
     */
    public function index()
    {
        return Category::all();
    }

    /**
     * Store a newly created category.
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
        ]);

        $category = Category::create([
            'name' => $request->name
        ]);

        return response()->json($category, 201);
    }

    /**
     * Display the specified category.
     * Access: All authenticated users can view
     */
    public function show($id)
    {
        return Category::findOrFail($id);
    }

    /**
     * Update the specified category.
     * Access: Admin only
     */
    public function update(Request $request, $id)
    {
        // Check if user has admin role
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized - Admin access required'], 403);
        }

        $category = Category::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category->update([
            'name' => $request->name
        ]);

        return response()->json($category);
    }

    /**
     * Remove the specified category.
     * Access: Admin only
     */
    public function destroy($id)
    {
        // Check if user has admin role
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized - Admin access required'], 403);
        }

        $category = Category::findOrFail($id);
        $category->delete();
        
        return response()->json(['message' => 'Category deleted']);
    }
}
