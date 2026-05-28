@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

    <div class="space-y-6">

        {{-- FILTERS --}}
        <div class="bg-white p-4 rounded-2xl shadow">

            <div class="flex flex-wrap gap-3">

                <a href="{{ route('dashboard', ['filter' => 'today']) }}" class="px-4 py-2 rounded-lg text-sm transition
                   {{ $filter == 'today'
        ? 'bg-blue-600 text-white'
        : 'bg-gray-100 hover:bg-gray-200' }}">

                    Today

                </a>

                <a href="{{ route('dashboard', ['filter' => 'week']) }}" class="px-4 py-2 rounded-lg text-sm transition
                   {{ $filter == 'week'
        ? 'bg-blue-600 text-white'
        : 'bg-gray-100 hover:bg-gray-200' }}">

                    This Week

                </a>

                <a href="{{ route('dashboard', ['filter' => 'month']) }}" class="px-4 py-2 rounded-lg text-sm transition
                   {{ $filter == 'month'
        ? 'bg-blue-600 text-white'
        : 'bg-gray-100 hover:bg-gray-200' }}">

                    This Month

                </a>

                <a href="{{ route('dashboard', ['filter' => 'year']) }}" class="px-4 py-2 rounded-lg text-sm transition
                   {{ $filter == 'year'
        ? 'bg-blue-600 text-white'
        : 'bg-gray-100 hover:bg-gray-200' }}">

                    This Year

                </a>

            </div>

        </div>

        {{-- ACTION BUTTONS --}}
        <div class="bg-white p-4 rounded-2xl shadow">

            <div class="flex flex-wrap gap-3">

                <a href="{{ route('users.create') }}" class="bg-indigo-600 hover:bg-indigo-700
                          text-white px-4 py-2 rounded-lg text-sm transition">

                    + Add User

                </a>

                <a href="{{ route('products.create') }}" class="bg-blue-600 hover:bg-blue-700
                          text-white px-4 py-2 rounded-lg text-sm transition">

                    + Add Product

                </a>

                <a href="{{ route('purchases.create') }}" class="bg-green-600 hover:bg-green-700
                          text-white px-4 py-2 rounded-lg text-sm transition">

                    + Add Purchase

                </a>

                <a href="{{ route('livestocks.create') }}" class="bg-purple-600 hover:bg-purple-700
                          text-white px-4 py-2 rounded-lg text-sm transition">

                    + Add Livestock

                </a>

                <a href="{{ route('businesses.create') }}" class="bg-gray-800 hover:bg-gray-900
                          text-white px-4 py-2 rounded-lg text-sm transition">

                    + Add Business

                </a>

                <a href="{{ route('company-incomes.create') }}" class="bg-emerald-600 hover:bg-emerald-700
                          text-white px-4 py-2 rounded-lg text-sm transition">

                    + Add Income

                </a>

                <a href="{{ route('company-expenses.create') }}" class="bg-red-600 hover:bg-red-700
                          text-white px-4 py-2 rounded-lg text-sm transition">

                    + Add Expense

                </a>

            </div>

        </div>

        {{-- TODAY ANALYTICS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

            {{-- INCOME --}}
            <div class="bg-white p-5 rounded-2xl shadow">

                <p class="text-sm text-gray-500">

                    Income

                </p>

                <h2 class="text-3xl font-bold text-green-600 mt-2">

                    TZS {{ number_format($todayIncome, 2) }}

                </h2>

            </div>

            {{-- EXPENSES --}}
            <div class="bg-white p-5 rounded-2xl shadow">

                <p class="text-sm text-gray-500">

                    Expenses

                </p>

                <h2 class="text-3xl font-bold text-red-600 mt-2">

                    TZS {{ number_format($todayExpenses, 2) }}

                </h2>

            </div>

            {{-- PROFIT --}}
            <div class="bg-white p-5 rounded-2xl shadow">

                <p class="text-sm text-gray-500">

                    Profit

                </p>

                <h2 class="text-3xl font-bold mt-2
                    {{ $todayProfit >= 0
        ? 'text-green-600'
        : 'text-red-600' }}">

                    TZS {{ number_format($todayProfit, 2) }}

                </h2>

            </div>

            {{-- TRANSACTIONS --}}
            <div class="bg-white p-5 rounded-2xl shadow">

                <p class="text-sm text-gray-500">

                    Transactions

                </p>

                <h2 class="text-3xl font-bold text-blue-600 mt-2">

                    {{ number_format($totalTransactions) }}

                </h2>

            </div>

        </div>

        {{-- MONTHLY ANALYTICS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">

            {{-- MONTHLY INCOME --}}
            <div class="bg-white p-5 rounded-2xl shadow">

                <p class="text-sm text-gray-500">

                    Monthly Income

                </p>

                <h2 class="text-3xl font-bold text-green-600 mt-2">

                    TZS {{ number_format($monthlyIncome, 2) }}

                </h2>

            </div>

            {{-- MONTHLY EXPENSES --}}
            <div class="bg-white p-5 rounded-2xl shadow">

                <p class="text-sm text-gray-500">

                    Monthly Expenses

                </p>

                <h2 class="text-3xl font-bold text-red-600 mt-2">

                    TZS {{ number_format($monthlyExpenses, 2) }}

                </h2>

            </div>

            {{-- MONTHLY PROFIT --}}
            <div class="bg-white p-5 rounded-2xl shadow">

                <p class="text-sm text-gray-500">

                    Monthly Profit

                </p>

                <h2 class="text-3xl font-bold mt-2
                    {{ $monthlyProfit >= 0
        ? 'text-green-600'
        : 'text-red-600' }}">

                    TZS {{ number_format($monthlyProfit, 2) }}

                </h2>

            </div>

        </div>

        {{-- CHART --}}
        <div class="bg-white p-6 rounded-2xl shadow">

            <div class="flex items-center justify-between mb-6">

                <h2 class="text-lg font-semibold">

                    Monthly Income vs Expenses

                </h2>

            </div>

            <div class="h-[400px]">

                <canvas id="financeChart"></canvas>

            </div>

        </div>

        {{-- SYSTEM TOTALS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

            <div class="bg-white p-5 rounded-2xl shadow">

                <p class="text-sm text-gray-500">

                    Total Businesses

                </p>

                <h2 class="text-3xl font-bold text-indigo-600 mt-2">

                    {{ $totalBusinesses }}

                </h2>

            </div>

            <div class="bg-white p-5 rounded-2xl shadow">

                <p class="text-sm text-gray-500">

                    Total Users

                </p>

                <h2 class="text-3xl font-bold text-purple-600 mt-2">

                    {{ $totalUsers }}

                </h2>

            </div>

            <div class="bg-white p-5 rounded-2xl shadow">

                <p class="text-sm text-gray-500">

                    Total Products

                </p>

                <h2 class="text-3xl font-bold text-orange-600 mt-2">

                    {{ $totalProducts }}

                </h2>

            </div>

            <div class="bg-white p-5 rounded-2xl shadow">

                <p class="text-sm text-gray-500">

                    Accounting Entries

                </p>

                <h2 class="text-3xl font-bold text-cyan-600 mt-2">

                    {{ $totalTransactions }}

                </h2>

            </div>

        </div>

        {{-- RECENT TRANSACTIONS --}}
        <div class="bg-white rounded-2xl shadow overflow-hidden">

            <div class="p-5 border-b">

                <h2 class="text-lg font-semibold">

                    Recent Transactions

                </h2>

            </div>

            <div class="overflow-x-auto">

                <table class="w-full min-w-[800px]">

                    <thead class="bg-gray-100">

                        <tr>

                            <th class="p-4 text-left">Type</th>
                            <th class="p-4 text-left">Business</th>
                            <th class="p-4 text-left">Category</th>
                            <th class="p-4 text-left">Amount</th>
                            <th class="p-4 text-left">Payment</th>
                            <th class="p-4 text-left">Date</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($recentTransactions as $transaction)

                                        <tr class="border-t hover:bg-gray-50 transition">

                                            <td class="p-4">

                                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                                        {{ $transaction->type == 'income'
                            ? 'bg-green-100 text-green-700'
                            : 'bg-red-100 text-red-700' }}">

                                                    {{ ucfirst($transaction->type) }}

                                                </span>

                                            </td>

                                            <td class="p-4 whitespace-nowrap">

                                                {{ $transaction->business->name ?? '-' }}

                                            </td>

                                            <td class="p-4 whitespace-nowrap">

                                                {{ $transaction->category }}

                                            </td>

                                            <td class="p-4 font-semibold whitespace-nowrap
                                                    {{ $transaction->type == 'income'
                            ? 'text-green-600'
                            : 'text-red-600' }}">

                                                TZS {{ number_format($transaction->amount, 2) }}

                                            </td>

                                            <td class="p-4 whitespace-nowrap">

                                                {{ ucfirst(str_replace('_', ' ', $transaction->payment_method)) }}

                                            </td>

                                            <td class="p-4 whitespace-nowrap">

                                                {{ $transaction->transaction_date }}

                                            </td>

                                        </tr>

                        @empty

                            <tr>

                                <td colspan="6" class="p-6 text-center text-gray-500">

                                    No recent transactions

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        {{-- BUSINESS PERFORMANCE --}}
        <div class="bg-white rounded-2xl shadow overflow-hidden">

            <div class="p-5 border-b">

                <h2 class="text-lg font-semibold">

                    Business Performance

                </h2>

            </div>

            <div class="overflow-x-auto">

                <table class="w-full min-w-[900px]">

                    <thead class="bg-gray-100">

                        <tr>

                            <th class="p-4 text-left">Business</th>
                            <th class="p-4 text-left">Income</th>
                            <th class="p-4 text-left">Expenses</th>
                            <th class="p-4 text-left">Profit</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($businessPerformance as $business)

                                        <tr class="border-t hover:bg-gray-50 transition">

                                            <td class="p-4 whitespace-nowrap font-medium">

                                                {{ $business['name'] }}

                                            </td>

                                            <td class="p-4 whitespace-nowrap text-green-600 font-semibold">

                                                TZS {{ number_format($business['income'], 2) }}

                                            </td>

                                            <td class="p-4 whitespace-nowrap text-red-600 font-semibold">

                                                TZS {{ number_format($business['expenses'], 2) }}

                                            </td>

                                            <td class="p-4 whitespace-nowrap font-bold
                                                    {{ $business['profit'] >= 0
                            ? 'text-green-600'
                            : 'text-red-600' }}">

                                                TZS {{ number_format($business['profit'], 2) }}

                                            </td>

                                        </tr>

                        @empty

                            <tr>

                                <td colspan="4" class="p-6 text-center text-gray-500">

                                    No business data found

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    {{-- CHART JS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>

        const ctx = document.getElementById('financeChart');

        new Chart(ctx, {

            type: 'bar',

            data: {

                labels: @json($chartLabels),

                datasets: [

                    {
                        label: 'Income',

                        data: @json($incomeData),

                        backgroundColor: '#16a34a',
                    },

                    {
                        label: 'Expenses',

                        data: @json($expenseData),

                        backgroundColor: '#dc2626',
                    }

                ]
            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                plugins: {

                    legend: {
                        position: 'top',
                    }

                },

                scales: {

                    y: {

                        beginAtZero: true

                    }

                }

            }

        });

    </script>

@endsection