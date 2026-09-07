<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierBill extends Model
{
    protected $fillable = ['bill_number','supplier_id','business_id','goods_receipt_id','original_amount','balance','bill_date','due_date','status','journal_id','created_by'];
    protected $casts = ['bill_date'=>'date','due_date'=>'date','original_amount'=>'decimal:2','balance'=>'decimal:2'];
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function business() { return $this->belongsTo(Business::class); }
    public function goodsReceipt() { return $this->belongsTo(GoodsReceipt::class); }
    public function payments() { return $this->hasMany(SupplierPayment::class); }
    public function journal() { return $this->belongsTo(Journal::class); }
}
