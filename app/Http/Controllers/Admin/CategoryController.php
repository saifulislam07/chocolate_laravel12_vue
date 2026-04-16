<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Categories/Index', [
            'categories' => Category::latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean'
        ]);

        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'is_active' => $request->input('is_active', true),
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('categories', 'uploads');
            $data['image'] = '/uploads/' . $path;
        }

        Category::create($data);
        return redirect()->back()->with('success', 'Category created successfully.');
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean'
        ]);

        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'is_active' => $request->input('is_active', true),
        ];

        if ($request->hasFile('image')) {
            if ($category->image) {
                $oldPath = str_replace('/uploads/', '', $category->image);
                Storage::disk('uploads')->delete($oldPath);
            }
            $path = $request->file('image')->store('categories', 'uploads');
            $data['image'] = '/uploads/' . $path;
        }

        $category->update($data);
        return redirect()->back()->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        if ($category->image) {
            $oldPath = str_replace('/uploads/', '', $category->image);
            Storage::disk('uploads')->delete($oldPath);
        }
        $category->delete();
        return redirect()->back()->with('success', 'Category deleted successfully.');
    }
}
