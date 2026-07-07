<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Order;
use App\Models\Customer;
use App\Models\District;
use App\Models\Expense;
use App\Models\ReturnRefund;
use App\Models\SalesReturn;
use App\Models\Supplier;
use App\Models\WebSetting;
use App\Services\MetaAdsReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $totalSales = (float) Order::sum('total');
        $totalReturns = (float) SalesReturn::sum('subtotal_refund');
        $totalRefunds = (float) ReturnRefund::sum('amount');

        return Inertia::render('Admin/Reports/Index', [
            'summary' => [
                'total_products' => Product::count(),
                'total_stock' => Product::sum('stock'),
                'stock_value' => Product::sum(DB::raw('stock * cost_price')),
                'total_purchases' => Purchase::sum('total_amount'),
                'total_sales' => $totalSales,
                'total_returns' => $totalReturns,
                'net_sales' => $totalSales - $totalReturns,
                'total_refunds' => $totalRefunds,
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
        $refunds = ReturnRefund::whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59'])->sum('amount');

        return Inertia::render('Admin/Reports/ProfitLoss', [
            'stats' => [
                'sales' => (float) $sales,
                'purchases' => (float) $purchases,
                'expenses' => (float) $expenses,
                'refunds' => (float) $refunds,
                'net_profit' => (float) ($sales - $refunds - ($purchases + $expenses)),
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

    public function areaSales()
    {
        $webSales = Order::select('district_id', DB::raw('COUNT(*) as orders_count'), DB::raw('SUM(total) as total_sales'))
            ->whereNotNull('district_id')
            ->groupBy('district_id')
            ->get()
            ->keyBy('district_id');

        $posSales = Order::query()
            ->join('customers', 'customers.id', '=', 'orders.customer_id')
            ->whereNotNull('customers.district_id')
            ->select('customers.district_id as district_id', DB::raw('COUNT(*) as orders_count'), DB::raw('SUM(orders.total) as total_sales'))
            ->groupBy('customers.district_id')
            ->get()
            ->keyBy('district_id');

        $districtIds = $webSales->keys()->merge($posSales->keys())->unique();
        $districts = District::with('division')->whereIn('id', $districtIds)->get()->keyBy('id');

        $areas = $districtIds->map(function ($id) use ($webSales, $posSales, $districts) {
            $district = $districts->get($id);

            return [
                'district_id' => $id,
                'district_name' => $district?->name,
                'division_name' => $district?->division?->name,
                'orders_count' => ($webSales[$id]->orders_count ?? 0) + ($posSales[$id]->orders_count ?? 0),
                'total_sales' => (float) (($webSales[$id]->total_sales ?? 0) + ($posSales[$id]->total_sales ?? 0)),
            ];
        })->sortByDesc('total_sales')->values();

        return Inertia::render('Admin/Reports/AreaSalesReport', ['areas' => $areas]);
    }

    public function areaCustomers()
    {
        $posCustomers = Customer::select('district_id', DB::raw('COUNT(*) as customers_count'))
            ->whereNotNull('district_id')
            ->groupBy('district_id')
            ->get()
            ->keyBy('district_id');

        $webCustomers = Order::select('district_id', DB::raw('COUNT(DISTINCT user_id) as customers_count'))
            ->whereNotNull('district_id')
            ->whereNotNull('user_id')
            ->groupBy('district_id')
            ->get()
            ->keyBy('district_id');

        $districtIds = $posCustomers->keys()->merge($webCustomers->keys())->unique();
        $districts = District::with('division')->whereIn('id', $districtIds)->get()->keyBy('id');

        $areas = $districtIds->map(function ($id) use ($posCustomers, $webCustomers, $districts) {
            $district = $districts->get($id);

            return [
                'district_id' => $id,
                'district_name' => $district?->name,
                'division_name' => $district?->division?->name,
                'customers_count' => ($posCustomers[$id]->customers_count ?? 0) + ($webCustomers[$id]->customers_count ?? 0),
            ];
        })->sortByDesc('customers_count')->values();

        return Inertia::render('Admin/Reports/AreaCustomerReport', ['areas' => $areas]);
    }

    public function metaCampaigns(Request $request, MetaAdsReportService $metaAdsReportService)
    {
        $datePreset = $request->query('date_preset', 'last_7d');

        return Inertia::render('Admin/Reports/MetaCampaigns', [
            'datePreset' => $datePreset,
            'report' => $metaAdsReportService->report(WebSetting::first(), $datePreset),
        ]);
    }
}
