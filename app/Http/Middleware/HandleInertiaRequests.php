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

        if ($cart) {
            $cartCount = \App\Models\CartItem::where('cart_id', $cart->id)->sum('quantity');
        }

        $wishlistCount = $user
            ? \App\Models\Wishlist::where('user_id', $user->id)->count()
            : 0;

        $mainMenu = \App\Models\Menu::with('children')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

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
            ],
            'cartCount' => (int) $cartCount,
            'wishlistCount' => (int) $wishlistCount,
            'mainMenu' => $mainMenu,
            'webSettings' => \App\Models\WebSetting::first(),
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'invoice' => $request->session()->get('invoice'),
            ],
        ];
    }
}
