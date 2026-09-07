<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierPayment extends Model
{
    protected $fillable = ['supplier_bill_id','payment_number','financial_account_id','amount','payment_date','payment_method','external_reference','notes','journal_id','paid_by'];
    protected $casts = ['payment_date'=>'date','amount'=>'decimal:2'];
    public function bill() { return $this->belongsTo(SupplierBill::class, 'supplier_bill_id'); }
    public function financialAccount() { return $this->belongsTo(FinancialAccount::class); }
    public function journal() { return $this->belongsTo(Journal::class); }
}
