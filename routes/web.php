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
Route::get('/home-v2', [HomeController::class, 'v2'])->name('home.v2');
Route::get('/shop', [ProductController::class, 'index'])->name('products.index');
Route::get('/search/suggestions', [ProductController::class, 'searchSuggestions'])->name('products.search');
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
Route::post('/newsletter/subscribe', [\App\Http\Controllers\NewsletterController::class, 'store'])->name('newsletter.subscribe');

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

Route::get('/admin/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('admin.dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin Routes (each section gated by that module's "view" permission)
    Route::resource('/admin/sliders', \App\Http\Controllers\Admin\SliderController::class)->names('admin.sliders')->middleware('permission:view_sliders');
    Route::resource('/admin/testimonials', \App\Http\Controllers\Admin\TestimonialController::class)->names('admin.testimonials')->middleware('permission:view_testimonials');

    Route::resource('/admin/suppliers', \App\Http\Controllers\Admin\SupplierController::class)->names('admin.suppliers')->middleware('permission:view_suppliers');
    Route::resource('/admin/categories', \App\Http\Controllers\Admin\CategoryController::class)->names('admin.categories')->middleware('permission:view_categories');
    Route::resource('/admin/products', \App\Http\Controllers\Admin\ProductController::class)->names('admin.products')->middleware('permission:view_products');
    Route::resource('/admin/bundles', \App\Http\Controllers\Admin\BundleController::class)->except(['show'])->names('admin.bundles')->middleware('permission:view_bundles');
    Route::resource('/admin/brands', \App\Http\Controllers\Admin\BrandController::class)->names('admin.brands')->middleware('permission:view_brands');
    Route::resource('/admin/units', \App\Http\Controllers\Admin\UnitController::class)->names('admin.units')->middleware('permission:view_units');
    Route::resource('/admin/purchases', \App\Http\Controllers\Admin\PurchaseController::class)->names('admin.purchases')->middleware('permission:view_purchases');
    Route::resource('/admin/expenses', \App\Http\Controllers\Admin\ExpenseController::class)->names('admin.expenses')->middleware('permission:view_expenses');
    Route::resource('/admin/expense-categories', \App\Http\Controllers\Admin\ExpenseCategoryController::class)->names('admin.expense-categories')->middleware('permission:view_expense_categories');
    Route::resource('/admin/pages', \App\Http\Controllers\Admin\PageController::class)->names('admin.pages')->middleware('permission:view_pages');
    Route::resource('/admin/roles', \App\Http\Controllers\Admin\RoleController::class)->names('admin.roles')->middleware('permission:view_roles');
    Route::resource('/admin/users', \App\Http\Controllers\Admin\UserController::class)->except(['create', 'show', 'edit'])->names('admin.users')->middleware('permission:view_users');
    Route::resource('/admin/menus', \App\Http\Controllers\Admin\MenuController::class)->names('admin.menus')->middleware('permission:view_menus');
    Route::resource('/admin/customers', \App\Http\Controllers\Admin\CustomerController::class)->names('admin.customers')->middleware('permission:view_customers');
    Route::get('/admin/settings', [\App\Http\Controllers\Admin\WebSettingController::class, 'index'])->name('admin.settings.index')->middleware('permission:view_settings');
    Route::post('/admin/settings', [\App\Http\Controllers\Admin\WebSettingController::class, 'update'])->name('admin.settings.update')->middleware('permission:edit_settings');

    // POS Routes
    Route::get('/admin/pos', [\App\Http\Controllers\Admin\POSController::class, 'index'])->name('admin.pos.index')->middleware('permission:access_pos');
    Route::post('/admin/pos', [\App\Http\Controllers\Admin\POSController::class, 'store'])->name('admin.pos.store')->middleware('permission:access_pos');

    // Reports Routes
    Route::group(['prefix' => 'admin/reports', 'as' => 'admin.reports.', 'middleware' => 'permission:view_reports'], function () {
        Route::get('/', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('index');
        Route::get('/stock', [\App\Http\Controllers\Admin\ReportController::class, 'stock'])->name('stock');
        Route::get('/products', [\App\Http\Controllers\Admin\ReportController::class, 'products'])->name('products');
        Route::get('/suppliers', [\App\Http\Controllers\Admin\ReportController::class, 'suppliers'])->name('suppliers');
        Route::get('/profit-loss', [\App\Http\Controllers\Admin\ReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/purchases', [\App\Http\Controllers\Admin\ReportController::class, 'purchases'])->name('purchases');
        Route::get('/expenses', [\App\Http\Controllers\Admin\ReportController::class, 'expenses'])->name('expenses');
        Route::get('/meta-campaigns', [\App\Http\Controllers\Admin\ReportController::class, 'metaCampaigns'])->name('meta-campaigns');
        Route::get('/area-sales', [\App\Http\Controllers\Admin\ReportController::class, 'areaSales'])->name('area-sales');
        Route::get('/area-customers', [\App\Http\Controllers\Admin\ReportController::class, 'areaCustomers'])->name('area-customers');
    });

    // Sales Routes
    Route::resource('/admin/sales', \App\Http\Controllers\Admin\SaleController::class)->names('admin.sales')->middleware('permission:view_sales');
    Route::patch('/admin/sales/{sale}/status', [\App\Http\Controllers\Admin\SaleController::class, 'updateStatus'])
        ->name('admin.sales.update-status')
        ->middleware('permission:edit_sales');
    Route::post('/admin/sales/{sale}/ship', [\App\Http\Controllers\Admin\SaleController::class, 'ship'])
        ->name('admin.sales.ship')
        ->middleware('permission:edit_sales');
    Route::get('/admin/courier/pathao/cities', [\App\Http\Controllers\Admin\SaleController::class, 'pathaoCities'])
        ->name('admin.courier.pathao.cities')
        ->middleware('permission:edit_sales');
    Route::get('/admin/courier/pathao/zones/{cityId}', [\App\Http\Controllers\Admin\SaleController::class, 'pathaoZones'])
        ->name('admin.courier.pathao.zones')
        ->middleware('permission:edit_sales');
    Route::get('/admin/courier/pathao/areas/{zoneId}', [\App\Http\Controllers\Admin\SaleController::class, 'pathaoAreas'])
        ->name('admin.courier.pathao.areas')
        ->middleware('permission:edit_sales');

    // Inventory Routes
    Route::get('/admin/inventory', [\App\Http\Controllers\Admin\StockAdjustmentController::class, 'index'])
        ->name('admin.inventory.index')
        ->middleware('permission:view_inventory');
    Route::post('/admin/inventory', [\App\Http\Controllers\Admin\StockAdjustmentController::class, 'store'])
        ->name('admin.inventory.store')
        ->middleware('permission:create_inventory');

    // Sales Returns & Refunds Routes
    Route::get('/admin/returns', [\App\Http\Controllers\Admin\SalesReturnController::class, 'index'])
        ->name('admin.returns.index')
        ->middleware('permission:view_returns');
    Route::get('/admin/returns/create', [\App\Http\Controllers\Admin\SalesReturnController::class, 'create'])
        ->name('admin.returns.create')
        ->middleware('permission:create_returns');
    Route::post('/admin/returns', [\App\Http\Controllers\Admin\SalesReturnController::class, 'store'])
        ->name('admin.returns.store')
        ->middleware('permission:create_returns');
    Route::delete('/admin/returns/{salesReturn}', [\App\Http\Controllers\Admin\SalesReturnController::class, 'destroy'])
        ->name('admin.returns.destroy')
        ->middleware('permission:delete_returns');
    Route::get('/admin/refunds', [\App\Http\Controllers\Admin\ReturnRefundController::class, 'index'])
        ->name('admin.refunds.index')
        ->middleware('permission:view_returns');
});

require __DIR__.'/auth.php';
