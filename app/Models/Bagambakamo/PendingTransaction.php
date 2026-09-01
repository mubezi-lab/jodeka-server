<?php

namespace App\Models\Bagambakamo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingTransaction extends Model
{
    protected $table =
        'bagambakamo_pending_transactions';

    protected $fillable = [
        'member_id',
        'recipient_name',
        'recipient_phone',
        'reference',
        'amount',
        'transaction_date',
        'account_balance',
        'raw_sms',
        'status',
        'classification',
        'processed_record_id',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'account_balance' => 'decimal:2',
        'transaction_date' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(
            Member::class
        );
    }
}