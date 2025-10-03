<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;

class MenuController extends Controller
{
    public function index()
    {
        return Menu::with('category')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
        ]);

        $menu = Menu::create($request->all());

        return response()->json($menu, 201);
    }

    public function show($id)
    {
        return Menu::with('category')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);
        $menu->update($request->all());

        return response()->json($menu);
    }

    public function destroy($id)
    {
        Menu::findOrFail($id)->delete();
        return response()->json(['message' => 'Menu deleted']);
    }
}
