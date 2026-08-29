<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\CartManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

/**
 * Signing in regenerates the session id, which strands a session-keyed bag.
 * These cover the hand-off that moves it onto the account instead.
 */
class GuestCartMergeTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(string $suffix = 'a'): Product
    {
        $category = Category::firstOrCreate(
            ['slug' => 'c'],
            ['name' => 'C', 'is_active' => true]
        );

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Bar ' . $suffix,
            'slug' => 'bar-' . $suffix,
            'price' => 100,
            'sku' => 'SKU-' . strtoupper($suffix),
            'stock' => 20,
            'is_active' => true,
        ]);
    }

    public function test_a_guest_bag_moves_onto_the_account_at_sign_in(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct('a');

        $guestCart = Cart::create(['session_id' => 'guest-session-id']);
        $guestCart->items()->create(['product_id' => $product->id, 'quantity' => 3]);

        app(CartManager::class)->mergeGuestCartInto($user, 'guest-session-id');

        $userCart = Cart::where('user_id', $user->id)->sole();
        $this->assertSame(3, (int) $userCart->items()->sole()->quantity);
        $this->assertNull(Cart::find($guestCart->id), 'the guest bag should be cleaned up');
    }

    public function test_quantities_are_combined_when_both_bags_hold_the_same_product(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct('a');

        $userCart = Cart::create(['user_id' => $user->id]);
        $userCart->items()->create(['product_id' => $product->id, 'quantity' => 2]);

        $guestCart = Cart::create(['session_id' => 'guest-session-id']);
        $guestCart->items()->create(['product_id' => $product->id, 'quantity' => 3]);

        app(CartManager::class)->mergeGuestCartInto($user, 'guest-session-id');

        $this->assertSame(1, CartItem::count(), 'the same product should not end up on two lines');
        $this->assertSame(5, (int) $userCart->items()->sole()->quantity);
    }

    public function test_distinct_products_from_both_bags_are_kept(): void
    {
        $user = User::factory()->create();
        $inBag = $this->makeProduct('a');
        $asGuest = $this->makeProduct('b');

        $userCart = Cart::create(['user_id' => $user->id]);
        $userCart->items()->create(['product_id' => $inBag->id, 'quantity' => 1]);

        $guestCart = Cart::create(['session_id' => 'guest-session-id']);
        $guestCart->items()->create(['product_id' => $asGuest->id, 'quantity' => 4]);

        app(CartManager::class)->mergeGuestCartInto($user, 'guest-session-id');

        $this->assertSame(2, $userCart->items()->count());
        $this->assertSame(4, (int) $userCart->items()->where('product_id', $asGuest->id)->sole()->quantity);
    }

    public function test_an_empty_guest_bag_is_simply_discarded(): void
    {
        $user = User::factory()->create();
        Cart::create(['session_id' => 'guest-session-id']);

        app(CartManager::class)->mergeGuestCartInto($user, 'guest-session-id');

        $this->assertSame(0, Cart::count());
    }

    public function test_signing_in_with_no_guest_bag_is_a_no_op(): void
    {
        $user = User::factory()->create();

        app(CartManager::class)->mergeGuestCartInto($user, 'never-seen-session-id');

        $this->assertSame(0, Cart::count());
    }

    /**
     * The test client issues each request with a brand new session, so the
     * hand-off cannot be observed end to end here. What these two pin down is
     * that the routes ask for it at all, against the pre-sign-in session id.
     */
    public function test_the_login_route_hands_the_guest_bag_over(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $carts = Mockery::mock(CartManager::class);
        $carts->shouldReceive('mergeGuestCartInto')
            ->once()
            ->with(Mockery::on(fn (User $signedIn): bool => $signedIn->is($user)), Mockery::type('string'));
        $this->instance(CartManager::class, $carts);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect();
    }

    public function test_the_register_route_hands_the_guest_bag_over(): void
    {
        $carts = Mockery::mock(CartManager::class);
        $carts->shouldReceive('mergeGuestCartInto')
            ->once()
            ->with(Mockery::type(User::class), Mockery::type('string'));
        $this->instance(CartManager::class, $carts);

        $this->post('/register', [
            'name' => 'New Shopper',
            'email' => 'shopper@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect();
    }
}
