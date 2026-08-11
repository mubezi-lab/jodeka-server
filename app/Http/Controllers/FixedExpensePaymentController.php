<?php

namespace App\Http\Controllers;

use App\Models\FixedExpense;
use App\Models\FixedExpensePayment;
use Illuminate\Http\Request;

class FixedExpensePaymentController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $fixedExpenses = FixedExpense::with([
            'business',
            'payments' => function ($query) use ($month, $year) {
                $query->where('month', $month)
                    ->where('year', $year);
            }
        ])
            ->where('is_active', true)
            ->latest()
            ->get();

        $totalBudget = $fixedExpenses->sum('default_amount');

        $paid = FixedExpensePayment::where('month', $month)
            ->where('year', $year)
            ->sum('amount');

        $remaining = $totalBudget - $paid;

        return view('fixed-expense-payments.index', compact(
            'fixedExpenses',
            'month',
            'year',
            'totalBudget',
            'paid',
            'remaining'
        ));
    }

    public function pay(Request $request, FixedExpense $fixedExpense)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
            'paid_at' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $exists = FixedExpensePayment::where('fixed_expense_id', $fixedExpense->id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Hili fixed expense tayari limelipwa kwa mwezi huu.');
        }

        FixedExpensePayment::create([
            'fixed_expense_id' => $fixedExpense->id,
            'amount' => $request->amount,
            'month' => $request->month,
            'year' => $request->year,
            'paid_at' => $request->paid_at,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Fixed expense payment imehifadhiwa.');
    }
}