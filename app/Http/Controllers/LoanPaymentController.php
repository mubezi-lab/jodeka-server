<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanPayment;
use Illuminate\Http\Request;

class LoanPaymentController extends Controller
{
    /**
     * Store payment
     */
    public function store(Request $request, Loan $loan)
    {
        $request->validate([

            'amount' => 'required|numeric|min:1',

            'payment_date' => 'required|date',

            'payment_method' => 'required',

            'notes' => 'nullable|string',

        ]);

        /*
        |--------------------------------------------------------------------------
        | VALIDATE PAYMENT
        |--------------------------------------------------------------------------
        */

        if ($request->amount > $loan->remaining_amount) {

            return back()->withErrors([

                'amount' => 'Payment exceeds remaining balance.'

            ])->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | CREATE PAYMENT
        |--------------------------------------------------------------------------
        */

        LoanPayment::create([

            'loan_id' => $loan->id,

            'amount' => $request->amount,

            'payment_date' => $request->payment_date,

            'payment_method' => $request->payment_method,

            'notes' => $request->notes,

            'created_by' => auth()->id(),

        ]);

        /*
        |--------------------------------------------------------------------------
        | UPDATE LOAN
        |--------------------------------------------------------------------------
        */

        $loan->paid_amount += $request->amount;

        $loan->remaining_amount =
            $loan->amount - $loan->paid_amount;

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        if ($loan->remaining_amount <= 0) {

            $loan->status = 'paid';

            $loan->remaining_amount = 0;

        } elseif ($loan->paid_amount > 0) {

            $loan->status = 'partial';

        } else {

            $loan->status = 'pending';
        }

        $loan->save();

        return redirect()
            ->route('loans.edit', $loan->id)
            ->with('success', 'Payment added successfully');
    }

    /**
     * Delete payment
     */
    public function destroy(LoanPayment $payment)
    {
        $loan = $payment->loan;

        /*
        |--------------------------------------------------------------------------
        | REVERSE PAYMENT
        |--------------------------------------------------------------------------
        */

        $loan->paid_amount -= $payment->amount;

        $loan->remaining_amount =
            $loan->amount - $loan->paid_amount;

        /*
        |--------------------------------------------------------------------------
        | UPDATE STATUS
        |--------------------------------------------------------------------------
        */

        if ($loan->paid_amount <= 0) {

            $loan->status = 'pending';

        } elseif ($loan->remaining_amount <= 0) {

            $loan->status = 'paid';

        } else {

            $loan->status = 'partial';
        }

        $loan->save();

        /*
        |--------------------------------------------------------------------------
        | DELETE PAYMENT
        |--------------------------------------------------------------------------
        */

        $payment->delete();

        return back()->with(
            'success',
            'Payment deleted successfully'
        );
    }
}