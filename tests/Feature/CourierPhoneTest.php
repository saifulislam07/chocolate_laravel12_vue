<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\User;
use App\Models\WebSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * A courier rejects anything that is not a bare 11-digit local mobile, and
 * answers the rejection with a flat "please fix the given errors" that tells an
 * operator nothing. Numbers are folded into shape before they are sent, plainly
 * bad ones never cost a call, and whatever the courier does complain about is
 * repeated back in words.
 */
class CourierPhoneTest extends TestCase
{
    use RefreshDatabase;

    public static function phoneProvider(): array
    {
        return [
            'already plain'      => ['01916665832', '01916665832', true],
            'country code'       => ['+8801916665832', '01916665832', true],
            'country code bare'  => ['8801916665832', '01916665832', true],
            'missing leading 0'  => ['1916665832', '01916665832', true],
            'punctuated'         => ['017-889-89890', '01788989890', true],
            'ten digits'         => ['0178898989', '0178898989', false],
            'landline prefix'    => ['01212345678', '01212345678', false],
            'empty'              => ['', '', false],
        ];
    }

    #[DataProvider('phoneProvider')]
    public function test_a_delivery_phone_is_folded_into_courier_shape(string $raw, string $expected, bool $valid): void
    {
        $order = new Order(['customer_phone' => $raw]);

        $this->assertSame($expected, $order->courierPhone());
        $this->assertSame($valid, $order->hasDeliverablePhone());
    }

    private function salesManager(): User
    {
        Permission::findOrCreate('view_sales', 'web');
        Permission::findOrCreate('edit_sales', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo(['view_sales', 'edit_sales']);

        return $user;
    }

    private function makeOrder(string $phone): Order
    {
        return Order::create([
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'cod',
            'subtotal' => 100,
            'shipping_cost' => 0,
            'tax' => 0,
            'total' => 100,
            'customer_phone' => $phone,
        ]);
    }

    private function configurePathao(): void
    {
        WebSetting::create([
            'pathao_enabled' => true,
            'pathao_base_url' => 'https://courier-api-sandbox.pathao.com',
            'pathao_client_id' => 'cid',
            'pathao_client_secret' => 'sec',
            'pathao_username' => 'u@e.com',
            'pathao_password' => 'pw',
            'pathao_store_id' => '150852',
        ]);
    }

    private function book(Order $order)
    {
        return $this->actingAs($this->salesManager())->post("/admin/sales/{$order->id}/ship", [
            'courier' => 'pathao',
            'city_id' => 1,
            'zone_id' => 298,
            'area_id' => 3069,
        ]);
    }

    public function test_a_bad_phone_is_refused_before_the_courier_is_called(): void
    {
        $this->configurePathao();
        Http::fake();

        $order = $this->makeOrder('0178898989');

        $this->book($order)->assertSessionHas('error');

        Http::assertNothingSent();
        $this->assertSame(0, $order->shipments()->count());
    }

    public function test_a_courier_rejection_is_reported_in_words(): void
    {
        $this->configurePathao();

        Http::fake([
            '*/issue-token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            '*/orders' => Http::response([
                'code' => 422,
                'type' => 'error',
                'message' => 'Please fix the given errors',
                'errors' => ['recipient_address' => ['The address is too short.']],
            ], 422),
        ]);

        $order = $this->makeOrder('01916665832');

        $response = $this->book($order);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('The address is too short.', session('error'));
    }

    public function test_a_country_coded_number_reaches_pathao_stripped(): void
    {
        $this->configurePathao();

        Http::fake([
            '*/issue-token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            '*/orders' => Http::response(['data' => [
                'consignment_id' => 'DT-OK-1',
                'order_status' => 'Pending',
            ]]),
        ]);

        $this->book($this->makeOrder('+8801916665832'))->assertSessionHas('success');

        Http::assertSent(fn ($request) => str_contains($request->url(), '/orders')
            && ($request['recipient_phone'] ?? null) === '01916665832');
    }

    public function test_a_refused_booking_shows_its_reason_on_the_order_page(): void
    {
        $this->configurePathao();

        $order = $this->makeOrder('01916665832');
        Shipment::create([
            'order_id' => $order->id,
            'courier' => 'pathao',
            'consignment_id' => null,
            'status' => 'Please fix the given errors',
            'raw_response' => [
                'message' => 'Please fix the given errors',
                'errors' => ['recipient_phone' => ['This recipient phone is not a valid phone number.']],
            ],
        ]);

        $this->actingAs($this->salesManager())
            ->get("/admin/sales/{$order->id}")
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Sales/Show')
                ->where('sale.shipments.0.failure_reason', 'This recipient phone is not a valid phone number.'));
    }
}
