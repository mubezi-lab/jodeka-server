<?php

namespace App\Http\Controllers\Bagambakamo;

use App\Http\Controllers\Controller;
use App\Models\Bagambakamo\SmsReport;
use Carbon\Carbon;

class SmsReportController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Determine Report Date
        |--------------------------------------------------------------------------
        |
        | 1. If there are SMS reports for today, show today's reports.
        | 2. If there are no reports today, show reports from the latest date
        |    available in the database.
        |
        */

        $today = today()->toDateString();

        $hasTodayReports = SmsReport::whereDate(
            'sent_at',
            $today
        )->exists();

        if ($hasTodayReports) {

            $reportDate = $today;

        } else {

            $latestSentAt = SmsReport::whereNotNull('sent_at')
                ->latest('sent_at')
                ->value('sent_at');

            $reportDate = $latestSentAt
                ? Carbon::parse($latestSentAt)->toDateString()
                : null;
        }


        /*
        |--------------------------------------------------------------------------
        | SMS Reports
        |--------------------------------------------------------------------------
        */

        $reports = SmsReport::query()
            ->when(
                $reportDate,
                function ($query) use ($reportDate) {
                    $query->whereDate(
                        'sent_at',
                        $reportDate
                    );
                }
            )
            ->latest('sent_at')
            ->latest('id')
            ->paginate(20);


        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'bagambakamo.sms-reports.index',
            compact(
                'reports',
                'reportDate'
            )
        );
    }
}