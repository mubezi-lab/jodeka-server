<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = ['purchase_order_id','product_id','ordered_packages','units_per_package','cost_per_package','total_cost'];
    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class); }
    public function product() { return $this->belongsTo(Product::class); }
    public function receiptItems() { return $this->hasMany(GoodsReceiptItem::class); }
}
