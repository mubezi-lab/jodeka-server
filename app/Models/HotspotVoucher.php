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
        'generated_at',
        'used_at',
        'generated_by',
        'comment',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'used_at' => 'datetime',
    ];

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