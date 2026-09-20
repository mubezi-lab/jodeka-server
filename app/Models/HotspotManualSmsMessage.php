<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotspotManualSmsMessage extends Model
{
    protected $fillable = [
        'hotspot_customer_id', 'phone', 'normalized_phone', 'message', 'status', 'attempts',
        'requested_by', 'sent_at', 'failed_at', 'error', 'response',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
        'response' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(HotspotCustomer::class, 'hotspot_customer_id');
    }
}
