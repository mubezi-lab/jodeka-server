<?php

namespace App\Http\Controllers;

use App\Models\DailyCashEntry;
use App\Models\Toilet;
use App\Models\ToiletDailyEntry;
use Illuminate\Http\Request;

class DailyCashEntryController extends Controller
{
    /**
     * Onyesha orodha ya Daily Cash.
     */
    public function index()
    {
        $entries = DailyCashEntry::with('creator')
            ->orderByDesc('entry_date')
            ->paginate(20);

        return view('daily-cash.index', compact('entries'));
    }

    /**
     * Onyesha form ya kuingiza Daily Cash.
     */
    public function create(Request $request)
    {
        $entryDate = $request->input(
            'entry_date',
            now()->toDateString()
        );

        /*
        |--------------------------------------------------------------------------
        | OPENING BALANCE
        |--------------------------------------------------------------------------
        |
        | Closing balance ya record ya mwisho kabla ya tarehe iliyochaguliwa.
        |
        */

        $previousEntry = DailyCashEntry::whereDate(
            'entry_date',
            '<',
            $entryDate
        )
            ->orderByDesc('entry_date')
            ->first();

        $openingBalance = (float) ($previousEntry?->closing_balance ?? 0);

        /*
        |--------------------------------------------------------------------------
        | AUTOMATIC EXTERNAL SOURCES
        |--------------------------------------------------------------------------
        */

        $automaticSources = $this->getAutomaticSources($entryDate);

        $stendiAmount = $automaticSources['stendi'];
        $sokoniAmount = $automaticSources['sokoni'];

        $automaticExternalTotal =
            $stendiAmount
            + $sokoniAmount;

        return view('daily-cash.create', compact(
            'entryDate',
            'openingBalance',
            'stendiAmount',
            'sokoniAmount',
            'automaticExternalTotal'
        ));
    }

    /**
     * Hifadhi Daily Cash.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'entry_date' => [
                'required',
                'date',
                'unique:daily_cash_entries,entry_date',
            ],

            'manual_opening_balance' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'yas' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'voda' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'halotel' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'airtel' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'token' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'noti' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'expenses_total' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'manual_external_total' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'external_description' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | CLOSING BALANCE
        |--------------------------------------------------------------------------
        */

        $yas = (float) ($validated['yas'] ?? 0);
        $voda = (float) ($validated['voda'] ?? 0);
        $halotel = (float) ($validated['halotel'] ?? 0);
        $airtel = (float) ($validated['airtel'] ?? 0);
        $token = (float) ($validated['token'] ?? 0);
        $noti = (float) ($validated['noti'] ?? 0);

        $closingBalance =
            $yas
            + $voda
            + $halotel
            + $airtel
            + $token
            + $noti;

        /*
        |--------------------------------------------------------------------------
        | OPENING BALANCE
        |--------------------------------------------------------------------------
        */

        $previousEntry = DailyCashEntry::whereDate(
            'entry_date',
            '<',
            $validated['entry_date']
        )
            ->orderByDesc('entry_date')
            ->first();

        $manualOpeningBalance = (float) (
            $validated['manual_opening_balance'] ?? 0
        );

        $openingBalance = $previousEntry
            ? (float) $previousEntry->closing_balance
            : $manualOpeningBalance;

        /*
        |--------------------------------------------------------------------------
        | AUTOMATIC EXTERNAL SOURCES
        |--------------------------------------------------------------------------
        */

        $automaticSources = $this->getAutomaticSources(
            $validated['entry_date']
        );

        $automaticExternalTotal =
            $automaticSources['stendi']
            + $automaticSources['sokoni'];

        /*
        |--------------------------------------------------------------------------
        | MANUAL EXTERNAL MONEY
        |--------------------------------------------------------------------------
        */

        $manualExternalTotal = (float) (
            $validated['manual_external_total'] ?? 0
        );

        $externalTotal =
            $automaticExternalTotal
            + $manualExternalTotal;

        /*
        |--------------------------------------------------------------------------
        | HQ EXPENSES
        |--------------------------------------------------------------------------
        */

        $expensesTotal = (float) (
            $validated['expenses_total'] ?? 0
        );

        /*
        |--------------------------------------------------------------------------
        | HQ SALES
        |--------------------------------------------------------------------------
        */

        $shopIncome =
            $closingBalance
            + $expensesTotal
            - $openingBalance
            - $externalTotal;

        /*
        |--------------------------------------------------------------------------
        | SAVE
        |--------------------------------------------------------------------------
        */

        DailyCashEntry::create([
            'entry_date' => $validated['entry_date'],

            'yas' => $yas,
            'voda' => $voda,
            'halotel' => $halotel,
            'airtel' => $airtel,
            'token' => $token,
            'noti' => $noti,

            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,

            'expenses_total' => $expensesTotal,
            'external_total' => $externalTotal,

            'shop_income' => $shopIncome,

            'raw_input' => $validated['external_description'] ?? null,

            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('daily-cash.index')
            ->with(
                'success',
                'Taarifa ya mapato na matumizi imehifadhiwa.'
            );
    }

    /**
     * Pata pesa za Stendi na Sokoni automatically.
     */
    private function getAutomaticSources(string $entryDate): array
    {
        return [
            'stendi' => $this->getToiletNetAmount(
                'stendi',
                $entryDate
            ),

            'sokoni' => $this->getToiletNetAmount(
                'sokoni',
                $entryDate
            ),
        ];
    }

    /**
     * Pata kiasi kilichobaki baada ya matumizi ya choo.
     *
     * total_revenue tayari ni:
     *
     * Stendi:
     * closing + expenses - opening
     *
     * Sokoni:
     * closing + expenses
     *
     * Kwa hiyo:
     *
     * total_revenue - total_expenses
     *
     * ndiyo kiasi kilichobaki baada ya matumizi.
     */
    private function getToiletNetAmount(
        string $toiletName,
        string $entryDate
    ): float {
        $toilet = Toilet::whereRaw(
            'LOWER(name) = ?',
            [strtolower($toiletName)]
        )->first();

        if (!$toilet) {
            return 0;
        }

        $entry = ToiletDailyEntry::where(
            'toilet_id',
            $toilet->id
        )
            ->whereDate('entry_date', $entryDate)
            ->first();

        if (!$entry) {
            return 0;
        }

        return max(
            0,
            (float) $entry->total_revenue
            - (float) $entry->total_expenses
        );
    }

}