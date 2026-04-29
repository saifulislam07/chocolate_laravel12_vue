<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\WebSetting;
use Illuminate\Support\Facades\Http;

class BkashPaymentService
{
    public function __construct(private readonly WebSetting $settings)
    {
    }

    public function enabled(): bool
    {
        return (bool) $this->settings->bkash_enabled
            && filled($this->settings->bkash_base_url)
            && filled($this->settings->bkash_app_key)
            && filled($this->settings->bkash_app_secret)
            && filled($this->settings->bkash_username)
            && filled($this->settings->bkash_password);
    }

    public function createPayment(Order $order, string $callbackUrl): array
    {
        $token = $this->grantToken();
        $invoice = $order->order_number;

        $payload = [
            'mode' => '0011',
            'payerReference' => (string) ($order->user_id ?: $order->id),
            'callbackURL' => $callbackUrl,
            'amount' => number_format((float) $order->total, 2, '.', ''),
            'currency' => 'BDT',
            'intent' => 'sale',
            'merchantInvoiceNumber' => $invoice,
        ];

        $response = Http::acceptJson()
            ->withHeaders([
                'Authorization' => $token,
                'X-APP-Key' => $this->settings->bkash_app_key,
            ])
            ->post($this->baseUrl() . '/tokenized/checkout/create', $payload)
            ->json();

        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'bkash',
            'gateway_payment_id' => $response['paymentID'] ?? null,
            'merchant_invoice_number' => $invoice,
            'amount' => $order->total,
            'currency' => 'BDT',
            'status' => isset($response['bkashURL']) ? 'created' : 'failed',
            'request_payload' => $payload,
            'response_payload' => $response,
            'failure_reason' => $response['statusMessage'] ?? null,
        ]);

        return $response;
    }

    public function executePayment(string $paymentId): array
    {
        $token = $this->grantToken();

        return Http::acceptJson()
            ->withHeaders([
                'Authorization' => $token,
                'X-APP-Key' => $this->settings->bkash_app_key,
            ])
            ->post($this->baseUrl() . '/tokenized/checkout/execute', [
                'paymentID' => $paymentId,
            ])
            ->json();
    }

    private function grantToken(): string
    {
        $response = Http::acceptJson()
            ->withHeaders([
                'username' => $this->settings->bkash_username,
                'password' => $this->settings->bkash_password,
            ])
            ->post($this->baseUrl() . '/tokenized/checkout/token/grant', [
                'app_key' => $this->settings->bkash_app_key,
                'app_secret' => $this->settings->bkash_app_secret,
            ])
            ->json();

        return $response['id_token'] ?? '';
    }

    private function baseUrl(): string
    {
        return rtrim($this->settings->bkash_base_url, '/');
    }
}
