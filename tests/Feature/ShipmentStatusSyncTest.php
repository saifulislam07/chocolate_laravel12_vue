<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\User;
use App\Models\WebSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Pathao's merchant API can neither cancel a consignment nor change its
 * address, so a parcel booked to the wrong place is called off in the courier
 * panel. Pulling the status back is what tells the shop that happened -- and
 * what releases the order for a corrected re-booking.
 */
class ShipmentStatusSyncTest extends TestCase
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

    private function bookedOrder(): array
    {
        $order = $this->makeOrder();
        $shipment = Shipment::create([
            'order_id' => $order->id,
            'courier' => 'pathao',
            'consignment_id' => 'DT100926BW5PQD',
            'tracking_code' => 'DT100926BW5PQD',
            'status' => 'Pending',
        ]);

        return [$order, $shipment];
    }

    private function sync(Order $order, Shipment $shipment)
    {
        return $this->actingAs($this->salesManager())
            ->post("/admin/sales/{$order->id}/shipments/{$shipment->id}/sync");
    }

    public function test_a_cancelled_parcel_releases_the_order_for_rebooking(): void
    {
        $this->configurePathao();
        [$order, $shipment] = $this->bookedOrder();

        $this->assertNotNull($order->load('shipments')->liveShipment());

        Http::fake([
            '*/issue-token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            '*/info' => Http::response(['data' => [
                'consignment_id' => 'DT100926BW5PQD',
                'order_status' => 'Cancelled',
            ]]),
        ]);

        $this->sync($order, $shipment)->assertSessionHas('success');

        $this->assertSame('Cancelled', $shipment->fresh()->status);
        $this->assertNull($order->load('shipments')->liveShipment());
    }

    public function test_a_still_live_parcel_keeps_the_order_locked(): void
    {
        $this->configurePathao();
        [$order, $shipment] = $this->bookedOrder();

        Http::fake([
            '*/issue-token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            '*/info' => Http::response(['data' => ['order_status' => 'In_Transit']]),
        ]);

        $this->sync($order, $shipment)->assertSessionHas('success');

        $this->assertSame('In_Transit', $shipment->fresh()->status);
        $this->assertNotNull($order->load('shipments')->liveShipment());
    }

    public function test_a_booking_that_never_reached_the_courier_has_nothing_to_pull(): void
    {
        $this->configurePathao();
        Http::fake();

        $order = $this->makeOrder();
        $shipment = Shipment::create([
            'order_id' => $order->id,
            'courier' => 'pathao',
            'consignment_id' => null,
            'status' => 'failed',
        ]);

        $this->sync($order, $shipment)->assertSessionHas('error');

        Http::assertNothingSent();
    }

    public function test_a_shipment_cannot_be_synced_through_another_order(): void
    {
        $this->configurePathao();
        Http::fake();

        [, $shipment] = $this->bookedOrder();
        $other = $this->makeOrder();

        $this->actingAs($this->salesManager())
            ->post("/admin/sales/{$other->id}/shipments/{$shipment->id}/sync")
            ->assertNotFound();
    }
}
