<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    protected $fillable = [
        'name',
        'category',
        'package_type',
        'units_per_package',

        'buy_price_per_unit',
        'sell_price_per_unit',
        'buy_price_per_package',
        'sell_price_per_package',
    ];
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
}
