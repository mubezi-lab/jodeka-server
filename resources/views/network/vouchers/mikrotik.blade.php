@extends('layouts.admin')

@section('title', 'MikroTik Vouchers')

@section('content')

    @php
        $formatBytes = function ($bytes) {
            $bytes = (int) $bytes;

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

    <div class="bg-white rounded-xl shadow p-6">

        <div class="flex items-center justify-between mb-6">

            <div>
                <h2 class="text-xl font-bold">
                    MikroTik Vouchers
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Vouchers created directly on MikroTik
                </p>
            </div>

            <a href="{{ route('hotspot-vouchers.index') }}"
                class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg">
                ← JODEKA Vouchers
            </a>

        </div>

        <div class="overflow-hidden border rounded-xl">

            <table class="w-full border-collapse text-sm">

                <thead class="bg-gray-100">
                    <tr>

                        <th class="px-4 py-3 text-left">
                            Voucher
                        </th>

                        <th class="px-4 py-3 text-left">
                            Status
                        </th>

                        <th class="px-4 py-3 text-left">
                            Expire Date
                        </th>

                        <th class="px-4 py-3 text-left">
                            Usage
                        </th>

                        <th class="px-4 py-3 text-left">
                            Data Value
                        </th>

                    </tr>
                </thead>

                <tbody>

                    @forelse($vouchers as $voucher)

                        @php
                            $bytesIn = (int) ($voucher->bytes_in ?? 0);
                            $bytesOut = (int) ($voucher->bytes_out ?? 0);
                            $totalBytes = $bytesIn + $bytesOut;
                        @endphp

                        <tr onclick="window.location='{{ route('hotspot-vouchers.show', $voucher) }}'"
                            class="border-t hover:bg-indigo-50 cursor-pointer transition">

                            <td class="px-4 py-4 font-semibold whitespace-nowrap">
                                {{ $voucher->username }}
                            </td>

                            <td class="px-4 py-4 whitespace-nowrap">

                                @if($voucher->status === 'unused')

                                    <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-sm">
                                        Unused
                                    </span>

                                @elseif($voucher->status === 'used')

                                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-sm">
                                        Used
                                    </span>

                                @elseif($voucher->status === 'expired')

                                    <span class="bg-gray-200 text-gray-700 px-2 py-1 rounded text-sm">
                                        Expired
                                    </span>

                                @elseif($voucher->status === 'cancelled')

                                    <span class="bg-orange-100 text-orange-700 px-2 py-1 rounded text-sm">
                                        Cancelled
                                    </span>

                                @elseif($voucher->status === 'disabled')

                                    <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-sm">
                                        Disabled
                                    </span>

                                @else

                                    <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-sm">
                                        {{ ucfirst($voucher->status) }}
                                    </span>

                                @endif

                            </td>

                            <td class="px-4 py-4 whitespace-nowrap">
                                {{ optional($voucher->expires_at)->format('d/m/Y H:i') ?? '-' }}
                            </td>

                            <td class="px-4 py-4 whitespace-nowrap font-semibold">
                                {{ $formatBytes($totalBytes) }}
                            </td>

                            <td class="px-4 py-4 whitespace-nowrap">

                                <span class="font-semibold text-emerald-700">
                                    TZS {{ number_format($voucher->data_value) }}
                                </span>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                No MikroTik vouchers found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

@endsection