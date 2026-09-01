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
            'allMenus' => Menu::orderBy('order')->get(), // For parent selection in create/edit
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateMenu($request);

        $validated['order'] ??= $this->nextOrder($validated['parent_id'] ?? null);

        Menu::create($validated);

        return redirect()->back()->with('success', 'Menu item created.');
    }

    public function update(Request $request, Menu $menu)
    {
        $validated = $this->validateMenu($request, $menu);

        // Leaving the order box empty means "keep the position it already has".
        $validated['order'] ??= $menu->order;

        $menu->update($validated);

        return redirect()->back()->with('success', 'Menu item updated.');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();

        return redirect()->back()->with('success', 'Menu item deleted.');
    }

    /**
     * The storefront header renders parents and one level of children, so the
     * hierarchy is deliberately capped at two levels. Without these checks a
     * parent could be turned into a sub menu and its own children would silently
     * disappear from both this screen and the site navigation.
     */
    protected function validateMenu(Request $request, ?Menu $menu = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'parent_id' => [
                'nullable',
                'exists:menus,id',
                function (string $attribute, $value, callable $fail) use ($menu) {
                    if ($menu && (int) $value === $menu->id) {
                        $fail('A menu item cannot be its own parent.');

                        return;
                    }

                    if (Menu::whereKey($value)->whereNotNull('parent_id')->exists()) {
                        $fail('The selected item is already a sub menu. Navigation supports two levels only.');

                        return;
                    }

                    if ($menu?->children()->exists()) {
                        $fail('This item has sub menu items of its own, so it cannot become a sub menu. Move or delete its children first.');
                    }
                },
            ],
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'show_categories' => 'boolean',
        ], [
            'order.integer' => 'Display order must be a whole number.',
            'order.min' => 'Display order cannot be negative.',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        // Only a main menu item can hold a dropdown, so a sub menu never carries
        // the category list even if the flag is posted.
        $validated['show_categories'] = empty($validated['parent_id'])
            && $request->boolean('show_categories');

        return $validated;
    }

    /**
     * Put a new item at the end of its own level instead of defaulting every
     * item to 0, which left the ordering down to whatever the database returned.
     */
    protected function nextOrder(?int $parentId): int
    {
        return (int) Menu::where('parent_id', $parentId)->max('order') + 1;
    }
}
