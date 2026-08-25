@extends('layouts.admin')

@section('title', 'Voucher Details')

@section('content')

    @php
        $bytesIn = (int) ($voucher->bytes_in ?? 0);
        $bytesOut = (int) ($voucher->bytes_out ?? 0);
        $totalBytes = $bytesIn + $bytesOut;

        $formatBytes = function ($bytes) {
            if ($bytes <= 0) {
                return '0 MB';
            }

            if ($bytes >= 1073741824) {
                return number_format($bytes / 1073741824, 2) . ' GB';
            }

            if ($bytes >= 1048576) {
                return number_format($bytes / 1048576, 2) . ' MB';
            }

            if ($bytes >= 1024) {
                return number_format($bytes / 1024, 2) . ' KB';
            }

            return number_format($bytes) . ' B';
        };
    @endphp

    <div class="max-w-5xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">

            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    Voucher Details
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    {{ $voucher->username }}
                </p>
            </div>

            <a href="{{ route('hotspot-vouchers.index') }}"
                class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg">
                ← Back to Vouchers
            </a>

        </div>

        {{-- Main card --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">

            {{-- Voucher heading --}}
            <div class="p-6 border-b flex items-center justify-between">

                <div>
                    <div class="text-sm text-gray-500">
                        Voucher
                    </div>

                    <div class="text-2xl font-bold text-gray-900 mt-1">
                        {{ $voucher->username }}
                    </div>
                </div>

                <div>

                    @if($voucher->status === 'unused')

                        <span class="bg-blue-100 text-blue-700 px-3 py-2 rounded-lg font-medium">
                            Unused
                        </span>

                    @elseif($voucher->status === 'used')

                        <span class="bg-green-100 text-green-700 px-3 py-2 rounded-lg font-medium">
                            Used
                        </span>

                    @elseif($voucher->status === 'expired')

                        <span class="bg-gray-200 text-gray-700 px-3 py-2 rounded-lg font-medium">
                            Expired
                        </span>

                    @elseif($voucher->status === 'cancelled')

                        <span class="bg-orange-100 text-orange-700 px-3 py-2 rounded-lg font-medium">
                            Cancelled
                        </span>

                    @elseif($voucher->status === 'disabled')

                        <span class="bg-red-100 text-red-700 px-3 py-2 rounded-lg font-medium">
                            Disabled
                        </span>

                    @else

                        <span class="bg-gray-100 text-gray-700 px-3 py-2 rounded-lg font-medium">
                            {{ ucfirst($voucher->status) }}
                        </span>

                    @endif

                </div>

            </div>


            {{-- Main information --}}
            <div class="p-6">

                <h3 class="font-bold text-lg mb-4">
                    Voucher Information
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div>
                        <div class="text-sm text-gray-500">Username</div>
                        <div class="font-semibold mt-1">
                            {{ $voucher->username }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Profile</div>
                        <div class="font-semibold mt-1">
                            {{ $voucher->profile->name ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Price</div>
                        <div class="font-semibold mt-1">
                            TZS {{ number_format($voucher->price) }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Generated</div>
                        <div class="font-semibold mt-1">
                            {{ optional($voucher->generated_at)->format('d/m/Y H:i') ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">First Login</div>
                        <div class="font-semibold mt-1">
                            {{ optional($voucher->first_login_at)->format('d/m/Y H:i') ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Expires</div>
                        <div class="font-semibold mt-1">
                            {{ optional($voucher->expires_at)->format('d/m/Y H:i') ?? '-' }}
                        </div>
                    </div>

                </div>

            </div>


            {{-- Usage --}}
            <div class="p-6 border-t bg-gray-50">

                <h3 class="font-bold text-lg mb-4">
                    Internet Usage
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                    <div class="bg-white border rounded-lg p-4">
                        <div class="text-sm text-gray-500">
                            Download
                        </div>

                        <div class="text-xl font-bold mt-1">
                            {{ $formatBytes($bytesOut) }}
                        </div>
                    </div>

                    <div class="bg-white border rounded-lg p-4">
                        <div class="text-sm text-gray-500">
                            Upload
                        </div>

                        <div class="text-xl font-bold mt-1">
                            {{ $formatBytes($bytesIn) }}
                        </div>
                    </div>

                    <div class="bg-white border rounded-lg p-4">
                        <div class="text-sm text-gray-500">
                            Total Usage
                        </div>

                        <div class="text-xl font-bold mt-1">
                            {{ $formatBytes($totalBytes) }}
                        </div>
                    </div>

                    <div class="bg-white border rounded-lg p-4">
                        <div class="text-sm text-gray-500">
                            Uptime
                        </div>

                        <div class="text-xl font-bold mt-1">
                            {{ $voucher->mikrotik_uptime ?? '-' }}
                        </div>
                    </div>

                </div>

            </div>


            {{-- Device --}}
            <div class="p-6 border-t">

                <h3 class="font-bold text-lg mb-4">
                    Connected Device
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <div class="text-sm text-gray-500">
                            IP Address
                        </div>

                        <div class="font-semibold mt-1">
                            {{ $voucher->used_by_ip ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">
                            MAC Address
                        </div>

                        <div class="font-semibold mt-1">
                            {{ $voucher->used_by_mac ?? '-' }}
                        </div>
                    </div>

                </div>

            </div>


            {{-- System --}}
            <div class="p-6 border-t bg-gray-50">

                <h3 class="font-bold text-lg mb-4">
                    System Information
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div>
                        <div class="text-sm text-gray-500">
                            Router
                        </div>

                        <div class="font-semibold mt-1">
                            {{ $voucher->router->name ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">
                            Last Seen
                        </div>

                        <div class="font-semibold mt-1">
                            {{ optional($voucher->last_seen_at)->format('d/m/Y H:i:s') ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">
                            Last Synced
                        </div>

                        <div class="font-semibold mt-1">
                            {{ optional($voucher->last_synced_at)->format('d/m/Y H:i:s') ?? '-' }}
                        </div>
                    </div>

                </div>

            </div>


            {{-- Cancel --}}
            @if(in_array($voucher->status, ['unused', 'used'], true))

                <div class="p-6 border-t flex justify-end">

                    <form method="POST" action="{{ route('hotspot-vouchers.cancel', $voucher) }}"
                        onsubmit="return confirm('Are you sure you want to cancel voucher {{ $voucher->username }}? This will disconnect the customer and remove the voucher from MikroTik.');">

                        @csrf

                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg">
                            Cancel Voucher
                        </button>

                    </form>

                </div>

            @endif

        </div>

    </div>

@endsection