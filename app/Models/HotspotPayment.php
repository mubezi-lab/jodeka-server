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
        'claimed_at',
        'claimed_by_mac',
        'claimed_by_ip',
        'voucher_sms_status',
        'voucher_sms_sent_at',
        'voucher_sms_failed_at',
        'voucher_sms_error',
        'voucher_sms_attempts',
        'voucher_sms_response',
        'hotspot_customer_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'claimed_at' => 'datetime',
        'voucher_sms_sent_at' => 'datetime',
        'voucher_sms_failed_at' => 'datetime',
        'voucher_sms_response' => 'array',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(HotspotProfile::class, 'hotspot_profile_id');
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(HotspotVoucher::class, 'voucher_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(HotspotCustomer::class, 'hotspot_customer_id');
    }
}
