<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    /**
     * Courier statuses that close a booking. Pathao and Steadfast each spell
     * these their own way, so they are matched lowercased with separators
     * normalised rather than compared verbatim.
     */
    public const CLOSED_STATUSES = [
        'failed', 'cancelled', 'canceled', 'returned', 'return', 'paid_return',
        'delivery_failed', 'pickup_failed', 'pickup_cancelled', 'delivery_cancelled',
    ];

    /**
     * The parcel reached the customer.
     */
    public const DELIVERED_STATUSES = ['delivered', 'partial_delivery'];

    /**
     * The parcel is somewhere between the shop and the door. Both couriers'
     * words for that are listed together -- what the status means matters to
     * the screen, which of the two said it does not.
     */
    public const MOVING_STATUSES = [
        'order_created', 'order_updated', 'exchange',
        'pickup_requested', 'assigned_for_pickup', 'picked', 'at_the_sorting_hub',
        'in_transit', 'received_at_last_mile_hub', 'assigned_for_delivery', 'in_review',
    ];

    /**
     * Stalled rather than finished: someone has to do something about it.
     */
    public const HELD_STATUSES = ['on_hold', 'hold', 'payment_invoice'];

    protected $fillable = [
        'order_id', 'courier', 'consignment_id', 'tracking_code', 'status', 'raw_response',
    ];

    protected function casts(): array
    {
        return [
            'raw_response' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Why the courier refused this booking, in words an operator can act on.
     *
     * Both couriers answer a rejection with a flat "please fix the given
     * errors" and put the actual complaint in a per-field list, which is no use
     * to anyone left sitting in the raw response.
     */
    public function failureReason(): string
    {
        $response = $this->raw_response ?? [];
        $errors = $response['errors'] ?? [];

        $messages = collect(is_array($errors) ? $errors : [])
            ->flatten()
            ->filter()
            ->implode(' ');

        return $messages ?: ($response['message'] ?? 'The courier gave no reason.');
    }

    /**
     * Whether this consignment is still standing at the courier.
     *
     * A booking that never reached the courier keeps no consignment id, and a
     * parcel that was cancelled or came back is finished with -- neither holds
     * the order, so both leave a re-book open. Anything else is a live parcel,
     * and putting a second one against the same order means two deliveries and
     * two charges.
     */
    public function isLive(): bool
    {
        if (blank($this->consignment_id)) {
            return false;
        }

        return ! in_array($this->normalisedStatus(), self::CLOSED_STATUSES, true);
    }

    /**
     * The status as a person reads it. Couriers answer in machine keys --
     * Pathao At_the_Sorting_HUB, Steadfast in_review -- but a rejection is a
     * sentence they already wrote, and that is left alone.
     */
    public function statusLabel(): string
    {
        $status = (string) $this->status;

        return str_contains($status, ' ')
            ? $status
            : ucwords(strtolower(str_replace(['_', '-'], ' ', $status)));
    }

    /**
     * Which badge colour the status earns, so a returned parcel cannot read the
     * same as one out for delivery.
     */
    public function statusTone(): string
    {
        $status = $this->normalisedStatus();

        return match (true) {
            in_array($status, self::DELIVERED_STATUSES, true) => 'success',
            in_array($status, self::CLOSED_STATUSES, true) => 'danger',
            in_array($status, self::HELD_STATUSES, true) => 'warning',
            in_array($status, self::MOVING_STATUSES, true) => 'info',
            default => 'secondary',
        };
    }

    /**
     * What the courier charges to carry this parcel, as quoted when it was
     * booked. Only the booking answer carries it, which is why a status pull
     * merges into the stored response rather than replacing it.
     */
    public function deliveryFee(): ?float
    {
        $fee = ($this->raw_response['data']['delivery_fee'] ?? $this->raw_response['delivery_fee'] ?? null);

        return $fee === null ? null : (float) $fee;
    }

    private function normalisedStatus(): string
    {
        return strtolower(str_replace([' ', '-'], '_', (string) $this->status));
    }
}
