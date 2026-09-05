<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotspotCustomerMessage extends Model
{
    protected $fillable = [
        'hotspot_customer_id', 'campaign_date', 'message_type', 'message',
        'status', 'attempts', 'sent_at', 'failed_at', 'error', 'response',
    ];

    protected $casts = [
        'campaign_date' => 'date',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
        'response' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(HotspotCustomer::class);
    }
}
