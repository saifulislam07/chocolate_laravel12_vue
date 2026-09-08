<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Money reaches the shop when the courier delivers and hands it over, less the
 * delivery charge it keeps. Every figure that claims to be revenue -- on the
 * dashboard, in the reports, on the sales list -- has to say that same number.
 */
class DeliveredRevenueTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        foreach (['manage_dashboard', 'view_sales', 'edit_sales', 'view_reports'] as $ability) {
            Permission::findOrCreate($ability, 'web');
        }

        $user = User::factory()->create();
        $user->givePermissionTo(['manage_dashboard', 'view_sales', 'edit_sales', 'view_reports']);

        return $user;
    }

    private function makeOrder(array $overrides = []): Order
    {
        return Order::create(array_merge([
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'cod',
            'order_source' => 'web',
            'subtotal' => 1000,
            'shipping_cost' => 70,
            'tax' => 0,
            'total' => 1070,
        ], $overrides));
    }

    public function test_revenue_leaves_out_the_shipping_charge(): void
    {
        $this->makeOrder(['status' => 'delivered', 'delivered_at' => now()]);

        $this->actingAs($this->admin())
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('stats.total_sales', 1000));
    }

    public function test_an_undelivered_order_is_not_revenue_yet(): void
    {
        $this->makeOrder(['status' => 'shipped']);
        $this->makeOrder(['status' => 'pending']);
        $this->makeOrder(['status' => 'cancelled']);

        $this->actingAs($this->admin())
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('stats.total_sales', 0)
                ->where('stats.orders_count', 3)
                ->where('stats.delivered_orders_count', 0));
    }

    /**
     * A POS sale is settled at the counter, so it counts the moment it is rung
     * up rather than waiting on a courier.
     */
    public function test_a_pos_sale_counts_immediately(): void
    {
        $this->makeOrder([
            'status' => 'completed',
            'order_source' => 'pos',
            'shipping_cost' => 0,
            'total' => 1000,
            'delivered_at' => now(),
        ]);

        $this->actingAs($this->admin())
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('stats.total_sales', 1000));
    }

    /**
     * Revenue is dated by delivery, not by when the order was taken: an order
     * placed last month and delivered today is today's money.
     */
    public function test_today_counts_what_was_delivered_today(): void
    {
        $this->makeOrder([
            'status' => 'delivered',
            'created_at' => now()->subMonth(),
            'delivered_at' => now(),
        ]);
        // Ordered today, still in transit -- no money yet.
        $this->makeOrder(['status' => 'shipped']);

        $this->actingAs($this->admin())
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('stats.today_sales', 1000));
    }

    public function test_marking_an_order_delivered_stamps_the_moment_the_money_arrived(): void
    {
        $order = $this->makeOrder(['status' => 'shipped']);

        $this->actingAs($this->admin())
            ->patch("/admin/sales/{$order->id}/status", [
                'status' => 'delivered',
                'payment_status' => 'paid',
            ])
            ->assertRedirect();

        $this->assertNotNull($order->fresh()->delivered_at);
    }

    /**
     * A delivery marked in error has to give the money back, or the reports
     * keep counting it.
     */
    public function test_moving_an_order_back_out_of_delivered_clears_the_stamp(): void
    {
        $order = $this->makeOrder(['status' => 'delivered', 'delivered_at' => now()]);

        $this->actingAs($this->admin())
            ->patch("/admin/sales/{$order->id}/status", [
                'status' => 'processing',
                'payment_status' => 'unpaid',
            ])
            ->assertRedirect();

        $this->assertNull($order->fresh()->delivered_at);
    }

    public function test_the_reports_summary_separates_revenue_from_the_courier_charge(): void
    {
        $this->makeOrder(['status' => 'delivered', 'delivered_at' => now()]);

        $this->actingAs($this->admin())
            ->get('/admin/reports')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('summary.total_sales', 1000)
                ->where('summary.shipping_collected', 70));
    }
}
