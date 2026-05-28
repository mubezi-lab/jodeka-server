<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Saving extends Model
{
    /**
     * Fillable
     */
    protected $fillable = [

        'business_id',

        'type',

        'name',

        'description',

        'target_amount',

        'balance',

        'start_date',

        'maturity_date',

        'status',

        'created_by',

    ];

    /**
     * Casts
     */
    protected $casts = [

        'target_amount' => 'decimal:2',

        'balance' => 'decimal:2',

        'start_date' => 'date',

        'maturity_date' => 'date',

    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Business
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Transactions
     */
    public function transactions()
    {
        return $this->hasMany(
            SavingTransaction::class,
            'saving_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Check if target reached
     */
    public function targetReached()
    {
        if (!$this->target_amount) {

            return false;
        }

        return $this->balance >= $this->target_amount;
    }

    /**
     * Remaining target amount
     */
    public function remainingTarget()
    {
        if (!$this->target_amount) {

            return 0;
        }

        return $this->target_amount - $this->balance;
    }
}