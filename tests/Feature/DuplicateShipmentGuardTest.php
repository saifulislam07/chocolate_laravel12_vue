<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\User;
use App\Models\WebSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * An order must never carry two live parcels: a second booking means a second
 * delivery and a second courier charge. A booking that failed or a parcel that
 * came back is finished with, though, and has to leave the re-book open.
 */
class DuplicateShipmentGuardTest extends TestCase
{
    use RefreshDatabase;

    private function salesManager(): User
    {
        Permission::findOrCreate('view_sales', 'web');
        Permission::findOrCreate('edit_sales', 'web');

        $user = User::factory()->create();
        $user->givePermissionTo(['view_sales', 'edit_sales']);

        return $user;
    }

    private function makeOrder(): Order
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
            'customer_phone' => '01916665832',
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

    private function fakePathao(): void
    {
        Http::fake([
            '*/issue-token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            '*/orders' => Http::response(['data' => [
                'consignment_id' => 'DT-NEW-1',
                'order_status' => 'Pending',
            ]]),
        ]);
    }

    private function book(User $user, Order $order)
    {
        return $this->actingAs($user)->post("/admin/sales/{$order->id}/ship", [
            'courier' => 'pathao',
            'city_id' => 1,
            'zone_id' => 298,
            'area_id' => 3069,
        ]);
    }

    public function test_a_live_consignment_blocks_a_second_booking(): void
    {
        $this->configurePathao();
        $this->fakePathao();

        $order = $this->makeOrder();
        Shipment::create([
            'order_id' => $order->id,
            'courier' => 'pathao',
            'consignment_id' => 'DT100926BW5PQD',
            'tracking_code' => 'DT100926BW5PQD',
            'status' => 'Pending',
        ]);

        $this->book($this->salesManager(), $order)->assertSessionHas('error');

        $this->assertSame(1, $order->shipments()->count());
        Http::assertNothingSent();
    }

    public function test_a_returned_parcel_leaves_the_rebook_open(): void
    {
        $this->configurePathao();
        $this->fakePathao();

        $order = $this->makeOrder();
        Shipment::create([
            'order_id' => $order->id,
            'courier' => 'pathao',
            'consignment_id' => 'DT-OLD-1',
            'status' => 'Returned',
        ]);

        $this->book($this->salesManager(), $order)->assertSessionHas('success');

        $this->assertSame(2, $order->shipments()->count());
    }

    public function test_a_failed_booking_leaves_the_rebook_open(): void
    {
        $this->configurePathao();
        $this->fakePathao();

        $order = $this->makeOrder();
        Shipment::create([
            'order_id' => $order->id,
            'courier' => 'pathao',
            'consignment_id' => null,
            'status' => 'Please contact with support',
        ]);

        $this->book($this->salesManager(), $order)->assertSessionHas('success');

        $this->assertSame(2, $order->shipments()->count());
    }

    public function test_the_show_page_carries_the_live_consignment(): void
    {
        $this->configurePathao();

        $order = $this->makeOrder();
        Shipment::create([
            'order_id' => $order->id,
            'courier' => 'pathao',
            'consignment_id' => 'DT100926BW5PQD',
            'status' => 'Pending',
        ]);

        $this->actingAs($this->salesManager())
            ->get("/admin/sales/{$order->id}")
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Sales/Show')
                ->where('liveShipment.consignment_id', 'DT100926BW5PQD'));
    }

    public function test_an_unbooked_order_carries_no_live_consignment(): void
    {
        $this->configurePathao();

        $this->actingAs($this->salesManager())
            ->get('/admin/sales/' . $this->makeOrder()->id)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Sales/Show')
                ->where('liveShipment', null));
    }
}
