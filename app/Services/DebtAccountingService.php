<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\Journal;
use Illuminate\Support\Str;
use RuntimeException;

class DebtAccountingService
{
    public function postDebt(Debt $debt): Journal
    {
        $journal = $this->journal($debt->business_id, $debt->debt_date, 'debt', $debt->id,
            "Deni {$debt->reference} - {$debt->customer->name}");

        $journal->entries()->createMany([
            ['account_id' => $this->account('1100')->id, 'debit' => $debt->original_amount, 'credit' => 0],
            ['account_id' => $this->account('4000')->id, 'debit' => 0, 'credit' => $debt->original_amount],
        ]);

        $debt->update(['journal_id' => $journal->id]);

        return $journal;
    }

    public function postPayment(DebtPayment $payment): Journal
    {
        $debt = $payment->debt;
        $journal = $this->journal($debt->business_id, $payment->payment_date, 'debt_payment', $payment->id,
            "Malipo {$payment->payment_number} ya deni {$debt->reference}");

        $journal->entries()->createMany([
            [
                'account_id' => $this->account('1000')->id,
                'financial_account_id' => $payment->financial_account_id,
                'debit' => $payment->amount,
                'credit' => 0,
            ],
            ['account_id' => $this->account('1100')->id, 'debit' => 0, 'credit' => $payment->amount],
        ]);

        $payment->update(['journal_id' => $journal->id]);

        return $journal;
    }

    private function journal(int $businessId, $date, string $sourceType, int $sourceId, string $description): Journal
    {
        return Journal::create([
            'business_id' => $businessId,
            'journal_number' => 'JRN-'.now()->format('Ymd').'-'.strtoupper(Str::random(8)),
            'journal_date' => $date,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'description' => $description,
            'status' => 'posted',
            'created_by' => auth()->id(),
            'posted_by' => auth()->id(),
            'posted_at' => now(),
        ]);
    }

    private function account(string $code): Account
    {
        return Account::where('code', $code)->where('is_active', true)->first()
            ?? throw new RuntimeException("Accounting account {$code} haijapatikana.");
    }
}
