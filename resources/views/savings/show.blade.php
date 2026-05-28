@extends('layouts.admin')

@section('title', 'Saving Details')

@section('content')

    <div class="space-y-6">

        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row
                    sm:items-center sm:justify-between gap-4">

            <div>

                <h1 class="text-2xl font-bold text-gray-800">

                    {{ $saving->name }}

                </h1>

                <p class="text-sm text-gray-500 mt-1">

                    Saving account details

                </p>

            </div>

            <div class="flex flex-wrap items-center gap-2">

                {{-- DEPOSIT --}}
                <a href="{{ route('savings.deposit.form', $saving->id) }}" class="bg-green-600 hover:bg-green-700
                          text-white px-4 py-2 rounded-lg text-sm">

                    Deposit

                </a>

                {{-- WITHDRAW --}}
                <a href="{{ route('savings.withdraw.form', $saving->id) }}" class="bg-red-600 hover:bg-red-700
                          text-white px-4 py-2 rounded-lg text-sm">

                    Withdraw

                </a>

                {{-- EDIT --}}
                <a href="{{ route('savings.edit', $saving->id) }}" class="bg-yellow-500 hover:bg-yellow-600
                          text-white px-4 py-2 rounded-lg text-sm">

                    Edit

                </a>

                {{-- BACK --}}
                <a href="{{ route('savings.index') }}" class="bg-gray-200 hover:bg-gray-300
                          px-4 py-2 rounded-lg text-sm">

                    Back

                </a>

            </div>

        </div>

        {{-- SUCCESS --}}
        @if(session('success'))

            <div class="bg-green-100 text-green-700
                            px-4 py-3 rounded-lg">

                {{ session('success') }}

            </div>

        @endif

        {{-- INFO CARDS --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- BALANCE --}}
            <div class="bg-white shadow rounded-2xl p-5">

                <p class="text-sm text-gray-500">

                    Current Balance

                </p>

                <h2 class="text-2xl font-bold mt-2 text-green-600">

                    TZS {{ number_format($saving->balance, 2) }}

                </h2>

            </div>

            {{-- TARGET --}}
            <div class="bg-white shadow rounded-2xl p-5">

                <p class="text-sm text-gray-500">

                    Target Amount

                </p>

                <h2 class="text-2xl font-bold mt-2 text-indigo-600">

                    @if($saving->target_amount)

                        TZS {{ number_format($saving->target_amount, 2) }}

                    @else

                        -

                    @endif

                </h2>

            </div>

            {{-- STATUS --}}
            <div class="bg-white shadow rounded-2xl p-5">

                <p class="text-sm text-gray-500">

                    Status

                </p>

                <div class="mt-3">

                    @if($saving->status == 'active')

                        <span class="bg-green-100 text-green-700
                                         px-3 py-1 rounded-full text-sm">

                            Active

                        </span>

                    @elseif($saving->status == 'completed')

                        <span class="bg-blue-100 text-blue-700
                                         px-3 py-1 rounded-full text-sm">

                            Completed

                        </span>

                    @else

                        <span class="bg-red-100 text-red-700
                                         px-3 py-1 rounded-full text-sm">

                            Closed

                        </span>

                    @endif

                </div>

            </div>

        </div>

        {{-- DETAILS --}}
        <div class="bg-white shadow rounded-2xl p-6">

            <h2 class="text-lg font-semibold mb-4">

                Saving Information

            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>

                    <p class="text-sm text-gray-500">

                        Saving Type

                    </p>

                    <p class="font-medium capitalize mt-1">

                        {{ $saving->type }}

                    </p>

                </div>

                <div>

                    <p class="text-sm text-gray-500">

                        Business

                    </p>

                    <p class="font-medium mt-1">

                        {{ $saving->business->name ?? '-' }}

                    </p>

                </div>

                <div>

                    <p class="text-sm text-gray-500">

                        Start Date

                    </p>

                    <p class="font-medium mt-1">

                        {{ $saving->start_date?->format('d M Y') ?? '-' }}

                    </p>

                </div>

                <div>

                    <p class="text-sm text-gray-500">

                        Maturity Date

                    </p>

                    <p class="font-medium mt-1">

                        {{ $saving->maturity_date?->format('d M Y') ?? '-' }}

                    </p>

                </div>

            </div>

            {{-- DESCRIPTION --}}
            <div class="mt-6">

                <p class="text-sm text-gray-500 mb-2">

                    Description

                </p>

                <div class="bg-gray-50 rounded-xl p-4 text-sm">

                    {{ $saving->description ?? 'No description available' }}

                </div>

            </div>

        </div>

        {{-- TRANSACTIONS --}}
        <div class="bg-white shadow rounded-2xl overflow-hidden">

            <div class="p-6 border-b">

                <h2 class="text-lg font-semibold">

                    Saving Transactions

                </h2>

            </div>

            <div class="overflow-x-auto">

                <table class="w-full min-w-[900px]">

                    <thead class="bg-gray-100">

                        <tr>

                            <th class="p-4 text-left text-sm">

                                #

                            </th>

                            <th class="p-4 text-left text-sm">

                                Type

                            </th>

                            <th class="p-4 text-left text-sm">

                                Amount

                            </th>

                            <th class="p-4 text-left text-sm">

                                Payment Method

                            </th>

                            <th class="p-4 text-left text-sm">

                                Date

                            </th>

                            <th class="p-4 text-left text-sm">

                                Description

                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($saving->transactions as $index => $transaction)

                            <tr class="border-t">

                                <td class="p-4">

                                    {{ $index + 1 }}

                                </td>

                                <td class="p-4">

                                    <span class="capitalize">

                                        {{ $transaction->type }}

                                    </span>

                                </td>

                                <td class="p-4 font-semibold">

                                    @if($transaction->type == 'deposit')

                                        <span class="text-green-600">

                                            + TZS {{ number_format($transaction->amount, 2) }}

                                        </span>

                                    @elseif($transaction->type == 'withdrawal')

                                        <span class="text-red-600">

                                            - TZS {{ number_format($transaction->amount, 2) }}

                                        </span>

                                    @else

                                        TZS {{ number_format($transaction->amount, 2) }}

                                    @endif

                                </td>

                                <td class="p-4">

                                    {{ ucfirst(str_replace('_', ' ', $transaction->payment_method)) }}

                                </td>

                                <td class="p-4">

                                    {{ $transaction->transaction_date->format('d M Y') }}

                                </td>

                                <td class="p-4">

                                    {{ $transaction->description ?? '-' }}

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6" class="p-6 text-center text-gray-500">

                                    No transactions found

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

@endsection