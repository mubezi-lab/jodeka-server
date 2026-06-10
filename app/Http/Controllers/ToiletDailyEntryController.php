<?php

namespace App\Http\Controllers;

use App\Models\ToiletDailyEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ToiletExpense;

class ToiletDailyEntryController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | SHOW FORM
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('toilets.add-sale');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE ENTRY
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'amount' => 'required|numeric|min:0',

            'customers' => 'required|integer|min:1',

            'note' => 'nullable|string',
        ]);

        /*
        |--------------------------------------------------------------------------
        | CURRENT USER
        |--------------------------------------------------------------------------
        */

        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | GET ASSIGNED TOILET
        |--------------------------------------------------------------------------
        */

        $toilet = $user->toilet;

        /*
        |--------------------------------------------------------------------------
        | SAVE ENTRY
        |--------------------------------------------------------------------------
        */

        ToiletDailyEntry::create([

            'toilet_id' => $toilet->id,

            'amount' => $request->amount,

            'customers' => $request->customers,

            'note' => $request->note,

            'entry_date' => now()->toDateString(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->back()
            ->with('success', 'Sale added successfully.');
    }

    public function update(Request $request, $id)
{
    $entry = ToiletDailyEntry::findOrFail($id);

    $request->validate([
        'entry_date' => 'required|date',
        'opening_balance' => 'required|numeric',
        'pos_amount' => 'nullable|numeric|min:0',
        'closing_balance' => 'required|numeric',
    ]);

    $oldDate = $entry->entry_date;

    $entry->update([
        'entry_date' => $request->entry_date,
        'opening_balance' => $request->opening_balance,
        'pos_amount' => $request->pos_amount ?? 0,
        'closing_balance' => $request->closing_balance,
    ]);

    /*
    |--------------------------------------------------------------------------
    | UPDATE EXPENSE DATES TOO
    |--------------------------------------------------------------------------
    */

    if ($oldDate != $request->entry_date) {

        foreach ($entry->expenses as $expense) {

            $expense->created_at = $request->entry_date;
            $expense->updated_at = now();

            $expense->save();
        }
    }

    return back()->with(
        'success',
        'Daily entry updated successfully.'
    );
}
}