<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Products/Index', [
            'products' => Product::with(['category', 'brand', 'unit', 'images'])->latest()->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Products/Create', [
            'categories' => Category::all(),
            'brands' => Brand::all(),
            'units' => Unit::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'unit_id' => 'required|exists:units,id',
            'cost_price' => 'required|numeric',
            'price' => 'required|numeric',
            'compare_at_price' => 'nullable|numeric',
            'stock' => 'required|integer',
            'alert_quantity' => 'nullable|integer',
            'sku' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'highlights' => 'nullable|array|max:6',
            'highlights.*' => 'nullable|string|max:60',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $product = Product::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . uniqid(),
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'unit_id' => $request->unit_id,
            'cost_price' => $request->cost_price,
            'price' => $request->price,
            'compare_at_price' => $request->compare_at_price,
            'stock' => $request->stock,
            'alert_quantity' => $request->alert_quantity ?? 10,
            'sku' => $request->sku,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
            'is_featured' => $request->boolean('is_featured', false),
            'is_new' => $request->boolean('is_new', true),
            'highlights' => $this->cleanHighlights($request->input('highlights')),
        ]);

        if ($request->hasFile('images')) {
            $isFirst = true;
            foreach ($request->file('images') as $file) {
                $path = $file->store('products', 'uploads');
                $product->images()->create([
                    'image_path' => '/uploads/' . $path,
                    'is_primary' => $isFirst,
                ]);
                $isFirst = false;
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    /**
     * Highlights come from a repeatable input list, so drop the blanks the
     * admin left behind and store null when nothing is filled in.
     */
    private function cleanHighlights($value): ?array
    {
        $items = collect(is_array($value) ? $value : [])
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->take(6)
            ->values()
            ->all();

        return $items ?: null;
    }

    public function edit(Product $product)
    {
        $product->load(['images']);
        return Inertia::render('Admin/Products/Edit', [
            'product' => $product,
            'categories' => Category::all(),
            'brands' => Brand::all(),
            'units' => Unit::all(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'unit_id' => 'required|exists:units,id',
            'cost_price' => 'required|numeric',
            'price' => 'required|numeric',
            'compare_at_price' => 'nullable|numeric',
            'stock' => 'required|integer',
            'alert_quantity' => 'nullable|integer',
            'sku' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'highlights' => 'nullable|array|max:6',
            'highlights.*' => 'nullable|string|max:60',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $product->update([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'unit_id' => $request->unit_id,
            'cost_price' => $request->cost_price,
            'price' => $request->price,
            'compare_at_price' => $request->compare_at_price,
            'stock' => $request->stock,
            'alert_quantity' => $request->alert_quantity,
            'sku' => $request->sku,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
            'is_featured' => $request->boolean('is_featured'),
            'is_new' => $request->boolean('is_new'),
            'highlights' => $this->cleanHighlights($request->input('highlights')),
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('products', 'uploads');
                $product->images()->create([
                    'image_path' => '/uploads/' . $path,
                    'is_primary' => $product->images()->count() === 0,
                ]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroyImage(Product $product, ProductImage $image)
    {
        abort_if($image->product_id !== $product->id, 404);

        $wasPrimary = $image->is_primary;

        Storage::disk('uploads')->delete(str_replace('/uploads/', '', $image->image_path));
        $image->delete();

        // Never leave the gallery without a main image.
        if ($wasPrimary) {
            $product->images()->oldest('id')->first()?->update(['is_primary' => true]);
        }

        return redirect()->back()->with('success', 'Image deleted successfully.');
    }

    public function setPrimaryImage(Product $product, ProductImage $image)
    {
        abort_if($image->product_id !== $product->id, 404);

        $product->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return redirect()->back()->with('success', 'Main image updated.');
    }

    public function destroy(Product $product)
    {
        foreach ($product->images as $image) {
            $storagePath = str_replace('/uploads/', '', $image->image_path);
            Storage::disk('uploads')->delete($storagePath);
        }
        $product->delete();
        return redirect()->back()->with('success', 'Product deleted successfully.');
    }
}
