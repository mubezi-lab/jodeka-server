<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\FixedExpense;
use Illuminate\Http\Request;

class FixedExpenseController extends Controller
{
    public function index()
    {
        $fixedExpenses = FixedExpense::with('business')
            ->latest()
            ->get();

        return view('fixed-expenses.index', compact('fixedExpenses'));
    }

    public function create()
    {
        $businesses = Business::orderBy('name')->get();

        return view('fixed-expenses.create', compact('businesses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'business_id' => 'nullable|exists:businesses,id',
            'name' => 'required|string|max:255',
            'default_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        FixedExpense::create([
            'business_id' => $request->business_id,
            'name' => $request->name,
            'default_amount' => $request->default_amount,
            'is_active' => $request->has('is_active'),
            'notes' => $request->notes,
        ]);

        return redirect()
            ->route('fixed-expenses.index')
            ->with('success', 'Fixed expense imeongezwa kikamilifu.');
    }

    public function edit(FixedExpense $fixedExpense)
    {
        $businesses = Business::orderBy('name')->get();

        return view('fixed-expenses.edit', compact('fixedExpense', 'businesses'));
    }

    public function update(Request $request, FixedExpense $fixedExpense)
    {
        $request->validate([
            'business_id' => 'nullable|exists:businesses,id',
            'name' => 'required|string|max:255',
            'default_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $fixedExpense->update([
            'business_id' => $request->business_id,
            'name' => $request->name,
            'default_amount' => $request->default_amount,
            'is_active' => $request->has('is_active'),
            'notes' => $request->notes,
        ]);

        return redirect()
            ->route('fixed-expenses.index')
            ->with('success', 'Fixed expense imesasishwa.');
    }

    public function destroy(FixedExpense $fixedExpense)
    {
        $fixedExpense->delete();

        return redirect()
            ->route('fixed-expenses.index')
            ->with('success', 'Fixed expense imefutwa.');
    }
}