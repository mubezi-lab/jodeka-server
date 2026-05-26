<?php

namespace App\Http\Controllers;

use App\Models\ToiletDailyEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}