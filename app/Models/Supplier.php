<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['supplier_number','name','phone','email','address','is_active'];
    protected $casts = ['is_active'=>'boolean'];
    public function bills() { return $this->hasMany(SupplierBill::class); }
    public function purchaseOrders() { return $this->hasMany(PurchaseOrder::class); }
    public function getOutstandingBalanceAttribute(): float { return (float) $this->bills()->sum('balance'); }
}
