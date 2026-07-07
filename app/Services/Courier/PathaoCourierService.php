<?php

namespace App\Services\Courier;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\WebSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PathaoCourierService
{
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
        return $this->authorizedGet('/aggregator/cities')['data']['data'] ?? [];
    }

    public function listZones(int $cityId): array
    {
        return $this->authorizedGet("/aggregator/cities/{$cityId}/zone-list")['data']['data'] ?? [];
    }

    public function listAreas(int $zoneId): array
    {
        return $this->authorizedGet("/aggregator/zones/{$zoneId}/area-list")['data']['data'] ?? [];
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

        $response = $this->authorizedPost('/aggregator/orders', $payload);
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

    private function recipientName(Order $order): string
    {
        return $order->customer_name ?: $order->customer?->name ?: $order->user?->name ?: 'Customer';
    }

    private function recipientPhone(Order $order): string
    {
        return $order->customer_phone ?: $order->customer?->phone ?: 'N/A';
    }

    private function accessToken(): string
    {
        return Cache::remember('pathao_access_token_' . $this->settings->id, now()->addMinutes(50), function () {
            $response = Http::acceptJson()->post($this->baseUrl() . '/aggregator/oauth/issue', [
                'client_id' => $this->settings->pathao_client_id,
                'client_secret' => $this->settings->pathao_client_secret,
                'username' => $this->settings->pathao_username,
                'password' => $this->settings->pathao_password,
                'grant_type' => 'password',
            ])->json();

            return $response['access_token'] ?? '';
        });
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
