<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Stock leaves the shelf when an order is placed, so it has to come back when
 * that order is cancelled or deleted — and must not come back twice.
 */
class OrderStockReconciliationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();

        foreach (['view_sales', 'edit_sales'] as $name) {
            $user->givePermissionTo(Permission::findOrCreate($name, 'web'));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user;
    }

    private function makeProduct(int $stock = 10): Product
    {
        $category = Category::create([
            'name' => 'C ' . uniqid(),
            'slug' => 'c-' . uniqid(),
            'is_active' => true,
        ]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Test Bar',
            'slug' => 'bar-' . uniqid(),
            'price' => 100,
            'sku' => 'SKU-' . strtoupper(uniqid()),
            'stock' => $stock,
            'is_active' => true,
        ]);
    }

    /**
     * An order as it stands just after checkout: stock already drawn down.
     */
    private function placedOrder(Product $product, int $quantity = 3, int $returned = 0): Order
    {
        $product->decrement('stock', $quantity);

        $order = Order::create([
            'order_number' => 'CHOC-' . strtoupper(uniqid()),
            'status' => 'processing',
            'subtotal' => 100 * $quantity,
            'tax' => 0,
            'shipping_cost' => 0,
            'total' => 100 * $quantity,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 100,
            'quantity' => $quantity,
            'returned_quantity' => $returned,
        ]);

        return $order;
    }

    private function patchStatus(User $admin, Order $order, string $status)
    {
        return $this->actingAs($admin)->patch('/admin/sales/' . $order->id . '/status', [
            'status' => $status,
            'payment_status' => 'unpaid',
        ]);
    }

    public function test_cancelling_an_order_puts_the_stock_back(): void
    {
        $product = $this->makeProduct(10);
        $order = $this->placedOrder($product, 3);
        $this->assertSame(7, $product->fresh()->stock);

        $this->patchStatus($this->admin(), $order, 'cancelled')->assertRedirect();

        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_cancelling_twice_does_not_restock_twice(): void
    {
        $product = $this->makeProduct(10);
        $order = $this->placedOrder($product, 3);
        $admin = $this->admin();

        $this->patchStatus($admin, $order, 'cancelled');
        $this->patchStatus($admin, $order, 'cancelled');

        $this->assertSame(10, $product->fresh()->stock, 'a second cancel must be a no-op for stock');
    }

    public function test_reopening_a_cancelled_order_takes_the_stock_again(): void
    {
        $product = $this->makeProduct(10);
        $order = $this->placedOrder($product, 3);
        $admin = $this->admin();

        $this->patchStatus($admin, $order, 'cancelled');
        $this->assertSame(10, $product->fresh()->stock);

        $this->patchStatus($admin, $order, 'processing');

        $this->assertSame(7, $product->fresh()->stock);
        $this->assertSame('processing', $order->fresh()->status);
    }

    public function test_reopening_is_refused_when_the_goods_are_gone(): void
    {
        $product = $this->makeProduct(10);
        $order = $this->placedOrder($product, 3);
        $admin = $this->admin();

        $this->patchStatus($admin, $order, 'cancelled');

        // Everything got sold to somebody else in the meantime.
        $product->update(['stock' => 0]);

        $this->patchStatus($admin, $order, 'processing')->assertSessionHas('error');

        $this->assertSame(0, $product->fresh()->stock, 'stock must not go negative');
        $this->assertSame('cancelled', $order->fresh()->status, 'the order should stay cancelled');
    }

    public function test_a_cancellation_skips_quantities_already_sent_back_as_returns(): void
    {
        $product = $this->makeProduct(10);
        // 3 sold, 1 of them already returned and restocked by the returns flow.
        $order = $this->placedOrder($product, 3, returned: 1);
        $product->increment('stock');
        $this->assertSame(8, $product->fresh()->stock);

        $this->patchStatus($this->admin(), $order, 'cancelled');

        $this->assertSame(10, $product->fresh()->stock, 'only the 2 still outstanding should come back');
    }

    public function test_deleting_an_order_releases_its_stock(): void
    {
        $product = $this->makeProduct(10);
        $order = $this->placedOrder($product, 3);

        $this->actingAs($this->admin())
            ->delete('/admin/sales/' . $order->id)
            ->assertRedirect();

        $this->assertSame(10, $product->fresh()->stock);
        $this->assertNull(Order::find($order->id));
    }

    public function test_deleting_an_already_cancelled_order_does_not_restock_again(): void
    {
        $product = $this->makeProduct(10);
        $order = $this->placedOrder($product, 3);
        $admin = $this->admin();

        $this->patchStatus($admin, $order, 'cancelled');
        $this->assertSame(10, $product->fresh()->stock);

        $this->actingAs($admin)->delete('/admin/sales/' . $order->id);

        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_the_restock_is_written_to_the_movement_ledger(): void
    {
        $product = $this->makeProduct(10);
        $order = $this->placedOrder($product, 3);

        $this->patchStatus($this->admin(), $order, 'cancelled');

        $movement = StockMovement::where('product_id', $product->id)->latest('id')->first();
        $this->assertNotNull($movement);
        $this->assertSame('cancel_in', $movement->type);
        $this->assertSame(3, (int) $movement->quantity);
        $this->assertSame(10, (int) $movement->resulting_stock);
    }
}
