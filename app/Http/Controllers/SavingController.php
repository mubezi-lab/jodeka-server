<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Saving;
use App\Models\SavingTransaction;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavingController extends Controller
{
    /**
     * Display all savings
     */
    public function index()
    {
        $savings = Saving::with('business', 'creator')
            ->latest()
            ->get();

        /*
        |--------------------------------------------------------------------------
        | ANALYTICS
        |--------------------------------------------------------------------------
        */

        $totalSavings = Saving::sum('balance');

        $activeSavings = Saving::where('status', 'active')
            ->count();

        $completedSavings = Saving::where('status', 'completed')
            ->count();

        return view('savings.index', compact(

            'savings',

            'totalSavings',

            'activeSavings',

            'completedSavings'

        ));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $businesses = Business::orderBy('name')
            ->get();

        return view('savings.create', compact('businesses'));
    }

    /**
     * Store saving
     */
    public function store(Request $request)
    {
        $request->validate([

            'business_id' => 'nullable|exists:businesses,id',

            'type' => 'required',

            'name' => 'required|string|max:255',

            'description' => 'nullable|string',

            'target_amount' => 'nullable|numeric|min:0',

            'start_date' => 'nullable|date',

            'maturity_date' => 'nullable|date',

        ]);

        Saving::create([

            'business_id' => $request->business_id,

            'type' => $request->type,

            'name' => $request->name,

            'description' => $request->description,

            'target_amount' => $request->target_amount,

            'balance' => 0,

            'start_date' => $request->start_date,

            'maturity_date' => $request->maturity_date,

            'status' => 'active',

            'created_by' => Auth::id(),

        ]);

        return redirect()
            ->route('savings.index')
            ->with('success', 'Saving created successfully');
    }

    /**
     * Show single saving
     */
    public function show(string $id)
    {
        $saving = Saving::with([
                'business',
                'creator',
                'transactions'
            ])
            ->findOrFail($id);

        return view('savings.show', compact('saving'));
    }

    /**
     * Show edit form
     */
    public function edit(string $id)
    {
        $saving = Saving::findOrFail($id);

        $businesses = Business::orderBy('name')
            ->get();

        return view('savings.edit', compact(

            'saving',
            'businesses'

        ));
    }

    /**
     * Update saving
     */
    public function update(Request $request, string $id)
    {
        $saving = Saving::findOrFail($id);

        $request->validate([

            'business_id' => 'nullable|exists:businesses,id',

            'type' => 'required',

            'name' => 'required|string|max:255',

            'description' => 'nullable|string',

            'target_amount' => 'nullable|numeric|min:0',

            'start_date' => 'nullable|date',

            'maturity_date' => 'nullable|date',

            'status' => 'required',

        ]);

        $saving->update([

            'business_id' => $request->business_id,

            'type' => $request->type,

            'name' => $request->name,

            'description' => $request->description,

            'target_amount' => $request->target_amount,

            'start_date' => $request->start_date,

            'maturity_date' => $request->maturity_date,

            'status' => $request->status,

        ]);

        return redirect()
            ->route('savings.index')
            ->with('success', 'Saving updated successfully');
    }

    /**
     * Delete saving
     */
    public function destroy(string $id)
    {
        $saving = Saving::findOrFail($id);

        $saving->delete();

        return redirect()
            ->route('savings.index')
            ->with('success', 'Saving deleted successfully');
    }

    /**
     * Show deposit form
     */
    public function depositForm(string $id)
    {
        $saving = Saving::findOrFail($id);

        return view('savings.deposit', compact('saving'));
    }

    /**
     * Store deposit
     */
    public function depositStore(Request $request, string $id)
    {
        $saving = Saving::findOrFail($id);

        $request->validate([

            'amount' => 'required|numeric|min:1',

            'payment_method' => 'required',

            'transaction_date' => 'required|date',

            'description' => 'nullable|string',

        ]);

        /*
        |--------------------------------------------------------------------------
        | CREATE TRANSACTION
        |--------------------------------------------------------------------------
        */

        SavingTransaction::create([

            'saving_id' => $saving->id,

            'type' => 'deposit',

            'amount' => $request->amount,

            'payment_method' => $request->payment_method,

            'description' => $request->description,

            'transaction_date' => $request->transaction_date,

            'created_by' => auth()->id(),

        ]);

        /*
        |--------------------------------------------------------------------------
        | UPDATE BALANCE
        |--------------------------------------------------------------------------
        */

        $saving->increment('balance', $request->amount);

        return redirect()
            ->route('savings.show', $saving->id)
            ->with('success', 'Deposit added successfully');
    }

    /**
     * Show withdrawal form
     */
    public function withdrawForm(string $id)
    {
        $saving = Saving::findOrFail($id);

        return view('savings.withdraw', compact('saving'));
    }

    /**
     * Store withdrawal
     */
    public function withdrawStore(Request $request, string $id)
    {
        $saving = Saving::findOrFail($id);

        $request->validate([

            'amount' => 'required|numeric|min:1',

            'payment_method' => 'required',

            'transaction_date' => 'required|date',

            'description' => 'nullable|string',

        ]);

        /*
        |--------------------------------------------------------------------------
        | CHECK BALANCE
        |--------------------------------------------------------------------------
        */

        if ($request->amount > $saving->balance) {

            return back()->withErrors([

                'amount' => 'Insufficient saving balance'

            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | CREATE TRANSACTION
        |--------------------------------------------------------------------------
        */

        SavingTransaction::create([

            'saving_id' => $saving->id,

            'type' => 'withdrawal',

            'amount' => $request->amount,

            'payment_method' => $request->payment_method,

            'description' => $request->description,

            'transaction_date' => $request->transaction_date,

            'created_by' => auth()->id(),

        ]);

        /*
        |--------------------------------------------------------------------------
        | UPDATE BALANCE
        |--------------------------------------------------------------------------
        */

        $saving->decrement('balance', $request->amount);

        return redirect()
            ->route('savings.show', $saving->id)
            ->with('success', 'Withdrawal added successfully');
    }
}
