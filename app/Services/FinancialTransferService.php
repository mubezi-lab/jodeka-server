<?php

namespace App\Services;

use App\Models\Account;
use App\Models\FinancialAccount;
use App\Models\FinancialAccountTransfer;
use App\Models\Journal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class FinancialTransferService
{
    public function submit(array $data): FinancialAccountTransfer
    {
        return DB::transaction(function () use ($data): FinancialAccountTransfer {
            $accountIds = [$data['from_financial_account_id'], $data['to_financial_account_id']];
            $accounts = FinancialAccount::whereIn('id', $accountIds)->lockForUpdate()->get()->keyBy('id');
            $from = $accounts->get($data['from_financial_account_id']);
            $to = $accounts->get($data['to_financial_account_id']);

            if (! $from || ! $to || ! $from->is_active || ! $to->is_active) {
                throw ValidationException::withMessages(['account' => 'Akaunti iliyochaguliwa haipatikani au haiko active.']);
            }
            if ($from->id === $to->id) {
                throw ValidationException::withMessages(['to_financial_account_id' => 'Akaunti ya kupokea lazima iwe tofauti.']);
            }
            if ((float) $data['amount'] > $from->current_balance) {
                throw ValidationException::withMessages(['amount' => 'Kiasi kinazidi salio la akaunti inayotoa fedha.']);
            }

            return FinancialAccountTransfer::create($data + [
                'transfer_number' => 'TRF-'.now()->format('Ymd').'-'.strtoupper(Str::random(8)),
                'status' => 'pending',
                'submitted_by' => auth()->id(),
            ]);
        });
    }

    public function confirm(FinancialAccountTransfer $transfer, float $confirmedAmount, ?string $reviewNotes): FinancialAccountTransfer
    {
        return DB::transaction(function () use ($transfer, $confirmedAmount, $reviewNotes): FinancialAccountTransfer {
            $lockedTransfer = FinancialAccountTransfer::lockForUpdate()->findOrFail($transfer->id);
            if ($lockedTransfer->status !== 'pending') {
                throw ValidationException::withMessages(['transfer' => 'Handover hii imeshafanyiwa uamuzi.']);
            }
            $accounts = FinancialAccount::whereIn('id', [
                $lockedTransfer->from_financial_account_id,
                $lockedTransfer->to_financial_account_id,
            ])->lockForUpdate()->get()->keyBy('id');
            $from = $accounts->get($lockedTransfer->from_financial_account_id);
            $to = $accounts->get($lockedTransfer->to_financial_account_id);
            if (! $from || ! $to || ! $from->is_active || ! $to->is_active) {
                throw ValidationException::withMessages(['account' => 'Akaunti ya handover haipatikani au haiko active.']);
            }
            if ($confirmedAmount > $from->current_balance) {
                throw ValidationException::withMessages(['confirmed_amount' => 'Kiasi kilichopokelewa kinazidi salio la branch.']);
            }

            $cashAccount = Account::where('code', '1000')->where('is_active', true)->first()
                ?? throw new RuntimeException('Accounting account 1000 haijapatikana.');

            $journal = Journal::create([
                'business_id' => $from->business_id,
                'journal_number' => 'JRN-'.now()->format('Ymd').'-'.strtoupper(Str::random(8)),
                'journal_date' => $lockedTransfer->transfer_date,
                'source_type' => 'financial_account_transfer',
                'source_id' => $lockedTransfer->id,
                'description' => "Handover {$lockedTransfer->transfer_number}: {$from->name} kwenda {$to->name}",
                'status' => 'posted',
                'created_by' => auth()->id(),
                'posted_by' => auth()->id(),
                'posted_at' => now(),
            ]);

            $journal->entries()->createMany([
                ['account_id' => $cashAccount->id, 'financial_account_id' => $to->id, 'debit' => $confirmedAmount, 'credit' => 0],
                ['account_id' => $cashAccount->id, 'financial_account_id' => $from->id, 'debit' => 0, 'credit' => $confirmedAmount],
            ]);
            $lockedTransfer->update([
                'confirmed_amount' => $confirmedAmount,
                'variance' => $confirmedAmount - (float) $lockedTransfer->amount,
                'status' => 'confirmed',
                'review_notes' => $reviewNotes,
                'journal_id' => $journal->id,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            return $lockedTransfer;
        });
    }

    public function reject(FinancialAccountTransfer $transfer, string $reviewNotes): FinancialAccountTransfer
    {
        return DB::transaction(function () use ($transfer, $reviewNotes): FinancialAccountTransfer {
            $lockedTransfer = FinancialAccountTransfer::lockForUpdate()->findOrFail($transfer->id);
            if ($lockedTransfer->status !== 'pending') {
                throw ValidationException::withMessages(['transfer' => 'Handover hii imeshafanyiwa uamuzi.']);
            }
            $lockedTransfer->update([
                'status' => 'rejected', 'review_notes' => $reviewNotes,
                'reviewed_by' => auth()->id(), 'reviewed_at' => now(),
            ]);

            return $lockedTransfer;
        });
    }
}
