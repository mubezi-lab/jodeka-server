<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotspotManualSmsMessage extends Model
{
    protected $fillable = [
        'phone', 'normalized_phone', 'message', 'status', 'attempts',
        'requested_by', 'sent_at', 'failed_at', 'error', 'response',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
        'response' => 'array',
    ];
}
