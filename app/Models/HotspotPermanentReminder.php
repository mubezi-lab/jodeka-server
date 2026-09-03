<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotspotPermanentReminder extends Model
{
    protected $fillable = [
        'hotspot_permanent_user_id', 'reminder_date', 'reminder_type',
        'message', 'status', 'attempts', 'sent_at', 'failed_at',
        'error', 'response',
    ];

    protected $casts = [
        'reminder_date' => 'date',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
        'response' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(HotspotPermanentUser::class, 'hotspot_permanent_user_id');
    }
}
