<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotspotManualSmsMessage extends Model
{
    protected $fillable = [
        'hotspot_customer_id', 'hotspot_voucher_id', 'message_type', 'phone',
        'normalized_phone', 'message', 'status', 'attempts', 'requested_by',
        'sent_at', 'failed_at', 'error', 'response', 'beem_request_id',
        'delivery_status', 'delivered_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
        'delivered_at' => 'datetime',
        'response' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(HotspotCustomer::class, 'hotspot_customer_id');
    }
}
