<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function index(Request $request): Response
    {
        $cart = $this->resolveCart($request);
        $cart->load('items.product.images');

        $items = $cart->items->map(function (CartItem $item): array {
            $price = (float) $item->product->price;
            $quantity = (int) $item->quantity;

            return [
                'id' => $item->id,
                'product_id' => $item->product->id,
                'name' => $item->product->name,
                'price' => $price,
                'quantity' => $quantity,
                'line_total' => $price * $quantity,
                'image' => $item->product->images->first()?->image_path,
            ];
        })->values();

        return Inertia::render('Cart/Index', [
            'items' => $items,
            'subtotal' => $items->sum('line_total'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $product = Product::query()
            ->where('is_active', true)
            ->findOrFail($payload['product_id']);

        $cart = $this->resolveCart($request);
        $quantityToAdd = $payload['quantity'] ?? 1;

        $item = $cart->items()
            ->where('product_id', $product->id)
            ->first();

        if ($item) {
            $item->increment('quantity', $quantityToAdd);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $quantityToAdd,
            ]);
        }

        return back()->with('success', 'Item added to bag.');
    }

    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->assertCartOwnership($request, $cartItem);

        $payload = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $cartItem->update([
            'quantity' => $payload['quantity'],
        ]);

        return back()->with('success', 'Bag updated.');
    }

    public function destroy(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->assertCartOwnership($request, $cartItem);
        $cartItem->delete();

        return back()->with('success', 'Item removed from bag.');
    }

    private function resolveCart(Request $request): Cart
    {
        $sessionId = $request->session()->getId();
        $user = Auth::user();

        if ($user) {
            return Cart::firstOrCreate(['user_id' => $user->id]);
        }

        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    private function assertCartOwnership(Request $request, CartItem $cartItem): void
    {
        $cart = $this->resolveCart($request);

        abort_unless($cartItem->cart_id === $cart->id, 403);
    }
}
