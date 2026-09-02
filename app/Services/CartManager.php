<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for "which bag belongs to this visitor".
 *
 * A bag is keyed to the user account once the shopper signs in, and to their
 * session id while they are still browsing as a guest.
 */
class CartManager
{
    /**
     * Most of any one product a shopper may hold in their bag at a time.
     * Lives here because the bag page, the checkout page and the add-to-bag
     * endpoint all have to agree on the same ceiling.
     */
    public const MAX_PER_ITEM = 20;

    /**
     * The visitor's bag, created if they don't have one yet. Only call this
     * when the visitor is actually putting something in it.
     */
    public function resolve(Request $request): Cart
    {
        return Cart::firstOrCreate($this->ownerKey($request));
    }

    /**
     * The visitor's bag, or null when they have never filled one. Never writes,
     * so merely viewing the bag or checkout page doesn't leave a row behind for
     * every passing crawler.
     */
    public function find(Request $request): ?Cart
    {
        return Cart::where($this->ownerKey($request))->first();
    }

    /**
     * Move a guest bag onto the account being signed in to.
     *
     * Signing in regenerates the session id, so a session-keyed bag becomes
     * unreachable from that moment on. Without this the shopper's bag empties
     * itself at exactly the point they sign in to pay for it.
     */
    public function mergeGuestCartInto(User $user, string $guestSessionId): void
    {
        $guestCart = Cart::with('items')->where('session_id', $guestSessionId)->first();

        if (! $guestCart) {
            return;
        }

        if ($guestCart->items->isEmpty()) {
            $guestCart->delete();

            return;
        }

        DB::transaction(function () use ($user, $guestCart): void {
            $userCart = Cart::firstOrCreate(['user_id' => $user->id]);

            foreach ($guestCart->items as $item) {
                $existing = $userCart->items()
                    ->where('product_id', $item->product_id)
                    ->where('product_variant_id', $item->product_variant_id)
                    ->first();

                if ($existing) {
                    $existing->increment('quantity', $item->quantity);

                    continue;
                }

                $item->update(['cart_id' => $userCart->id]);
            }

            // Anything left still pointing at the guest bag was merged by
            // quantity above, and goes with it on delete.
            $guestCart->delete();
        });
    }

    /**
     * @return array{user_id: int}|array{session_id: string}
     */
    private function ownerKey(Request $request): array
    {
        $user = $request->user();

        return $user
            ? ['user_id' => $user->id]
            : ['session_id' => $request->session()->getId()];
    }
}
