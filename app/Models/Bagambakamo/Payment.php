<?php

namespace App\Models\Bagambakamo;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $table = 'bagambakamo_payments';

    protected $fillable = [
        'member_id',
        'amount',
        'type',
        'description',
        'payment_date',
        'method',
        'reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function scopeMonthly(Builder $query): Builder
    {
        return $query->where(
            'type',
            'monthly'
        );
    }

    public function scopeMchango(Builder $query): Builder
    {
        return $query->where(
            'type',
            'mchango'
        );
    }
}