<?php

namespace App\Http\Controllers\Bagambakamo;

use App\Http\Controllers\Controller;
use App\Models\Bagambakamo\SmsReport;

class SmsReportController extends Controller
{
    public function index()
    {
        $reports = SmsReport::latest('sent_at')
            ->paginate(20);

        return view(
            'bagambakamo.sms-reports.index',
            compact('reports')
        );
    }
}