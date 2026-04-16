<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SaleController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Sales/Index', [
            'sales' => Order::with(['customer', 'user'])->latest()->get()
        ]);
    }

    public function show($id)
    {
        $sale = Order::with(['customer', 'user', 'items.product'])->findOrFail($id);
        return Inertia::render('Admin/Sales/Show', [
            'sale' => $sale
        ]);
    }

    public function destroy($id)
    {
        $sale = Order::findOrFail($id);
        $sale->delete();
        return redirect()->route('admin.sales.index')->with('success', 'Sale record deleted successfully.');
    }
}
