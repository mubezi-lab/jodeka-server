<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

    class Stock extends Model
    {
        //
    protected $fillable = [
        'product_id',
        'business_id',
        'opening_stock',
        'purchased',
        'total_stock',
        'closing_stock',
        'sold',
        'price', // 🔥 ADD THIS
        'sales_amount',
        'profit',
        'date',
    ];


    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
