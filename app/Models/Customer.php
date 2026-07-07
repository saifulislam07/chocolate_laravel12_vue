<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'address', 'division_id', 'district_id', 'balance'];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
        ];
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Add store-credit to this customer's wallet (e.g. a return refund).
     */
    public function credit(float $amount): void
    {
        DB::transaction(function () use ($amount) {
            static::whereKey($this->id)->lockForUpdate()->increment('balance', $amount);
        });
        $this->refresh();
    }

    /**
     * Deduct store-credit from this customer's wallet (e.g. spent on an order).
     */
    public function debit(float $amount): void
    {
        DB::transaction(function () use ($amount) {
            static::whereKey($this->id)->lockForUpdate()->decrement('balance', $amount);
        });
        $this->refresh();
    }
}
