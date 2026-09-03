<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotspotPermanentPayment extends Model
{
    protected $fillable = [
        'hotspot_permanent_user_id', 'hotspot_payment_id', 'method',
        'reference', 'amount', 'allocated_amount', 'credit_amount',
        'paid_at', 'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'allocated_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];
}
