<?php

namespace App\Services;

use App\Models\Account;
use App\Models\FinancialAccount;
use App\Models\Journal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class FinancialAccountOpeningService
{
    public function create(array $data): FinancialAccount
    {
        return DB::transaction(function () use ($data): FinancialAccount {
            $account = FinancialAccount::create($data + ['is_active' => true]);
            $amount = (float) $account->opening_balance;

            if ($amount <= 0) {
                return $account;
            }

            $cash = Account::where('code', '1000')->where('is_active', true)->first();
            $capital = Account::where('code', '3000')->where('is_active', true)->first();
            if (! $cash || ! $capital) {
                throw new RuntimeException('Accounts 1000 na 3000 zinahitajika kwa opening balance.');
            }

            $journal = Journal::create([
                'business_id' => $account->business_id,
                'journal_number' => 'JRN-'.now()->format('Ymd').'-'.strtoupper(Str::random(8)),
                'journal_date' => $account->opening_balance_date ?? today(),
                'source_type' => 'financial_account_opening',
                'source_id' => $account->id,
                'description' => "Opening balance - {$account->name}",
                'status' => 'posted',
                'created_by' => auth()->id(),
                'posted_by' => auth()->id(),
                'posted_at' => now(),
            ]);
            $journal->entries()->createMany([
                ['account_id' => $cash->id, 'financial_account_id' => $account->id, 'debit' => $amount, 'credit' => 0],
                ['account_id' => $capital->id, 'debit' => 0, 'credit' => $amount],
            ]);

            return $account;
        });
    }
}
