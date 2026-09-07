<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyStockClosingItem extends Model
{
    protected $fillable = [
        'daily_stock_closing_id','product_id','opening_units','received_units','available_units',
        'closing_units','sold_units','selling_price','average_cost_per_unit','revenue',
        'cost_of_goods_sold','gross_profit','closing_stock_value',
    ];

    public function closing() { return $this->belongsTo(DailyStockClosing::class, 'daily_stock_closing_id'); }
    public function product() { return $this->belongsTo(Product::class); }
}
