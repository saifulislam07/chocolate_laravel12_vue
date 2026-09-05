<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\District;
use App\Models\Division;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\WebSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

/**
 * Which gateways admin settings let a shopper actually pay with.
 *
 * The checkout form is built from the same list store() validates against, so
 * these cover both halves: what the shopper is offered, and what the server
 * accepts from a request that never went near the form.
 */
class CheckoutPaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    private function shopperWithFullBag(): User
    {
        $category = Category::create([
            'name' => 'Truffles',
            'slug' => 'truffles',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Test Bar',
            'slug' => 'test-bar',
            'description' => 'Yum',
            'price' => 100,
            'cost_price' => 40,
            'sku' => 'SKU-PAY-1',
            'stock' => 10,
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $this->actingAs($user)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 1]);

        return $user;
    }

    /** @return array{0: Division, 1: District} */
    private function makeArea(): array
    {
        $division = Division::create(['name' => 'Dhaka']);

        return [$division, District::create([
            'division_id' => $division->id,
            'name' => 'Gazipur',
            'shipping_charge' => 80,
        ])];
    }

    private function orderPayload(Division $division, District $district, string $method): array
    {
        return [
            'full_name' => 'Jane Doe',
            'phone' => '01700000000',
            'address' => '12 Test Road',
            'division_id' => $division->id,
            'district_id' => $district->id,
            'payment_method' => $method,
        ];
    }

    public function test_checkout_offers_only_cash_when_every_gateway_is_off(): void
    {
        WebSetting::create([
            'site_name' => 'Coco',
            'bkash_enabled' => false,
            'nagad_enabled' => false,
        ]);

        $this->actingAs($this->shopperWithFullBag())
            ->get('/checkout')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Checkout/Index')
                ->has('paymentMethods', 1)
                ->where('paymentMethods.0.value', 'cod'));
    }

    public function test_checkout_offers_only_cash_when_settings_have_never_been_saved(): void
    {
        $this->actingAs($this->shopperWithFullBag())
            ->get('/checkout')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('paymentMethods', 1)
                ->where('paymentMethods.0.value', 'cod'));
    }

    public function test_an_enabled_gateway_joins_the_checkout_form(): void
    {
        WebSetting::create([
            'site_name' => 'Coco',
            'bkash_enabled' => true,
            'nagad_enabled' => false,
        ]);

        $this->actingAs($this->shopperWithFullBag())
            ->get('/checkout')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('paymentMethods', 2)
                ->where('paymentMethods.0.value', 'cod')
                ->where('paymentMethods.1.value', 'bkash'));
    }

    public function test_a_disabled_gateway_is_refused_even_when_posted_directly(): void
    {
        WebSetting::create([
            'site_name' => 'Coco',
            'bkash_enabled' => false,
            'nagad_enabled' => false,
        ]);

        $user = $this->shopperWithFullBag();
        [$division, $district] = $this->makeArea();

        $this->actingAs($user)
            ->post('/checkout', $this->orderPayload($division, $district, 'bkash'))
            ->assertSessionHasErrors('payment_method');

        $this->assertSame(0, Order::count(), 'no order should be placed through a gateway that is switched off');
    }

    public function test_the_retired_card_option_is_no_longer_accepted(): void
    {
        $user = $this->shopperWithFullBag();
        [$division, $district] = $this->makeArea();

        $this->actingAs($user)
            ->post('/checkout', $this->orderPayload($division, $district, 'card'))
            ->assertSessionHasErrors('payment_method');

        $this->assertSame(0, Order::count());
    }

    public function test_an_enabled_gateway_is_accepted(): void
    {
        WebSetting::create([
            'site_name' => 'Coco',
            'nagad_enabled' => true,
        ]);

        $user = $this->shopperWithFullBag();
        [$division, $district] = $this->makeArea();

        $this->actingAs($user)
            ->post('/checkout', $this->orderPayload($division, $district, 'nagad'))
            ->assertSessionHasNoErrors();

        $this->assertSame('nagad', Order::sole()->payment_method);
    }

    public function test_cash_is_always_accepted(): void
    {
        $user = $this->shopperWithFullBag();
        [$division, $district] = $this->makeArea();

        $this->actingAs($user)
            ->post('/checkout', $this->orderPayload($division, $district, 'cod'))
            ->assertSessionHasNoErrors();

        $this->assertSame('cod', Order::sole()->payment_method);
    }
}
