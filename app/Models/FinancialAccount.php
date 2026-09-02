<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialAccount extends Model
{
    protected $fillable = [
        'business_id',
        'name',
        'account_type',
        'provider',
        'account_number',
        'opening_balance',
        'opening_balance_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'opening_balance_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function outgoingTransfers()
    {
        return $this->hasMany(FinancialAccountTransfer::class, 'from_financial_account_id');
    }

    public function incomingTransfers()
    {
        return $this->hasMany(FinancialAccountTransfer::class, 'to_financial_account_id');
    }

    public function getCurrentBalanceAttribute(): float
    {
        $movement = (float) $this->journalEntries()
            ->selectRaw('COALESCE(SUM(debit - credit), 0) as balance')
            ->value('balance');

        return round($movement, 2);
    }
}
