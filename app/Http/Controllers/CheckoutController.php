<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\WebSetting;
use App\Services\Payments\BkashPaymentService;
use App\Services\Payments\NagadPaymentService;
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
            'paymentGateways' => $this->paymentGatewayOptions(),
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
            'payment_method' => ['required', 'in:cod,card,bkash,nagad'],
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

        if ($order->payment_method === 'bkash') {
            $settings = WebSetting::first();
            $bkash = new BkashPaymentService($settings);

            if (! $settings || ! $bkash->enabled()) {
                return redirect()
                    ->route('checkout.success', $order)
                    ->with('error', 'bKash merchant payment is not configured yet.');
            }

            $response = $bkash->createPayment($order, route('payment.bkash.callback'));

            if (! empty($response['bkashURL'])) {
                return redirect()->away($response['bkashURL']);
            }

            return redirect()
                ->route('checkout.success', $order)
                ->with('error', $response['statusMessage'] ?? 'Could not start bKash payment.');
        }

        if ($order->payment_method === 'nagad') {
            $settings = WebSetting::first();
            $nagad = new NagadPaymentService($settings);

            if ($settings && $nagad->enabled()) {
                $nagad->createPendingPayment($order);
            }

            return redirect()
                ->route('checkout.success', $order)
                ->with('error', 'Nagad merchant credentials are saved. Automatic Nagad redirect requires final merchant API signing details from Nagad.');
        }

        return redirect()
            ->route('checkout.success', $order)
            ->with('success', 'Order placed successfully.');
    }

    public function bkashCallback(Request $request): RedirectResponse
    {
        $paymentId = $request->query('paymentID');
        $status = $request->query('status');

        $transaction = PaymentTransaction::where('gateway', 'bkash')
            ->where('gateway_payment_id', $paymentId)
            ->latest()
            ->first();

        if (! $transaction) {
            return redirect()->route('cart.index')->with('error', 'bKash payment session was not found.');
        }

        $order = $transaction->order;

        $transaction->update([
            'callback_payload' => $request->query(),
        ]);

        if ($status !== 'success') {
            $transaction->update([
                'status' => $status ?: 'failed',
                'failure_reason' => 'Payment was not completed.',
            ]);
            $order->update(['payment_status' => 'unpaid']);

            return redirect()->route('checkout.success', $order)->with('error', 'bKash payment was not completed.');
        }

        $settings = WebSetting::first();
        $response = (new BkashPaymentService($settings))->executePayment($paymentId);
        $trxId = $response['trxID'] ?? null;
        $paid = ($response['transactionStatus'] ?? null) === 'Completed';

        $transaction->update([
            'gateway_transaction_id' => $trxId,
            'status' => $paid ? 'paid' : 'failed',
            'response_payload' => $response,
            'failure_reason' => $paid ? null : ($response['statusMessage'] ?? 'bKash execution failed.'),
        ]);

        $order->update([
            'payment_status' => $paid ? 'paid' : 'unpaid',
            'status' => $paid ? 'processing' : $order->status,
            'paid_amount' => $paid ? $order->total : 0,
            'due_amount' => $paid ? 0 : $order->total,
        ]);

        return redirect()
            ->route('checkout.success', $order)
            ->with($paid ? 'success' : 'error', $paid ? 'bKash payment completed successfully.' : 'bKash payment execution failed.');
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
                'payment_status' => $order->payment_status,
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

    private function paymentGatewayOptions(): array
    {
        $settings = WebSetting::first();

        return [
            'bkash' => [
                'enabled' => (bool) $settings?->bkash_enabled,
            ],
            'nagad' => [
                'enabled' => (bool) $settings?->nagad_enabled,
            ],
        ];
    }
}
