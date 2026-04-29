<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\WebSetting;

class NagadPaymentService
{
    public function __construct(private readonly WebSetting $settings)
    {
    }

    public function enabled(): bool
    {
        return (bool) $this->settings->nagad_enabled
            && filled($this->settings->nagad_base_url)
            && filled($this->settings->nagad_merchant_id);
    }

    public function createPendingPayment(Order $order): PaymentTransaction
    {
        return PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'nagad',
            'merchant_invoice_number' => $order->order_number,
            'amount' => $order->total,
            'currency' => 'BDT',
            'status' => 'pending_gateway_configuration',
            'request_payload' => [
                'merchant_id' => $this->settings->nagad_merchant_id,
                'merchant_number' => $this->settings->nagad_merchant_number,
                'mode' => $this->settings->nagad_mode,
            ],
            'failure_reason' => 'Nagad merchant credentials are stored. API signing/production endpoint confirmation is required before automatic redirect.',
        ]);
    }
}
