<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    protected $fillable = [
        'customer_id', 'business_id', 'reference', 'source_type', 'source_id',
        'original_amount', 'balance', 'debt_date', 'due_date', 'status',
        'description', 'journal_id', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'original_amount' => 'decimal:2', 'balance' => 'decimal:2',
            'debt_date' => 'date', 'due_date' => 'date',
        ];
    }

    public function customer() { return $this->belongsTo(Customer::class); }
    public function business() { return $this->belongsTo(Business::class); }
    public function payments() { return $this->hasMany(DebtPayment::class); }
    public function journal() { return $this->belongsTo(Journal::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
