<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Toilet extends Model
{
    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNABLE
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'name',

        'location',

        'type',

        'monthly_fee',

        'council_percentage',

        'office_percentage',

        'user_id',

        'is_active',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIP WITH USER
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dailyEntries()
    {
        return $this->hasMany(ToiletDailyEntry::class);
    }
}