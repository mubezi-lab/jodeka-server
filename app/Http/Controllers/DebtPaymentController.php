<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesBusinessAccess;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\FinancialAccount;
use App\Services\DebtAccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DebtPaymentController extends Controller
{
    use AuthorizesBusinessAccess;

    public function store(Request $request, Debt $debt, DebtAccountingService $accounting)
    {
        $this->authorizeBusiness($debt->business_id);
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:1'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', Rule::in(['cash', 'bank', 'mpesa', 'airtel_money', 'mixx', 'halopesa', 'pos'])],
            'financial_account_id' => ['nullable', 'exists:financial_accounts,id'],
            'external_reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        if (! empty($data['financial_account_id'])) {
            $validAccount = FinancialAccount::whereKey($data['financial_account_id'])
                ->where(fn ($q) => $q->whereNull('business_id')->orWhere('business_id', $debt->business_id))
                ->exists();
            abort_unless($validAccount, 403, 'Akaunti hii haitumiki kwenye branch hii.');
        }

        DB::transaction(function () use ($debt, $data, $accounting): void {
            $lockedDebt = Debt::lockForUpdate()->findOrFail($debt->id);
            if ($data['amount'] > $lockedDebt->balance) {
                throw ValidationException::withMessages([
                    'amount' => 'Malipo hayawezi kuzidi salio la deni.',
                ]);
            }

            $payment = DebtPayment::create($data + [
                'debt_id' => $lockedDebt->id,
                'payment_number' => 'PAY-'.now()->format('Ymd').'-'.strtoupper(Str::random(8)),
                'received_by' => auth()->id(),
            ]);

            $balanceCents = (int) round((float) $lockedDebt->balance * 100);
            $paymentCents = (int) round((float) $data['amount'] * 100);
            $newBalance = ($balanceCents - $paymentCents) / 100;
            $lockedDebt->update([
                'balance' => $newBalance,
                'status' => $newBalance <= 0 ? 'paid' : 'partial',
            ]);
            $accounting->postPayment($payment->load('debt'));
        });

        return back()->with('success', 'Malipo ya deni yamepokelewa vizuri.');
    }
}
