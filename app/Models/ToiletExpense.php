<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ToiletExpense extends Model
{
    protected $fillable = [

        'toilet_daily_entry_id',
        'expense_name',
        'amount',
        'note',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIP
    |--------------------------------------------------------------------------
    */

    public function entry()
    {
        return $this->belongsTo(
            ToiletDailyEntry::class,
            'toilet_daily_entry_id'
        );
    }
}