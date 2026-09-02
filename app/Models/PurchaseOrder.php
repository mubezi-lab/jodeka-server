<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = ['order_number','stock_request_id','business_id','supplier','order_date','status','notes','ordered_by'];
    protected $casts = ['order_date' => 'date'];
    public function business() { return $this->belongsTo(Business::class); }
    public function stockRequest() { return $this->belongsTo(StockRequest::class); }
    public function items() { return $this->hasMany(PurchaseOrderItem::class); }
    public function receipts() { return $this->hasMany(GoodsReceipt::class); }
    public function orderedBy() { return $this->belongsTo(User::class, 'ordered_by'); }
}
