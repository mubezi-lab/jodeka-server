<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyStockClosing extends Model
{
    protected $fillable = ['business_id','closing_date','status','notes','recorded_by','submitted_at'];
    protected $casts = ['closing_date' => 'date', 'submitted_at' => 'datetime'];

    public function business() { return $this->belongsTo(Business::class); }
    public function items() { return $this->hasMany(DailyStockClosingItem::class); }
    public function recorder() { return $this->belongsTo(User::class, 'recorded_by'); }
    public function autoRequest() { return $this->hasOne(StockRequest::class); }
}
