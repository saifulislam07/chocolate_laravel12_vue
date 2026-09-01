<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    /**
     * Most of any one product a shopper may hold in their bag at a time.
     */
    private const MAX_PER_ITEM = 20;

    public function __construct(private readonly CartManager $carts)
    {
    }

    public function index(Request $request): Response
    {
        $cart = $this->carts->find($request);
        $cart?->load('items.product.images');

        $items = collect($cart?->items ?? [])->map(function (CartItem $item): array {
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
            'freeShippingThreshold' => app(\App\Services\ShippingCalculator::class)->freeShippingThreshold(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:' . self::MAX_PER_ITEM],
        ]);

        $product = Product::query()
            ->where('is_active', true)
            ->findOrFail($payload['product_id']);

        $cart = $this->carts->resolve($request);
        $quantityToAdd = $payload['quantity'] ?? 1;

        $item = $cart->items()
            ->where('product_id', $product->id)
            ->first();

        // The rules below apply to what the bag would end up holding, not to
        // this one click — otherwise adding 20 twice sails past both limits.
        $this->assertQuantityIsAllowed($product, ((int) $item?->quantity) + $quantityToAdd);

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
            'quantity' => ['required', 'integer', 'min:1', 'max:' . self::MAX_PER_ITEM],
        ]);

        $cartItem->loadMissing('product');
        $this->assertQuantityIsAllowed($cartItem->product, $payload['quantity']);

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

    /**
     * Guard the bag against quantities the shop can't honour: more than the
     * per-item cap, or more than there is on the shelf. Checking here means the
     * shopper hears about it while browsing rather than at the payment step.
     */
    private function assertQuantityIsAllowed(?Product $product, int $quantity): void
    {
        if ($product === null) {
            return;
        }

        if ($quantity > self::MAX_PER_ITEM) {
            throw ValidationException::withMessages([
                'quantity' => "You can keep at most " . self::MAX_PER_ITEM . " of \"{$product->name}\" in your bag.",
            ]);
        }

        if ($quantity > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => "\"{$product->name}\" only has {$product->stock} left in stock.",
            ]);
        }
    }

    private function assertCartOwnership(Request $request, CartItem $cartItem): void
    {
        $cart = $this->carts->find($request);

        abort_unless($cart && $cartItem->cart_id === $cart->id, 403);
    }
}
