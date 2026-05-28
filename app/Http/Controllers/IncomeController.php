<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncomeController extends Controller
{
    /**
     * Display all incomes
     */
    public function index()
    {
        $incomes = Transaction::with('business', 'creator')
            ->where('type', 'income')
            ->latest()
            ->get();

        return view('company-income.index', compact('incomes'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $businesses = Business::orderBy('name')->get();

        return view('company-income.create', compact('businesses'));
    }

    /**
     * Store income
     */
    public function store(Request $request)
    {
        $request->validate([

            'business_id' => 'nullable|exists:businesses,id',

            'category' => 'required|string|max:255',

            'amount' => 'required|numeric|min:1',

            'payment_method' => 'required|in:cash,bank,mpesa,airtel_money,mix',

            'transaction_date' => 'required|date',

            'description' => 'nullable|string',

        ]);

        Transaction::create([

            'business_id' => $request->business_id,

            'type' => 'income',

            'category' => $request->category,

            'amount' => $request->amount,

            'payment_method' => $request->payment_method,

            'transaction_date' => $request->transaction_date,

            'reference' => 'INC-' . time(),

            'description' => $request->description,

            'created_by' => Auth::id(),

        ]);

        return redirect()
            ->route('company-incomes.index')
            ->with('success', 'Income added successfully');
    }

    /**
     * Show single income
     */
    public function show(string $id)
    {
        $income = Transaction::with('business', 'creator')
            ->where('type', 'income')
            ->findOrFail($id);

        return view('company-income.show', compact('income'));
    }

    /**
     * Edit form
     */
    public function edit(string $id)
    {
        $income = Transaction::where('type', 'income')
            ->findOrFail($id);

        $businesses = Business::orderBy('name')->get();

        return view('company-income.edit', compact('income', 'businesses'));
    }

    /**
     * Update income
     */
    public function update(Request $request, string $id)
    {
        $income = Transaction::where('type', 'income')
            ->findOrFail($id);

        $request->validate([

            'business_id' => 'nullable|exists:businesses,id',

            'category' => 'required|string|max:255',

            'amount' => 'required|numeric|min:1',

            'payment_method' => 'required|in:cash,bank,mpesa,airtel_money,mix',

            'transaction_date' => 'required|date',

            'description' => 'nullable|string',

        ]);

        $income->update([

            'business_id' => $request->business_id,

            'category' => $request->category,

            'amount' => $request->amount,

            'payment_method' => $request->payment_method,

            'transaction_date' => $request->transaction_date,

            'description' => $request->description,

        ]);

        return redirect()
            ->route('company-incomes.index')
            ->with('success', 'Income updated successfully');
    }

    /**
     * Delete income
     */
    public function destroy(string $id)
    {
        $income = Transaction::where('type', 'income')
            ->findOrFail($id);

        $income->delete();

        return redirect()
            ->route('company-incomes.index')
            ->with('success', 'Income deleted successfully');
    }
}
