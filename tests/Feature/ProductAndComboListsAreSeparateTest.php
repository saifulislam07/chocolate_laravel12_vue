<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * A combo is a row in the products table, but it is not a product the catalogue
 * can edit: its price and stock come from the items it holds. The two lists
 * therefore have to stay apart, and the product form has to refuse a combo even
 * when its URL is typed by hand.
 */
class ProductAndComboListsAreSeparateTest extends TestCase
{
    use RefreshDatabase;

    private function catalogueManager(): User
    {
        foreach (['view_products', 'view_bundles'] as $ability) {
            Permission::findOrCreate($ability, 'web');
        }

        $user = User::factory()->create();
        $user->givePermissionTo(['view_products', 'view_bundles']);

        return $user;
    }

    private function makeProduct(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Dark Truffle',
            'slug' => 'dark-truffle-' . uniqid(),
            'cost_price' => 100,
            'price' => 200,
            'stock' => 10,
            'is_active' => true,
            'is_bundle' => false,
        ], $overrides));
    }

    private function makeCombo(): Product
    {
        return $this->makeProduct([
            'name' => 'Festive Combo',
            'slug' => 'festive-combo-' . uniqid(),
            'is_bundle' => true,
        ]);
    }

    public function test_the_product_list_leaves_combos_to_the_combo_list(): void
    {
        $this->makeProduct();
        $this->makeCombo();

        $this->actingAs($this->catalogueManager())
            ->get('/admin/products')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Products/Index')
                ->has('products', 1)
                ->where('products.0.name', 'Dark Truffle'));
    }

    public function test_the_combo_list_carries_only_combos(): void
    {
        $this->makeProduct();
        $this->makeCombo();

        $this->actingAs($this->catalogueManager())
            ->get('/admin/bundles')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Bundles/Index')
                ->has('bundles', 1)
                ->where('bundles.0.name', 'Festive Combo'));
    }

    /**
     * The product form has no way to edit the items a combo holds, so opening a
     * combo there would let someone save it with its bundle pricing recomputed
     * from nothing. Hand them to the combo editor instead.
     */
    public function test_opening_a_combo_in_the_product_editor_redirects_to_the_combo_editor(): void
    {
        $combo = $this->makeCombo();

        $this->actingAs($this->catalogueManager())
            ->get("/admin/products/{$combo->id}/edit")
            ->assertRedirect(route('admin.bundles.edit', $combo));
    }

    public function test_a_combo_cannot_be_saved_through_the_product_form(): void
    {
        $combo = $this->makeCombo();

        $this->actingAs($this->catalogueManager())
            ->put("/admin/products/{$combo->id}", [
                'name' => 'Renamed by the product form',
                'price' => 1,
            ])
            ->assertNotFound();

        $this->assertSame('Festive Combo', $combo->fresh()->name);
    }

    public function test_a_combo_cannot_be_deleted_through_the_product_form(): void
    {
        $combo = $this->makeCombo();

        $this->actingAs($this->catalogueManager())
            ->delete("/admin/products/{$combo->id}")
            ->assertNotFound();

        $this->assertNotNull($combo->fresh());
    }
}
