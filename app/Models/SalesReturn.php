<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SalesReturn extends Model
{
    protected $fillable = [
        'return_number', 'order_id', 'customer_id', 'return_source',
        'reason', 'subtotal_refund', 'status', 'processed_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_refund' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    public function refund(): HasOne
    {
        return $this->hasOne(ReturnRefund::class);
    }
}
