<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToiletDailyEntry extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    protected $table = 'toilet_daily_entries';

    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'toilet_id',

        'opening_balance',

        'closing_balance',

        'total_expenses',

        'total_revenue',

        'pos_amount',

        'daily_pos_collection',

        'note',

        'entry_date',

        'is_closed',
    ];

    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'entry_date' => 'date',

        'is_closed' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | TOILET RELATIONSHIP
    |--------------------------------------------------------------------------
    */

    public function toilet()
    {
        return $this->belongsTo(
            Toilet::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EXPENSES RELATIONSHIP
    |--------------------------------------------------------------------------
    */

    public function expenses()
    {
        return $this->hasMany(
            ToiletExpense::class
        );
    }
}