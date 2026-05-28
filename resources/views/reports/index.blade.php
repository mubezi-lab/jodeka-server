@extends('layouts.admin')

@section('title', 'Reports Dashboard')

@section('content')

    <div class="space-y-6">

        {{-- ====================================================== --}}
        {{-- PAGE HEADER --}}
        {{-- ====================================================== --}}
        <div class="flex flex-col lg:flex-row lg:items-center
                    lg:justify-between gap-4">

            <div>

                <h1 class="text-2xl font-bold text-gray-800">

                    Reports Dashboard

                </h1>

                <p class="text-gray-500 mt-1">

                    Financial and business analytics overview

                </p>

            </div>

            {{-- QUICK ACTIONS --}}
            <div class="flex flex-wrap gap-3">

                <a href="{{ route('company-incomes.create') }}" class="bg-green-600 hover:bg-green-700
                          text-white px-5 py-3 rounded-xl transition">

                    + Add Income

                </a>

                <a href="{{ route('company-expenses.create') }}" class="bg-red-600 hover:bg-red-700
                          text-white px-5 py-3 rounded-xl transition">

                    + Add Expense

                </a>

            </div>

        </div>

        {{-- ====================================================== --}}
        {{-- FILTERS --}}
        {{-- ====================================================== --}}
        <div class="bg-white shadow rounded-2xl p-6">

            <form method="GET" action="{{ route('reports.index') }}">

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                    {{-- FROM DATE --}}
                    <div>

                        <label class="block text-sm font-medium mb-2">

                            From Date

                        </label>

                        <input type="date" name="from" value="{{ $from }}" class="w-full border rounded-xl px-4 py-3
                                      focus:ring-2 focus:ring-indigo-500">

                    </div>

                    {{-- TO DATE --}}
                    <div>

                        <label class="block text-sm font-medium mb-2">

                            To Date

                        </label>

                        <input type="date" name="to" value="{{ $to }}" class="w-full border rounded-xl px-4 py-3
                                      focus:ring-2 focus:ring-indigo-500">

                    </div>

                    {{-- BUSINESS --}}
                    <div>

                        <label class="block text-sm font-medium mb-2">

                            Business

                        </label>

                        <select name="business_id" class="w-full border rounded-xl px-4 py-3
                                       focus:ring-2 focus:ring-indigo-500">

                            <option value="">
                                All Businesses
                            </option>

                            @foreach($businesses as $business)

                                <option value="{{ $business->id }}" {{ $businessId == $business->id ? 'selected' : '' }}>

                                    {{ $business->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- BUTTONS --}}
                    <div class="flex items-end gap-3">

                        {{-- GENERATE --}}
                        <button class="bg-indigo-600 hover:bg-indigo-700
                                   text-white px-6 py-3 rounded-xl
                                   transition w-full">

                            Generate

                        </button>

                        {{-- RESET --}}
                        <a href="{{ route('reports.index') }}" class="bg-gray-200 hover:bg-gray-300
                                  px-6 py-3 rounded-xl transition">

                            Reset

                        </a>

                    </div>

                </div>

            </form>

        </div>

        {{-- ====================================================== --}}
        {{-- SUMMARY CARDS --}}
        {{-- ====================================================== --}}
        <div class="grid grid-cols-1 sm:grid-cols-2
                    xl:grid-cols-3 gap-6">

            {{-- TOTAL INCOME --}}
            <div class="bg-white shadow rounded-2xl p-6 border-l-4 border-green-500">

                <div class="flex items-center justify-between">

                    <div>

                        <p class="text-sm text-gray-500">

                            Total Income

                        </p>

                        <h2 class="text-3xl font-bold
                                   text-green-600 mt-3">

                            TZS {{ number_format($totalIncome, 2) }}

                        </h2>

                    </div>

                    <div class="text-4xl">

                        💰

                    </div>

                </div>

            </div>

            {{-- TOTAL EXPENSES --}}
            <div class="bg-white shadow rounded-2xl p-6 border-l-4 border-red-500">

                <div class="flex items-center justify-between">

                    <div>

                        <p class="text-sm text-gray-500">

                            Total Expenses

                        </p>

                        <h2 class="text-3xl font-bold
                                   text-red-600 mt-3">

                            TZS {{ number_format($totalExpenses, 2) }}

                        </h2>

                    </div>

                    <div class="text-4xl">

                        📉

                    </div>

                </div>

            </div>

            {{-- NET PROFIT --}}
            <div class="bg-white shadow rounded-2xl p-6 border-l-4 border-indigo-500">

                <div class="flex items-center justify-between">

                    <div>

                        <p class="text-sm text-gray-500">

                            Net Profit

                        </p>

                        <h2 class="text-3xl font-bold
                                   {{ $netProfit >= 0
        ? 'text-indigo-600'
        : 'text-red-600' }} mt-3">

                            TZS {{ number_format($netProfit, 2) }}

                        </h2>

                    </div>

                    <div class="text-4xl">

                        📊

                    </div>

                </div>

            </div>

            {{-- TOTAL SAVINGS --}}
            <div class="bg-white shadow rounded-2xl p-6 border-l-4 border-yellow-500">

                <div class="flex items-center justify-between">

                    <div>

                        <p class="text-sm text-gray-500">

                            Total Savings

                        </p>

                        <h2 class="text-3xl font-bold
                                   text-yellow-600 mt-3">

                            TZS {{ number_format($totalSavings, 2) }}

                        </h2>

                    </div>

                    <div class="text-4xl">

                        🏦

                    </div>

                </div>

            </div>

            {{-- TOTAL LOANS --}}
            <div class="bg-white shadow rounded-2xl p-6 border-l-4 border-pink-500">

                <div class="flex items-center justify-between">

                    <div>

                        <p class="text-sm text-gray-500">

                            Total Loans

                        </p>

                        <h2 class="text-3xl font-bold
                                   text-pink-600 mt-3">

                            TZS {{ number_format($totalLoans, 2) }}

                        </h2>

                    </div>

                    <div class="text-4xl">

                        💳

                    </div>

                </div>

            </div>

            {{-- TOTAL BUSINESSES --}}
            <div class="bg-white shadow rounded-2xl p-6 border-l-4 border-blue-500">

                <div class="flex items-center justify-between">

                    <div>

                        <p class="text-sm text-gray-500">

                            Total Businesses

                        </p>

                        <h2 class="text-3xl font-bold
                                   text-blue-600 mt-3">

                            {{ $totalBusinesses }}

                        </h2>

                    </div>

                    <div class="text-4xl">

                        🏢

                    </div>

                </div>

            </div>

        </div>

        {{-- ====================================================== --}}
        {{-- TRANSACTIONS TABLE --}}
        {{-- ====================================================== --}}
        <div class="bg-white shadow rounded-2xl overflow-hidden">

            {{-- HEADER --}}
            <div class="p-6 border-b flex flex-col lg:flex-row
                        lg:items-center lg:justify-between gap-4">

                <div>

                    <h2 class="text-xl font-semibold text-gray-800">

                        Transaction Reports

                    </h2>

                    <p class="text-sm text-gray-500 mt-1">

                        Detailed income and expense transactions

                    </p>

                </div>

                {{-- EXPORT BUTTON --}}
                <a href="{{ route('reports.monthly', [
        'from' => $from,
        'to' => $to
    ]) }}" class="bg-red-600 hover:bg-red-700
                          text-white px-5 py-3 rounded-xl transition">

                    Export PDF

                </a>

            </div>

            {{-- TABLE --}}
            <div class="overflow-x-auto">

                <table class="w-full min-w-[1100px]">

                    <thead class="bg-gray-100">

                        <tr>

                            <th class="p-4 text-left">
                                Date
                            </th>

                            <th class="p-4 text-left">
                                Type
                            </th>

                            <th class="p-4 text-left">
                                Business
                            </th>

                            <th class="p-4 text-left">
                                Category
                            </th>

                            <th class="p-4 text-left">
                                Payment Method
                            </th>

                            <th class="p-4 text-left">
                                Amount
                            </th>

                            <th class="p-4 text-left">
                                Description
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($transactions as $transaction)

                                        <tr class="border-t hover:bg-gray-50 transition">

                                            {{-- DATE --}}
                                            <td class="p-4 whitespace-nowrap">

                                                {{ $transaction->transaction_date }}

                                            </td>

                                            {{-- TYPE --}}
                                            <td class="p-4">

                                                <span class="px-3 py-1 rounded-full
                                                                 text-xs font-medium

                                                        {{ $transaction->type == 'income'
                            ? 'bg-green-100 text-green-700'
                            : 'bg-red-100 text-red-700' }}">

                                                    {{ ucfirst($transaction->type) }}

                                                </span>

                                            </td>

                                            {{-- BUSINESS --}}
                                            <td class="p-4 whitespace-nowrap">

                                                {{ $transaction->business->name ?? '-' }}

                                            </td>

                                            {{-- CATEGORY --}}
                                            <td class="p-4 whitespace-nowrap">

                                                {{ $transaction->category }}

                                            </td>

                                            {{-- PAYMENT METHOD --}}
                                            <td class="p-4 whitespace-nowrap">

                                                {{ ucfirst(str_replace('_', ' ', $transaction->payment_method)) }}

                                            </td>

                                            {{-- AMOUNT --}}
                                            <td class="p-4 whitespace-nowrap font-semibold">

                                                <span class="
                                                        {{ $transaction->type == 'income'
                            ? 'text-green-600'
                            : 'text-red-600' }}
                                                    ">

                                                    TZS {{ number_format($transaction->amount, 2) }}

                                                </span>

                                            </td>

                                            {{-- DESCRIPTION --}}
                                            <td class="p-4">

                                                {{ $transaction->description ?? '-' }}

                                            </td>

                                        </tr>

                        @empty

                            <tr>

                                <td colspan="7" class="p-8 text-center text-gray-500">

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