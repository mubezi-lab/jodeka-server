<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesBusinessAccess;
use App\Models\Customer;
use App\Models\Debt;
use App\Models\FinancialAccount;
use App\Services\DebtAccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DebtController extends Controller
{
    use AuthorizesBusinessAccess;

    public function index(Request $request)
    {
        $businessIds = $this->accessibleBusinesses()->pluck('id');
        $query = Debt::with(['customer', 'business'])->whereIn('business_id', $businessIds);

        if ($request->filled('business_id')) {
            $this->authorizeBusiness((int) $request->business_id);
            $query->where('business_id', $request->business_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%"));
            });
        }

        $debts = $query->latest('debt_date')->latest('id')->paginate(25)->withQueryString();
        $summaryQuery = Debt::whereIn('business_id', $businessIds);
        $totalOutstanding = (clone $summaryQuery)->sum('balance');
        $unpaidCount = (clone $summaryQuery)->where('status', 'unpaid')->count();
        $partialCount = (clone $summaryQuery)->where('status', 'partial')->count();
        $overdueCount = (clone $summaryQuery)->where('balance', '>', 0)
            ->whereNotNull('due_date')->whereDate('due_date', '<', today())->count();
        $businesses = $this->accessibleBusinesses()->orderBy('name')->get();

        return view('debts.index', compact(
            'debts', 'totalOutstanding', 'unpaidCount', 'partialCount', 'overdueCount', 'businesses'
        ));
    }

    public function create()
    {
        return view('debts.create', [
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'businesses' => $this->accessibleBusinesses()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, DebtAccountingService $accounting)
    {
        $allowedBusinessIds = $this->accessibleBusinesses()->pluck('id')->all();
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'business_id' => ['required', Rule::in($allowedBusinessIds)],
            'original_amount' => ['required', 'numeric', 'decimal:0,2', 'min:1'],
            'debt_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:debt_date'],
            'description' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data, $accounting): void {
            $customer = Customer::lockForUpdate()->findOrFail($data['customer_id']);
            if ($customer->credit_limit !== null) {
                $outstanding = $customer->debts()->sum('balance');
                if ($outstanding + $data['original_amount'] > $customer->credit_limit) {
                    throw ValidationException::withMessages([
                        'original_amount' => 'Deni hili linazidi credit limit ya mteja.',
                    ]);
                }
            }

            $debt = Debt::create($data + [
                'reference' => 'DBT-'.now()->format('Ymd').'-'.strtoupper(Str::random(8)),
                'balance' => $data['original_amount'],
                'status' => 'unpaid',
                'created_by' => auth()->id(),
            ]);
            $accounting->postDebt($debt->load('customer'));
        });

        return redirect()->route('debts.index')->with('success', 'Deni limeandikwa vizuri.');
    }

    public function show(Debt $debt)
    {
        $this->authorizeBusiness($debt->business_id);
        $debt->load(['customer', 'business', 'payments.financialAccount', 'payments.receiver']);
        $accounts = FinancialAccount::where('is_active', true)
            ->where(fn ($q) => $q->whereNull('business_id')->orWhere('business_id', $debt->business_id))
            ->orderBy('name')->get();

        return view('debts.show', compact('debt', 'accounts'));
    }
}
