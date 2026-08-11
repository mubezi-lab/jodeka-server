<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NetworkRouter extends Model
{
    protected $fillable = [
        'name',
        'host',
        'api_port',
        'username',
        'password',
        'use_ssl',
        'enabled',
        'location',
        'description',
    ];

    public function hotspotProfiles(): HasMany
    {
        return $this->hasMany(HotspotProfile::class);
    }

    public function hotspotVouchers(): HasMany
    {
        return $this->hasMany(HotspotVoucher::class);
    }
}