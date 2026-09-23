<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BpayTransaction extends Model
{
    protected $fillable = [
        'transaction_id',
        'reference_number',
        'amount',
        'mobile',
        'email',
        'purpose',
        'status',
        'checkout_url',
        'beem_timestamp',
        'callback_received_at',
        'paid_at',
        'failed_at',
        'metadata',
        'raw_callback',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'beem_timestamp' => 'datetime',
        'callback_received_at' => 'datetime',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
        'metadata' => 'array',
        'raw_callback' => 'array',
    ];
}