<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\WebSetting;
use App\Services\Courier\PathaoCourierService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Checkout records a district, Pathao routes by city. The two agree on most
 * names and disagree on the handful Pathao still spells the older way, so the
 * booking screen can fill the city in for the operator either way -- and must
 * do it off a cached list, because Pathao answers 429 after a few dozen calls.
 */
class PathaoCitySuggestionTest extends TestCase
{
    use RefreshDatabase;

    private function settings(): WebSetting
    {
        return WebSetting::create([
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
            '*/city-list' => Http::response(['data' => ['data' => [
                ['city_id' => 1, 'city_name' => 'Dhaka'],
                ['city_id' => 2, 'city_name' => 'Chittagong'],
                ['city_id' => 32, 'city_name' => 'B. Baria'],
                ['city_id' => 56, 'city_name' => 'Gopalgonj '],
            ]]]),
        ]);
    }

    public function test_a_district_spelled_the_same_way_resolves(): void
    {
        $this->fakePathao();

        $this->assertSame(1, (new PathaoCourierService($this->settings()))->resolveCityId('Dhaka'));
    }

    public function test_a_district_pathao_spells_differently_still_resolves(): void
    {
        $this->fakePathao();
        $service = new PathaoCourierService($this->settings());

        $this->assertSame(2, $service->resolveCityId('Chattogram'));
        $this->assertSame(32, $service->resolveCityId('Brahmanbaria'));
        $this->assertSame(56, $service->resolveCityId('Gopalganj'));
    }

    public function test_an_unknown_district_resolves_to_nothing(): void
    {
        $this->fakePathao();
        $service = new PathaoCourierService($this->settings());

        $this->assertNull($service->resolveCityId('Atlantis'));
        $this->assertNull($service->resolveCityId(null));
    }

    public function test_the_city_list_is_fetched_once_and_then_cached(): void
    {
        $this->fakePathao();
        $service = new PathaoCourierService($this->settings());

        $service->listCities();
        $service->listCities();
        $service->resolveCityId('Dhaka');

        Http::assertSentCount(2); // the token, then the city list -- once each
    }

    public function test_a_throttled_answer_is_not_cached(): void
    {
        Http::fake([
            '*/issue-token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            '*/city-list' => Http::response(['message' => 'Too Many Requests'], 429),
        ]);

        $service = new PathaoCourierService($this->settings());

        $this->assertSame([], $service->listCities());
        $this->assertSame([], $service->listCities());

        Http::assertSentCount(3); // token once, then a real retry rather than a cached blank
    }

    public function test_the_cities_endpoint_suggests_the_orders_own_district(): void
    {
        $this->settings();
        $this->fakePathao();

        Permission::findOrCreate('edit_sales', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo('edit_sales');

        $divisionId = DB::table('divisions')->insertGetId([
            'name' => 'Chattogram',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $districtId = DB::table('districts')->insertGetId([
            'division_id' => $divisionId,
            'name' => 'Chattogram',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'cod',
            'subtotal' => 100,
            'shipping_cost' => 0,
            'tax' => 0,
            'total' => 100,
            'district_id' => $districtId,
        ]);

        $this->actingAs($user)
            ->getJson('/admin/courier/pathao/cities?order=' . $order->id)
            ->assertOk()
            ->assertJsonPath('suggested_city_id', 2)
            ->assertJsonPath('data.0.city_name', 'Dhaka');
    }
}
