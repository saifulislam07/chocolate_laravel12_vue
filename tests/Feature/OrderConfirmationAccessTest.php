<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The confirmation page shows the buyer's name, phone, items and totals, so it
 * must not be readable just by knowing (or guessing) an order id.
 */
class OrderConfirmationAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $overrides = []): Order
    {
        $order = Order::create(array_merge([
            'order_number' => 'CHOC-TEST-' . strtoupper(uniqid()),
            'status' => 'pending',
            'subtotal' => 200,
            'discount' => 0,
            'tax' => 10,
            'shipping_cost' => 80,
            'total' => 290,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'customer_name' => 'Jane Doe',
            'customer_phone' => '01700000000',
        ], $overrides));

        $order->items()->create([
            'product_name' => 'Test Bar',
            'price' => 100,
            'quantity' => 2,
        ]);

        return $order;
    }

    public function test_a_stranger_cannot_read_someone_elses_confirmation(): void
    {
        $order = $this->makeOrder();

        $this->get('/checkout/success/' . $order->id)->assertNotFound();
    }

    public function test_a_signed_in_shopper_cannot_read_another_accounts_order(): void
    {
        $order = $this->makeOrder(['user_id' => User::factory()->create()->id]);

        $this->actingAs(User::factory()->create())
            ->get('/checkout/success/' . $order->id)
            ->assertNotFound();
    }

    public function test_the_account_that_owns_the_order_can_read_it(): void
    {
        $buyer = User::factory()->create();
        $order = $this->makeOrder(['user_id' => $buyer->id]);

        $this->actingAs($buyer)->get('/checkout/success/' . $order->id)->assertOk();
    }

    public function test_the_guest_session_that_placed_the_order_can_read_it(): void
    {
        $order = $this->makeOrder();

        $this->withSession(['placed_order_ids' => [$order->id]])
            ->get('/checkout/success/' . $order->id)
            ->assertOk();
    }

    public function test_placing_an_order_records_it_on_the_session(): void
    {
        $user = User::factory()->create();

        $category = \App\Models\Category::create(['name' => 'C', 'slug' => 'c', 'is_active' => true]);
        $product = \App\Models\Product::create([
            'category_id' => $category->id,
            'name' => 'Bar', 'slug' => 'bar', 'price' => 100,
            'sku' => 'B1', 'stock' => 5, 'is_active' => true,
        ]);
        $division = \App\Models\Division::create(['name' => 'Dhaka']);
        $district = \App\Models\District::create([
            'division_id' => $division->id, 'name' => 'Gazipur', 'shipping_charge' => 80,
        ]);

        $this->actingAs($user)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 1]);
        $this->actingAs($user)->post('/checkout', [
            'phone' => '01700000000',
            'address' => '12 Test Road',
            'division_id' => $division->id,
            'district_id' => $district->id,
            'payment_method' => 'cod',
        ])->assertSessionHas('placed_order_ids', [Order::sole()->id]);
    }
}
