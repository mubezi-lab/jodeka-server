<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesBusinessAccess;
use App\Models\FinancialAccount;
use App\Models\FinancialAccountTransfer;
use App\Services\FinancialTransferService;
use Illuminate\Http\Request;

class FinancialAccountTransferController extends Controller
{
    use AuthorizesBusinessAccess;

    public function index()
    {
        $user = request()->user();
        $accounts = FinancialAccount::with('business')->where('is_active', true)
            ->when($user->role?->name !== 'admin', function ($q) {
                $ids = $this->accessibleBusinesses()->pluck('id');
                $q->where(fn ($a) => $a->whereNull('business_id')->orWhereIn('business_id', $ids));
            })->orderByRaw('business_id IS NOT NULL')->orderBy('name')->get();

        $sourceAccountIds = $user->role?->name === 'admin'
            ? $accounts->pluck('id')
            : $accounts->whereNotNull('business_id')->pluck('id');
        $transfers = FinancialAccountTransfer::with(['fromAccount.business', 'toAccount.business', 'submitter', 'reviewer'])
            ->whereIn('from_financial_account_id', $sourceAccountIds)
            ->latest('transfer_date')->latest('id')->paginate(25);

        return view('financial-account-transfers.index', compact('accounts', 'transfers'));
    }

    public function store(Request $request, FinancialTransferService $service)
    {
        $data = $request->validate([
            'from_financial_account_id' => ['required', 'exists:financial_accounts,id'],
            'to_financial_account_id' => ['required', 'different:from_financial_account_id', 'exists:financial_accounts,id'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:1'],
            'transfer_date' => ['required', 'date'],
            'external_reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $from = FinancialAccount::findOrFail($data['from_financial_account_id']);
        $to = FinancialAccount::findOrFail($data['to_financial_account_id']);
        if (request()->user()->role?->name !== 'admin') {
            abort_if($from->business_id === null, 403, 'Manager hawezi kutoa fedha kutoka akaunti kuu.');
            $this->authorizeBusiness($from->business_id);
            if ($to->business_id !== null) {
                $this->authorizeBusiness($to->business_id);
            }
        }
        $service->submit($data);

        return back()->with('success', 'Handover imetumwa; inasubiri manager/admin kuthibitisha.');
    }

    public function confirm(Request $request, FinancialAccountTransfer $transfer, FinancialTransferService $service)
    {
        $this->authorizeReview($transfer);
        $data = $request->validate([
            'confirmed_amount' => ['required', 'numeric', 'decimal:0,2', 'min:1'],
            'review_notes' => ['nullable', 'string'],
        ]);
        if (round((float) $data['confirmed_amount'], 2) !== round((float) $transfer->amount, 2)
            && empty(trim((string) ($data['review_notes'] ?? '')))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'review_notes' => 'Eleza sababu ya tofauti kati ya kiasi kilichotumwa na kilichopokelewa.',
            ]);
        }
        $service->confirm($transfer, (float) $data['confirmed_amount'], $data['review_notes'] ?? null);

        return back()->with('success', 'Handover imethibitishwa na balances zimesasishwa.');
    }

    public function reject(Request $request, FinancialAccountTransfer $transfer, FinancialTransferService $service)
    {
        $this->authorizeReview($transfer);
        $data = $request->validate(['review_notes' => ['required', 'string', 'min:3']]);
        $service->reject($transfer, $data['review_notes']);

        return back()->with('success', 'Handover imekataliwa bila kubadilisha balances.');
    }

    private function authorizeReview(FinancialAccountTransfer $transfer): void
    {
        if (request()->user()->role?->name === 'admin') return;
        $from = $transfer->fromAccount;
        abort_if($from->business_id === null, 403);
        $this->authorizeBusiness($from->business_id);
    }
}
