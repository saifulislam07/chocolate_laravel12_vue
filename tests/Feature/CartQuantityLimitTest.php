<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The per-item cap and the stock ceiling have to hold against what the bag
 * ends up containing, not against a single click.
 */
class CartQuantityLimitTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(int $stock = 100): Product
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

    public function test_repeated_adds_cannot_climb_past_the_per_item_cap(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct(100);

        $this->actingAs($user)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 20]);
        $this->assertSame(20, (int) CartItem::sole()->quantity);

        // Adding another 20 would make 40 — well past the cap of 20.
        $this->actingAs($user)
            ->post('/cart/items', ['product_id' => $product->id, 'quantity' => 20])
            ->assertSessionHasErrors('quantity');

        $this->assertSame(20, (int) CartItem::sole()->quantity);
    }

    public function test_a_single_add_beyond_the_cap_is_rejected(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct(100);

        $this->actingAs($user)
            ->post('/cart/items', ['product_id' => $product->id, 'quantity' => 21])
            ->assertSessionHasErrors('quantity');

        $this->assertSame(0, CartItem::count());
    }

    public function test_the_bag_cannot_hold_more_than_there_is_in_stock(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct(3);

        $this->actingAs($user)
            ->post('/cart/items', ['product_id' => $product->id, 'quantity' => 5])
            ->assertSessionHasErrors('quantity');

        $this->assertSame(0, CartItem::count());
    }

    public function test_repeated_adds_cannot_creep_past_the_stock_on_hand(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct(3);

        $this->actingAs($user)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 2]);

        $this->actingAs($user)
            ->post('/cart/items', ['product_id' => $product->id, 'quantity' => 2])
            ->assertSessionHasErrors('quantity');

        $this->assertSame(2, (int) CartItem::sole()->quantity);
    }

    public function test_adding_up_to_the_available_stock_is_allowed(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct(3);

        $this->actingAs($user)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 2]);
        $this->actingAs($user)
            ->post('/cart/items', ['product_id' => $product->id, 'quantity' => 1])
            ->assertSessionHasNoErrors();

        $this->assertSame(3, (int) CartItem::sole()->quantity);
    }

    public function test_editing_a_line_is_held_to_the_same_stock_ceiling(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct(3);

        $this->actingAs($user)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 1]);
        $item = CartItem::sole();

        $this->actingAs($user)
            ->patch('/cart/items/' . $item->id, ['quantity' => 5])
            ->assertSessionHasErrors('quantity');

        $this->assertSame(1, (int) $item->fresh()->quantity);
    }

    public function test_editing_a_line_within_stock_still_works(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct(10);

        $this->actingAs($user)->post('/cart/items', ['product_id' => $product->id, 'quantity' => 1]);
        $item = CartItem::sole();

        $this->actingAs($user)
            ->patch('/cart/items/' . $item->id, ['quantity' => 4])
            ->assertSessionHasNoErrors();

        $this->assertSame(4, (int) $item->fresh()->quantity);
    }
}
