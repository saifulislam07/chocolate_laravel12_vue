<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless(
            $request->user()->hasAnyRole(['Super Admin', 'Administrator', 'Staff'])
                || $request->user()->can('manage_dashboard'),
            403
        );

        $today = now()->startOfDay();

        // Every sales figure below is money received, not money ordered: only
        // orders whose payment has reached the shop count, dated by delivery,
        // and the shipping charge is left out because it goes to the courier.
        $salesByDay = Order::revenueEarning()
            ->selectRaw('DATE(delivered_at) as date, SUM(' . Order::NET_REVENUE_SQL . ') as total')
            ->where('delivered_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $last7Days = collect(range(6, 0))->map(function (int $daysAgo) use ($salesByDay) {
            $date = now()->subDays($daysAgo)->format('Y-m-d');

            return [
                'date' => $date,
                'label' => now()->subDays($daysAgo)->format('D'),
                'total' => (float) ($salesByDay[$date]->total ?? 0),
            ];
        });

        $ordersByStatus = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_sales' => (float) Order::revenueEarning()->sum(DB::raw(Order::NET_REVENUE_SQL)),
                'orders_count' => Order::count(),
                'delivered_orders_count' => Order::revenueEarning()->count(),
                'customers_count' => Customer::count(),
                'products_count' => Product::count(),
                'expenses_total' => (float) Expense::sum('amount'),
                'low_stock_count' => Product::whereRaw('stock <= alert_quantity')->count(),
                'today_sales' => (float) Order::revenueEarning()
                    ->where('delivered_at', '>=', $today)
                    ->sum(DB::raw(Order::NET_REVENUE_SQL)),
                'today_orders_count' => Order::where('created_at', '>=', $today)->count(),
                'today_customers_count' => Customer::where('created_at', '>=', $today)->count(),
            ],
            'recent_orders' => Order::with('user')->latest()->take(5)->get(),
            'top_products' => OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(price * quantity) as total_revenue'))
                ->with('product')
                ->groupBy('product_id')
                ->orderByDesc('total_sold')
                ->take(5)
                ->get(),
            'recent_expenses' => Expense::with('category')->latest()->take(5)->get(),
            'salesTrend' => $last7Days,
            'ordersByStatus' => $ordersByStatus,
        ]);
    }
}
