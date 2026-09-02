<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceipt extends Model
{
    protected $fillable = ['receipt_number','purchase_order_id','receipt_date','status','notes','received_by'];
    protected $casts = ['receipt_date' => 'date'];
    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class); }
    public function items() { return $this->hasMany(GoodsReceiptItem::class); }
    public function receiver() { return $this->belongsTo(User::class, 'received_by'); }
}
