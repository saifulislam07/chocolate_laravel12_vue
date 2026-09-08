<?php

namespace App\Models;

use App\Services\AmountInWords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /**
     * The channels a sale can be attributed to, in the order they appear in the
     * POS dropdown. Kept here so the form, the validation rule and any report
     * that groups by channel all read from one list.
     */
    public const LEAD_SOURCES = [
        'Facebook',
        'Instagram',
        'WhatsApp',
        'Messenger',
        'Direct Call',
        'Walk-in',
        'Referral',
        'Other',
    ];

    /**
     * The statuses whose money has actually reached the shop. A web order pays
     * out once the courier delivers it; a POS sale is settled at the counter,
     * which is what 'completed' means.
     *
     * Returned orders stay on this list because a return here is raised against
     * a delivered order (see SalesReturnController) -- the money did arrive and
     * then went back out. Dropping them would subtract the refund twice, since
     * every report that shows returns already takes them off separately.
     */
    public const REVENUE_STATUSES = ['delivered', 'completed', 'partially_returned', 'returned'];

    /**
     * The shop's share of an order. The shipping charge is collected on the
     * courier's behalf and handed straight back to it, so it is never income.
     */
    public const NET_REVENUE_SQL = '(orders.total - orders.shipping_cost)';

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_id',
        'status',
        'delivered_at',
        'subtotal',
        'discount',
        'tax',
        'shipping_cost',
        'total',
        'paid_amount',
        'due_amount',
        'payment_method',
        'payment_status',
        'order_source',
        'lead_source',
        'shipping_address',
        'customer_phone',
        'customer_name',
        'division_id',
        'district_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'delivered_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_amount' => 'decimal:2',
        ];
    }

    /**
     * Orders whose money the shop has actually received.
     */
    public function scopeRevenueEarning(Builder $query): Builder
    {
        // Qualified: the area report joins customers, which carries columns of
        // its own that would otherwise make this ambiguous.
        return $query->whereIn($query->qualifyColumn('status'), self::REVENUE_STATUSES);
    }

    /**
     * Narrows to money received inside a period. Revenue is dated by delivery,
     * not by when the order was placed -- an order taken in March and delivered
     * in April is April's money.
     */
    public function scopeDeliveredBetween(Builder $query, string $start, string $end): Builder
    {
        return $query->whereBetween($query->qualifyColumn('delivered_at'), [$start, $end]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SalesReturn::class);
    }

    /**
     * What the order is worth to the shop: the total less the shipping charge
     * the courier keeps. Kept out of $appends -- only the screens that report
     * on revenue ask for it.
     */
    protected function netRevenue(): Attribute
    {
        return Attribute::get(fn (): float => (float) $this->total - (float) $this->shipping_cost);
    }

    /**
     * The total spelled out, for the line every invoice carries under its
     * figures. Not in $appends: only the invoice asks for it.
     */
    protected function totalInWords(): Attribute
    {
        return Attribute::get(fn (): string => (new AmountInWords())->format($this->total));
    }

    /**
     * What brought the sale in. A POS operator records where the customer came
     * from -- Facebook, a referral, a walk-in -- and that is the answer worth
     * reading. A web order has no such answer, so it falls back to naming the
     * channel it arrived through.
     *
     * Kept out of $appends for the same reason as shippingStatus below: only
     * the screens that show it should pay for it.
     */
    protected function sourceLabel(): Attribute
    {
        return Attribute::get(fn (): string => $this->lead_source ?: strtoupper((string) $this->order_source));
    }

    /**
     * Where the parcel has got to, as one word the sales list can show and sort
     * on. An order can be handed to a courier more than once (a re-book after a
     * failed delivery), so the newest shipment is the one that speaks for it.
     *
     * Deliberately not in $appends: it needs `shipments` loaded, and every
     * screen that does not show it should not pay for the query.
     */
    protected function shippingStatus(): Attribute
    {
        return Attribute::get(fn (): string => $this->shipments
            ->sortByDesc('id')
            ->first()?->status ?? 'not_shipped');
    }
}
