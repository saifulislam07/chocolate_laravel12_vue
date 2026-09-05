<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\WebSetting;
use App\Services\CartManager;
use App\Services\InventoryService;
use App\Services\ShippingCalculator;
use App\Services\Payments\BkashPaymentService;
use App\Services\Payments\NagadPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    /**
     * Ids of the orders placed from this browser session, so a guest can still
     * open their own confirmation page without an account.
     */
    private const PLACED_ORDERS_KEY = 'placed_order_ids';

    public function __construct(
        private readonly ShippingCalculator $shipping,
        private readonly CartManager $carts,
    ) {
    }

    public function index(Request $request): Response|RedirectResponse
    {
        $cart = $this->carts->find($request);
        $cart?->load('items.product.images');

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your bag is empty.');
        }

        $items = $cart->items->map(function ($item): array {
            $price = (float) $item->product->price;
            $quantity = (int) $item->quantity;

            return [
                'id' => $item->id,
                'product_id' => $item->product->id,
                'name' => $item->product->name,
                'quantity' => $quantity,
                'price' => $price,
                'line_total' => $price * $quantity,
                'image' => $item->product->images->first()?->image_path,
                'stock' => (int) $item->product->stock,
                // The ceiling the +/- stepper on this page may not cross; the
                // same rule CartController enforces when the request lands.
                'max_quantity' => min(CartManager::MAX_PER_ITEM, (int) $item->product->stock),
            ];
        })->values();

        $subtotal = $items->sum('line_total');
        // No area picked yet, so quote the default rate; the page re-prices live on selection.
        $shipping = $this->shipping->charge($subtotal);
        $tax = 0;
        $total = $subtotal + $shipping + $tax;

        return Inertia::render('Checkout/Index', [
            'items' => $items,
            'summary' => [
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'tax' => $tax,
                'total' => $total,
            ],
            'shippingConfig' => $this->shipping->frontendConfig(),
            'paymentMethods' => $this->paymentMethods(),
            'divisions' => Division::with('districts:id,division_id,name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request, InventoryService $inventory): RedirectResponse
    {
        $payload = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:120'],
            'phone' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:255'],
            'division_id' => ['required', 'exists:divisions,id'],
            'district_id' => [
                'required',
                Rule::exists('districts', 'id')->where('division_id', $request->input('division_id')),
            ],
            'postal_code' => ['nullable', 'string', 'max:30'],
            // Validated against the same list the form was built from, so a
            // gateway switched off in settings cannot be forced through by a
            // request that never went near the form.
            'payment_method' => ['required', Rule::in(array_column($this->paymentMethods(), 'value'))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $cart = $this->carts->find($request);
        $cart?->load('items.product');

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your bag is empty.');
        }

        foreach ($cart->items as $item) {
            if (! $inventory->hasSufficientStock($item->product, $item->quantity)) {
                throw ValidationException::withMessages([
                    'items' => "\"{$item->product->name}\" only has {$item->product->stock} left in stock.",
                ]);
            }
        }

        $subtotal = $cart->items->sum(fn ($item) => (float) $item->product->price * (int) $item->quantity);
        $shipping = $this->shipping->charge($subtotal, $payload['district_id']);
        $tax = 0;
        $total = $subtotal + $shipping + $tax;

        $district = \App\Models\District::find($payload['district_id']);

        // Unify customer identity across web and POS: every order (guest or logged-in)
        // links to a Customer record keyed by phone, so wallet balances work everywhere.
        $customer = \App\Models\Customer::firstOrCreate(
            ['phone' => $payload['phone']],
            [
                'name' => $payload['full_name'],
                'email' => $payload['email'] ?? null,
                'address' => $payload['address'],
                'division_id' => $payload['division_id'],
                'district_id' => $payload['district_id'],
            ]
        );

        $order = DB::transaction(function () use ($cart, $payload, $district, $customer, $subtotal, $shipping, $tax, $total, $inventory): Order {
            $order = Order::create([
                'order_number' => 'CHOC-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                'user_id' => Auth::id(),
                'customer_id' => $customer->id,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax' => $tax,
                'shipping_cost' => $shipping,
                'total' => $total,
                'payment_method' => $payload['payment_method'],
                'payment_status' => 'unpaid',
                // Street address only — name, phone and email live in their own
                // columns and are rendered separately on the invoice.
                'shipping_address' => collect([
                    $payload['address'],
                    trim(($district?->name ?? '') . ' ' . ($payload['postal_code'] ?? '')) ?: null,
                ])->filter()->implode("\n"),
                'customer_phone' => $payload['phone'],
                'customer_name' => $payload['full_name'],
                'division_id' => $payload['division_id'],
                'district_id' => $payload['district_id'],
                'notes' => $payload['notes'] ?? null,
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'price' => $item->product->price,
                    'quantity' => $item->quantity,
                ]);

                $inventory->adjust($item->product, -$item->quantity, 'sale_out', $order, "Online order {$order->order_number}");
            }

            $cart->items()->delete();

            return $order;
        });

        $request->session()->push(self::PLACED_ORDERS_KEY, $order->id);

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

    public function success(Request $request, Order $order): Response
    {
        // 404 rather than 403 so a stranger can't use the status to tell a real
        // order id from one that doesn't exist.
        abort_unless($this->canViewOrder($request, $order), 404);

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

    /**
     * An order confirmation exposes the customer's name, phone, items and totals,
     * so it is readable only by the browser that placed it or the account that
     * owns it — otherwise anyone could walk the id range and read every order.
     */
    private function canViewOrder(Request $request, Order $order): bool
    {
        if ($order->user_id !== null && $order->user_id === $request->user()?->id) {
            return true;
        }

        return in_array($order->id, $request->session()->get(self::PLACED_ORDERS_KEY, []), true);
    }

    /**
     * The ways a shopper may pay right now. Cash is always on the table; a
     * gateway joins the list only once it is switched on in admin settings, so
     * turning one off there takes it off the checkout form. Card was a demo
     * option nothing in settings could ever enable, and is gone with it.
     *
     * @return array<int, array{value: string, label: string, note: string|null}>
     */
    private function paymentMethods(): array
    {
        $settings = WebSetting::first();

        $methods = [
            ['value' => 'cod', 'label' => 'Cash on Delivery', 'note' => null],
        ];

        if ($settings?->bkash_enabled) {
            $methods[] = [
                'value' => 'bkash',
                'label' => 'bKash Merchant',
                'note' => 'After placing the order, you will be redirected to bKash merchant checkout to complete payment.',
            ];
        }

        if ($settings?->nagad_enabled) {
            $methods[] = [
                'value' => 'nagad',
                'label' => 'Nagad Merchant',
                'note' => 'Nagad merchant details are configured. Complete redirect needs the final signed Nagad production API details from your merchant account.',
            ];
        }

        return $methods;
    }
}
