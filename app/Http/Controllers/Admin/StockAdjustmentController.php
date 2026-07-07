<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StockAdjustmentController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Inventory/Index', [
            'products' => Product::select('id', 'name', 'sku', 'stock', 'alert_quantity')->orderBy('name')->get(),
            'movements' => StockMovement::with(['product:id,name', 'creator:id,name'])
                ->latest()
                ->take(50)
                ->get(),
        ]);
    }

    public function store(Request $request, InventoryService $inventory): RedirectResponse
    {
        $payload = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'delta' => ['required', 'integer', 'not_in:0'],
            'note' => ['required', 'string', 'max:255'],
        ]);

        $product = Product::findOrFail($payload['product_id']);

        try {
            $inventory->adjust($product, (int) $payload['delta'], 'adjustment', null, $payload['note']);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Stock adjusted successfully.');
    }
}
