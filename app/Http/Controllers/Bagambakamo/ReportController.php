<?php

namespace App\Http\Controllers\Bagambakamo;

use App\Http\Controllers\Controller;
use App\Models\Bagambakamo\Event;
use App\Models\Bagambakamo\Member;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index()
    {
        $members = Member::with([
            'payments',
            'events',
        ])
            ->get()
            ->filter(fn ($member) =>
                $member->total_paid >= 210000
            )
            ->values();

        return view(
            'bagambakamo.reports.index',
            compact('members')
        );
    }

    public function downloadPDF()
    {
        $allMembers = Member::with([
            'payments',
            'events',
        ])->get();

        $members = $allMembers
            ->filter(fn ($member) =>
                $member->total_paid >= 210000
            )
            ->sort(function ($a, $b) {

                if (
                    $a->balance_amount
                    !=
                    $b->balance_amount
                ) {
                    return $a->balance_amount
                        <=>
                        $b->balance_amount;
                }

                if (
                    $a->total_paid
                    !=
                    $b->total_paid
                ) {
                    return $b->total_paid
                        <=>
                        $a->total_paid;
                }

                return strcmp(
                    $a->full_name,
                    $b->full_name
                );
            })
            ->values();

        $expectedMain = $members->sum(
            fn ($member) =>
                $member->expected_amount
                +
                $member->total_events
        );

        $lowMembersTotal = $allMembers
            ->filter(fn ($member) =>
                $member->total_paid < 210000
            )
            ->sum('total_paid');

        $expected =
            $expectedMain
            +
            $lowMembersTotal;

        $collected = $allMembers
            ->sum('total_paid');

        $msiba = Event::sum('amount');

        $remaining =
            $collected
            -
            $msiba;

        $excess = $members->sum(
            function ($member) {

                $expectedTotal =
                    $member->expected_amount
                    +
                    $member->total_events;

                $extra =
                    $member->total_paid
                    -
                    $expectedTotal;

                return $extra > 0
                    ? $extra
                    : 0;
            }
        );

        $totalDebt = $members
            ->sum('balance_amount');

        $sumExpected = $members
            ->sum('expected_amount');

        $sumEvents = $members
            ->sum('total_events');

        $sumTotal = $members->sum(
            fn ($member) =>
                $member->expected_amount
                +
                $member->total_events
        );

        $sumPaid = $members
            ->sum('total_paid');

        $sumBalance = $members
            ->sum('balance_amount');

        $reportDate = now();

        $pdf = Pdf::loadView(
            'bagambakamo.reports.members_report',
            compact(
                'members',
                'expected',
                'collected',
                'msiba',
                'remaining',
                'excess',
                'totalDebt',
                'reportDate',
                'sumExpected',
                'sumEvents',
                'sumTotal',
                'sumPaid',
                'sumBalance',
                'lowMembersTotal'
            )
        );

        return $pdf->download(
            'bagambakamo_report.pdf'
        );
    }
}