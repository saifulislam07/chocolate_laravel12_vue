<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Slider;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        $wishlistProductIds = $request->user()
            ? $request->user()->wishlists()->pluck('product_id')->all()
            : [];

        $sliders = Slider::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        $productsQuery = Product::query()
            ->with('images')
            ->where('is_active', true)
            ->latest();

        $featuredItems = (clone $productsQuery)
            ->where('is_bundle', false)
            ->where('is_featured', true)
            ->take(8)
            ->get()
            ->map(fn($p) => $this->mapProduct($p, $wishlistProductIds));

        $newArrivals = (clone $productsQuery)
            ->where('is_bundle', false)
            ->where('is_new', true)
            ->take(8)
            ->get()
            ->map(fn($p) => $this->mapProduct($p, $wishlistProductIds));

        $discountItems = (clone $productsQuery)
            ->where('is_bundle', false)
            ->whereNotNull('compare_at_price')
            ->whereColumn('compare_at_price', '>', 'price')
            ->take(8)
            ->get()
            ->map(fn($p) => $this->mapProduct($p, $wishlistProductIds));

        $bundleItems = Product::query()
            ->with(['images', 'bundleItems'])
            ->where('is_active', true)
            ->where('is_bundle', true)
            ->latest()
            ->take(8)
            ->get()
            ->map(fn($p) => $this->mapProduct($p, $wishlistProductIds));

        $categories = Category::query()
            ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
            ->where('is_active', true)
            ->whereHas('products', fn ($query) => $query->where('is_active', true))
            ->orderBy('name')
            ->take(6)
            ->get()
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image' => $category->image ?: $category->products()->with('images')->where('is_active', true)->first()?->images->first()?->image_path,
                'products_count' => $category->products_count,
            ]);

        $categorySections = Category::query()
            ->with(['products' => fn ($query) => $query->with('images')->where('is_active', true)->latest()->take(4)])
            ->where('is_active', true)
            ->whereHas('products', fn ($query) => $query->where('is_active', true))
            ->orderBy('name')
            ->take(4)
            ->get()
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'products' => $category->products->map(fn (Product $product) => $this->mapProduct($product, $wishlistProductIds)),
            ]);

        return Inertia::render('Home', [
            'sliders' => $sliders,
            'categories' => $categories,
            'categorySections' => $categorySections,
            'featuredItems' => $featuredItems,
            'newArrivals' => $newArrivals,
            'discountItems' => $discountItems,
            'bundleItems' => $bundleItems,
        ]);
    }

    private function mapProduct(Product $product, array $wishlistProductIds): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => (float) $product->price,
            'compare_at_price' => (float) $product->compare_at_price,
            'badge' => $product->is_new ? 'New' : ($product->is_featured ? 'Featured' : null),
            'image' => $product->images->first()?->image_path,
            'is_wishlisted' => in_array($product->id, $wishlistProductIds, true),
            'is_bundle' => (bool) $product->is_bundle,
            'bundle_note' => $product->bundle_note,
            'bundle_items_count' => $product->relationLoaded('bundleItems') ? $product->bundleItems->count() : 0,
        ];
    }
}
