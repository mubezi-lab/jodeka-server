<?php

namespace App\Http\Controllers\Bagambakamo;

use App\Http\Controllers\Controller;
use App\Models\Bagambakamo\Event;
use App\Models\Bagambakamo\Member;
use App\Models\Bagambakamo\Payment;
use App\Models\Bagambakamo\PendingTransaction;

class AdminController extends Controller
{
    public function index()
    {
        $totalMembers = Member::withSum('payments', 'amount')
            ->having('payments_sum_amount', '>=', 80000)
            ->count();

        $totalPayments = Payment::sum('amount');

        $monthlyPayments = Payment::whereYear('payment_date', now()->year)
            ->whereMonth('payment_date', now()->month)
            ->sum('amount');

        $totalEventsGiven = Event::sum('amount');

        $groupBalance = $totalPayments - $totalEventsGiven;

        $totalDebts = Member::with([
                'payments',
                'events'
            ])
            ->get()
            ->filter(
                fn ($member) =>
                    $member->total_paid >= 80000
            )
            ->sum(
                fn ($member) =>
                    max(0, $member->balance_amount)
            );

        $recentPayments = Payment::with('member')
            ->latest('payment_date')
            ->latest('id')
            ->take(5)
            ->get();

        $recentEvents = Event::with('member')
            ->latest('event_date')
            ->latest('id')
            ->take(5)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | PENDING M-KOBA OUTGOING TRANSACTIONS
        |--------------------------------------------------------------------------
        |
        | Outgoing transactions received from SMS Forwarder
        | which still require admin confirmation.
        |
        */

        $pendingTransactions = PendingTransaction::with('member')
            ->where('status', 'pending')
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $pendingTransactionsCount =
            $pendingTransactions->count();


        /*
        |--------------------------------------------------------------------------
        | MONTHLY CHART
        |--------------------------------------------------------------------------
        */

        $monthlyPaymentChart = [];
        $monthlyEventChart = [];

        for ($month = 1; $month <= 12; $month++) {

            $monthlyPaymentChart[] =
                Payment::whereYear(
                    'payment_date',
                    now()->year
                )
                ->whereMonth(
                    'payment_date',
                    $month
                )
                ->sum('amount');

            $monthlyEventChart[] =
                Event::whereYear(
                    'event_date',
                    now()->year
                )
                ->whereMonth(
                    'event_date',
                    $month
                )
                ->sum('amount');
        }


        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'bagambakamo.admin.dashboard',
            compact(
                'totalMembers',
                'totalPayments',
                'monthlyPayments',
                'totalEventsGiven',
                'groupBalance',
                'totalDebts',
                'recentPayments',
                'recentEvents',
                'pendingTransactions',
                'pendingTransactionsCount',
                'monthlyPaymentChart',
                'monthlyEventChart'
            )
        );
    }
}