<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotspotPermanentCharge extends Model
{
    protected $fillable = [
        'hotspot_permanent_user_id', 'hotspot_permanent_daily_usage_id',
        'charge_date', 'amount', 'paid_amount', 'status', 'paid_at',
    ];

    protected $casts = [
        'charge_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(HotspotPermanentUser::class, 'hotspot_permanent_user_id');
    }
}
