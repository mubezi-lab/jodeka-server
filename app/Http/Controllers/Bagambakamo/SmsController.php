<?php

namespace App\Http\Controllers\Bagambakamo;

use App\Http\Controllers\Controller;
use App\Models\Bagambakamo\Member;
use App\Models\Bagambakamo\SmsReport;
use App\Jobs\SendSmsJob;

class SmsController extends Controller
{
    public function sendToDebtors()
    {
        /*
        |--------------------------------------------------------------------------
        | Find Members With Outstanding Balance
        |--------------------------------------------------------------------------
        */

        $members = Member::with('payments')
            ->get()
            ->filter(function ($member) {

                $totalPaid = $member
                    ->payments
                    ->sum('amount');

                return (
                    (
                        $totalPaid >= 210000
                        &&
                        $member->balance_amount > 0
                    )
                    ||
                    (
                        $totalPaid >= 0
                        &&
                        $totalPaid < 210000
                        &&
                        $member->balance_amount > 0
                    )
                );
            });

        if ($members->isEmpty()) {
            return back()->with(
                'error',
                'Hakuna wadaiwa waliofikia vigezo.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Counters
        |--------------------------------------------------------------------------
        */

        $count = 0;
        $skipped = 0;
        $invalidPhone = 0;

        /*
        |--------------------------------------------------------------------------
        | Prepare SMS Jobs
        |--------------------------------------------------------------------------
        */

        foreach ($members as $member) {

            /*
            |--------------------------------------------------------------------------
            | Validate Phone
            |--------------------------------------------------------------------------
            */

            if (
                !$member->phone
                ||
                strlen($member->phone) < 10
            ) {
                $invalidPhone++;

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Determine Member Group
            |--------------------------------------------------------------------------
            */

            $totalPaid = $member
                ->payments
                ->sum('amount');

            if ($totalPaid >= 210000) {

                $group = 1;

            } elseif (
                $totalPaid >= 0
                &&
                $totalPaid < 210000
            ) {

                $group = 2;

            } else {

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Prevent Duplicate Debt Reminder
            |--------------------------------------------------------------------------
            |
            | If this member has already received a successful debt reminder
            | today for the same group, do not dispatch another SMS.
            |
            */

            $alreadySentToday = SmsReport::where(
                    'member_id',
                    $member->id
                )
                ->where(
                    'group_type',
                    $group
                )
                ->where(
                    'status',
                    'sent'
                )
                ->whereDate(
                    'sent_at',
                    today()
                )
                ->exists();

            if ($alreadySentToday) {
                $skipped++;

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Dispatch SMS
            |--------------------------------------------------------------------------
            */

            SendSmsJob::dispatch(
                $member,
                $group
            );

            $count++;
        }

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        if ($count === 0) {

            if ($skipped > 0) {
                return back()->with(
                    'success',
                    "Hakuna SMS mpya iliyotumwa. {$skipped} members tayari wametumiwa reminder leo."
                );
            }

            return back()->with(
                'error',
                'Hakuna SMS iliyotumwa. Hakikisha members wana namba sahihi za simu.'
            );
        }

        $message = "SMS zinatumwa kwa {$count} members (background).";

        if ($skipped > 0) {
            $message .= " {$skipped} tayari wametumiwa reminder leo.";
        }

        if ($invalidPhone > 0) {
            $message .= " {$invalidPhone} wameachwa kwa sababu ya namba za simu zisizo sahihi.";
        }

        return back()->with(
            'success',
            $message
        );
    }
}