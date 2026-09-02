<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialAccountTransfer extends Model
{
    protected $fillable = [
        'transfer_number', 'from_financial_account_id', 'to_financial_account_id',
        'amount', 'confirmed_amount', 'variance', 'transfer_date', 'external_reference',
        'notes', 'status', 'review_notes', 'journal_id', 'submitted_by',
        'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2', 'confirmed_amount' => 'decimal:2',
            'variance' => 'decimal:2', 'transfer_date' => 'date', 'reviewed_at' => 'datetime',
        ];
    }

    public function fromAccount() { return $this->belongsTo(FinancialAccount::class, 'from_financial_account_id'); }
    public function toAccount() { return $this->belongsTo(FinancialAccount::class, 'to_financial_account_id'); }
    public function journal() { return $this->belongsTo(Journal::class); }
    public function submitter() { return $this->belongsTo(User::class, 'submitted_by'); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
}
