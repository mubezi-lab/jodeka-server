<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotspotVoucher extends Model
{
    protected $fillable = [
        'network_router_id',
        'hotspot_profile_id',
        'username',
        'password',
        'price',
        'status',
        'source',
        'generated_at',
        'sold_at',
        'used_at',
        'first_login_at',
        'expires_at',
        'last_seen_at',
        'used_by_mac',
        'used_by_ip',

        'bytes_in',
        'bytes_out',
        'packets_in',
        'packets_out',
        'mikrotik_uptime',
        'last_synced_at',

        'disabled_at',
        'generated_by',
        'comment',
    ];

    protected $casts = [
        'price' => 'decimal:2',

        'bytes_in' => 'integer',
        'bytes_out' => 'integer',
        'packets_in' => 'integer',
        'packets_out' => 'integer',

        'generated_at' => 'datetime',
        'sold_at' => 'datetime',
        'used_at' => 'datetime',
        'first_login_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'disabled_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | TOTAL DATA USAGE
    |--------------------------------------------------------------------------
    */

    public function getTotalUsageBytesAttribute(): int
    {
        return (int) ($this->bytes_in ?? 0)
            + (int) ($this->bytes_out ?? 0);
    }

    /*
    |--------------------------------------------------------------------------
    | EQUIVALENT MOBILE DATA VALUE
    |--------------------------------------------------------------------------
    |
    | Returns the lowest bundle cost that can cover the total
    | amount of data used by this voucher.
    |
    */

    public function getDataValueAttribute(): int
    {
        $totalBytes = $this->total_usage_bytes;

        if ($totalBytes <= 0) {
            return 0;
        }

        /*
        |--------------------------------------------------------------------------
        | CONVERT BYTES TO MB
        |--------------------------------------------------------------------------
        */

        $totalMb = $totalBytes / 1048576;

        /*
        |--------------------------------------------------------------------------
        | MOBILE DATA RATE
        |--------------------------------------------------------------------------
        |
        | TZS 500 = 246 MB
        |
        | Therefore:
        | 1 MB = 500 / 246
        |
        */

        $pricePerMb = 500 / 246;

        /*
        |--------------------------------------------------------------------------
        | CALCULATE ACTUAL DATA VALUE
        |--------------------------------------------------------------------------
        */

        return (int) round(
            $totalMb * $pricePerMb
        );
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(
            NetworkRouter::class,
            'network_router_id'
        );
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(
            HotspotProfile::class,
            'hotspot_profile_id'
        );
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'generated_by'
        );
    }
}