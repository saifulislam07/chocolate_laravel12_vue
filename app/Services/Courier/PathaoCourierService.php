<?php

namespace App\Services\Courier;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\WebSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PathaoCourierService
{
    /**
     * Districts Pathao still spells the older way. Every other district matches
     * its city once case and punctuation are stripped, so only the strays are
     * listed here -- keyed by what checkout calls them.
     */
    private const CITY_ALIASES = [
        'barishal' => 'barisal',
        'brahmanbaria' => 'bbaria',
        'bogura' => 'bogra',
        'chattogram' => 'chittagong',
        'gopalganj' => 'gopalgonj',
        'jhalokati' => 'jhalokathi',
        'jhenaidah' => 'jhenidah',
        'khagrachhari' => 'khagrachari',
        'munshiganj' => 'munsiganj',
        'narsingdi' => 'narshingdi',
        'netrokona' => 'netrakona',
    ];

    public function __construct(private readonly WebSetting $settings)
    {
    }

    public function enabled(): bool
    {
        return (bool) $this->settings->pathao_enabled
            && filled($this->settings->pathao_base_url)
            && filled($this->settings->pathao_client_id)
            && filled($this->settings->pathao_client_secret)
            && filled($this->settings->pathao_username)
            && filled($this->settings->pathao_password)
            && filled($this->settings->pathao_store_id);
    }

    public function listCities(): array
    {
        return $this->reference('cities', '/aladdin/api/v1/city-list');
    }

    /**
     * Which Pathao city an order's district points at, if any.
     *
     * Checkout records a district; Pathao routes by city, zone and area. Only
     * the first of those three can be answered from what the order already
     * knows, so this fills the city and leaves the finer two to the operator.
     */
    public function resolveCityId(?string $district): ?int
    {
        if (blank($district)) {
            return null;
        }

        $wanted = $this->normalise($district);
        $wanted = self::CITY_ALIASES[$wanted] ?? $wanted;

        foreach ($this->listCities() as $city) {
            if ($this->normalise((string) ($city['city_name'] ?? '')) === $wanted) {
                return (int) $city['city_id'];
            }
        }

        return null;
    }

    private function normalise(string $name): string
    {
        return strtolower(preg_replace('/[^a-z]/i', '', $name));
    }

    public function listZones(int $cityId): array
    {
        return $this->reference("zones_{$cityId}", "/aladdin/api/v1/cities/{$cityId}/zone-list");
    }

    public function listAreas(int $zoneId): array
    {
        return $this->reference("areas_{$zoneId}", "/aladdin/api/v1/zones/{$zoneId}/area-list");
    }

    /**
     * Cities, zones and areas barely move, and Pathao rate-limits hard enough to
     * answer 429 after a few dozen calls -- a booking screen that re-asked on
     * every view would run the shop into that. The base url is folded into the
     * key so switching between sandbox and production cannot serve one's list
     * for the other, and an empty answer is never kept: that is what a throttled
     * or failed call looks like.
     */
    private function reference(string $key, string $path): array
    {
        $cacheKey = 'pathao_' . $key . '_' . $this->settings->id . '_' . substr(md5($this->baseUrl()), 0, 8);

        $cached = Cache::get($cacheKey);

        if (is_array($cached) && $cached !== []) {
            return $cached;
        }

        $data = $this->authorizedGet($path)['data']['data'] ?? [];

        if ($data !== []) {
            Cache::put($cacheKey, $data, now()->addDay());
        }

        return $data;
    }

    /**
     * @param  array{city_id: int, zone_id: int, area_id: int}  $area
     */
    public function createOrder(Order $order, array $area): Shipment
    {
        $payload = [
            'store_id' => (int) $this->settings->pathao_store_id,
            'merchant_order_id' => $order->order_number,
            'recipient_name' => $this->recipientName($order),
            'recipient_phone' => $this->recipientPhone($order),
            'recipient_address' => $order->shipping_address ?: 'N/A',
            'recipient_city' => $area['city_id'],
            'recipient_zone' => $area['zone_id'],
            'recipient_area' => $area['area_id'],
            'delivery_type' => 48,
            'item_type' => 2,
            'special_instruction' => $order->notes,
            'item_quantity' => max(1, $order->items()->count()),
            'item_weight' => 0.5,
            'amount_to_collect' => $order->payment_status === 'paid' ? 0 : (float) $order->total,
            'item_description' => 'Chocolate order ' . $order->order_number,
        ];

        $response = $this->authorizedPost('/aladdin/api/v1/orders', $payload);
        $data = $response['data'] ?? [];

        return Shipment::create([
            'order_id' => $order->id,
            'courier' => 'pathao',
            'consignment_id' => $data['consignment_id'] ?? null,
            'tracking_code' => $data['consignment_id'] ?? null,
            'status' => $data['order_status'] ?? ($response['message'] ?? 'failed'),
            'raw_response' => $response,
        ]);
    }

    /**
     * What Pathao currently says about a consignment.
     *
     * The merchant API carries no cancel and no address-edit route -- both are
     * done from the Pathao panel -- so this read is the only way the shop finds
     * out that a mis-addressed parcel was called off there.
     */
    public function orderInfo(string $consignmentId): array
    {
        return $this->authorizedGet("/aladdin/api/v1/orders/{$consignmentId}/info")['data'] ?? [];
    }

    private function recipientName(Order $order): string
    {
        return $order->customer_name ?: $order->customer?->name ?: $order->user?->name ?: 'Customer';
    }

    private function recipientPhone(Order $order): string
    {
        return $order->courierPhone() ?: 'N/A';
    }

    private function accessToken(): string
    {
        $key = 'pathao_access_token_' . $this->settings->id;

        if ($cached = Cache::get($key)) {
            return $cached;
        }

        $response = Http::acceptJson()->post($this->baseUrl() . '/aladdin/api/v1/issue-token', [
            'client_id' => $this->settings->pathao_client_id,
            'client_secret' => $this->settings->pathao_client_secret,
            'username' => $this->settings->pathao_username,
            'password' => $this->settings->pathao_password,
            'grant_type' => 'password',
        ])->json();

        $token = $response['access_token'] ?? '';

        // Never cache a failed handshake, otherwise a credential fix stays invisible for 50 minutes.
        if ($token !== '') {
            Cache::put($key, $token, now()->addSeconds((int) ($response['expires_in'] ?? 3000) - 300));
        }

        return $token;
    }

    private function authorizedGet(string $path): array
    {
        return Http::acceptJson()
            ->withToken($this->accessToken())
            ->get($this->baseUrl() . $path)
            ->json() ?? [];
    }

    private function authorizedPost(string $path, array $payload): array
    {
        return Http::acceptJson()
            ->withToken($this->accessToken())
            ->post($this->baseUrl() . $path, $payload)
            ->json() ?? [];
    }

    private function baseUrl(): string
    {
        return rtrim($this->settings->pathao_base_url, '/');
    }
}
