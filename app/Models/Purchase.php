<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'supplier_id',
        'reference_no',
        'purchase_date',
        'total_amount',
        'tax_amount',
        'shipping_cost',
        'total_discount',
        'paid_amount',
        'due_amount',
        'status',
        'payment_status',
        'notes',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
