<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryReorderPolicy extends Model
{
    protected $fillable = ['business_id','product_id','minimum_units','target_units','is_active','updated_by'];
    protected $casts = ['is_active' => 'boolean'];

    public function business() { return $this->belongsTo(Business::class); }
    public function product() { return $this->belongsTo(Product::class); }
}
