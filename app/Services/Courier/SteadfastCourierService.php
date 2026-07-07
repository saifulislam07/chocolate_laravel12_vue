<?php

namespace App\Services\Courier;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\WebSetting;
use Illuminate\Support\Facades\Http;

class SteadfastCourierService
{
    public function __construct(private readonly WebSetting $settings)
    {
    }

    public function enabled(): bool
    {
        return (bool) $this->settings->steadfast_enabled
            && filled($this->settings->steadfast_base_url)
            && filled($this->settings->steadfast_api_key)
            && filled($this->settings->steadfast_secret_key);
    }

    public function createOrder(Order $order): Shipment
    {
        $payload = [
            'invoice' => $order->order_number,
            'recipient_name' => $order->customer_name ?: $order->customer?->name ?: $order->user?->name ?: 'Customer',
            'recipient_phone' => $order->customer_phone ?: $order->customer?->phone ?: 'N/A',
            'recipient_address' => $order->shipping_address ?: 'N/A',
            'cod_amount' => $order->payment_status === 'paid' ? 0 : (float) $order->total,
            'note' => $order->notes,
        ];

        $response = $this->request()->post($this->baseUrl() . '/create_order', $payload)->json() ?? [];
        $consignment = $response['consignment'] ?? [];

        return Shipment::create([
            'order_id' => $order->id,
            'courier' => 'steadfast',
            'consignment_id' => $consignment['consignment_id'] ?? null,
            'tracking_code' => $consignment['tracking_code'] ?? null,
            'status' => $consignment['status'] ?? ($response['message'] ?? 'failed'),
            'raw_response' => $response,
        ]);
    }

    public function getStatus(string $consignmentId): array
    {
        return $this->request()->get($this->baseUrl() . "/status_by_cid/{$consignmentId}")->json() ?? [];
    }

    private function request()
    {
        return Http::acceptJson()->withHeaders([
            'Api-Key' => $this->settings->steadfast_api_key,
            'Secret-Key' => $this->settings->steadfast_secret_key,
        ]);
    }

    private function baseUrl(): string
    {
        return rtrim($this->settings->steadfast_base_url, '/');
    }
}
