<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockRequest extends Model
{
    protected $fillable = ['request_number','business_id','request_date','status','notes','requested_by','reviewed_by','reviewed_at','review_notes'];
    protected $casts = ['request_date' => 'date', 'reviewed_at' => 'datetime'];
    public function business() { return $this->belongsTo(Business::class); }
    public function items() { return $this->hasMany(StockRequestItem::class); }
    public function requester() { return $this->belongsTo(User::class, 'requested_by'); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function purchaseOrder() { return $this->hasOne(PurchaseOrder::class); }
}
