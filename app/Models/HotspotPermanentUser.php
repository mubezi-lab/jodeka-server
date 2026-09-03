<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HotspotPermanentUser extends Model
{
    protected $fillable = [
        'network_router_id', 'name', 'phone', 'normalized_phone',
        'mac_address', 'user_type', 'daily_rate',
        'usage_threshold_bytes', 'credit_balance', 'enabled',
        'is_online', 'last_ip', 'last_seen_at',
    ];

    protected $casts = [
        'daily_rate' => 'decimal:2',
        'credit_balance' => 'decimal:2',
        'enabled' => 'boolean',
        'is_online' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    public function router(): BelongsTo
    {
        return $this->belongsTo(NetworkRouter::class, 'network_router_id');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(HotspotPermanentDailyUsage::class);
    }

    public function charges(): HasMany
    {
        return $this->hasMany(HotspotPermanentCharge::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(HotspotPermanentReminder::class);
    }
}
