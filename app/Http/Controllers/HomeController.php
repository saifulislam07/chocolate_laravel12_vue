<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $productsQuery = Product::query()
            ->with('images')
            ->where('is_active', true)
            ->latest();

        $featuredItems = (clone $productsQuery)
            ->where('is_featured', true)
            ->take(8)
            ->get()
            ->map(fn($p) => $this->mapProduct($p));

        $newArrivals = (clone $productsQuery)
            ->where('is_new', true)
            ->take(8)
            ->get()
            ->map(fn($p) => $this->mapProduct($p));

        $discountItems = (clone $productsQuery)
            ->whereNotNull('compare_at_price')
            ->whereColumn('compare_at_price', '>', 'price')
            ->take(8)
            ->get()
            ->map(fn($p) => $this->mapProduct($p));

        return Inertia::render('Home', [
            'featuredItems' => $featuredItems,
            'newArrivals' => $newArrivals,
            'discountItems' => $discountItems,
        ]);
    }

    private function mapProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => (float) $product->price,
            'compare_at_price' => (float) $product->compare_at_price,
            'badge' => $product->is_new ? 'New' : ($product->is_featured ? 'Featured' : null),
            'image' => $product->images->first()?->image_path,
        ];
    }
}
