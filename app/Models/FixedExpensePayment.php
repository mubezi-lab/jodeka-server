<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FixedExpensePayment extends Model
{
    protected $fillable = [
        'fixed_expense_id',
        'amount',
        'month',
        'year',
        'paid_at',
        'notes',
    ];

    public function fixedExpense()
    {
        return $this->belongsTo(FixedExpense::class);
    }
}