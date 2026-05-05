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
        $selectedCategory = null;
        $wishlistProductIds = $request->user()
            ? $request->user()->wishlists()->pluck('product_id')->all()
            : [];

        $priceBounds = Product::query()
            ->where('is_active', true)
            ->where('is_bundle', false)
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();

        $query = Product::query()
            ->with(['images', 'category'])
            ->where('is_active', true)
            ->where('is_bundle', false);

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                    ->orWhere('description', 'like', '%' . $request->q . '%')
                    ->orWhere('sku', 'like', '%' . $request->q . '%');
            });
        }

        if ($request->filled('category')) {
            $selectedCategory = Category::where('slug', $request->category)->first();

            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->boolean('sale')) {
            $query->whereNotNull('compare_at_price')
                ->whereColumn('compare_at_price', '>', 'price');
        }

        if ($request->boolean('in_stock')) {
            $query->where('stock', '>', 0);
        }

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

        $products = $query->paginate(9)->withQueryString();
        $products->getCollection()->transform(function (Product $product) use ($wishlistProductIds) {
            $product->setAttribute('is_wishlisted', in_array($product->id, $wishlistProductIds, true));

            return $product;
        });

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => Category::withCount(['products' => fn ($query) => $query->where('is_active', true)])
                ->whereHas('products', fn ($query) => $query->where('is_active', true))
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
            'selectedCategory' => $selectedCategory ? [
                'id' => $selectedCategory->id,
                'name' => $selectedCategory->name,
                'slug' => $selectedCategory->slug,
                'description' => $selectedCategory->description,
            ] : null,
            'filters' => [
                'q' => $request->string('q')->toString(),
                'category' => $request->string('category')->toString(),
                'min_price' => $request->string('min_price')->toString(),
                'max_price' => $request->string('max_price')->toString(),
                'sort' => $request->string('sort', 'latest')->toString(),
                'sale' => $request->boolean('sale'),
                'in_stock' => $request->boolean('in_stock'),
            ],
            'priceBounds' => [
                'min' => (float) ($priceBounds->min_price ?? 0),
                'max' => (float) ($priceBounds->max_price ?? 0),
            ],
        ]);
    }

    public function category(Category $category)
    {
        abort_unless($category->is_active, 404);

        return redirect()->route('products.index', ['category' => $category->slug]);
    }
    public function show(Request $request, Product $product): Response
    {
        abort_unless($product->is_active, 404);
        $wishlistProductIds = $request->user()
            ? $request->user()->wishlists()->pluck('product_id')->all()
            : [];

        $product->load(['images', 'variants', 'category']);
        $product->load(['bundleItems.images']);

        $relatedProducts = Product::query()
            ->with('images')
            ->where('is_active', true)
            ->where('is_bundle', $product->is_bundle)
            ->where('id', '!=', $product->id)
            ->when(!$product->is_bundle, fn ($query) => $query->where('category_id', $product->category_id))
            ->latest()
            ->take(4)
            ->get()
            ->map(fn (Product $item): array => [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'price' => (float) $item->price,
                'image' => $item->images->first()?->image_path,
                'is_wishlisted' => in_array($item->id, $wishlistProductIds, true),
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
                'is_bundle' => (bool) $product->is_bundle,
                'bundle_note' => $product->bundle_note,
                'bundle_items' => $product->bundleItems->map(fn (Product $item): array => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'quantity' => (int) $item->pivot->quantity,
                    'price' => (float) $item->price,
                    'image' => $item->images->first()?->image_path,
                ]),
                'images' => $product->images->pluck('image_path')->values(),
                'is_wishlisted' => in_array($product->id, $wishlistProductIds, true),
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
