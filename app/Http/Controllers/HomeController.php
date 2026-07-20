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
        // New Figma-matched design (HomeV2). Old design preserved at resources/js/Pages/Home.vue.
        return Inertia::render('HomeV2', $this->homeData($request));
    }

    public function v2(Request $request): Response
    {
        return Inertia::render('HomeV2', $this->homeData($request));
    }

    private function homeData(Request $request): array
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

        $categories = Category::query()
            ->where('is_active', true)
            ->whereHas('products', fn ($query) => $query->where('is_active', true))
            ->orderBy('id')
            ->take(3)
            ->get()
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image' => $category->image ?: $category->products()->with('images')->where('is_active', true)->first()?->images->first()?->image_path,
            ]);

        return [
            'sliders' => $sliders,
            'categories' => $categories,
            'featuredItems' => $featuredItems,
            'newArrivals' => $newArrivals,
        ];
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
