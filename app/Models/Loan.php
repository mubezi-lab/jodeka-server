<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $fillable = [

        'business_id',

        'type',

        'name',

        'phone',

        'amount',

        'paid_amount',

        'remaining_amount',

        'status',

        'loan_date',

        'due_date',

        'description',

        'created_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isPartial()
    {
        return $this->status === 'partial';
    }

    public function payments()
    {
        return $this->hasMany(LoanPayment::class);
    }
}