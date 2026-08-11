@extends('layouts.admin')

@section('title', 'Fixed Expense Payments')

@section('content')

<div class="space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">
            Monthly Fixed Expense Payments
        </h1>

        <a href="{{ route('fixed-expenses.index') }}"
           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
            Back to Fixed Expenses
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <form method="GET" action="{{ route('fixed-expense-payments.index') }}"
          class="bg-white p-4 rounded-xl shadow flex gap-4 items-end">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Month</label>
            <select name="month" class="border-gray-300 rounded-lg">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                    </option>
                @endfor
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
            <input type="number"
                   name="year"
                   value="{{ $year }}"
                   class="border-gray-300 rounded-lg">
        </div>

        <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
            Filter
        </button>

    </form>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-gray-500 text-sm">Total Budget</p>
            <h2 class="text-2xl font-bold text-gray-800">
                TZS {{ number_format($totalBudget) }}
            </h2>
        </div>

        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-gray-500 text-sm">Paid</p>
            <h2 class="text-2xl font-bold text-green-600">
                TZS {{ number_format($paid) }}
            </h2>
        </div>

        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-gray-500 text-sm">Remaining</p>
            <h2 class="text-2xl font-bold text-red-600">
                TZS {{ number_format($remaining) }}
            </h2>
        </div>

    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">

            <table class="w-full text-sm text-left">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-4 py-3">Business</th>
                        <th class="px-4 py-3">Expense</th>
                        <th class="px-4 py-3">Default Amount</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Payment</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">

                    @forelse($fixedExpenses as $expense)

                        @php
                            $payment = $expense->payments->first();
                        @endphp

                        <tr>
                            <td class="px-4 py-3">
                                {{ $expense->business->name ?? 'Personal' }}
                            </td>

                            <td class="px-4 py-3 font-medium">
                                {{ $expense->name }}
                            </td>

                            <td class="px-4 py-3">
                                TZS {{ number_format($expense->default_amount) }}
                            </td>

                            <td class="px-4 py-3">
                                @if($payment)
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs">
                                        Paid
                                    </span>
                                @else
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs">
                                        Pending
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                @if($payment)

                                    <div class="text-sm text-gray-700">
                                        <div>
                                            TZS {{ number_format($payment->amount) }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Paid on {{ \Carbon\Carbon::parse($payment->paid_at)->format('d M Y') }}
                                        </div>
                                    </div>

                                @else

                                    <form action="{{ route('fixed-expense-payments.pay', $expense) }}"
                                          method="POST"
                                          class="flex flex-wrap gap-2 items-center">
                                        @csrf

                                        <input type="hidden" name="month" value="{{ $month }}">
                                        <input type="hidden" name="year" value="{{ $year }}">

                                        <input type="number"
                                               name="amount"
                                               value="{{ $expense->default_amount }}"
                                               class="w-32 border-gray-300 rounded-lg"
                                               required>

                                        <input type="date"
                                               name="paid_at"
                                               value="{{ now()->format('Y-m-d') }}"
                                               class="border-gray-300 rounded-lg"
                                               required>

                                        <button type="submit"
                                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-lg">
                                            Pay
                                        </button>
                                    </form>

                                @endif
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                Hakuna active fixed expenses.
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>

        </div>
    </div>

</div>

@endsection