<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        $cart = $this->resolveCart($request);
        $cart->load('items.product.images');

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your bag is empty.');
        }

        $items = $cart->items->map(function ($item): array {
            $price = (float) $item->product->price;
            $quantity = (int) $item->quantity;

            return [
                'id' => $item->id,
                'name' => $item->product->name,
                'quantity' => $quantity,
                'price' => $price,
                'line_total' => $price * $quantity,
                'image' => $item->product->images->first()?->image_path,
            ];
        })->values();

        $subtotal = $items->sum('line_total');
        $shipping = $subtotal >= 80 ? 0 : 8;
        $tax = round($subtotal * 0.05, 2);
        $total = $subtotal + $shipping + $tax;

        return Inertia::render('Checkout/Index', [
            'items' => $items,
            'summary' => [
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'tax' => $tax,
                'total' => $total,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:30'],
            'payment_method' => ['required', 'in:cod,card'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $cart = $this->resolveCart($request);
        $cart->load('items.product');

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your bag is empty.');
        }

        $subtotal = $cart->items->sum(fn ($item) => (float) $item->product->price * (int) $item->quantity);
        $shipping = $subtotal >= 80 ? 0 : 8;
        $tax = round($subtotal * 0.05, 2);
        $total = $subtotal + $shipping + $tax;

        $order = DB::transaction(function () use ($cart, $payload, $subtotal, $shipping, $tax, $total): Order {
            $order = Order::create([
                'order_number' => 'CHOC-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                'user_id' => Auth::id(),
                'status' => 'pending',
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax' => $tax,
                'shipping_cost' => $shipping,
                'total' => $total,
                'payment_method' => $payload['payment_method'],
                'payment_status' => 'unpaid',
                'shipping_address' => sprintf(
                    "%s\n%s\n%s %s\nPhone: %s",
                    $payload['full_name'],
                    $payload['address'],
                    $payload['city'],
                    $payload['postal_code'] ?? '',
                    $payload['phone'] ?? 'N/A'
                ),
                'notes' => $payload['notes'] ?? null,
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'price' => $item->product->price,
                    'quantity' => $item->quantity,
                ]);
            }

            $cart->items()->delete();

            return $order;
        });

        return redirect()
            ->route('checkout.success', $order)
            ->with('success', 'Order placed successfully.');
    }

    public function success(Order $order): Response
    {
        $order->load('items');

        return Inertia::render('Checkout/Success', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'total' => (float) $order->total,
                'created_at' => $order->created_at?->toDateTimeString(),
                'items' => $order->items->map(fn ($item): array => [
                    'id' => $item->id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'price' => (float) $item->price,
                ]),
            ],
        ]);
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
}
