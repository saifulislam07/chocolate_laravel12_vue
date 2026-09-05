<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SalesInvoiceTest extends TestCase
{
    use RefreshDatabase;

    private function salesManager(): User
    {
        Permission::findOrCreate('view_sales', 'web');

        $user = User::factory()->create();
        $user->givePermissionTo('view_sales');

        return $user;
    }

    private function makeOrder(float $total): Order
    {
        return Order::create([
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'cod',
            'subtotal' => $total,
            'shipping_cost' => 0,
            'tax' => 0,
            'total' => $total,
        ]);
    }

    public function test_the_invoice_carries_the_total_in_words(): void
    {
        $order = $this->makeOrder(4200);

        $this->actingAs($this->salesManager())
            ->get('/admin/sales/' . $order->id)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Sales/Show')
                ->where('sale.total_in_words', 'Four Thousand Two Hundred Taka Only'));
    }

    public function test_paisa_survive_the_trip_through_the_database(): void
    {
        $order = $this->makeOrder(454.40);

        $this->actingAs($this->salesManager())
            ->get('/admin/sales/' . $order->id)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('sale.total_in_words', 'Four Hundred Fifty Four Taka and Forty Paisa Only'));
    }
}
