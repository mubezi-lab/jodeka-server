<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotspotCustomerInvitation extends Model
{
    protected $fillable = [
        'phone',
        'campaign_date',
        'message',
        'status',
        'attempts',
        'sent_at',
        'failed_at',
        'error',
        'response',
    ];

    protected $casts = [
        'campaign_date' => 'date',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
        'response' => 'array',
    ];
}
