<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Loan;
use App\Models\Saving;
use App\Models\Transaction;

use Illuminate\Http\Request;

use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | REPORTS DASHBOARD
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | FILTERS
        |--------------------------------------------------------------------------
        */

        $from = $request->from
            ?? now()->startOfMonth()->toDateString();

        $to = $request->to
            ?? now()->toDateString();

        $businessId = $request->business_id;

        /*
        |--------------------------------------------------------------------------
        | INCOME QUERY
        |--------------------------------------------------------------------------
        */

        $incomeQuery = Transaction::where('type', 'income')
            ->whereBetween('transaction_date', [$from, $to]);

        /*
        |--------------------------------------------------------------------------
        | EXPENSE QUERY
        |--------------------------------------------------------------------------
        */

        $expenseQuery = Transaction::where('type', 'expense')
            ->whereBetween('transaction_date', [$from, $to]);

        /*
        |--------------------------------------------------------------------------
        | BUSINESS FILTER
        |--------------------------------------------------------------------------
        */

        if ($businessId) {

            $incomeQuery->where(
                'business_id',
                $businessId
            );

            $expenseQuery->where(
                'business_id',
                $businessId
            );
        }

        /*
        |--------------------------------------------------------------------------
        | TOTALS
        |--------------------------------------------------------------------------
        */

        $totalIncome = $incomeQuery->sum('amount');

        $totalExpenses = $expenseQuery->sum('amount');

        $netProfit = $totalIncome - $totalExpenses;

        /*
        |--------------------------------------------------------------------------
        | SAVINGS
        |--------------------------------------------------------------------------
        */

        $savingsQuery = Saving::query();

        if ($businessId) {

            $savingsQuery->where(
                'business_id',
                $businessId
            );
        }

        $totalSavings = $savingsQuery->sum('balance');

        /*
        |--------------------------------------------------------------------------
        | LOANS
        |--------------------------------------------------------------------------
        */

        $loanQuery = Loan::query();

        if ($businessId) {

            $loanQuery->where(
                'business_id',
                $businessId
            );
        }

        $totalLoans = $loanQuery->sum(
            'remaining_amount'
        );

        /*
        |--------------------------------------------------------------------------
        | BUSINESSES
        |--------------------------------------------------------------------------
        */

        $totalBusinesses = Business::count();

        $businesses = Business::orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | TRANSACTIONS
        |--------------------------------------------------------------------------
        */

        $transactions = Transaction::with('business')
            ->whereBetween(
                'transaction_date',
                [$from, $to]
            )
            ->latest();

        if ($businessId) {

            $transactions->where(
                'business_id',
                $businessId
            );
        }

        $transactions = $transactions->get();

        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view('reports.index', compact(

            'totalIncome',

            'totalExpenses',

            'netProfit',

            'totalSavings',

            'totalLoans',

            'totalBusinesses',

            'businesses',

            'transactions',

            'from',

            'to',

            'businessId'

        ));
    }

    /*
    |--------------------------------------------------------------------------
    | MONTHLY PDF REPORT
    |--------------------------------------------------------------------------
    */
    public function monthly(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | FILTERS
        |--------------------------------------------------------------------------
        */

        $from = $request->from
            ?? now()->startOfMonth()->toDateString();

        $to = $request->to
            ?? now()->toDateString();

        /*
        |--------------------------------------------------------------------------
        | TRANSACTIONS
        |--------------------------------------------------------------------------
        */

        $transactions = Transaction::with('business')
            ->whereBetween(
                'transaction_date',
                [$from, $to]
            )
            ->latest()
            ->get();

        /*
        |--------------------------------------------------------------------------
        | TOTALS
        |--------------------------------------------------------------------------
        */

        $totalIncome = Transaction::where('type', 'income')
            ->whereBetween(
                'transaction_date',
                [$from, $to]
            )
            ->sum('amount');

        $totalExpenses = Transaction::where('type', 'expense')
            ->whereBetween(
                'transaction_date',
                [$from, $to]
            )
            ->sum('amount');

        $netProfit = $totalIncome - $totalExpenses;

        /*
        |--------------------------------------------------------------------------
        | PDF DATA
        |--------------------------------------------------------------------------
        */

        $data = [

            'transactions' => $transactions,

            'totalIncome' => $totalIncome,

            'totalExpenses' => $totalExpenses,

            'netProfit' => $netProfit,

            'from' => $from,

            'to' => $to

        ];

        /*
        |--------------------------------------------------------------------------
        | GENERATE PDF
        |--------------------------------------------------------------------------
        */

        $pdf = Pdf::loadView(
            'reports.pdf',
            $data
        );

        /*
        |--------------------------------------------------------------------------
        | DOWNLOAD PDF
        |--------------------------------------------------------------------------
        */

        return $pdf->download(
            'financial-report.pdf'
        );
    }
}
