<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesBusinessAccess;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    use AuthorizesBusinessAccess;

    public function index()
    {
        $businessIds = $this->accessibleBusinesses()->pluck('id');
        $customers = Customer::withSum([
            'debts as outstanding_balance' => fn ($q) => $q
                ->whereIn('business_id', $businessIds)
                ->where('balance', '>', 0),
        ], 'balance')
            ->orderBy('name')->paginate(25);

        return view('customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'credit_limit' => ['nullable', 'numeric', 'decimal:0,2', 'min:0'],
        ]);

        DB::transaction(function () use ($data): void {
            Customer::create($data + [
                'customer_number' => 'CUS-'.now()->format('ymd').'-'.strtoupper(Str::random(6)),
                'is_active' => true,
                'created_by' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Mteja ameongezwa vizuri.');
    }
}
