<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
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
