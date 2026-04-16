<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MenuController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Menus/Index', [
            'menus' => Menu::with('children')->whereNull('parent_id')->orderBy('order')->get(),
            'allMenus' => Menu::all() // For parent selection in create/edit
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:menus,id',
            'order' => 'integer',
            'is_active' => 'boolean',
        ]);

        Menu::create($validated);

        return redirect()->back()->with('success', 'Menu item created.');
    }

    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:menus,id',
            'order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $menu->update($validated);

        return redirect()->back()->with('success', 'Menu item updated.');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();
        return redirect()->back()->with('success', 'Menu item deleted.');
    }
}
