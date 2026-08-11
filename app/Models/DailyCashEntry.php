<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyCashEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_date',

        'yas',
        'voda',
        'halotel',
        'airtel',
        'token',
        'noti',

        'opening_balance',
        'closing_balance',

        'expenses_total',
        'external_total',

        'shop_income',
        'raw_input',
        'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',

        'yas' => 'decimal:2',
        'voda' => 'decimal:2',
        'halotel' => 'decimal:2',
        'airtel' => 'decimal:2',
        'token' => 'decimal:2',
        'noti' => 'decimal:2',

        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',

        'expenses_total' => 'decimal:2',
        'external_total' => 'decimal:2',

        'shop_income' => 'decimal:2',
    ];

    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}