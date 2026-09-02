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
}
