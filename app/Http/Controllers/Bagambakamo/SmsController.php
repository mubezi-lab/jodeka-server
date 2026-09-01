<?php

namespace App\Http\Controllers\Bagambakamo;

use App\Http\Controllers\Controller;
use App\Models\Bagambakamo\Member;
use App\Jobs\SendSmsJob;

class SmsController extends Controller
{
    public function sendToDebtors()
    {
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

        $count = 0;

        foreach ($members as $member) {

            if (
                !$member->phone
                ||
                strlen($member->phone) < 10
            ) {
                continue;
            }

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

            SendSmsJob::dispatch(
                $member,
                $group
            );

            $count++;
        }

        return back()->with(
            'success',
            "SMS zinatumwa kwa {$count} members (background)."
        );
    }
}