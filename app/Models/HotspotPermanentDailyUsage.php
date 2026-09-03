<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotspotPermanentDailyUsage extends Model
{
    protected $fillable = [
        'hotspot_permanent_user_id', 'usage_date', 'first_seen_at',
        'last_seen_at', 'is_online', 'last_ip', 'last_host_id',
        'last_bytes_in', 'last_bytes_out', 'bytes_in', 'bytes_out',
        'last_uptime_seconds',
    ];

    protected $casts = [
        'usage_date' => 'date',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'is_online' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(HotspotPermanentUser::class, 'hotspot_permanent_user_id');
    }
}
