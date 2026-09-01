<?php

namespace App\Models\Bagambakamo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberBalance extends Model
{
    protected $table = 'bagambakamo_member_balances';

    protected $fillable = [
        'member_id',
        'total_contribution',
        'total_paid',
        'balance',
    ];

    protected $casts = [
        'total_contribution' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}