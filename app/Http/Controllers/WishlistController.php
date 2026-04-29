<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WishlistController extends Controller
{
    public function index(Request $request): Response
    {
        $items = Wishlist::query()
            ->with(['product.images'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn (Wishlist $wishlist): array => [
                'id' => $wishlist->id,
                'product' => [
                    'id' => $wishlist->product->id,
                    'name' => $wishlist->product->name,
                    'slug' => $wishlist->product->slug,
                    'price' => (float) $wishlist->product->price,
                    'compare_at_price' => $wishlist->product->compare_at_price ? (float) $wishlist->product->compare_at_price : null,
                    'image' => $wishlist->product->images->first()?->image_path,
                    'stock' => $wishlist->product->stock,
                ],
            ]);

        return Inertia::render('Wishlist/Index', [
            'items' => $items,
        ]);
    }

    public function toggle(Request $request, Product $product): RedirectResponse
    {
        $wishlist = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->first();

        if ($wishlist) {
            $wishlist->delete();

            return back()->with('success', 'Removed from wishlist.');
        }

        Wishlist::create([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);

        return back()->with('success', 'Added to wishlist.');
    }
}
