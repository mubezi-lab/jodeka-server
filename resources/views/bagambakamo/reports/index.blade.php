@extends('layouts.admin')

@section('title', 'Bagambakamo Reports')

@section('content')

    <style>
        .bag-report-page {
            padding: 28px;
        }

        .bag-report-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 24px;
        }

        .bag-report-title-wrap {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .bag-report-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: #eef2ff;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .bag-report-title {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            color: #111827;
        }

        .bag-report-subtitle {
            margin: 5px 0 0;
            color: #6b7280;
            font-size: 15px;
        }

        .bag-report-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .bag-pdf-btn {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 12px 18px;
            border-radius: 12px;
            background: #dc2626;
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            transition: .2s ease;
            box-shadow: 0 6px 14px rgba(220, 38, 38, 0.18);
        }

        .bag-pdf-btn:hover {
            background: #b91c1c;
            color: #fff;
            transform: translateY(-1px);
        }

        .bag-report-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .bag-stat-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
        }

        .bag-stat-label {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .bag-stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            line-height: 1.2;
        }

        .bag-stat-value.green {
            color: #15803d;
        }

        .bag-stat-value.red {
            color: #b91c1c;
        }

        .bag-stat-value.blue {
            color: #1d4ed8;
        }

        .bag-table-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.05);
        }

        .bag-table-card-header {
            padding: 20px 22px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .bag-table-card-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #111827;
        }

        .bag-table-card-header p {
            margin: 5px 0 0;
            color: #6b7280;
            font-size: 14px;
        }

        .bag-table-responsive {
            overflow-x: auto;
        }

        .bag-report-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 980px;
        }

        .bag-report-table thead th {
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

        .bag-report-table tbody td {
            padding: 15px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #1f2937;
            font-size: 14px;
            vertical-align: middle;
        }

        .bag-report-table tbody tr:hover {
            background: #fafcff;
        }

        .bag-member {
            font-weight: 700;
            color: #111827;
            white-space: nowrap;
        }

        .bag-phone {
            white-space: nowrap;
            color: #475569;
        }

        .bag-money {
            white-space: nowrap;
            font-weight: 600;
        }

        .bag-money.paid {
            color: #15803d;
        }

        .bag-money.balance-due {
            color: #b91c1c;
        }

        .bag-money.balance-clear {
            color: #15803d;
        }

        .bag-balance-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .bag-balance-badge.due {
            background: #fee2e2;
            color: #b91c1c;
        }

        .bag-balance-badge.clear {
            background: #dcfce7;
            color: #15803d;
        }

        .bag-empty {
            text-align: center;
            padding: 40px 20px !important;
            color: #94a3b8 !important;
        }

        @media (max-width: 1100px) {
            .bag-report-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .bag-report-page {
                padding: 18px;
            }

            .bag-report-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .bag-report-stats {
                grid-template-columns: 1fr;
            }

            .bag-report-title {
                font-size: 24px;
            }

            .bag-report-actions {
                width: 100%;
            }

            .bag-pdf-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    @php
        $memberCount = $members->count();

        $totalExpected = $members->sum(function ($member) {
            return $member->expected_amount;
        });

        $totalPaid = $members->sum(function ($member) {
            return $member->total_paid;
        });

        $totalBalance = $members->sum(function ($member) {
            return $member->balance_amount;
        });
    @endphp

    <div class="bag-report-page">

        <div class="bag-report-header">

            <div class="bag-report-title-wrap">

                <div class="bag-report-icon">
                    <i class="fa-solid fa-chart-column"></i>
                </div>

                <div>
                    <h1 class="bag-report-title">
                        Bagambakamo Reports
                    </h1>

                    <p class="bag-report-subtitle">
                        Member financial summary and outstanding balances
                    </p>
                </div>

            </div>

            <div class="bag-report-actions">

                <a href="{{ route('bagambakamo.report.pdf') }}" target="_blank" class="bag-pdf-btn">
                    <i class="fa-solid fa-file-pdf"></i>
                    Download PDF
                </a>

            </div>

        </div>


        <div class="bag-report-stats">

            <div class="bag-stat-card">
                <div class="bag-stat-label">
                    Members
                </div>

                <div class="bag-stat-value blue">
                    {{ number_format($memberCount) }}
                </div>
            </div>

            <div class="bag-stat-card">
                <div class="bag-stat-label">
                    Total Expected
                </div>

                <div class="bag-stat-value">
                    TSH {{ number_format($totalExpected) }}
                </div>
            </div>

            <div class="bag-stat-card">
                <div class="bag-stat-label">
                    Total Paid
                </div>

                <div class="bag-stat-value green">
                    TSH {{ number_format($totalPaid) }}
                </div>
            </div>

            <div class="bag-stat-card">
                <div class="bag-stat-label">
                    Outstanding Balance
                </div>

                <div class="bag-stat-value red">
                    TSH {{ number_format($totalBalance) }}
                </div>
            </div>

        </div>


        <div class="bag-table-card">

            <div class="bag-table-card-header">

                <div>
                    <h3>
                        <i class="fa-solid fa-users"></i>
                        Member Financial Report
                    </h3>

                    <p>
                        Current payment, event contribution and balance position
                    </p>
                </div>

            </div>


            <div class="bag-table-responsive">

                <table class="bag-report-table">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Member</th>
                            <th>Phone</th>
                            <th>Expected</th>
                            <th>Events</th>
                            <th>Total Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($members as $index => $member)

                                        <tr>

                                            <td>
                                                {{ $index + 1 }}
                                            </td>

                                            <td>
                                                <span class="bag-member">
                                                    {{ $member->full_name }}
                                                </span>
                                            </td>

                                            <td>
                                                <span class="bag-phone">
                                                    {{ $member->phone ?: '-' }}
                                                </span>
                                            </td>

                                            <td>
                                                <span class="bag-money">
                                                    TSH {{ number_format($member->expected_amount) }}
                                                </span>
                                            </td>

                                            <td>
                                                <span class="bag-money">
                                                    TSH {{ number_format($member->total_events) }}
                                                </span>
                                            </td>

                                            <td>
                                                <span class="bag-money paid">
                                                    TSH {{ number_format($member->total_paid) }}
                                                </span>
                                            </td>

                                            <td>
                                                <span class="bag-money {{
                            $member->balance_amount > 0
                            ? 'balance-due'
                            : 'balance-clear'
                                                        }}">
                                                    TSH {{ number_format($member->balance_amount) }}
                                                </span>
                                            </td>

                                            <td>

                                                @if($member->balance_amount > 0)

                                                    <span class="bag-balance-badge due">
                                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                                        Outstanding
                                                    </span>

                                                @else

                                                    <span class="bag-balance-badge clear">
                                                        <i class="fa-solid fa-circle-check"></i>
                                                        Paid
                                                    </span>

                                                @endif

                                            </td>

                                        </tr>

                        @empty

                            <tr>
                                <td colspan="8" class="bag-empty">
                                    <i class="fa-solid fa-chart-column"></i>
                                    No report data found.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

@endsection