<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Loan;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | LOANS
        |--------------------------------------------------------------------------
        */

        $loans = Loan::with('business')
            ->latest()
            ->get();

        /*
        |--------------------------------------------------------------------------
        | ANALYTICS
        |--------------------------------------------------------------------------
        */

        $totalReceivable = Loan::where('type', 'receivable')
            ->sum('remaining_amount');

        $totalPayable = Loan::where('type', 'payable')
            ->sum('remaining_amount');

        $totalPaidLoans = Loan::where('status', 'paid')
            ->count();

        $totalPartialLoans = Loan::where('status', 'partial')
            ->count();

        $overdueLoans = Loan::whereNotNull('due_date')
            ->whereDate('due_date', '<', today())
            ->where('status', '!=', 'paid')
            ->count();

        return view('loans.index', compact(

            'loans',

            'totalReceivable',

            'totalPayable',

            'totalPaidLoans',

            'totalPartialLoans',

            'overdueLoans'

        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $businesses = Business::all();

        return view('loans.create', compact('businesses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([

            'business_id' => 'nullable|exists:businesses,id',

            'type' => 'required|in:payable,receivable',

            'name' => 'required|string|max:255',

            'phone' => 'nullable|string|max:255',

            'amount' => 'required|numeric|min:0',

            'loan_date' => 'required|date',

            'due_date' => 'nullable|date',

            'description' => 'nullable|string',

        ]);

        Loan::create([

            'business_id' => $request->business_id,

            'type' => $request->type,

            'name' => $request->name,

            'phone' => $request->phone,

            'amount' => $request->amount,

            'paid_amount' => 0,

            'remaining_amount' => $request->amount,

            'status' => 'pending',

            'loan_date' => $request->loan_date,

            'due_date' => $request->due_date,

            'description' => $request->description,

            'created_by' => auth()->id(),

        ]);

        return redirect()
            ->route('loans.index')
            ->with('success', 'Loan created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Loan $loan)
    {
        return view('loans.show', compact('loan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Loan $loan)
    {
        $loan->load('payments');

        $businesses = Business::all();

        return view('loans.edit', compact(

            'loan',
            'businesses'

        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Loan $loan)
    {
        $request->validate([

            'business_id' => 'nullable|exists:businesses,id',

            'type' => 'required|in:payable,receivable',

            'name' => 'required|string|max:255',

            'phone' => 'nullable|string|max:255',

            'amount' => 'required|numeric|min:0',

            'loan_date' => 'required|date',

            'due_date' => 'nullable|date',

            'description' => 'nullable|string',

        ]);

        $loan->update([

            'business_id' => $request->business_id,

            'type' => $request->type,

            'name' => $request->name,

            'phone' => $request->phone,

            'amount' => $request->amount,

            'remaining_amount' =>
                $request->amount - $loan->paid_amount,

            'loan_date' => $request->loan_date,

            'due_date' => $request->due_date,

            'description' => $request->description,

        ]);

        return redirect()
            ->route('loans.index')
            ->with('success', 'Loan updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Loan $loan)
    {
        $loan->delete();

        return redirect()
            ->route('loans.index')
            ->with('success', 'Loan deleted successfully');
    }
}