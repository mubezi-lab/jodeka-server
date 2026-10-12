<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PalmPesaCallbackEvent extends Model
{
    protected $table = 'palmpesa_callback_events';

    protected $fillable = [
        'palmpesa_transaction_id',
        'event_id',
        'event',
        'status',
        'payload',
        'received_at',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(
            PalmPesaTransaction::class,
            'palmpesa_transaction_id'
        );
    }
}