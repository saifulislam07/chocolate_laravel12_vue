<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The toast watches a token rather than the message text, because an operator
 * who presses the same button twice and gets the same answer twice has been
 * told something twice -- and a watcher comparing text would show it once.
 */
class FlashToastTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Permission::findOrCreate('view_sales', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo('view_sales');

        return $user;
    }

    public function test_a_flashed_message_carries_a_token(): void
    {
        $this->actingAs($this->admin())
            ->withSession(['success' => 'Pathao says Pending.'])
            ->get('/admin/sales')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('flash.success', 'Pathao says Pending.')
                ->whereNot('flash.token', null));
    }

    public function test_the_same_message_twice_carries_two_different_tokens(): void
    {
        $admin = $this->admin();

        $first = $this->actingAs($admin)
            ->withSession(['success' => 'Pathao says Pending.'])
            ->get('/admin/sales')
            ->viewData('page')['props']['flash']['token'];

        $second = $this->actingAs($admin)
            ->withSession(['success' => 'Pathao says Pending.'])
            ->get('/admin/sales')
            ->viewData('page')['props']['flash']['token'];

        $this->assertNotNull($first);
        $this->assertNotSame($first, $second);
    }

    public function test_a_page_with_nothing_to_say_carries_no_token(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/sales')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('flash.token', null));
    }
}
