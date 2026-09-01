@extends('layouts.admin')

@section('title', 'Bagambakamo SMS Reports')

@section('content')

    <style>
        .sms-report-page {
            padding: 28px;
        }

        .sms-report-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 24px;
        }

        .sms-report-title-wrap {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .sms-report-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: #eaf2ff;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .sms-report-title {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            color: #111827;
        }

        .sms-report-subtitle {
            margin: 5px 0 0;
            color: #6b7280;
            font-size: 15px;
        }

        .sms-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .sms-stat-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
        }

        .sms-stat-label {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .sms-stat-value {
            font-size: 28px;
            line-height: 1;
            font-weight: 700;
            color: #111827;
        }

        .sms-table-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.05);
        }

        .sms-table-card-header {
            padding: 20px 22px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .sms-table-card-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #111827;
        }

        .sms-table-card-header p {
            margin: 5px 0 0;
            color: #6b7280;
            font-size: 14px;
        }

        .sms-table-responsive {
            overflow-x: auto;
        }

        .sms-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1050px;
        }

        .sms-table thead th {
            background: #f8fafc;
            color: #475569;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: .04em;
            font-weight: 700;
            padding: 14px 16px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            white-space: nowrap;
        }

        .sms-table tbody td {
            padding: 15px 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
            color: #1f2937;
            font-size: 14px;
        }

        .sms-table tbody tr:hover {
            background: #fafcff;
        }

        .sms-message {
            max-width: 520px;
            line-height: 1.55;
            color: #334155;
        }

        .sms-name {
            font-weight: 600;
            color: #111827;
            white-space: nowrap;
        }

        .sms-phone {
            white-space: nowrap;
            color: #475569;
        }

        .sms-group-badge,
        .sms-status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border-radius: 999px;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .sms-group-1 {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .sms-group-2 {
            background: #fef3c7;
            color: #b45309;
        }

        .sms-status-sent {
            background: #dcfce7;
            color: #15803d;
        }

        .sms-status-failed {
            background: #fee2e2;
            color: #b91c1c;
        }

        .sms-time {
            white-space: nowrap;
            color: #64748b;
        }

        .sms-empty {
            text-align: center;
            padding: 40px 20px !important;
            color: #94a3b8 !important;
        }

        .sms-pagination {
            padding: 18px 22px;
            border-top: 1px solid #e5e7eb;
        }

        @media (max-width: 992px) {
            .sms-report-page {
                padding: 18px;
            }

            .sms-stats {
                grid-template-columns: 1fr;
            }

            .sms-report-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .sms-report-title {
                font-size: 24px;
            }
        }
    </style>

    @php
        $totalReports = $reports->total();

        $sentCount = collect($reports->items())
            ->where('status', 'sent')
            ->count();

        $failedCount = collect($reports->items())
            ->where('status', '!=', 'sent')
            ->count();
    @endphp

    <div class="sms-report-page">

        <div class="sms-report-header">

            <div class="sms-report-title-wrap">

                <div class="sms-report-icon">
                    <i class="fa-solid fa-comments"></i>
                </div>

                <div>
                    <h1 class="sms-report-title">
                        Bagambakamo SMS Reports
                    </h1>

                    <p class="sms-report-subtitle">
                        History of debt reminders and SMS notifications sent to members
                    </p>
                </div>

            </div>

        </div>


        <div class="sms-stats">

            <div class="sms-stat-card">
                <div class="sms-stat-label">
                    Total SMS Reports
                </div>

                <div class="sms-stat-value">
                    {{ number_format($totalReports) }}
                </div>
            </div>

            <div class="sms-stat-card">
                <div class="sms-stat-label">
                    Sent on This Page
                </div>

                <div class="sms-stat-value">
                    {{ number_format($sentCount) }}
                </div>
            </div>

            <div class="sms-stat-card">
                <div class="sms-stat-label">
                    Failed / Other on This Page
                </div>

                <div class="sms-stat-value">
                    {{ number_format($failedCount) }}
                </div>
            </div>

        </div>


        <div class="sms-table-card">

            <div class="sms-table-card-header">

                <div>
                    <h3>
                        <i class="fa-solid fa-message"></i>
                        SMS History
                    </h3>

                    <p>
                        Showing {{ $reports->firstItem() ?? 0 }}
                        -
                        {{ $reports->lastItem() ?? 0 }}
                        of {{ number_format($reports->total()) }} records
                    </p>
                </div>

            </div>


            <div class="sms-table-responsive">

                <table class="sms-table">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Message</th>
                            <th>Group</th>
                            <th>Status</th>
                            <th>Time</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($reports as $report)

                            <tr>

                                <td>
                                    {{ $report->id }}
                                </td>

                                <td>
                                    <span class="sms-name">
                                        {{ $report->name ?: '-' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="sms-phone">
                                        {{ $report->phone ?: '-' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="sms-message">
                                        {{ $report->message ?: '-' }}
                                    </div>
                                </td>

                                <td>

                                    @if((int) $report->group_type === 1)

                                        <span class="sms-group-badge sms-group-1">
                                            Group 1
                                        </span>

                                    @elseif((int) $report->group_type === 2)

                                        <span class="sms-group-badge sms-group-2">
                                            Group 2
                                        </span>

                                    @else

                                        <span class="sms-group-badge">
                                            -
                                        </span>

                                    @endif

                                </td>

                                <td>

                                    @if($report->status === 'sent')

                                        <span class="sms-status-badge sms-status-sent">
                                            <i class="fa-solid fa-circle-check"></i>
                                            Sent
                                        </span>

                                    @else

                                        <span class="sms-status-badge sms-status-failed">
                                            <i class="fa-solid fa-circle-xmark"></i>
                                            {{ ucfirst($report->status ?? 'Unknown') }}
                                        </span>

                                    @endif

                                </td>

                                <td>
                                    <span class="sms-time">

                                        @if($report->sent_at)

                                            {{ $report->sent_at->format('d/m/Y') }}
                                            <br>
                                            {{ $report->sent_at->format('H:i') }}

                                        @else

                                            -

                                        @endif

                                    </span>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="7" class="sms-empty">
                                    <i class="fa-regular fa-message"></i>
                                    No SMS reports found.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            <div class="sms-pagination">
                {{ $reports->links() }}
            </div>

        </div>

    </div>

@endsection