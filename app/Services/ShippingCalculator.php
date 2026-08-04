<?php

namespace App\Services;

use App\Models\District;
use App\Models\WebSetting;

/**
 * Single source of truth for delivery charges.
 *
 * Rate lookup order:
 *   1. Free, if the order subtotal reaches the free-shipping threshold.
 *   2. The district's own charge, when the admin has set one.
 *   3. The default charge from settings.
 */
class ShippingCalculator
{
    private ?WebSetting $settings = null;

    public function charge(float $subtotal, int|string|null $districtId = null): float
    {
        if ($this->qualifiesForFreeShipping($subtotal)) {
            return 0.0;
        }

        $districtCharge = $districtId
            ? District::whereKey($districtId)->value('shipping_charge')
            : null;

        return round((float) ($districtCharge ?? $this->defaultCharge()), 2);
    }

    public function qualifiesForFreeShipping(float $subtotal): bool
    {
        $threshold = $this->freeShippingThreshold();

        return $threshold !== null && $subtotal >= $threshold;
    }

    public function defaultCharge(): float
    {
        return (float) ($this->settings()?->default_shipping_charge ?? 120);
    }

    public function freeShippingThreshold(): ?float
    {
        $threshold = $this->settings()?->free_shipping_threshold;

        return $threshold === null ? null : (float) $threshold;
    }

    /**
     * Everything the checkout page needs to price shipping live as the
     * customer picks an area, without a round trip per selection.
     *
     * @return array{default_charge: float, free_threshold: float|null, charges: array<int, float>}
     */
    public function frontendConfig(): array
    {
        return [
            'default_charge' => $this->defaultCharge(),
            'free_threshold' => $this->freeShippingThreshold(),
            'charges' => District::whereNotNull('shipping_charge')
                ->pluck('shipping_charge', 'id')
                ->map(fn ($charge): float => (float) $charge)
                ->all(),
        ];
    }

    private function settings(): ?WebSetting
    {
        return $this->settings ??= WebSetting::first();
    }
}
