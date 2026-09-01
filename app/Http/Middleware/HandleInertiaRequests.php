<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $cartCount = 0;
        $sessionId = $request->session()->getId();
        $user = $request->user();

        $cart = $user
            ? \App\Models\Cart::where('user_id', $user->id)->first()
            : \App\Models\Cart::where('session_id', $sessionId)->first();

        $cartItems = collect();

        if ($cart) {
            $cart->load('items.product.images');

            $cartItems = $cart->items
                ->filter(fn ($item) => $item->product !== null)
                ->map(function ($item): array {
                    $price = (float) $item->product->price;
                    $quantity = (int) $item->quantity;

                    return [
                        'id' => $item->id,
                        'product_id' => $item->product->id,
                        'slug' => $item->product->slug,
                        'name' => $item->product->name,
                        'price' => $price,
                        'quantity' => $quantity,
                        'line_total' => $price * $quantity,
                        'image' => $item->product->images->first()?->image_path,
                    ];
                })
                ->values();

            $cartCount = $cartItems->sum('quantity');
        }

        $wishlistCount = $user
            ? \App\Models\Wishlist::where('user_id', $user->id)->count()
            : 0;

        $mainMenu = $this->mainMenu();

        if ($mainMenu->isEmpty()) {
            $mainMenu = collect([
                ['id' => 'home', 'name' => 'Home', 'url' => '/', 'children' => []],
                ['id' => 'shop', 'name' => 'Shop', 'url' => '/shop', 'children' => []],
                ['id' => 'wishlist', 'name' => 'Wishlist', 'url' => '/wishlist', 'children' => []],
            ]);
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'roles' => $user?->getRoleNames() ?? [],
                'permissions' => $user?->getAllPermissions()->pluck('name') ?? [],
            ],
            'cartCount' => (int) $cartCount,
            'cartItems' => $cartItems->all(),
            'cartSubtotal' => (float) $cartItems->sum('line_total'),
            'wishlistCount' => (int) $wishlistCount,
            'mainMenu' => $mainMenu,
            // Published CMS pages, listed in the footer.
            'footerPages' => \App\Models\Page::where('is_active', true)
                ->orderBy('title')
                ->get(['id', 'title', 'slug']),
            'webSettings' => \App\Models\WebSetting::first(),
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'invoice' => $request->session()->get('invoice'),
            ],
        ];
    }

    /**
     * The storefront navigation. A main menu item flagged with show_categories
     * takes its dropdown straight from the product categories instead of its own
     * child rows, so the menu follows the catalogue without any manual upkeep.
     */
    protected function mainMenu(): \Illuminate\Support\Collection
    {
        $categoryLinks = null;

        return \App\Models\Menu::with(['children' => function ($query) {
                $query->where('is_active', true)->orderBy('order');
            }])
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('order')
            ->get()
            ->map(function (\App\Models\Menu $item) use (&$categoryLinks) {
                $children = $item->children->map(fn (\App\Models\Menu $child) => [
                    'id' => $child->id,
                    'name' => $child->name,
                    'url' => $child->url,
                ]);

                // The flag hands the whole dropdown over to the catalogue, so any
                // child rows still attached to this item are deliberately ignored.
                if ($item->show_categories) {
                    $children = $categoryLinks ??= $this->categoryLinks();
                }

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'url' => $item->url,
                    'children' => $children->values()->all(),
                ];
            });
    }

    /**
     * Active categories that actually have something to show.
     */
    protected function categoryLinks(): \Illuminate\Support\Collection
    {
        return \App\Models\Category::query()
            ->where('is_active', true)
            ->whereHas('products', fn ($query) => $query->where('is_active', true))
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (\App\Models\Category $category) => [
                // Prefixed so it never collides with a menu row id in Vue's :key.
                'id' => 'category-' . $category->id,
                'name' => $category->name,
                'url' => '/categories/' . $category->slug,
            ]);
    }
}
