<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = [
        'journal_id',
        'account_id',
        'financial_account_id',
        'debit',
        'credit',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
        ];
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function financialAccount()
    {
        return $this->belongsTo(FinancialAccount::class);
    }
}
