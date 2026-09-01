<?php

namespace App\Models\Bagambakamo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $table =
        'bagambakamo_expenses';

    protected $fillable = [
        'member_id',
        'category',
        'description',
        'amount',
        'expense_date',
        'reference',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(
            Member::class
        );
    }
}