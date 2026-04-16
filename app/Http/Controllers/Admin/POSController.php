<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class POSController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/POS/Index', [
            'products' => Product::with(['category', 'brand', 'unit', 'images'])
                ->where('is_active', true)
                ->latest()
                ->get(),
            'customers' => Customer::latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
            'subtotal' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'tax' => 'nullable|numeric',
            'shipping_cost' => 'nullable|numeric',
            'total' => 'required|numeric',
            'paid_amount' => 'required|numeric',
            'due_amount' => 'required|numeric',
            'payment_method' => 'required|string',
        ]);

        $order = \App\Models\Order::create([
            'order_number' => 'POS-' . strtoupper(uniqid()),
            'customer_id' => $request->customer_id,
            'status' => 'completed',
            'subtotal' => $request->subtotal,
            'discount' => $request->discount ?? 0,
            'tax' => $request->tax ?? 0,
            'shipping_cost' => $request->shipping_cost ?? 0,
            'total' => $request->total,
            'paid_amount' => $request->paid_amount,
            'due_amount' => $request->due_amount,
            'payment_method' => $request->payment_method,
            'payment_status' => $request->due_amount > 0 ? ($request->paid_amount > 0 ? 'partial' : 'unpaid') : 'paid',
            'order_source' => 'pos',
        ]);

        foreach ($request->items as $item) {
            $order->items()->create([
                'product_id' => $item['id'],
                'product_name' => $item['name'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);

            // Deduct stock
            $product = \App\Models\Product::find($item['id']);
            if ($product) {
                $product->decrement('stock', $item['quantity']);
            }
        }

        // Return invoice data for printing
        $customer = $order->customer;
        return redirect()->back()->with('invoice', [
            'invoice_no' => $order->order_number,
            'customer_name' => $customer ? $customer->name : null,
            'customer_phone' => $customer ? $customer->phone : null,
            'payment_method' => $order->payment_method,
            'items' => $request->items,
            'subtotal' => $order->subtotal,
            'discount' => $order->discount,
            'tax' => $order->tax,
            'shipping_cost' => $order->shipping_cost,
            'total' => $order->total,
            'paid_amount' => $order->paid_amount,
            'due_amount' => $order->due_amount,
        ]);
    }
}
