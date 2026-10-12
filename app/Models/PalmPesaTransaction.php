<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PalmPesaTransaction extends Model
{
    protected $table = 'palmpesa_transactions';

    protected $fillable = [
        'hotspot_profile_id',
        'hotspot_payment_id',
        'transaction_id',
        'order_id',
        'amount',
        'currency',
        'payer_phone',
        'payer_name',
        'payer_email',
        'status',
        'reference',
        'channel',
        'failure_reason',
        'request_payload',
        'response_payload',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'request_payload' => 'array',
        'response_payload' => 'array',
        'completed_at' => 'datetime',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(
            HotspotProfile::class,
            'hotspot_profile_id'
        );
    }

    public function hotspotPayment(): BelongsTo
    {
        return $this->belongsTo(
            HotspotPayment::class,
            'hotspot_payment_id'
        );
    }

    public function callbackEvents(): HasMany
    {
        return $this->hasMany(
            PalmPesaCallbackEvent::class,
            'palmpesa_transaction_id'
        );
    }

    public function isCompleted(): bool
    {
        return $this->status === 'COMPLETED';
    }
}