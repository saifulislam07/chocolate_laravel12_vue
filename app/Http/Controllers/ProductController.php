<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Product::query()
            ->with(['images', 'category'])
            ->where('is_active', true);

        // Filter by Category
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filter by Price Range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sorting
        switch ($request->sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            default:
                $query->latest();
                break;
        }

        $products = $query->paginate(12)->withQueryString();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => Category::whereHas('products')->orderBy('name')->get(['id', 'name', 'slug']),
            'filters' => $request->only(['category', 'min_price', 'max_price', 'sort']),
        ]);
    }
    public function show(Product $product): Response
    {
        abort_unless($product->is_active, 404);

        $product->load(['images', 'variants', 'category']);

        $relatedProducts = Product::query()
            ->with('images')
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->latest()
            ->take(4)
            ->get()
            ->map(fn (Product $item): array => [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'price' => (float) $item->price,
                'image' => $item->images->first()?->image_path,
            ]);

        return Inertia::render('Products/Show', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'price' => (float) $product->price,
                'compare_at_price' => $product->compare_at_price ? (float) $product->compare_at_price : null,
                'stock' => $product->stock,
                'category' => $product->category?->name,
                'images' => $product->images->pluck('image_path')->values(),
                'variants' => $product->variants->map(fn ($variant): array => [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'value' => $variant->value,
                    'additional_price' => (float) $variant->additional_price,
                ]),
            ],
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
