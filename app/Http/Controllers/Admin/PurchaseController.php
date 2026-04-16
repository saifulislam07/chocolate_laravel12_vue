<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Purchases/Index', [
            'purchases' => Purchase::with('supplier')->latest()->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Purchases/Create', [
            'suppliers' => Supplier::all(),
            'products' => Product::with('unit')->where('is_active', true)->get(),
            'reference_no' => 'PUR-' . strtoupper(uniqid())
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'reference_no' => 'required|unique:purchases,reference_no',
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'total_discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $subtotal = collect($request->items)->sum(function($item) {
                return ($item['quantity'] * $item['unit_cost']) - ($item['discount_amount'] ?? 0);
            });

            $tax = (float)($request->tax_amount ?? 0);
            $shipping = (float)($request->shipping_cost ?? 0);
            $global_discount = (float)($request->total_discount ?? 0);
            $total_amount = ($subtotal + $tax + $shipping) - $global_discount;

            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'reference_no' => $request->reference_no,
                'purchase_date' => $request->purchase_date,
                'total_amount' => $total_amount,
                'tax_amount' => $tax,
                'shipping_cost' => $shipping,
                'total_discount' => $global_discount,
                'paid_amount' => $request->paid_amount,
                'due_amount' => $total_amount - $request->paid_amount,
                'status' => 'Received',
                'payment_status' => ($request->paid_amount >= $total_amount) ? 'Paid' : (($request->paid_amount > 0) ? 'Partial' : 'Pending'),
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'subtotal' => ($item['quantity'] * $item['unit_cost']) - ($item['discount_amount'] ?? 0),
                ]);

                // Increment Product Stock
                Product::where('id', $item['product_id'])->increment('stock', $item['quantity']);
                
                // Update Product Cost Price if needed (optional best practice)
                Product::where('id', $item['product_id'])->update(['cost_price' => $item['unit_cost']]);
            }

            DB::commit();
            return redirect()->route('admin.purchases.index')->with('success', 'Purchase recorded and stock updated.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function show(Purchase $purchase)
    {
        return Inertia::render('Admin/Purchases/Show', [
            'purchase' => $purchase->load('supplier', 'items.product.unit')
        ]);
    }

    public function edit(Purchase $purchase)
    {
        return Inertia::render('Admin/Purchases/Edit', [
            'purchase' => $purchase->load('items.product'),
            'suppliers' => Supplier::all(),
            'products' => Product::with('unit')->where('is_active', true)->get(),
        ]);
    }

    public function update(Request $request, Purchase $purchase)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'reference_no' => 'required|unique:purchases,reference_no,' . $purchase->id,
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // 1. Reverse old stock
            foreach ($purchase->items as $oldItem) {
                Product::where('id', $oldItem->product_id)->decrement('stock', $oldItem->quantity);
            }

            // 2. Delete old items
            $purchase->items()->delete();

            // 3. Calculate new totals
            $subtotal = collect($request->items)->sum(function($item) {
                return ($item['quantity'] * $item['unit_cost']) - ($item['discount_amount'] ?? 0);
            });

            $tax = (float)($request->tax_amount ?? 0);
            $shipping = (float)($request->shipping_cost ?? 0);
            $global_discount = (float)($request->total_discount ?? 0);
            $total_amount = ($subtotal + $tax + $shipping) - $global_discount;

            // 4. Update purchase
            $purchase->update([
                'supplier_id' => $request->supplier_id,
                'reference_no' => $request->reference_no,
                'purchase_date' => $request->purchase_date,
                'total_amount' => $total_amount,
                'tax_amount' => $tax,
                'shipping_cost' => $shipping,
                'total_discount' => $global_discount,
                'paid_amount' => $request->paid_amount,
                'due_amount' => $total_amount - $request->paid_amount,
                'payment_status' => ($request->paid_amount >= $total_amount) ? 'Paid' : (($request->paid_amount > 0) ? 'Partial' : 'Pending'),
                'notes' => $request->notes,
            ]);

            // 5. Create new items and update stock
            foreach ($request->items as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'subtotal' => ($item['quantity'] * $item['unit_cost']) - ($item['discount_amount'] ?? 0),
                ]);

                Product::where('id', $item['product_id'])->increment('stock', $item['quantity']);
            }

            DB::commit();
            return redirect()->route('admin.purchases.index')->with('success', 'Purchase updated and stock adjusted.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function destroy(Purchase $purchase)
    {
        try {
            DB::beginTransaction();
            // Reverse stock
            foreach ($purchase->items as $item) {
                Product::where('id', $item['product_id'])->decrement('stock', $item['quantity']);
            }
            $purchase->delete();
            DB::commit();
            return redirect()->back()->with('success', 'Purchase deleted and stock reversed.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Delete failed.');
        }
    }
}
