<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'product_id',
        'business_id',
        'quantity',
        'unit_cost',   // 🔥 HII ILIKUWA INAKOSEKANA
        'total_cost',
        'date',
        'supplier',
        'notes',
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