@extends('layouts.admin')

@section('title', 'Hotspot Vouchers')

@section('content')

    @php
        /*
        |--------------------------------------------------------------------------
        | FORMAT BYTES
        |--------------------------------------------------------------------------
        */

        $formatBytes = function ($bytes) {
            $bytes = (int) $bytes;

            if ($bytes <= 0) {
                return '0 MB';
            }

            if ($bytes >= 1073741824) {
                return number_format(
                    $bytes / 1073741824,
                    2
                ) . ' GB';
            }

            if ($bytes >= 1048576) {
                return number_format(
                    $bytes / 1048576,
                    2
                ) . ' MB';
            }

            if ($bytes >= 1024) {
                return number_format(
                    $bytes / 1024,
                    2
                ) . ' KB';
            }

            return number_format($bytes) . ' B';
        };


        /*
        |--------------------------------------------------------------------------
        | TAB URL
        |--------------------------------------------------------------------------
        */

        $tabUrl = function ($name) {
            return route(
                'hotspot-vouchers.index',
                [
                    'tab' => $name,
                ]
            );
        };


        /*
        |--------------------------------------------------------------------------
        | DISPLAY CUSTOMER NAME
        |--------------------------------------------------------------------------
        |
        | SMS example:
        |
        | SMS payment 26493358391111 - JACKSON KAIKA
        |
        | Display:
        |
        | J. KAIKA
        |
        | Manual voucher comments remain unchanged.
        |
        */

        $displayCustomerName = function ($voucher) {

            $comment = trim(
                (string) (
                    $voucher->comment
                    ?? ''
                )
            );

            if ($comment === '') {
                return '-';
            }

            /*
            |--------------------------------------------------------------------------
            | SMS PAYMENT
            |--------------------------------------------------------------------------
            */

            if (
                preg_match(
                    '/^SMS payment\s+\S+\s*-\s*(.+)$/i',
                    $comment,
                    $match
                )
            ) {
                $fullName = trim(
                    $match[1]
                );

                if ($fullName === '') {
                    return '-';
                }

                $parts = preg_split(
                    '/\s+/',
                    $fullName,
                    -1,
                    PREG_SPLIT_NO_EMPTY
                );

                /*
                |--------------------------------------------------------------------------
                | ONLY ONE NAME
                |--------------------------------------------------------------------------
                */

                if (count($parts) === 1) {
                    return $parts[0];
                }

                /*
                |--------------------------------------------------------------------------
                | FIRST INITIAL + LAST NAME
                |--------------------------------------------------------------------------
                */

                $firstName = $parts[0];

                $lastName =
                    $parts[
                        count($parts) - 1
                    ];

                $initial = mb_strtoupper(
                    mb_substr(
                        $firstName,
                        0,
                        1
                    )
                );

                return
                    $initial
                    . '. '
                    . $lastName;
            }

            /*
            |--------------------------------------------------------------------------
            | MANUAL VOUCHER COMMENT
            |--------------------------------------------------------------------------
            */

            return $comment;
        };
    @endphp


    <style>
        .hotspot-page {
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
        }

        .hotspot-heading {
            margin-bottom: 22px;
        }

        .hotspot-heading h1 {
            margin: 0;
            color: #0f2447;
            font-size: 27px;
            font-weight: 800;
            line-height: 1.2;
        }

        .hotspot-heading p {
            margin-top: 5px;
            color: #62799c;
            font-size: 14px;
        }

        .hotspot-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 19px;
            flex-wrap: wrap;
        }

        .voucher-tabs {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .voucher-tab {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;

            min-height: 44px;
            padding: 0 14px;

            border: 1px solid #e2e8f0;
            border-radius: 10px;

            color: #183153;
            background: #f1f5f9;

            text-decoration: none;

            font-size: 12px;
            font-weight: 700;

            transition: .16s ease;
        }

        .voucher-tab:hover {
            transform: translateY(-1px);
            background: #e9eff7;
        }

        .voucher-tab.active {
            color: #ffffff;
            border-color: #08a86c;

            background:
                linear-gradient(135deg,
                    #12ad72,
                    #019864);

            box-shadow:
                0 7px 18px rgba(5, 150, 105, .18);
        }

        .voucher-tab-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            min-width: 23px;
            height: 21px;

            padding: 0 6px;

            border-radius: 999px;

            color: #ffffff;
            background: #94a3b8;

            font-size: 10px;
            font-weight: 800;
        }

        .voucher-tab.active .voucher-tab-count {
            color: #059669;
            background: #ffffff;
        }

        .hotspot-actions {
            display: flex;
            align-items: center;
            gap: 9px;
            flex-wrap: wrap;
        }

        .hotspot-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;

            min-height: 44px;

            padding: 0 15px;

            border: none;
            border-radius: 10px;

            color: #ffffff;

            text-decoration: none;

            cursor: pointer;

            font-size: 12px;
            font-weight: 700;
        }

        .hotspot-action.dark {
            background: #263c5c;
        }

        .hotspot-action.green {
            background: #059669;
        }

        .hotspot-action.blue {
            background: #2563eb;
        }

        .hotspot-alert {
            margin-bottom: 16px;
            padding: 12px 15px;
            border-radius: 9px;
            font-size: 13px;
        }

        .hotspot-alert.success {
            color: #166534;
            background: #dcfce7;
        }

        .hotspot-alert.error {
            color: #991b1b;
            background: #fee2e2;
        }

        .hotspot-alert.warning {
            color: #92400e;
            background: #fef3c7;
        }

        .online-card {
            overflow: hidden;

            border: 1px solid #e2e8f0;
            border-radius: 14px;

            background: #ffffff;

            box-shadow:
                0 2px 8px rgba(15, 23, 42, .04);
        }

        .online-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 20px;

            padding: 18px 20px;

            border-bottom: 1px solid #e2e8f0;

            flex-wrap: wrap;
        }

        .online-title-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .online-dot {
            width: 17px;
            height: 17px;

            border-radius: 50%;

            background: #0aa76d;

            box-shadow:
                0 0 0 4px rgba(16, 185, 129, .08);
        }

        .online-card-title {
            color: #10254a;

            font-size: 20px;
            font-weight: 800;
        }

        .online-card-description {
            margin-top: 4px;

            color: #64748b;

            font-size: 13px;
        }

        .refresh-controls {
            display: flex;
            align-items: center;
            gap: 11px;

            color: #334155;

            font-size: 12px;
        }

        .refresh-toggle {
            position: relative;

            width: 46px;
            height: 25px;

            border: none;
            border-radius: 30px;

            cursor: pointer;

            background: #059669;
        }

        .refresh-toggle::after {
            content: '';

            position: absolute;

            top: 3px;
            right: 3px;

            width: 19px;
            height: 19px;

            border-radius: 50%;

            background: #ffffff;
        }

        .refresh-toggle.off {
            background: #cbd5e1;
        }

        .refresh-toggle.off::after {
            right: 24px;
        }

        .refresh-now {
            display: inline-flex;
            align-items: center;
            gap: 7px;

            padding: 9px 13px;

            border: none;
            border-radius: 8px;

            color: #183357;
            background: #eaf1fb;

            cursor: pointer;

            font-size: 12px;
            font-weight: 700;
        }

        .voucher-table-wrap {
            overflow-x: auto;
            padding: 14px;
        }

        .voucher-table {
            width: 100%;
            min-width: 1120px;

            border-collapse: separate;
            border-spacing: 0;

            overflow: hidden;

            border: 1px solid #e2e8f0;
            border-radius: 9px;

            color: #172554;

            font-size: 12px;
        }

        .voucher-table th {
            padding: 12px 10px;

            color: #172554;
            background: #f5f7fb;

            text-align: left;
            white-space: nowrap;

            font-size: 11px;
            font-weight: 800;

            border-bottom: 1px solid #e2e8f0;
        }

        .voucher-table td {
            padding: 11px 10px;

            white-space: nowrap;
            vertical-align: middle;

            border-bottom: 1px solid #e5e7eb;
        }

        .voucher-table tbody tr:last-child td {
            border-bottom: none;
        }

        .voucher-table tbody tr:hover {
            background: #f8fbff;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;

            min-width: 78px;

            padding: 5px 9px;

            border-radius: 7px;

            font-size: 11px;
            font-weight: 700;
        }

        .status-pill.online {
            color: #ffffff;
            background: #0aa76d;
        }

        .status-pill.online::before {
            content: '';

            width: 6px;
            height: 6px;

            border-radius: 50%;

            background: #ffffff;
        }

        .status-pill.offline {
            color: #a16207;
            background: #fef3c7;
        }

        .status-pill.unused {
            color: #1d4ed8;
            background: #dbeafe;
        }

        .status-pill.expired {
            color: #475569;
            background: #e2e8f0;
        }

        .status-pill.cancelled {
            color: #c2410c;
            background: #ffedd5;
        }

        .status-pill.disabled {
            color: #b91c1c;
            background: #fee2e2;
        }

        .usage-value {
            color: #0f172a;
            font-weight: 800;
        }

        .money-value {
            color: #059669;
            font-weight: 700;
        }

        /*
            |--------------------------------------------------------------------------
            | CUSTOMER NAME
            |--------------------------------------------------------------------------
            */

        .customer-name {
            max-width: 145px;

            overflow: hidden;

            text-overflow: ellipsis;

            font-weight: 600;
        }

        .details-button {
            display: inline-flex;
            align-items: center;
            gap: 5px;

            padding: 6px 10px;

            border-radius: 6px;

            color: #ffffff;
            background: #2563eb;

            text-decoration: none;

            font-size: 10px;
            font-weight: 700;
        }

        .online-note {
            display: flex;
            align-items: center;
            gap: 9px;

            margin: 0 14px 14px;

            padding: 11px 13px;

            border-radius: 8px;

            color: #0756c8;
            background: #eaf4ff;

            font-size: 12px;
        }

        .online-note-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            width: 19px;
            height: 19px;

            flex: 0 0 19px;

            border-radius: 50%;

            color: #ffffff;
            background: #1677e8;

            font-size: 11px;
            font-weight: 800;
        }

        .empty-row {
            padding: 42px !important;

            color: #64748b;

            text-align: center;
        }

        @media (max-width: 1150px) {

            .hotspot-toolbar {
                align-items: stretch;
                flex-direction: column;
            }

            .voucher-tabs,
            .hotspot-actions {
                width: 100%;
            }
        }

        @media (max-width: 760px) {

            .voucher-tab {
                flex: 1 1 calc(50% - 8px);
            }

            .online-card-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .refresh-controls {
                width: 100%;
                flex-wrap: wrap;
            }
        }
    </style>


    <div class="hotspot-page">

        <div class="hotspot-heading">

            <h1>
                Hotspot Vouchers
            </h1>

            <p>
                Manage hotspot vouchers and monitor online users
            </p>

        </div>


        {{-- ============================================================
        TOOLBAR
        ============================================================ --}}

        <div class="hotspot-toolbar">

            <div class="voucher-tabs">


                {{-- ONLINE --}}

                <a href="{{ $tabUrl('online') }}" class="voucher-tab {{ $tab === 'online' ? 'active' : '' }}">

                    <i class="fa-solid fa-wifi"></i>

                    Online

                    <span class="voucher-tab-count">
                        {{ $onlineCount }}
                    </span>

                </a>


                {{-- USED / OFFLINE --}}

                <a href="{{ $tabUrl('offline') }}" class="voucher-tab {{ $tab === 'offline' ? 'active' : '' }}">

                    <i class="fa-solid fa-user-clock"></i>

                    Used / Offline

                    <span class="voucher-tab-count">
                        {{ $offlineCount }}
                    </span>

                </a>


                {{-- UNUSED --}}

                <a href="{{ $tabUrl('unused') }}" class="voucher-tab {{ $tab === 'unused' ? 'active' : '' }}">

                    <i class="fa-solid fa-ticket"></i>

                    Unused

                    <span class="voucher-tab-count">
                        {{ $unusedCount }}
                    </span>

                </a>


                {{-- EXPIRED --}}

                <a href="{{ $tabUrl('expired') }}" class="voucher-tab {{ $tab === 'expired' ? 'active' : '' }}">

                    <i class="fa-regular fa-clock"></i>

                    Expired

                    <span class="voucher-tab-count">
                        {{ $expiredCount }}
                    </span>

                </a>


                {{-- CANCELLED --}}

                <a href="{{ $tabUrl('cancelled') }}" class="voucher-tab {{ $tab === 'cancelled' ? 'active' : '' }}">

                    <i class="fa-solid fa-ban"></i>

                    Cancelled

                    <span class="voucher-tab-count">
                        {{ $cancelledCount }}
                    </span>

                </a>


                {{-- ALL --}}

                <a href="{{ $tabUrl('all') }}" class="voucher-tab {{ $tab === 'all' ? 'active' : '' }}">

                    <i class="fa-solid fa-list"></i>

                    All

                    <span class="voucher-tab-count">
                        {{ $allCount }}
                    </span>

                </a>

            </div>


            <div class="hotspot-actions">

                <a href="{{ route('hotspot-vouchers.mikrotik') }}" class="hotspot-action dark">

                    <i class="fa-solid fa-gear"></i>

                    MikroTik Vouchers

                </a>


                <form method="POST" action="{{ route('hotspot-vouchers.sync-status') }}">

                    @csrf

                    <button type="submit" class="hotspot-action green">

                        <i class="fa-solid fa-rotate"></i>

                        Sync Status

                    </button>

                </form>


                <a href="{{ route('hotspot-vouchers.create') }}" class="hotspot-action blue">

                    <i class="fa-solid fa-plus"></i>

                    Create Voucher

                </a>

            </div>

        </div>


        {{-- ============================================================
        MESSAGES
        ============================================================ --}}

        @if(session('success'))

            <div class="hotspot-alert success">
                {{ session('success') }}
            </div>

        @endif


        @if(session('error'))

            <div class="hotspot-alert error">
                {{ session('error') }}
            </div>

        @endif


        @if(!empty($routerErrors))

            <div class="hotspot-alert warning">

                <strong>
                    MikroTik connection warning:
                </strong>

                {{ implode(' | ', $routerErrors) }}

            </div>

        @endif


        {{-- ============================================================
        MAIN CARD
        ============================================================ --}}

        <div class="online-card">

            <div class="online-card-header">

                <div>

                    <div class="online-title-row">

                        @if($tab === 'online')
                            <span class="online-dot"></span>
                        @endif

                        <div class="online-card-title">

                            @if($tab === 'online')

                                Online Users
                                ({{ $onlineCount }})

                            @elseif($tab === 'offline')

                                Used / Offline
                                ({{ $offlineCount }})

                            @elseif($tab === 'unused')

                                Unused Vouchers
                                ({{ $unusedCount }})

                            @elseif($tab === 'expired')

                                Expired Vouchers
                                ({{ $expiredCount }})

                            @elseif($tab === 'cancelled')

                                Cancelled Vouchers
                                ({{ $cancelledCount }})

                            @else

                                All Vouchers
                                ({{ $allCount }})

                            @endif

                        </div>

                    </div>


                    <div class="online-card-description">

                        @if($tab === 'online')

                            Users currently connected to the hotspot,
                            ordered by highest data usage.

                        @elseif($tab === 'offline')

                            Used vouchers that are still valid,
                            but their users are currently offline.

                        @elseif($tab === 'unused')

                            Vouchers that have never been used.

                        @elseif($tab === 'expired')

                            Vouchers whose validity period has ended.

                        @elseif($tab === 'cancelled')

                            Vouchers cancelled by the administrator.

                        @else

                            Complete JODEKA hotspot voucher history.

                        @endif

                    </div>

                </div>


                @if($tab === 'online')

                    <div class="refresh-controls">

                        <span>
                            Auto refresh:
                            <strong>30s</strong>
                        </span>

                        <button type="button" id="autoRefreshToggle" class="refresh-toggle" title="Toggle auto refresh">
                        </button>

                        <button type="button" class="refresh-now" onclick="window.location.reload()">

                            <i class="fa-solid fa-rotate-right"></i>

                            Refresh Now

                        </button>

                    </div>

                @endif

            </div>


            {{-- ============================================================
            TABLE
            ============================================================ --}}

            <div class="voucher-table-wrap">

                <table class="voucher-table">

                    <thead>

                        <tr>

                            <th>#</th>

                            <th>Status</th>

                            <th>Voucher</th>

                            <th>Customer Name</th>

                            <th>IP Address</th>

                            <th>MAC Address</th>

                            <th>Uptime</th>

                            <th>
                                Data Usage

                                @if($tab === 'online')
                                    <i class="fa-solid fa-caret-down"></i>
                                @endif
                            </th>

                            <th>Data Value</th>

                            <th>Expires At</th>

                            <th>Actions</th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($vouchers as $index => $voucher)

                                        @php
                                            $displayBytes = (int) (
                                                $voucher->display_bytes
                                                ??
                                                (
                                                    (int) ($voucher->bytes_in ?? 0)
                                                    +
                                                    (int) ($voucher->bytes_out ?? 0)
                                                )
                                            );
                                        @endphp


                                        <tr>

                                            <td>
                                                {{ $index + 1 }}
                                            </td>


                                            {{-- STATUS --}}

                                            <td>

                                                @if($voucher->is_online)

                                                    <span class="status-pill online">
                                                        Online
                                                    </span>

                                                @elseif($voucher->status === 'cancelled')

                                                    <span class="status-pill cancelled">
                                                        Cancelled
                                                    </span>

                                                @elseif($voucher->status === 'disabled')

                                                    <span class="status-pill disabled">
                                                        Disabled
                                                    </span>

                                                @elseif($voucher->is_expired_now)

                                                    <span class="status-pill expired">
                                                        Expired
                                                    </span>

                                                @elseif($voucher->status === 'unused')

                                                    <span class="status-pill unused">
                                                        Unused
                                                    </span>

                                                @elseif($voucher->status === 'used')

                                                    <span class="status-pill offline">
                                                        Used
                                                    </span>

                                                @else

                                                    <span class="status-pill offline">
                                                        {{ ucfirst($voucher->status) }}
                                                    </span>

                                                @endif

                                            </td>


                                            {{-- VOUCHER --}}

                                            <td>

                                                <strong>
                                                    {{ $voucher->username }}
                                                </strong>

                                            </td>


                                            {{-- CUSTOMER NAME --}}

                                            <td class="customer-name" title="{{ $voucher->comment }}">

                                                {{ $displayCustomerName($voucher) }}

                                            </td>


                                            {{-- IP --}}

                                            <td>

                                                {{
                            $voucher->online_ip
                            ??
                            $voucher->used_by_ip
                            ??
                            '-'
                                                        }}

                                            </td>


                                            {{-- MAC --}}

                                            <td>

                                                {{
                            $voucher->online_mac
                            ??
                            $voucher->used_by_mac
                            ??
                            '-'
                                                        }}

                                            </td>


                                            {{-- UPTIME --}}

                                            <td>

                                                {{
                            $voucher->online_uptime
                            ??
                            $voucher->mikrotik_uptime
                            ??
                            '-'
                                                        }}

                                            </td>


                                            {{-- DATA USAGE --}}

                                            <td>

                                                <span class="usage-value">

                                                    {{
                            $formatBytes(
                                $displayBytes
                            )
                                                            }}

                                                </span>

                                            </td>


                                            {{-- DATA VALUE --}}

                                            <td>

                                                <span class="money-value">

                                                    TZS
                                                    {{
                            number_format(
                                $voucher->data_value
                            )
                                                            }}

                                                </span>

                                            </td>


                                            {{-- EXPIRES --}}

                                            <td>

                                                {{
                            optional(
                                $voucher->expires_at
                            )
                                ->format(
                                    'd/m/Y H:i'
                                )
                            ??
                            '-'
                                                        }}

                                            </td>


                                            {{-- ACTIONS --}}

                                            <td>

                                                <a href="{{ route('hotspot-vouchers.show', $voucher) }}" class="details-button">

                                                    <i class="fa-regular fa-eye"></i>

                                                    Details

                                                </a>

                                            </td>

                                        </tr>


                        @empty

                            <tr>

                                <td colspan="11" class="empty-row">

                                    @if($tab === 'online')

                                        No hotspot users are currently online.

                                    @elseif($tab === 'offline')

                                        No used vouchers are currently offline.

                                    @elseif($tab === 'unused')

                                        No unused vouchers found.

                                    @elseif($tab === 'expired')

                                        No expired vouchers found.

                                    @elseif($tab === 'cancelled')

                                        No cancelled vouchers found.

                                    @else

                                        No vouchers found.

                                    @endif

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            @if($tab === 'online')

                <div class="online-note">

                    <span class="online-note-icon">
                        i
                    </span>

                    <span>

                        Only currently connected users are shown here.
                        When a user disconnects or leaves the hotspot area,
                        the voucher moves to
                        <strong>Used / Offline</strong>
                        until it expires.

                    </span>

                </div>

            @elseif($tab === 'offline')

                <div class="online-note">

                    <span class="online-note-icon">
                        i
                    </span>

                    <span>

                        These vouchers are still valid.
                        Their users have previously connected,
                        but they are not currently online.
                        When they reconnect, they return automatically to
                        <strong>Online Users</strong>.

                    </span>

                </div>

            @endif

        </div>

    </div>


    @if($tab === 'online')

        <script>
            let autoRefreshEnabled = true;

            let autoRefreshTimer = null;

            const toggle =
                document.getElementById(
                    'autoRefreshToggle'
                );


            function startAutoRefresh() {
                clearInterval(
                    autoRefreshTimer
                );

                autoRefreshTimer =
                    setInterval(
                        function () {

                            if (autoRefreshEnabled) {
                                window.location.reload();
                            }

                        },
                        30000
                    );
            }


            if (toggle) {

                toggle.addEventListener(
                    'click',
                    function () {

                        autoRefreshEnabled =
                            !autoRefreshEnabled;

                        toggle.classList.toggle(
                            'off',
                            !autoRefreshEnabled
                        );

                        if (autoRefreshEnabled) {

                            startAutoRefresh();

                        } else {

                            clearInterval(
                                autoRefreshTimer
                            );
                        }
                    }
                );
            }


            startAutoRefresh();
        </script>

    @endif

@endsection