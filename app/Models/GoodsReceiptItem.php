<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceiptItem extends Model
{
    protected $fillable = ['goods_receipt_id','purchase_order_item_id','received_packages','purchase_id','notes'];
    public function goodsReceipt() { return $this->belongsTo(GoodsReceipt::class); }
    public function orderItem() { return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id'); }
    public function purchase() { return $this->belongsTo(Purchase::class); }
}
