<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The sales list has to say where each order stands and which channel it came
 * through. Where the parcel has got to is no longer one of its columns, so the
 * shipping status is covered against the model that derives it.
 */
class SalesListStatusTest extends TestCase
{
    use RefreshDatabase;

    private function salesManager(): User
    {
        Permission::findOrCreate('view_sales', 'web');

        $user = User::factory()->create();
        $user->givePermissionTo('view_sales');

        return $user;
    }

    private function makeOrder(array $overrides = []): Order
    {
        return Order::create(array_merge([
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'cod',
            'subtotal' => 100,
            'shipping_cost' => 0,
            'tax' => 0,
            'total' => 100,
        ], $overrides));
    }

    public function test_the_list_carries_each_order_status(): void
    {
        $this->makeOrder(['status' => 'delivered']);

        $this->actingAs($this->salesManager())
            ->get('/admin/sales')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Sales/Index')
                ->where('sales.0.status', 'delivered'));
    }

    public function test_an_unshipped_order_reads_as_not_shipped(): void
    {
        $this->assertSame('not_shipped', $this->makeOrder()->shipping_status);
    }

    public function test_shipping_status_comes_from_the_shipment(): void
    {
        $order = $this->makeOrder(['status' => 'shipped']);

        Shipment::create([
            'order_id' => $order->id,
            'courier' => 'pathao',
            'consignment_id' => 'CN-1',
            'tracking_code' => 'TRK-1',
            'status' => 'in_transit',
        ]);

        $this->assertSame('in_transit', $order->fresh()->shipping_status);
    }

    /**
     * A failed delivery gets re-booked, so an order can hold more than one
     * shipment. The newest is the one that describes where the parcel is.
     */
    public function test_the_newest_shipment_wins(): void
    {
        $order = $this->makeOrder();

        Shipment::create([
            'order_id' => $order->id,
            'courier' => 'pathao',
            'status' => 'returned',
        ]);
        Shipment::create([
            'order_id' => $order->id,
            'courier' => 'steadfast',
            'status' => 'delivered',
        ]);

        $this->assertSame('delivered', $order->fresh()->shipping_status);
    }

    /**
     * The column is gone, and with it the shipments the accessor needs -- the
     * list should not be paying to load them.
     */
    public function test_the_list_no_longer_carries_a_shipping_column(): void
    {
        $this->makeOrder();

        $this->actingAs($this->salesManager())
            ->get('/admin/sales')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->missing('sales.0.shipping_status')
                ->missing('sales.0.shipments'));
    }

    /**
     * A POS operator picks where the customer came from. That answer is the
     * point of the column -- showing "POS" instead just repeats the channel.
     */
    public function test_a_pos_sale_is_listed_under_the_lead_source_the_operator_recorded(): void
    {
        $this->makeOrder(['order_source' => 'pos', 'lead_source' => 'Facebook']);

        $this->actingAs($this->salesManager())
            ->get('/admin/sales')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('sales.0.source_label', 'Facebook')
                ->where('sales.0.lead_source', 'Facebook')
                ->where('sales.0.order_source', 'pos'));
    }

    public function test_a_pos_sale_with_no_lead_source_falls_back_to_the_channel(): void
    {
        $this->makeOrder(['order_source' => 'pos']);

        $this->actingAs($this->salesManager())
            ->get('/admin/sales')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('sales.0.source_label', 'POS'));
    }

    public function test_a_web_order_is_listed_under_its_channel(): void
    {
        $this->makeOrder(['order_source' => 'web']);

        $this->actingAs($this->salesManager())
            ->get('/admin/sales')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('sales.0.source_label', 'WEB'));
    }
}
