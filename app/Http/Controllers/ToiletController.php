<?php

namespace App\Http\Controllers;

use App\Models\Toilet;
use App\Models\ToiletDailyEntry;
use App\Models\ToiletExpense;
use Carbon\Carbon;

class ToiletController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | DATES
        |--------------------------------------------------------------------------
        */

        $today = Carbon::today();

        $startOfWeek = Carbon::now()->startOfWeek();

        $endOfWeek = Carbon::now()->endOfWeek();

        $startOfMonth = Carbon::now()->startOfMonth();

        $endOfMonth = Carbon::now()->endOfMonth();



        /*
        |--------------------------------------------------------------------------
        | LAST SUBMITTED REPORT DATE
        |--------------------------------------------------------------------------
        */

        $lastDate = ToiletDailyEntry::latest('entry_date')
            ->value('entry_date');



        /*
        |--------------------------------------------------------------------------
        | FALLBACK IF NO DATA
        |--------------------------------------------------------------------------
        */

        if (!$lastDate) {

            $lastDate = $today;
        }



        /*
        |--------------------------------------------------------------------------
        | DAILY ENTRY QUERY
        |--------------------------------------------------------------------------
        */

$monthlyEntryForToilet = function (string $name) use (
    $startOfMonth,
    $endOfMonth
) {

    return ToiletDailyEntry::whereHas(
        'toilet',
        function ($query) use ($name) {

            $query->where('name', $name);
        }
    )->whereBetween(
        'entry_date',
        [
            $startOfMonth,
            $endOfMonth
        ]
    );
};



        /*
        |--------------------------------------------------------------------------
        | EXPENSE QUERY
        |--------------------------------------------------------------------------
        */

$monthlyExpensesForToilet = function (string $name) use (
    $startOfMonth,
    $endOfMonth
) {

    return ToiletExpense::whereHas(
        'entry.toilet',
        function ($query) use ($name) {

            $query->where('name', $name);
        }
    )->whereBetween(
        'created_at',
        [
            $startOfMonth,
            $endOfMonth
        ]
    );
};



        /*
        |--------------------------------------------------------------------------
        | STENDI DATA
        |--------------------------------------------------------------------------
        */

        $stendiCollections =
            $monthlyEntryForToilet('stendi')
                ->sum('total_revenue');



        $stendiExpenses =
            $monthlyExpensesForToilet('stendi')
                ->sum('amount');



        $stendiNet =
            $stendiCollections -
            $stendiExpenses;



        /*
        |--------------------------------------------------------------------------
        | SOKONI COLLECTIONS
        |--------------------------------------------------------------------------
        */

        $sokoniCollections =
            $monthlyEntryForToilet('sokoni')
                ->sum('total_revenue');



        /*
        |--------------------------------------------------------------------------
        | POS AMOUNT
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | Replace 'pos_amount' with your real column
        | if your table uses another name.
        |--------------------------------------------------------------------------
        */

        $sokoniPosAmount =
            $monthlyEntryForToilet('sokoni')
                ->sum('pos_amount');



        /*
        |--------------------------------------------------------------------------
        | COUNCIL SHARE (40%)
        |--------------------------------------------------------------------------
        */

        $sokoniCouncilShare =
            $sokoniPosAmount * 0.40;



        /*
        |--------------------------------------------------------------------------
        | YOUR SHARE (60%)
        |--------------------------------------------------------------------------
        */

        $sokoniYourShare =
            $sokoniPosAmount * 0.60;



        /*
        |--------------------------------------------------------------------------
        | CASH OUTSIDE POS
        |--------------------------------------------------------------------------
        */

        // $sokoniOutsidePos =
        //     $sokoniCollections -
        //     $sokoniPosAmount;

            $sokoniOutsidePos = max(
                0,
                $sokoniCollections - $sokoniPosAmount
            );



        /*
        |--------------------------------------------------------------------------
        | GROSS PROFIT
        |--------------------------------------------------------------------------
        */

        $sokoniGrossProfit =
            $sokoniOutsidePos +
            $sokoniYourShare;






        /*
        |--------------------------------------------------------------------------
        | SOKONI EXPENSES
        |--------------------------------------------------------------------------
        */

        $sokoniExpenses =
            $monthlyExpensesForToilet('sokoni')
                ->sum('amount');



        /*
        |--------------------------------------------------------------------------
        | FINAL SOKONI PROFIT
        |--------------------------------------------------------------------------
        */

        $sokoniNet =
            $sokoniGrossProfit -
            $sokoniExpenses;



        /*
        |--------------------------------------------------------------------------
        | SUMMARY TOTALS
        |--------------------------------------------------------------------------
        */

        $stendiRevenue = $stendiCollections;

        $sokoniRevenue = $sokoniCollections;



        $todayRevenue =
            $stendiCollections +
            $sokoniCollections;



        $todayExpenses =
            $stendiExpenses +
            $sokoniExpenses;



        $todayNet =
            $stendiNet +
            $sokoniNet;

        $totalProfit =
            $sokoniNet +
            $stendiNet;



        /*
        |--------------------------------------------------------------------------
        | WEEKLY TOTALS
        |--------------------------------------------------------------------------
        */

        $weeklyRevenue =
            ToiletDailyEntry::whereBetween(
                'entry_date',
                [
                    $startOfWeek,
                    $endOfWeek,
                ]
            )->sum('total_revenue');



        $weeklyExpenses =
            ToiletExpense::whereBetween(
                'created_at',
                [
                    $startOfWeek,
                    $endOfWeek,
                ]
            )->sum('amount');



        $weeklyNet =
            $weeklyRevenue -
            $weeklyExpenses;



        /*
        |--------------------------------------------------------------------------
        | MONTHLY TOTALS
        |--------------------------------------------------------------------------
        */

        $monthlyRevenue =
            ToiletDailyEntry::whereBetween(
                'entry_date',
                [
                    $startOfMonth,
                    $endOfMonth,
                ]
            )->sum('total_revenue');



        $monthlyExpenses =
            ToiletExpense::whereBetween(
                'created_at',
                [
                    $startOfMonth,
                    $endOfMonth,
                ]
            )->sum('amount');



        $monthlyNet =
            $monthlyRevenue -
            $monthlyExpenses;



        /*
        |--------------------------------------------------------------------------
        | ACTIVE TOILETS
        |--------------------------------------------------------------------------
        */

        $activeToilets = Toilet::count();



        /*
        |--------------------------------------------------------------------------
        | RECENT REPORTS
        |--------------------------------------------------------------------------
        */

        $recentReports = ToiletDailyEntry::with([
            'toilet',
            'expenses',
        ])
            ->latest('entry_date')
            ->take(10)
            ->get();



        /*
        |--------------------------------------------------------------------------
        | CHART DATA
        |--------------------------------------------------------------------------
        */

        $chartLabels = [];

        $revenueChart = [];

        $expensesChart = [];



        for ($i = 6; $i >= 0; $i--) {

            $date = Carbon::now()->subDays($i);



            $chartLabels[] =
                $date->format('D');



            $revenueChart[] =
                ToiletDailyEntry::whereDate(
                    'entry_date',
                    $date
                )->sum('total_revenue');



            $expensesChart[] =
                ToiletExpense::whereDate(
                    'created_at',
                    $date
                )->sum('amount');
        }



        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'toilets.index',
            compact(

                'lastDate',

                'todayRevenue',
                'todayExpenses',
                'todayNet',

                'weeklyRevenue',
                'weeklyExpenses',
                'weeklyNet',

                'monthlyRevenue',
                'monthlyExpenses',
                'monthlyNet',

                'stendiRevenue',
                'stendiCollections',
                'stendiExpenses',
                'stendiNet',

                'sokoniRevenue',
                'sokoniCollections',
                'sokoniPosAmount',
                'sokoniCouncilShare',
                'sokoniYourShare',
                'sokoniOutsidePos',
                'sokoniGrossProfit',
                'sokoniExpenses',
                'sokoniNet',

                'activeToilets',

                'recentReports',

                'chartLabels',
                'revenueChart',
                'expensesChart',


                'totalProfit'
            )
        );
    }
}