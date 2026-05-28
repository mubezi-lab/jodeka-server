<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingTransaction extends Model
{
    /**
     * Fillable
     */
    protected $fillable = [

        'saving_id',

        'type',

        'amount',

        'payment_method',

        'description',

        'transaction_date',

        'created_by',

    ];

    /**
     * Casts
     */
    protected $casts = [

        'amount' => 'decimal:2',

        'transaction_date' => 'date',

    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Saving
     */

    /**
     * Saving Account
     */
    public function savingAccount()
    {
        return $this->belongsTo(\App\Models\Saving::class, 'saving_id');
    }



    /**
     * Creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Check if deposit
     */
    public function isDeposit()
    {
        return $this->type === 'deposit';
    }

    /**
     * Check if withdrawal
     */
    public function isWithdrawal()
    {
        return $this->type === 'withdrawal';
    }
}