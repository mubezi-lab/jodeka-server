<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebtPayment extends Model
{
    protected $fillable = [
        'debt_id', 'payment_number', 'financial_account_id', 'amount',
        'payment_date', 'payment_method', 'external_reference', 'notes',
        'journal_id', 'received_by',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'payment_date' => 'date'];
    }

    public function debt() { return $this->belongsTo(Debt::class); }
    public function financialAccount() { return $this->belongsTo(FinancialAccount::class); }
    public function journal() { return $this->belongsTo(Journal::class); }
    public function receiver() { return $this->belongsTo(User::class, 'received_by'); }
}
