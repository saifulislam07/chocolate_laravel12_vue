<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Order;
use App\Models\Expense;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Reports/Index', [
            'summary' => [
                'total_products' => Product::count(),
                'total_stock' => Product::sum('stock'),
                'stock_value' => Product::sum(DB::raw('stock * cost_price')),
                'total_purchases' => Purchase::sum('total_amount'),
                'total_sales' => Order::sum('total'),
                'total_expenses' => Expense::sum('amount'),
            ]
        ]);
    }

    public function stock()
    {
        return Inertia::render('Admin/Reports/StockReport', [
            'products' => Product::with('unit', 'category', 'brand')->get()
        ]);
    }

    public function products()
    {
        return Inertia::render('Admin/Reports/ProductReport', [
            'products' => Product::withCount(['purchaseItems as total_purchased' => function($query) {
                $query->select(DB::raw('sum(quantity)'));
            }])->get()
        ]);
    }

    public function suppliers()
    {
        return Inertia::render('Admin/Reports/SupplierReport', [
            'suppliers' => Supplier::withCount('purchases')
                ->withSum('purchases', 'total_amount')
                ->withSum('purchases', 'paid_amount')
                ->withSum('purchases', 'due_amount')
                ->get()
        ]);
    }

    public function profitLoss(Request $request)
    {
        $start_date = $request->start_date ?? now()->startOfMonth()->toDateString();
        $end_date = $request->end_date ?? now()->toDateString();

        $sales = Order::whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59'])->sum('total');
        $purchases = Purchase::whereBetween('purchase_date', [$start_date, $end_date])->sum('total_amount');
        $expenses = Expense::whereBetween('expense_date', [$start_date, $end_date])->sum('amount');

        return Inertia::render('Admin/Reports/ProfitLoss', [
            'stats' => [
                'sales' => (float)$sales,
                'purchases' => (float)$purchases,
                'expenses' => (float)$expenses,
                'net_profit' => (float)($sales - ($purchases + $expenses)),
                'start_date' => $start_date,
                'end_date' => $end_date,
            ]
        ]);
    }

    public function purchases(Request $request)
    {
        $query = Purchase::with('supplier');
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('purchase_date', [$request->start_date, $request->end_date]);
        }
        return Inertia::render('Admin/Reports/PurchaseReport', [
            'purchases' => $query->latest()->get()
        ]);
    }

    public function expenses(Request $request)
    {
        $query = Expense::with('category');
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('expense_date', [$request->start_date, $request->end_date]);
        }
        return Inertia::render('Admin/Reports/ExpenseReport', [
            'expenses' => $query->latest()->get()
        ]);
    }
}
