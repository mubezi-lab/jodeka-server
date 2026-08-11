<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FixedExpense extends Model
{
    protected $fillable = [
        'business_id',
        'name',
        'default_amount',
        'is_active',
        'notes',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function payments()
    {
        return $this->hasMany(FixedExpensePayment::class);
    }
}