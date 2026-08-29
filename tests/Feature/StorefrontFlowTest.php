<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\District;
use App\Models\Division;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\WebSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the storefront money path: bag -> checkout -> order -> stock.
 *
 * Laravel's test client starts a fresh session per request, so any flow that
 * spans more than one request signs a user in — a user cart is keyed to the
 * account and is therefore stable across requests.
 */
class StorefrontFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(array $overrides = []): Product
    {
        $category = Category::create([
            'name' => 'Truffles ' . uniqid(),
            'slug' => 'truffles-' . uniqid(),
            'is_active' => true,
        ]);

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'Test Bar',
            'slug' => 'test-bar-' . uniqid(),
            'description' => 'Yum',
            'price' => 100,
            'cost_price' => 40,
            'sku' => 'SKU-' . strtoupper(uniqid()),
            'stock' => 10,
            'is_active' => true,
        ], $overrides));
    }

    /** @return array{0: Division, 1: District} */
    private function makeArea(?float $charge = 80): array
    {
        $division = Division::create(['name' => 'Dhaka']);
        $district = District::create([
            'division_id' => $division->id,
            'name' => 'Gazipur',
            'shipping_charge' => $charge,
        ]);

        return [$division, $district];
    }

    public function test_public_pages_render(): void
    {
        $this->makeProduct();

        $this->get('/')->assertOk();
        $this->get('/shop')->assertOk();
        $this->get('/cart')->assertOk();
    }

    public function test_product_detail_page_renders(): void
    {
        $product = $this->makeProduct();
        $this->get('/products/' . $product->slug)->assertOk();
    }

    public function test_inactive_product_is_hidden(): void
    {
        $product = $this->makeProduct(['is_active' => false]);
        $this->get('/products/' . $product->slug)->assertNotFound();
    }

    public function test_shopper_can_fill_a_bag_and_place_an_order(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct(['stock' => 10]);
        [$division, $district] = $this->makeArea(80);

        $this->actingAs($user)
            ->post('/cart/items', ['product_id' => $product->id, 'quantity' => 2])
            ->assertRedirect();

        $this->actingAs($user)->get('/checkout')->assertOk();

        $this->actingAs($user)->post('/checkout', [
            'full_name' => 'Jane Doe',
            'phone' => '01700000000',
            'address' => '12 Test Road',
            'division_id' => $division->id,
            'district_id' => $district->id,
            'payment_method' => 'cod',
        ])->assertRedirect();

        $order = Order::sole();
        // 2 x 100 subtotal, 80 district shipping, 5% tax.
        $this->assertEquals(200, (float) $order->subtotal);
        $this->assertEquals(80, (float) $order->shipping_cost);
        $this->assertEquals(10, (float) $order->tax);
        $this->assertEquals(290, (float) $order->total);

        $this->assertSame(8, $product->fresh()->stock, 'stock should be drawn down by the order');
        $this->assertSame(0, CartItem::count(), 'the bag should be emptied once the order is placed');
    }

    /**
     * The bag itself refuses to hold more than there is in stock, so the guard
     * at checkout exists for the gap after that: stock that sold out, or was
     * adjusted down, while the item was already sitting in someone's bag.
     */
    public function test_checkout_refuses_to_oversell(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct(['stock' => 5]);
        [$division, $district] = $this->makeArea();

        $this->actingAs($user)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 5]);

        // Someone else clears the shelf before this shopper reaches payment.
        $product->update(['stock' => 1]);

        $this->actingAs($user)->post('/checkout', [
            'phone' => '01700000000',
            'address' => '12 Test Road',
            'division_id' => $division->id,
            'district_id' => $district->id,
            'payment_method' => 'cod',
        ])->assertSessionHasErrors('items');

        $this->assertSame(0, Order::count());
        $this->assertSame(1, $product->fresh()->stock);
    }

    public function test_free_shipping_threshold_waives_the_delivery_charge(): void
    {
        WebSetting::create([
            'site_name' => 'Coco',
            'free_shipping_threshold' => 150,
            'default_shipping_charge' => 120,
        ]);

        $user = User::factory()->create();
        $product = $this->makeProduct(['stock' => 10]);
        [$division, $district] = $this->makeArea(80);

        $this->actingAs($user)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 2]);
        $this->actingAs($user)->post('/checkout', [
            'phone' => '01700000000',
            'address' => '12 Test Road',
            'division_id' => $division->id,
            'district_id' => $district->id,
            'payment_method' => 'cod',
        ]);

        $this->assertEquals(0, (float) Order::sole()->shipping_cost);
    }

    public function test_district_without_its_own_rate_falls_back_to_the_default_charge(): void
    {
        WebSetting::create(['site_name' => 'Coco', 'default_shipping_charge' => 120]);

        $user = User::factory()->create();
        $product = $this->makeProduct(['stock' => 10]);
        [$division, $district] = $this->makeArea(null);

        $this->actingAs($user)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 1]);
        $this->actingAs($user)->post('/checkout', [
            'phone' => '01700000000',
            'address' => '12 Test Road',
            'division_id' => $division->id,
            'district_id' => $district->id,
            'payment_method' => 'cod',
        ]);

        $this->assertEquals(120, (float) Order::sole()->shipping_cost);
    }

    public function test_a_bag_item_cannot_be_touched_by_someone_else(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $product = $this->makeProduct();

        $this->actingAs($owner)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 1]);
        $item = CartItem::sole();

        $this->actingAs($stranger)->patch('/cart/items/' . $item->id, ['quantity' => 5])->assertForbidden();
        $this->actingAs($stranger)->delete('/cart/items/' . $item->id)->assertForbidden();

        $this->assertSame(1, $item->fresh()->quantity);
    }

    public function test_merely_viewing_the_bag_does_not_create_a_cart_row(): void
    {
        $this->get('/cart')->assertOk();
        $this->get('/checkout')->assertRedirect(route('cart.index'));

        $this->assertSame(0, Cart::count(), 'read-only pages should not litter the carts table');
    }

    public function test_admin_dashboard_is_blocked_for_plain_customers(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/dashboard')->assertForbidden();
    }
}
