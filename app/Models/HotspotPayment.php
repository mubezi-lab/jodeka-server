<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotspotPayment extends Model
{
    protected $fillable = [
        'provider',
        'amount',
        'hotspot_profile_id',
        'payer_phone',
        'payer_name',
        'reference',
        'paid_at',
        'raw_sms',
        'status',
        'voucher_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(HotspotProfile::class, 'hotspot_profile_id');
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(HotspotVoucher::class, 'voucher_id');
    }
}