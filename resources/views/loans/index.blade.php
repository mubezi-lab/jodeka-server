@extends('layouts.admin')

@section('title', 'Loans')

@section('content')

    <div class="max-w-7xl mx-auto py-6 px-6">

        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">

            <h1 class="text-2xl font-semibold">

                Loans

            </h1>

            <a href="{{ route('loans.create') }}" class="bg-blue-600 hover:bg-blue-700
                      text-white px-4 py-2 rounded-lg w-fit transition">

                + Add Loan

            </a>

        </div>

        {{-- SUCCESS --}}
        @if(session('success'))

            <div class="bg-green-100 text-green-700 p-3 rounded mb-6">

                {{ session('success') }}

            </div>

        @endif

        {{-- ANALYTICS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 mb-6">

            {{-- RECEIVABLE --}}
            <div class="bg-green-50 rounded-2xl p-5 shadow-sm">

                <p class="text-sm text-green-600">

                    Total Receivable

                </p>

                <h2 class="text-2xl font-bold mt-2 text-green-700">

                    TZS {{ number_format($totalReceivable, 2) }}

                </h2>

            </div>

            {{-- PAYABLE --}}
            <div class="bg-red-50 rounded-2xl p-5 shadow-sm">

                <p class="text-sm text-red-600">

                    Total Payable

                </p>

                <h2 class="text-2xl font-bold mt-2 text-red-700">

                    TZS {{ number_format($totalPayable, 2) }}

                </h2>

            </div>

            {{-- PAID --}}
            <div class="bg-blue-50 rounded-2xl p-5 shadow-sm">

                <p class="text-sm text-blue-600">

                    Paid Loans

                </p>

                <h2 class="text-2xl font-bold mt-2 text-blue-700">

                    {{ $totalPaidLoans }}

                </h2>

            </div>

            {{-- PARTIAL --}}
            <div class="bg-yellow-50 rounded-2xl p-5 shadow-sm">

                <p class="text-sm text-yellow-600">

                    Partial Loans

                </p>

                <h2 class="text-2xl font-bold mt-2 text-yellow-700">

                    {{ $totalPartialLoans }}

                </h2>

            </div>

            {{-- OVERDUE --}}
            <div class="bg-red-100 rounded-2xl p-5 shadow-sm">

                <p class="text-sm text-red-700">

                    Overdue Loans

                </p>

                <h2 class="text-2xl font-bold mt-2 text-red-800">

                    {{ $overdueLoans }}

                </h2>

            </div>

        </div>

        {{-- TABLE --}}
        <div class="bg-white shadow rounded-2xl overflow-hidden">

            <div class="overflow-x-auto">

                <table class="w-full min-w-[1200px]">

                    <thead class="bg-gray-100">

                        <tr>

                            <th class="p-4 text-left">#</th>

                            <th class="p-4 text-left">Type</th>

                            <th class="p-4 text-left">Name</th>

                            <th class="p-4 text-left">Phone</th>

                            <th class="p-4 text-left">Business</th>

                            <th class="p-4 text-left">Amount</th>

                            <th class="p-4 text-left">Paid</th>

                            <th class="p-4 text-left">Remaining</th>

                            <th class="p-4 text-left">Status</th>

                            <th class="p-4 text-left">Due Date</th>

                            <th class="p-4 text-left">Actions</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($loans as $index => $loan)

                                        <tr class="border-t hover:bg-gray-50 transition">

                                            {{-- NUMBER --}}
                                            <td class="p-4">

                                                {{ $index + 1 }}

                                            </td>

                                            {{-- TYPE --}}
                                            <td class="p-4">

                                                <span class="px-3 py-1 rounded-full text-xs font-medium

                                                        {{ $loan->type == 'receivable'
                            ? 'bg-green-100 text-green-700'
                            : 'bg-red-100 text-red-700' }}">

                                                    {{ ucfirst($loan->type) }}

                                                </span>

                                            </td>

                                            {{-- NAME --}}
                                            <td class="p-4 whitespace-nowrap font-medium">

                                                {{ $loan->name }}

                                            </td>

                                            {{-- PHONE --}}
                                            <td class="p-4 whitespace-nowrap">

                                                {{ $loan->phone ?? '-' }}

                                            </td>

                                            {{-- BUSINESS --}}
                                            <td class="p-4 whitespace-nowrap">

                                                {{ $loan->business->name ?? '-' }}

                                            </td>

                                            {{-- AMOUNT --}}
                                            <td class="p-4 whitespace-nowrap font-semibold">

                                                TZS {{ number_format($loan->amount, 2) }}

                                            </td>

                                            {{-- PAID --}}
                                            <td class="p-4 whitespace-nowrap text-green-600 font-semibold">

                                                TZS {{ number_format($loan->paid_amount, 2) }}

                                            </td>

                                            {{-- REMAINING --}}
                                            <td class="p-4 whitespace-nowrap text-red-600 font-semibold">

                                                TZS {{ number_format($loan->remaining_amount, 2) }}

                                            </td>

                                            {{-- STATUS --}}
                                            <td class="p-4">

                                                <div class="flex flex-col gap-2">

                                                    <span class="px-3 py-1 rounded-full text-xs font-medium w-fit

                                                            {{ $loan->status == 'paid'
                            ? 'bg-green-100 text-green-700'
                            : ($loan->status == 'partial'
                                ? 'bg-yellow-100 text-yellow-700'
                                : 'bg-red-100 text-red-700') }}">

                                                        {{ ucfirst($loan->status) }}

                                                    </span>

                                                    {{-- OVERDUE --}}
                                                    @if(
                                                            $loan->due_date &&
                                                            $loan->due_date < today() &&
                                                            $loan->status != 'paid'
                                                        )

                                                        <span class="bg-red-600 text-white
                                                                             text-xs px-2 py-1 rounded-full w-fit">

                                                            OVERDUE

                                                        </span>

                                                    @endif

                                                </div>

                                            </td>

                                            {{-- DUE DATE --}}
                                            <td class="p-4 whitespace-nowrap">

                                                {{ $loan->due_date ?? '-' }}

                                            </td>

                                            {{-- ACTIONS --}}
                                            <td class="p-4">

                                                <div class="flex items-center gap-2">

                                                    {{-- PAYMENT --}}
                                                    <a href="{{ route('loans.edit', $loan->id) }}" class="bg-green-600 hover:bg-green-700
                                                                  text-white px-3 py-1 rounded-md text-sm">

                                                        Payment

                                                    </a>

                                                    {{-- EDIT --}}
                                                    <a href="{{ route('loans.edit', $loan->id) }}" class="bg-yellow-500 hover:bg-yellow-600
                                                                  text-white px-3 py-1 rounded-md text-sm">

                                                        Edit

                                                    </a>

                                                    {{-- DELETE --}}
                                                    <form action="{{ route('loans.destroy', $loan->id) }}" method="POST">

                                                        @csrf
                                                        @method('DELETE')

                                                        <button onclick="return confirm('Delete loan?')" class="bg-red-500 hover:bg-red-600
                                                                       text-white px-3 py-1 rounded-md text-sm">

                                                            Delete

                                                        </button>

                                                    </form>

                                                </div>

                                            </td>

                                        </tr>

                        @empty

                            <tr>

                                <td colspan="11" class="p-6 text-center text-gray-500">

                                    No loans found

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

@endsection