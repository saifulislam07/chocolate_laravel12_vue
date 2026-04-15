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

        $cart = \App\Models\Cart::where('user_id', $user?->id)
            ->orWhere('session_id', $sessionId)
            ->first();

        if ($cart) {
            $cartCount = \App\Models\CartItem::where('cart_id', $cart->id)->sum('quantity');
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
            ],
            'cartCount' => (int) $cartCount,
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }
}
