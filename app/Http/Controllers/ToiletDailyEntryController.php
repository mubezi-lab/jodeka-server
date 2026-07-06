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
        $entry = ToiletDailyEntry::with('toilet', 'expenses')
            ->findOrFail($id);

        $request->validate([
            'entry_date' => 'required|date',
            'opening_balance' => 'required|numeric',
            'pos_amount' => 'nullable|numeric|min:0',
            'closing_balance' => 'required|numeric',
        ]);

        $oldDate = $entry->entry_date;

        $isStendi = strtolower($entry->toilet->name) === 'stendi';

        $posAmount = $isStendi
            ? 0
            : ($request->pos_amount ?? 0);

        $totalExpenses = $entry->expenses()->sum('amount');

        $totalRevenue = $isStendi
            ? (($request->closing_balance + $totalExpenses) - $request->opening_balance)
            : ($request->closing_balance + $totalExpenses);

        if ($totalRevenue < 0) {
            return back()
                ->withErrors([
                    'closing_balance' => 'Closing balance plus expenses cannot be less than opening balance.',
                ])
                ->withInput();
        }

        $entry->update([
            'entry_date' => $request->entry_date,
            'opening_balance' => $request->opening_balance,
            'closing_balance' => $request->closing_balance,
            'pos_amount' => $posAmount,
            'total_expenses' => $totalExpenses,
            'total_revenue' => $totalRevenue,
        ]);

        if ($oldDate != $request->entry_date) {
            foreach ($entry->expenses as $expense) {
                $expense->created_at = $request->entry_date;
                $expense->updated_at = now();
                $expense->save();
            }
        }

        if (!$isStendi) {
            $previousEntry = ToiletDailyEntry::where('toilet_id', $entry->toilet_id)
                ->whereDate('entry_date', '<', $request->entry_date)
                ->orderBy('entry_date', 'desc')
                ->first();

            $previousPosAmount = $previousEntry
                ? ($previousEntry->pos_amount ?? 0)
                : null;

            $entriesToRecalculate = ToiletDailyEntry::where('toilet_id', $entry->toilet_id)
                ->whereDate('entry_date', '>=', $request->entry_date)
                ->orderBy('entry_date', 'asc')
                ->get();

            foreach ($entriesToRecalculate as $currentEntry) {
                $currentPosAmount = $currentEntry->pos_amount ?? 0;

                if ($previousPosAmount === null) {
                    $dailyPosCollection = $currentPosAmount;
                } elseif ($currentPosAmount < $previousPosAmount) {
                    $dailyPosCollection = $currentPosAmount;
                } else {
                    $dailyPosCollection = $currentPosAmount - $previousPosAmount;
                }

                $currentEntry->update([
                    'daily_pos_collection' => $dailyPosCollection,
                ]);

                $previousPosAmount = $currentPosAmount;
            }
        }

    $routeName = strtolower($entry->toilet->name) === 'stendi'
        ? 'stendi.expenses'
        : 'sokoni.expenses';

    return redirect()
        ->route($routeName, [
            'entry_date' => \Carbon\Carbon::parse($request->entry_date)
                ->toDateString(),
        ])
        ->with(
            'success',
            'Daily entry updated successfully.'
        );
    }
}