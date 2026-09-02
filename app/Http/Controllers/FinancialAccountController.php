<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\FinancialAccount;
use App\Services\FinancialAccountOpeningService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FinancialAccountController extends Controller
{
    public function index()
    {
        $accounts = FinancialAccount::with('business')->orderByRaw('business_id IS NOT NULL')
            ->orderBy('business_id')->orderBy('name')->get();
        $businesses = Business::orderBy('name')->get();

        return view('financial-accounts.index', compact('accounts', 'businesses'));
    }

    public function store(Request $request, FinancialAccountOpeningService $service)
    {
        $data = $this->validated($request);
        $this->ensureUniqueName($data['name'], $data['business_id'] ?? null);
        $service->create($data);

        return back()->with('success', 'Akaunti ya fedha imeongezwa vizuri.');
    }

    public function update(Request $request, FinancialAccount $financialAccount)
    {
        $data = $this->validated($request);
        $this->ensureUniqueName($data['name'], $data['business_id'] ?? null, $financialAccount->id);

        // Opening balances are posted accounting entries and are not edited in place.
        unset($data['opening_balance'], $data['opening_balance_date']);
        $financialAccount->update($data);

        return back()->with('success', 'Akaunti ya fedha imesasishwa.');
    }

    public function toggle(FinancialAccount $financialAccount)
    {
        $financialAccount->update(['is_active' => ! $financialAccount->is_active]);

        return back()->with('success', 'Status ya akaunti imebadilishwa.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'business_id' => ['nullable', 'exists:businesses,id'],
            'name' => ['required', 'string', 'max:255'],
            'account_type' => ['required', Rule::in(['cash', 'bank', 'mobile_money', 'pos', 'clearing'])],
            'provider' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:255'],
            'opening_balance' => ['required', 'numeric', 'decimal:0,2', 'min:0'],
            'opening_balance_date' => ['nullable', 'date'],
        ]);
    }

    private function ensureUniqueName(string $name, ?int $businessId, ?int $ignoreId = null): void
    {
        $query = FinancialAccount::where('name', $name)
            ->where(function ($q) use ($businessId) {
                $businessId === null ? $q->whereNull('business_id') : $q->where('business_id', $businessId);
            });
        if ($ignoreId) $query->whereKeyNot($ignoreId);

        if ($query->exists()) {
            throw ValidationException::withMessages(['name' => 'Jina hili tayari linatumika katika branch hii.']);
        }
    }
}
