<?php

namespace App\Http\Controllers;

use App\Models\Toilet;
use App\Models\ToiletDailyEntry;
use App\Models\ToiletExpense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ToiletAttendantController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    public function dashboard()
    {
        $user = Auth::user();
        $toilet = $this->assignedToilet();

        /*
        |--------------------------------------------------------------------------
        | LAST 7 DAYS TABLE
        |--------------------------------------------------------------------------
        */

        // $entries = $toilet->dailyEntries()
        //     ->latest('entry_date')
        //     ->take(7)
        //     ->get();

        $entries = $toilet->dailyEntries()
        ->latest('entry_date')
        ->get();

        /*
        |--------------------------------------------------------------------------
        | WEEKLY CHART DATA
        |--------------------------------------------------------------------------
        */

        $weeklyEntries = $toilet->dailyEntries()
            ->latest('entry_date')
            ->take(7)
            ->get()
            ->reverse();

        /*
        |--------------------------------------------------------------------------
        | MONTHLY DATA
        |--------------------------------------------------------------------------
        */

        $monthlyEntries = $toilet->dailyEntries()
            ->whereBetween('entry_date', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ]);

        $monthlyCollection = (clone $monthlyEntries)
            ->sum('total_revenue');

        /*
        |--------------------------------------------------------------------------
        | MONTHLY EXPENSES
        | TOTAL EXPENSES EXCLUDING POSHO
        |--------------------------------------------------------------------------
        */

        $monthlyExpenses = ToiletExpense::whereHas(

            'entry',

            function ($query) use ($toilet) {

                $query->where(

                    'toilet_id',

                    $toilet->id

                )->whereBetween(

                    'entry_date',

                    [

                        now()->startOfMonth()->toDateString(),

                        now()->endOfMonth()->toDateString(),
                    ]
                );
            }

        )->whereRaw(

            'LOWER(expense_name) NOT LIKE ?',

            ['%posho%']

        )->sum('amount');

        /*
        |--------------------------------------------------------------------------
        | COUNCIL SHARE
        |--------------------------------------------------------------------------
        */

        $councilShare = ($monthlyCollection * 40) / 100;

        /*
        |--------------------------------------------------------------------------
        | ATTENDANT ALLOWANCE
        |--------------------------------------------------------------------------
        */

        $attendantAllowance = ToiletExpense::whereHas(
            'entry',
            function ($query) use ($toilet) {

                $query->where('toilet_id', $toilet->id)
                    ->whereBetween('entry_date', [

                        now()->startOfMonth()->toDateString(),
                        now()->endOfMonth()->toDateString(),

                    ]);
            }
        )
        ->whereRaw(
            'LOWER(expense_name) LIKE ?',
            ['%posho%']
        )
        ->sum('amount');

        /*
        |--------------------------------------------------------------------------
        | REAL OFFICE PROFIT
        |--------------------------------------------------------------------------
        */

        $officeShare = (

            ($monthlyCollection * 60) / 100

        ) - (

            $monthlyExpenses +

            $attendantAllowance
        );

        /*
        |--------------------------------------------------------------------------
        | AVOID NEGATIVE PROFIT
        |--------------------------------------------------------------------------
        */

        if ($officeShare < 0) {

            $officeShare = 0;
        }

        return view(
            'toilets.dashboard',
            compact(
                'user',
                'toilet',
                'entries',
                'weeklyEntries',
                'monthlyCollection',
                'monthlyExpenses',
                'councilShare',
                'officeShare',
                'attendantAllowance'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE ENTRY FORM
    |--------------------------------------------------------------------------
    */

    public function createEntry()
    {
        $user = Auth::user();
        $toilet = $this->assignedToilet();

        return view(
            'toilets.add-entry',
            compact(
                'user',
                'toilet'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE ENTRY
    |--------------------------------------------------------------------------
    */

public function storeEntry(Request $request)
{
    $toilet = $this->assignedToilet();

    $isStendi = $this->isStendi($toilet);

    $rules = [
        'entry_date' => 'required|date',
        'closing_balance' => 'required|numeric|min:0',
    ];

    /*
    |--------------------------------------------------------------------------
    | STENDI REQUIREMENTS
    |--------------------------------------------------------------------------
    */

    if ($isStendi) {

        $rules['opening_balance'] = 'required|numeric|min:0';

    } else {

        /*
        |--------------------------------------------------------------------------
        | SOKONI REQUIREMENTS
        |--------------------------------------------------------------------------
        */

        $rules['pos_amount'] = 'nullable|numeric|min:0';
    }

    $validated = $request->validate($rules);

    /*
    |--------------------------------------------------------------------------
    | CHECK EXISTING ENTRY
    |--------------------------------------------------------------------------
    */

    $existingEntry = $toilet->dailyEntries()
        ->whereDate('entry_date', $validated['entry_date'])
        ->first();

    if ($existingEntry) {

        return redirect()->route(
            $this->expensesRouteName($toilet),
            [
                'entry_date' => Carbon::parse(
                    $existingEntry->entry_date
                )->toDateString(),
            ]
        )->with(
            'success',
            'Cash entry already exists. You can continue adding expenses.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BALANCES
    |--------------------------------------------------------------------------
    */

    $openingBalance = $isStendi
        ? $validated['opening_balance']
        : 0;

    $closingBalance = $validated['closing_balance'];

    /*
    |--------------------------------------------------------------------------
    | POS AMOUNT
    |--------------------------------------------------------------------------
    */

    $posAmount = $isStendi
        ? null
        : ($validated['pos_amount'] ?? 0);

    /*
    |--------------------------------------------------------------------------
    | DAILY POS COLLECTION
    |--------------------------------------------------------------------------
    */

    $dailyPosCollection = 0;

    if (!$isStendi) {

        $previousEntry = $toilet->dailyEntries()
            ->whereDate('entry_date', '<', $validated['entry_date'])
            ->orderBy('entry_date', 'desc')
            ->first();

        if ($previousEntry) {

            /*
            |--------------------------------------------------------------------------
            | POS RESET / NEW CONTROL NUMBER
            |--------------------------------------------------------------------------
            */

            if ($posAmount < ($previousEntry->pos_amount ?? 0)) {

                $dailyPosCollection = $posAmount;

            } else {

                $dailyPosCollection =
                    $posAmount - ($previousEntry->pos_amount ?? 0);
            }

        } else {

            $dailyPosCollection = $posAmount;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | REVENUE CALCULATION
    |--------------------------------------------------------------------------
    */

    $revenue = $this->calculateRevenue(
        $toilet,
        $openingBalance,
        $closingBalance,
        $posAmount
    );

    if ($revenue < 0) {

        return redirect()->back()
            ->withErrors([
                'closing_balance' =>
                    'Closing balance cannot be less than opening balance.',
            ])
            ->withInput();
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE ENTRY
    |--------------------------------------------------------------------------
    */

    $entry = ToiletDailyEntry::create([

        'toilet_id' => $toilet->id,

        'opening_balance' => $openingBalance,

        'closing_balance' => $closingBalance,

        'pos_amount' => $posAmount,

        'daily_pos_collection' => $dailyPosCollection,

        'total_expenses' => 0,

        'total_revenue' => $revenue,

        'note' => null,

        'entry_date' => $validated['entry_date'],

        'is_closed' => false,
    ]);

    /*
    |--------------------------------------------------------------------------
    | REDIRECT TO EXPENSES PAGE
    |--------------------------------------------------------------------------
    */

    return redirect()->route(
        $this->expensesRouteName($toilet),
        [
            'entry_date' => Carbon::parse(
                $entry->entry_date
            )->toDateString(),
        ]
    )->with(
        'success',
        'Cash entry saved successfully.'
    );
}

    /*
    |--------------------------------------------------------------------------
    | EXPENSES PAGE
    |--------------------------------------------------------------------------
    */

    public function expenses(Request $request)
    {
        $user = Auth::user();
        $toilet = $this->assignedToilet();

        $selectedDate = $request->entry_date
            ?? now()->toDateString();

        $entry = $toilet->dailyEntries()
            ->with('expenses')
            ->whereDate('entry_date', $selectedDate)
            ->first();

        $entries = $toilet->dailyEntries()
            ->latest('entry_date')
            ->get();

        if (!$entry) {
            return redirect()
                ->route(
                    $this->isStendi($toilet)
                        ? 'stendi.dashboard'
                        : 'sokoni.dashboard'
                )
                ->with(
                    'error',
                    'Entry for selected date was not found.'
                );
        }

        return view(
            'toilets.expenses',
            compact(
                'user',
                'toilet',
                'entry',
                'entries',
                'selectedDate'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE EXPENSE
    |--------------------------------------------------------------------------
    */

    public function storeExpense(Request $request)
    {
        $toilet = $this->assignedToilet();

        $validated = $request->validate([
            'entry_id' => 'required|exists:toilet_daily_entries,id',
            'expense_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        $entry = $toilet->dailyEntries()
            ->whereKey($validated['entry_id'])
            ->firstOrFail();

        ToiletExpense::create([
            'toilet_daily_entry_id' => $entry->id,
            'expense_name' => $validated['expense_name'],
            'amount' => $validated['amount'],
            'note' => $validated['note'] ?? null,
        ]);

        $this->refreshEntryTotals($entry);

        return redirect()->route(
            $this->expensesRouteName($toilet),
            [
                'entry_date' => Carbon::parse($entry->entry_date)
                    ->toDateString(),
            ]
        )->with(
            'success',
            'Expense added successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE EXPENSE
    |--------------------------------------------------------------------------
    */

    public function updateExpense(Request $request, $id)
    {
        $toilet = $this->assignedToilet();

        $validated = $request->validate([
            'expense_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        $expense = ToiletExpense::whereHas(
            'entry',
            function ($query) use ($toilet) {
                $query->where('toilet_id', $toilet->id);
            }
        )->findOrFail($id);

        $expense->update([
            'expense_name' => $validated['expense_name'],
            'amount' => $validated['amount'],
            'note' => $validated['note'] ?? null,
        ]);

        $entry = $expense->entry;

        $this->refreshEntryTotals($entry);

        return redirect()->route(
            $this->expensesRouteName($toilet),
            [
                'entry_date' => Carbon::parse($entry->entry_date)
                    ->toDateString(),
            ]
        )->with(
            'success',
            'Expense updated successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE EXPENSE
    |--------------------------------------------------------------------------
    */

    public function deleteExpense($id)
    {
        $toilet = $this->assignedToilet();

        $expense = ToiletExpense::whereHas(
            'entry',
            function ($query) use ($toilet) {
                $query->where('toilet_id', $toilet->id);
            }
        )->findOrFail($id);

        $entry = $expense->entry;

        $expense->delete();

        $this->refreshEntryTotals($entry);

        return redirect()->route(
            $this->expensesRouteName($toilet),
            [
                'entry_date' => Carbon::parse($entry->entry_date)
                    ->toDateString(),
            ]
        )->with(
            'success',
            'Expense deleted successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE DAILY ENTRY
    |--------------------------------------------------------------------------
    */

    public function updateEntry(Request $request, $id)
    {
        $toilet = $this->assignedToilet();
        $isStendi = $this->isStendi($toilet);

        $entry = $toilet->dailyEntries()
            ->whereKey($id)
            ->firstOrFail();

        $rules = [
            'entry_date' => 'required|date',
            'closing_balance' => 'required|numeric|min:0',
        ];

        if ($isStendi) {

            $rules['opening_balance'] = 'required|numeric|min:0';

        } else {

            $rules['pos_amount'] = 'nullable|numeric|min:0';
        }

        $validated = $request->validate($rules);

        $existingEntry = $toilet->dailyEntries()
            ->whereDate('entry_date', $validated['entry_date'])
            ->whereKeyNot($entry->id)
            ->first();

        if ($existingEntry) {

            return redirect()->back()->with(
                'error',
                'Another entry already exists for this date.'
            );
        }

        $openingBalance = $isStendi
            ? $validated['opening_balance']
            : 0;

        $closingBalance = $validated['closing_balance'];

        /*
        |--------------------------------------------------------------------------
        | DAILY POS COLLECTION
        |--------------------------------------------------------------------------
        */

        $dailyPosCollection = $entry->daily_pos_collection;

        if (!$isStendi) {

            $posAmount = $validated['pos_amount'] ?? 0;

            $previousEntry = $toilet->dailyEntries()
                ->whereDate('entry_date', '<', $validated['entry_date'])
                ->orderBy('entry_date', 'desc')
                ->first();

            if ($previousEntry) {

                $dailyPosCollection =
                    $posAmount - $previousEntry->pos_amount;

            } else {

                $dailyPosCollection = $posAmount;
            }

            if ($dailyPosCollection < 0) {

                $dailyPosCollection = 0;
            }
        }

        $revenue = $this->calculateRevenue(
            $toilet,
            $openingBalance,
            $closingBalance,
            $entry->expenses()->sum('amount')
        );

        if ($revenue < 0) {

            return redirect()->back()
                ->withErrors([
                    'closing_balance' =>
                        'Closing balance plus expenses cannot be less than opening balance.',
                ])
                ->withInput();
        }

        $entry->update([

            'entry_date' => $validated['entry_date'],

            'opening_balance' => $openingBalance,

            'closing_balance' => $closingBalance,

            'pos_amount' => $isStendi
                ? null
                : ($validated['pos_amount'] ?? null),

            'total_revenue' => $revenue,
        ]);

        return redirect()->route(
            $this->expensesRouteName($toilet),
            [
                'entry_date' => Carbon::parse(
                    $validated['entry_date']
                )->toDateString(),
            ]
        )->with(
            'success',
            'Daily summary updated successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | REPORTS PAGE
    |--------------------------------------------------------------------------
    */

    public function reports()
    {
        $user = Auth::user();
        $toilet = $this->assignedToilet();

        $entries = $toilet->dailyEntries()
            ->latest('entry_date')
            ->get();

        return view(
            'toilets.reports',
            compact(
                'user',
                'toilet',
                'entries'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ASSIGNED TOILET
    |--------------------------------------------------------------------------
    */

    private function assignedToilet(): Toilet
    {
        $toilet = Auth::user()->toilet;

        if (!$toilet) {
            abort(403, 'No toilet assigned.');
        }

        return $toilet;
    }

    /*
    |--------------------------------------------------------------------------
    | TOILET TYPE
    |--------------------------------------------------------------------------
    */

    private function isStendi(Toilet $toilet): bool
    {
        return strtolower($toilet->name) === 'stendi';
    }

    /*
    |--------------------------------------------------------------------------
    | EXPENSES ROUTE
    |--------------------------------------------------------------------------
    */

    private function expensesRouteName(Toilet $toilet): string
    {
        return $this->isStendi($toilet)
            ? 'stendi.expenses'
            : 'sokoni.expenses';
    }

    /*
    |--------------------------------------------------------------------------
    | REVENUE CALCULATION
    |--------------------------------------------------------------------------
    */

    private function calculateRevenue(
        Toilet $toilet,
        float $openingBalance,
        float $closingBalance,
        float $totalExpenses
    ): float {
        if ($this->isStendi($toilet)) {
            return ($closingBalance + $totalExpenses) - $openingBalance;
        }

        return $closingBalance + $totalExpenses;
    }

    /*
    |--------------------------------------------------------------------------
    | REFRESH ENTRY TOTALS
    |--------------------------------------------------------------------------
    */

    private function refreshEntryTotals(ToiletDailyEntry $entry): void
    {
        $entry->loadMissing('toilet');

        $totalExpenses = $entry->expenses()
            ->sum('amount');

        $entry->update([
            'total_expenses' => $totalExpenses,
            'total_revenue' => $this->calculateRevenue(
                $entry->toilet,
                $entry->opening_balance,
                $entry->closing_balance,
                $totalExpenses
            ),
        ]);
    }
    /*
    |--------------------------------------------------------------------------
    | CALCULATE POS BREAKDOWN
    |--------------------------------------------------------------------------
    */

    private function calculatePosBreakdown(float $totalCollection, float $posAmount): array
    {
        $outsidePos = max(
            0,
            $totalCollection - $posAmount
        );

        $councilShare = $posAmount * 0.40;

        $yourShare = $posAmount * 0.60;

        return [
            'total_collection' => $totalCollection,
            'pos_amount'       => $posAmount,
            'outside_pos'      => $outsidePos,
            'council_share'    => $councilShare,
            'your_share'       => $yourShare,
        ];
    }
}