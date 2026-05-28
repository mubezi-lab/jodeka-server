<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | FILTER RANGE
        |--------------------------------------------------------------------------
        */

        $filter = $request->filter ?? 'month';

        $query = Transaction::query();

        switch ($filter) {

            case 'today':

                $query->whereDate(
                    'transaction_date',
                    today()
                );

                break;

            case 'week':

                $query->whereBetween('transaction_date', [

                    now()->startOfWeek(),

                    now()->endOfWeek()

                ]);

                break;

            case 'year':

                $query->whereYear(
                    'transaction_date',
                    now()->year
                );

                break;

            default:

                $query->whereMonth(
                    'transaction_date',
                    now()->month
                )->whereYear(
                    'transaction_date',
                    now()->year
                );

                break;
        }

        /*
        |--------------------------------------------------------------------------
        | FILTERED ANALYTICS
        |--------------------------------------------------------------------------
        */

        $todayIncome = (clone $query)
            ->where('type', 'income')
            ->sum('amount');

        $todayExpenses = (clone $query)
            ->where('type', 'expense')
            ->sum('amount');

        $todayProfit = $todayIncome - $todayExpenses;

        /*
        |--------------------------------------------------------------------------
        | MONTHLY ANALYTICS
        |--------------------------------------------------------------------------
        */

        $monthlyIncome = Transaction::where('type', 'income')
            ->whereMonth('transaction_date', Carbon::now()->month)
            ->whereYear('transaction_date', Carbon::now()->year)
            ->sum('amount');

        $monthlyExpenses = Transaction::where('type', 'expense')
            ->whereMonth('transaction_date', Carbon::now()->month)
            ->whereYear('transaction_date', Carbon::now()->year)
            ->sum('amount');

        $monthlyProfit = $monthlyIncome - $monthlyExpenses;

        /*
        |--------------------------------------------------------------------------
        | SYSTEM TOTALS
        |--------------------------------------------------------------------------
        */

        $totalBusinesses = Business::count();

        $totalUsers = User::count();

        $totalProducts = Product::count();

        $totalTransactions = Transaction::count();

        /*
        |--------------------------------------------------------------------------
        | RECENT TRANSACTIONS
        |--------------------------------------------------------------------------
        */

        $recentTransactions = Transaction::with('business')
            ->latest()
            ->take(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | MONTHLY CHART DATA
        |--------------------------------------------------------------------------
        */

        $monthlyChart = Transaction::select(

                DB::raw('MONTH(transaction_date) as month'),

                DB::raw("
                    SUM(
                        CASE
                            WHEN type = 'income'
                            THEN amount
                            ELSE 0
                        END
                    ) as income
                "),

                DB::raw("
                    SUM(
                        CASE
                            WHEN type = 'expense'
                            THEN amount
                            ELSE 0
                        END
                    ) as expenses
                ")

            )
            ->whereYear('transaction_date', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $chartLabels = [];

        $incomeData = [];

        $expenseData = [];

        foreach ($monthlyChart as $data) {

            $chartLabels[] = date(
                'M',
                mktime(0, 0, 0, $data->month, 1)
            );

            $incomeData[] = $data->income;

            $expenseData[] = $data->expenses;
        }

        /*
        |--------------------------------------------------------------------------
        | BUSINESS PERFORMANCE
        |--------------------------------------------------------------------------
        */

        $businessPerformance = Business::with('transactions')
            ->get()
            ->map(function ($business) {

                $income = $business->transactions
                    ->where('type', 'income')
                    ->sum('amount');

                $expenses = $business->transactions
                    ->where('type', 'expense')
                    ->sum('amount');

                return [

                    'name' => $business->name,

                    'income' => $income,

                    'expenses' => $expenses,

                    'profit' => $income - $expenses,

                ];
            });

        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view('dashboard', compact(

            'filter',

            'todayIncome',
            'todayExpenses',
            'todayProfit',

            'monthlyIncome',
            'monthlyExpenses',
            'monthlyProfit',

            'totalBusinesses',
            'totalUsers',
            'totalProducts',
            'totalTransactions',

            'recentTransactions',

            'chartLabels',
            'incomeData',
            'expenseData',

            'businessPerformance'

        ));
    }
}