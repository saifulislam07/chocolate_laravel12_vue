<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReturnRefund;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class SalesReturnController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Returns/Index', [
            'returns' => SalesReturn::with(['order:id,order_number', 'customer:id,name,phone', 'refund'])
                ->latest()
                ->get(),
        ]);
    }

    public function create(Request $request): Response
    {
        $order = Order::with('items')->findOrFail($request->query('order_id'));

        return Inertia::render('Admin/Returns/Create', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'order_source' => $order->order_source,
                'has_customer' => (bool) $order->customer_id,
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product_name,
                    'price' => (float) $item->price,
                    'quantity' => $item->quantity,
                    'returned_quantity' => $item->returned_quantity,
                    'returnable' => $item->quantity - $item->returned_quantity,
                ])->values(),
            ],
        ]);
    }

    public function store(Request $request, InventoryService $inventory): RedirectResponse
    {
        $payload = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'reason' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'exists:order_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.condition' => ['required', 'in:resellable,damaged'],
            'refund_method' => ['required', 'in:wallet,cash,bkash,nagad,bank,card'],
        ]);

        $order = Order::with('items.product', 'customer')->findOrFail($payload['order_id']);

        if ($payload['refund_method'] === 'wallet' && ! $order->customer_id) {
            throw ValidationException::withMessages([
                'refund_method' => 'This order has no linked customer to credit a wallet refund to.',
            ]);
        }

        $salesReturn = DB::transaction(function () use ($order, $payload, $inventory) {
            $salesReturn = SalesReturn::create([
                'return_number' => 'RET-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'return_source' => $order->order_source,
                'reason' => $payload['reason'] ?? null,
                'subtotal_refund' => 0,
                'status' => 'completed',
                'processed_by' => Auth::id(),
            ]);

            $subtotalRefund = 0;

            foreach ($payload['items'] as $itemPayload) {
                $orderItem = $order->items->firstWhere('id', $itemPayload['order_item_id']);

                if (! $orderItem) {
                    throw ValidationException::withMessages(['items' => 'Invalid order item for this order.']);
                }

                $returnable = $orderItem->quantity - $orderItem->returned_quantity;
                if ($itemPayload['quantity'] > $returnable) {
                    throw ValidationException::withMessages([
                        'items' => "Cannot return {$itemPayload['quantity']} of \"{$orderItem->product_name}\" — only {$returnable} left returnable.",
                    ]);
                }

                $lineTotal = $orderItem->price * $itemPayload['quantity'];
                $subtotalRefund += $lineTotal;

                SalesReturnItem::create([
                    'sales_return_id' => $salesReturn->id,
                    'order_item_id' => $orderItem->id,
                    'product_id' => $orderItem->product_id,
                    'product_name' => $orderItem->product_name,
                    'quantity' => $itemPayload['quantity'],
                    'price' => $orderItem->price,
                    'condition' => $itemPayload['condition'],
                    'line_total' => $lineTotal,
                ]);

                $orderItem->increment('returned_quantity', $itemPayload['quantity']);

                if ($itemPayload['condition'] === 'resellable' && $orderItem->product) {
                    $inventory->adjust($orderItem->product, $itemPayload['quantity'], 'return_in', $salesReturn, "Return {$salesReturn->return_number}");
                }
            }

            $salesReturn->update(['subtotal_refund' => $subtotalRefund]);

            if ($payload['refund_method'] === 'wallet') {
                $order->customer->credit($subtotalRefund);
            }

            ReturnRefund::create([
                'sales_return_id' => $salesReturn->id,
                'customer_id' => $order->customer_id,
                'amount' => $subtotalRefund,
                'method' => $payload['refund_method'],
                'status' => 'completed',
                'processed_by' => Auth::id(),
            ]);

            $order->refresh();
            $allReturned = $order->items->every(fn ($item) => $item->returned_quantity >= $item->quantity);
            $order->update(['status' => $allReturned ? 'returned' : 'partially_returned']);

            return $salesReturn;
        });

        return redirect()->route('admin.returns.index')->with('success', "Return {$salesReturn->return_number} processed successfully.");
    }

    /**
     * Voiding a return reverses everything it did: restock deduction, wallet credit,
     * returned_quantity counters, and the order status — keeping the ledger honest.
     */
    public function destroy(SalesReturn $salesReturn, InventoryService $inventory): RedirectResponse
    {
        DB::transaction(function () use ($salesReturn, $inventory) {
            $order = Order::with('items')->findOrFail($salesReturn->order_id);

            foreach ($salesReturn->items()->with('product')->get() as $returnItem) {
                $orderItem = $order->items->firstWhere('id', $returnItem->order_item_id);
                $orderItem?->decrement('returned_quantity', $returnItem->quantity);

                if ($returnItem->condition === 'resellable' && $returnItem->product) {
                    $inventory->adjust($returnItem->product, -$returnItem->quantity, 'adjustment', $salesReturn, "Voided return {$salesReturn->return_number}");
                }
            }

            $refund = $salesReturn->refund;
            if ($refund && $refund->method === 'wallet' && $salesReturn->customer) {
                $salesReturn->customer->debit((float) $refund->amount);
            }

            $order->refresh();
            $allReturned = $order->items->every(fn ($item) => $item->returned_quantity >= $item->quantity);
            $anyReturned = $order->items->contains(fn ($item) => $item->returned_quantity > 0);
            $order->update(['status' => $allReturned ? 'returned' : ($anyReturned ? 'partially_returned' : 'processing')]);

            $salesReturn->delete();
        });

        return redirect()->back()->with('success', 'Return voided and all effects reversed.');
    }
}
