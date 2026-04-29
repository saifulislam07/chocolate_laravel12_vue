<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/shop', [ProductController::class, 'index'])->name('products.index');
Route::get('/categories/{category:slug}', [ProductController::class, 'category'])->name('categories.show');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/items', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/items/{cartItem}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/p/{slug}', [\App\Http\Controllers\PublicPageController::class, 'show'])->name('page.public');
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/payment/bkash/callback', [CheckoutController::class, 'bkashCallback'])->name('payment.bkash.callback');

Route::middleware('auth')->group(function () {
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
});

Route::get('/dashboard', fn () => redirect()->route('customer.dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/customer/dashboard', function () {
    $user = request()->user();

    return Inertia::render('Customer/Dashboard', [
        'stats' => [
            'orders_count' => \App\Models\Order::where('user_id', $user->id)->count(),
            'wishlist_count' => \App\Models\Wishlist::where('user_id', $user->id)->count(),
            'cart_count' => \App\Models\Cart::where('user_id', $user->id)->withCount('items')->first()?->items_count ?? 0,
            'total_spent' => (float) \App\Models\Order::where('user_id', $user->id)->sum('total'),
        ],
        'recentOrders' => \App\Models\Order::with('items')
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get(),
        'wishlistItems' => \App\Models\Wishlist::with('product.images')
            ->where('user_id', $user->id)
            ->latest()
            ->take(4)
            ->get(),
    ]);
})->middleware(['auth', 'verified'])->name('customer.dashboard');

Route::get('/admin/dashboar', fn () => redirect()->route('admin.dashboard'))
    ->middleware(['auth', 'verified']);

Route::get('/admin/dashboard', function () {
    abort_unless(request()->user()->hasAnyRole(['Super Admin', 'Administrator', 'Staff']), 403);

    return Inertia::render('Dashboard', [
        'stats' => [
            'total_sales' => \App\Models\Order::sum('total'),
            'orders_count' => \App\Models\Order::count(),
            'customers_count' => \App\Models\Customer::count(),
            'products_count' => \App\Models\Product::count(),
            'expenses_total' => \App\Models\Expense::sum('amount'),
            'low_stock_count' => \App\Models\Product::whereRaw('stock <= alert_quantity')->count(),
        ],
        'recent_orders' => \App\Models\Order::with('user')->latest()->take(5)->get(),
        'top_products' => \App\Models\OrderItem::select('product_id', \DB::raw('SUM(quantity) as total_sold'), \DB::raw('SUM(price * quantity) as total_revenue'))
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get(),
        'recent_expenses' => \App\Models\Expense::with('category')->latest()->take(5)->get(),
    ]);
})->middleware(['auth', 'verified'])->name('admin.dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin Routes
    Route::resource('/admin/sliders', \App\Http\Controllers\Admin\SliderController::class)->names('admin.sliders');

    Route::resource('/admin/suppliers', \App\Http\Controllers\Admin\SupplierController::class)->names('admin.suppliers');
    Route::resource('/admin/categories', \App\Http\Controllers\Admin\CategoryController::class)->names('admin.categories');
    Route::resource('/admin/products', \App\Http\Controllers\Admin\ProductController::class)->names('admin.products');
    Route::resource('/admin/brands', \App\Http\Controllers\Admin\BrandController::class)->names('admin.brands');
    Route::resource('/admin/units', \App\Http\Controllers\Admin\UnitController::class)->names('admin.units');
    Route::resource('/admin/purchases', \App\Http\Controllers\Admin\PurchaseController::class)->names('admin.purchases');
    Route::resource('/admin/expenses', \App\Http\Controllers\Admin\ExpenseController::class)->names('admin.expenses');
    Route::resource('/admin/expense-categories', \App\Http\Controllers\Admin\ExpenseCategoryController::class)->names('admin.expense-categories');
    Route::resource('/admin/pages', \App\Http\Controllers\Admin\PageController::class)->names('admin.pages');
    Route::resource('/admin/roles', \App\Http\Controllers\Admin\RoleController::class)->names('admin.roles');
    Route::resource('/admin/menus', \App\Http\Controllers\Admin\MenuController::class)->names('admin.menus');
    Route::resource('/admin/customers', \App\Http\Controllers\Admin\CustomerController::class)->names('admin.customers');
    Route::get('/admin/settings', [\App\Http\Controllers\Admin\WebSettingController::class, 'index'])->name('admin.settings.index');
    Route::post('/admin/settings', [\App\Http\Controllers\Admin\WebSettingController::class, 'update'])->name('admin.settings.update');

    // POS Routes
    Route::get('/admin/pos', [\App\Http\Controllers\Admin\POSController::class, 'index'])->name('admin.pos.index');
    Route::post('/admin/pos', [\App\Http\Controllers\Admin\POSController::class, 'store'])->name('admin.pos.store');

    // Reports Routes
    Route::group(['prefix' => 'admin/reports', 'as' => 'admin.reports.'], function () {
        Route::get('/', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('index');
        Route::get('/stock', [\App\Http\Controllers\Admin\ReportController::class, 'stock'])->name('stock');
        Route::get('/products', [\App\Http\Controllers\Admin\ReportController::class, 'products'])->name('products');
        Route::get('/suppliers', [\App\Http\Controllers\Admin\ReportController::class, 'suppliers'])->name('suppliers');
        Route::get('/profit-loss', [\App\Http\Controllers\Admin\ReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/purchases', [\App\Http\Controllers\Admin\ReportController::class, 'purchases'])->name('purchases');
        Route::get('/expenses', [\App\Http\Controllers\Admin\ReportController::class, 'expenses'])->name('expenses');
    });

    // Sales Routes
    Route::resource('/admin/sales', \App\Http\Controllers\Admin\SaleController::class)->names('admin.sales');
});

require __DIR__.'/auth.php';
