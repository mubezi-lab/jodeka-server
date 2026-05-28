<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanPayment extends Model
{
    protected $fillable = [

        'loan_id',

        'amount',

        'payment_date',

        'payment_method',

        'notes',

        'created_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}