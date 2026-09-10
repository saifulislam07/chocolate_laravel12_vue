<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Couriers answer in machine keys, and a returned parcel must not read like one
 * that is merely on its way. The order page spells the status out and colours it
 * by what it means, so the two couriers' vocabularies land as one.
 */
class ShipmentStatusDisplayTest extends TestCase
{
    use RefreshDatabase;

    public static function statusProvider(): array
    {
        return [
            'pathao fresh booking'  => ['Pending', 'Pending', 'secondary'],
            'pathao moving'         => ['In_Transit', 'In Transit', 'info'],
            'pathao sorting hub'    => ['At_the_Sorting_HUB', 'At The Sorting Hub', 'info'],
            'pathao delivered'      => ['Delivered', 'Delivered', 'success'],
            'pathao partial'        => ['Partial_Delivery', 'Partial Delivery', 'success'],
            'pathao returned'       => ['Returned', 'Returned', 'danger'],
            'pathao held'           => ['On_Hold', 'On Hold', 'warning'],
            'steadfast moving'      => ['in_review', 'In Review', 'info'],
            'steadfast cancelled'   => ['cancelled', 'Cancelled', 'danger'],
        ];
    }

    #[DataProvider('statusProvider')]
    public function test_a_courier_status_is_spelled_out_and_coloured(string $raw, string $label, string $tone): void
    {
        $shipment = new Shipment(['status' => $raw, 'consignment_id' => 'DT1']);

        $this->assertSame($label, $shipment->statusLabel());
        $this->assertSame($tone, $shipment->statusTone());
    }

    public function test_a_courier_written_sentence_is_left_alone(): void
    {
        $shipment = new Shipment(['status' => 'Please fix the given errors']);

        $this->assertSame('Please fix the given errors', $shipment->statusLabel());
    }

    public function test_the_delivery_fee_survives_a_status_pull(): void
    {
        $shipment = new Shipment([
            'status' => 'Pending',
            'raw_response' => ['data' => ['delivery_fee' => 110], 'order_status' => 'In_Transit'],
        ]);

        $this->assertSame(110.0, $shipment->deliveryFee());
    }

    public function test_a_shipment_with_no_quoted_fee_reports_none(): void
    {
        $this->assertNull((new Shipment(['raw_response' => []]))->deliveryFee());
    }

    public function test_the_order_page_carries_the_spelled_out_status(): void
    {
        Permission::findOrCreate('view_sales', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo('view_sales');

        $order = Order::create([
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

        Shipment::create([
            'order_id' => $order->id,
            'courier' => 'pathao',
            'consignment_id' => 'DT100926JD76H6',
            'tracking_code' => 'DT100926JD76H6',
            'status' => 'In_Transit',
            'raw_response' => ['data' => ['delivery_fee' => 110]],
        ]);

        $this->actingAs($user)
            ->get("/admin/sales/{$order->id}")
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Sales/Show')
                ->where('sale.shipments.0.status_label', 'In Transit')
                ->where('sale.shipments.0.status_tone', 'info')
                // JSON has one number type, so the float arrives as 110.
                ->where('sale.shipments.0.delivery_fee', 110)
                ->where('sale.shipments.0.failure_reason', null));
    }
}
