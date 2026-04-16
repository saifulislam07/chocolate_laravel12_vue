<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BrandController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Brands/Index', [
            'brands' => Brand::latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean'
        ]);

        $data = [
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'is_active' => $request->input('is_active', true),
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('brands', 'uploads');
            $data['image'] = '/uploads/' . $path;
        }

        Brand::create($data);
        return redirect()->back()->with('success', 'Brand created successfully.');
    }

    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean'
        ]);

        $data = [
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'is_active' => $request->input('is_active', true),
        ];

        if ($request->hasFile('image')) {
            if ($brand->image) {
                $oldPath = str_replace('/uploads/', '', $brand->image);
                \Illuminate\Support\Facades\Storage::disk('uploads')->delete($oldPath);
            }
            $path = $request->file('image')->store('brands', 'uploads');
            $data['image'] = '/uploads/' . $path;
        }

        $brand->update($data);
        return redirect()->back()->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->image) {
            $oldPath = str_replace('/uploads/', '', $brand->image);
            \Illuminate\Support\Facades\Storage::disk('uploads')->delete($oldPath);
        }
        $brand->delete();
        return redirect()->back()->with('success', 'Brand deleted successfully.');
    }
}
