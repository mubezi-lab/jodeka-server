<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HotspotProfile extends Model
{
    protected $fillable = [
        'network_router_id',
        'name',
        'mikrotik_profile',
        'price',
        'validity_hours',
        'validity_value',
        'validity_unit',
        'voucher_prefix',
        'enabled',
        'description',
    ];

    public function router(): BelongsTo
    {
        return $this->belongsTo(NetworkRouter::class, 'network_router_id');
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(
            HotspotVoucher::class,
            'hotspot_profile_id'
        );
    }
}