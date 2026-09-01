<?php

namespace App\Http\Controllers\Bagambakamo;

use App\Http\Controllers\Controller;
use App\Models\Bagambakamo\Event;
use App\Models\Bagambakamo\Expense;
use App\Models\Bagambakamo\PendingTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PendingTransactionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | CREATE EVENT FROM PENDING TRANSACTION
    |--------------------------------------------------------------------------
    */

    public function storeEvent(
        Request $request,
        PendingTransaction $pendingTransaction
    ) {
        $validated = $request->validate([

            'member_id' => [
                'required',
                Rule::exists(
                    'bagambakamo_members',
                    'id'
                ),
            ],

            'type' => [
                'required',
                Rule::in([
                    'msiba',
                    'sherehe',
                ]),
            ],

            'event_amount' => [
                'required',
                'numeric',
                'min:0',
            ],

            'contribution_per_member' => [
                'required',
                'numeric',
                'min:0',
            ],

            'event_date' => [
                'required',
                'date',
            ],
        ]);


        DB::transaction(function () use (
            $validated,
            $pendingTransaction
        ) {

            /*
            |--------------------------------------------------------------------------
            | LOCK PENDING TRANSACTION
            |--------------------------------------------------------------------------
            */

            $pending = PendingTransaction::query()
                ->lockForUpdate()
                ->findOrFail(
                    $pendingTransaction->id
                );


            /*
            |--------------------------------------------------------------------------
            | PREVENT DOUBLE PROCESSING
            |--------------------------------------------------------------------------
            */

            if ($pending->status !== 'pending') {

                abort(
                    422,
                    'This transaction has already been processed.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | CREATE EVENT
            |--------------------------------------------------------------------------
            */

            $event = Event::create([

                'member_id' =>
                    $validated['member_id'],

                'type' =>
                    $validated['type'],

                'amount' =>
                    $validated['event_amount'],

                'contribution_per_member' =>
                    $validated['contribution_per_member'],

                'event_date' =>
                    $validated['event_date'],
            ]);


            /*
            |--------------------------------------------------------------------------
            | MARK PENDING TRANSACTION AS PROCESSED
            |--------------------------------------------------------------------------
            */

            $pending->update([

                'status' =>
                    'processed',

                'classification' =>
                    'event',

                'processed_record_id' =>
                    $event->id,

                'processed_at' =>
                    now(),
            ]);
        });


        return redirect()
            ->route('bagambakamo.dashboard')
            ->with(
                'success',
                'Transaction recorded as an event successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | SAVE AS EXPENDITURE
    |--------------------------------------------------------------------------
    */

    public function storeExpense(
        Request $request,
        PendingTransaction $pendingTransaction
    ) {
        $validated = $request->validate([

            'category' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);


        DB::transaction(function () use (
            $validated,
            $pendingTransaction
        ) {

            /*
            |--------------------------------------------------------------------------
            | LOCK PENDING TRANSACTION
            |--------------------------------------------------------------------------
            */

            $pending = PendingTransaction::query()
                ->lockForUpdate()
                ->findOrFail(
                    $pendingTransaction->id
                );


            /*
            |--------------------------------------------------------------------------
            | PREVENT DOUBLE PROCESSING
            |--------------------------------------------------------------------------
            */

            if ($pending->status !== 'pending') {

                abort(
                    422,
                    'This transaction has already been processed.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | RECIPIENT MUST BE IDENTIFIED
            |--------------------------------------------------------------------------
            */

            if (! $pending->member_id) {

                abort(
                    422,
                    'Money recipient could not be identified.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | CREATE EXPENDITURE
            |--------------------------------------------------------------------------
            */

            $expense = Expense::create([

                'member_id' =>
                    $pending->member_id,

                'category' =>
                    $validated['category'],

                'description' =>
                    $validated['description']
                    ?? null,

                'amount' =>
                    $pending->amount,

                'expense_date' =>
                    $pending->transaction_date
                        ->format('Y-m-d'),

                'reference' =>
                    $pending->reference,

                'notes' =>
                    $validated['notes']
                    ?? null,
            ]);


            /*
            |--------------------------------------------------------------------------
            | MARK PENDING TRANSACTION AS PROCESSED
            |--------------------------------------------------------------------------
            */

            $pending->update([

                'status' =>
                    'processed',

                'classification' =>
                    'expense',

                'processed_record_id' =>
                    $expense->id,

                'processed_at' =>
                    now(),
            ]);
        });


        return redirect()
            ->route('bagambakamo.dashboard')
            ->with(
                'success',
                'Transaction recorded as expenditure successfully.'
            );
    }
}