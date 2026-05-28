@extends('layouts.admin')

@section('title', 'Edit Loan')

@section('content')

    <div class="max-w-5xl mx-auto">

        <div class="bg-white shadow rounded-2xl p-6">

            {{-- TITLE --}}
            <div class="mb-6">

                <h1 class="text-2xl font-semibold">

                    Edit Loan

                </h1>

            </div>

            {{-- SUCCESS --}}
            @if(session('success'))

                <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6">

                    {{ session('success') }}

                </div>

            @endif

            {{-- ERRORS --}}
            @if ($errors->any())

                <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6">

                    <ul class="list-disc pl-5">

                        @foreach ($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif

            {{-- UPDATE FORM --}}
            <form method="POST" action="{{ route('loans.update', $loan->id) }}">

                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- TYPE --}}
                    <div>

                        <label class="block text-sm font-medium mb-2">

                            Loan Type

                        </label>

                        <select name="type" class="w-full border rounded-xl px-4 py-3">

                            <option value="">
                                -- Select Type --
                            </option>

                            <option value="receivable" {{ old('type', $loan->type) == 'receivable' ? 'selected' : '' }}>

                                Receivable (Someone owes you)

                            </option>

                            <option value="payable" {{ old('type', $loan->type) == 'payable' ? 'selected' : '' }}>

                                Payable (You owe someone)

                            </option>

                        </select>

                    </div>

                    {{-- BUSINESS --}}
                    <div>

                        <label class="block text-sm font-medium mb-2">

                            Business

                        </label>

                        <select name="business_id" class="w-full border rounded-xl px-4 py-3">

                            <option value="">
                                -- Select Business --
                            </option>

                            @foreach($businesses as $business)

                                <option value="{{ $business->id }}" {{ old('business_id', $loan->business_id) == $business->id ? 'selected' : '' }}>

                                    {{ $business->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- NAME --}}
                    <div>

                        <label class="block text-sm font-medium mb-2">

                            Person / Company Name

                        </label>

                        <input type="text" name="name" value="{{ old('name', $loan->name) }}"
                            class="w-full border rounded-xl px-4 py-3">

                    </div>

                    {{-- PHONE --}}
                    <div>

                        <label class="block text-sm font-medium mb-2">

                            Phone Number

                        </label>

                        <input type="text" name="phone" value="{{ old('phone', $loan->phone) }}"
                            class="w-full border rounded-xl px-4 py-3">

                    </div>

                    {{-- AMOUNT --}}
                    <div>

                        <label class="block text-sm font-medium mb-2">

                            Loan Amount

                        </label>

                        <input type="number" step="0.01" name="amount" value="{{ old('amount', $loan->amount) }}"
                            class="w-full border rounded-xl px-4 py-3">

                    </div>

                    {{-- LOAN DATE --}}
                    <div>

                        <label class="block text-sm font-medium mb-2">

                            Loan Date

                        </label>

                        <input type="date" name="loan_date" value="{{ old('loan_date', $loan->loan_date) }}"
                            class="w-full border rounded-xl px-4 py-3">

                    </div>

                    {{-- DUE DATE --}}
                    <div>

                        <label class="block text-sm font-medium mb-2">

                            Due Date

                        </label>

                        <input type="date" name="due_date" value="{{ old('due_date', $loan->due_date) }}"
                            class="w-full border rounded-xl px-4 py-3">

                    </div>

                    {{-- STATUS --}}
                    <div>

                        <label class="block text-sm font-medium mb-2">

                            Current Status

                        </label>

                        <input type="text" disabled value="{{ ucfirst($loan->status) }}"
                            class="w-full bg-gray-100 border rounded-xl px-4 py-3">

                    </div>

                </div>

                {{-- DESCRIPTION --}}
                <div class="mt-6">

                    <label class="block text-sm font-medium mb-2">

                        Description

                    </label>

                    <textarea name="description" rows="5"
                        class="w-full border rounded-xl px-4 py-3">{{ old('description', $loan->description) }}</textarea>

                </div>

                {{-- PAYMENT SUMMARY --}}
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">

                    <div class="bg-gray-50 rounded-xl p-4">

                        <p class="text-sm text-gray-500">

                            Total Amount

                        </p>

                        <h2 class="text-xl font-bold mt-2">

                            TZS {{ number_format($loan->amount, 2) }}

                        </h2>

                    </div>

                    <div class="bg-green-50 rounded-xl p-4">

                        <p class="text-sm text-green-600">

                            Paid Amount

                        </p>

                        <h2 class="text-xl font-bold mt-2 text-green-700">

                            TZS {{ number_format($loan->paid_amount, 2) }}

                        </h2>

                    </div>

                    <div class="bg-red-50 rounded-xl p-4">

                        <p class="text-sm text-red-600">

                            Remaining Balance

                        </p>

                        <h2 class="text-xl font-bold mt-2 text-red-700">

                            TZS {{ number_format($loan->remaining_amount, 2) }}

                        </h2>

                    </div>

                </div>

                {{-- UPDATE BUTTON --}}
                <div class="mt-6 flex items-center gap-3">

                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-600
                                   text-white px-6 py-3 rounded-xl transition">

                        Update Loan

                    </button>

                    <a href="{{ route('loans.index') }}" class="bg-gray-200 hover:bg-gray-300
                              px-6 py-3 rounded-xl transition">

                        Cancel

                    </a>

                </div>

            </form>

            {{-- PAYMENT SECTION --}}
            <div class="mt-8">

                <div class="bg-gray-50 rounded-2xl p-6">

                    <h2 class="text-xl font-semibold mb-6">

                        Loan Payments

                    </h2>

                    @if($loan->remaining_amount > 0)

                        <form method="POST" action="{{ route('loan-payments.store', $loan->id) }}">

                            @csrf

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                {{-- AMOUNT --}}
                                <div>

                                    <label class="block text-sm font-medium mb-2">

                                        Payment Amount

                                    </label>

                                    <input type="number" step="0.01" name="amount" class="w-full border rounded-xl px-4 py-3">

                                </div>

                                {{-- DATE --}}
                                <div>

                                    <label class="block text-sm font-medium mb-2">

                                        Payment Date

                                    </label>

                                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}"
                                        class="w-full border rounded-xl px-4 py-3">

                                </div>

                                {{-- METHOD --}}
                                <div>

                                    <label class="block text-sm font-medium mb-2">

                                        Payment Method

                                    </label>

                                    <select name="payment_method" class="w-full border rounded-xl px-4 py-3">

                                        <option value="cash">
                                            Cash
                                        </option>

                                        <option value="bank">
                                            Bank
                                        </option>

                                        <option value="mpesa">
                                            M-Pesa
                                        </option>

                                        <option value="airtel_money">
                                            Airtel Money
                                        </option>

                                        <option value="mix">
                                            Mixed
                                        </option>

                                    </select>

                                </div>

                            </div>

                            {{-- NOTES --}}
                            <div class="mt-6">

                                <label class="block text-sm font-medium mb-2">

                                    Notes

                                </label>

                                <textarea name="notes" rows="4" class="w-full border rounded-xl px-4 py-3"></textarea>

                            </div>

                            {{-- BUTTON --}}
                            <div class="mt-6">

                                <button class="bg-green-600 hover:bg-green-700
                                               text-white px-6 py-3 rounded-xl transition">

                                    Add Payment

                                </button>

                            </div>

                        </form>

                    @else

                        <div class="bg-green-100 text-green-700 p-4 rounded-xl">

                            This loan has been fully paid.

                        </div>

                    @endif

                </div>

            </div>

            {{-- PAYMENT HISTORY --}}
            <div class="mt-8">

                <div class="bg-white border rounded-2xl overflow-hidden">

                    <div class="p-5 border-b">

                        <h2 class="text-lg font-semibold">

                            Payment History

                        </h2>

                    </div>

                    <div class="overflow-x-auto">

                        <table class="w-full min-w-[700px]">

                            <thead class="bg-gray-100">

                                <tr>

                                    <th class="p-4 text-left">#</th>

                                    <th class="p-4 text-left">Amount</th>

                                    <th class="p-4 text-left">Method</th>

                                    <th class="p-4 text-left">Date</th>

                                    <th class="p-4 text-left">Notes</th>

                                    <th class="p-4 text-left">Actions</th>

                                </tr>

                            </thead>

                            <tbody>

                                @forelse($loan->payments as $index => $payment)

                                    <tr class="border-t hover:bg-gray-50">

                                        <td class="p-4">

                                            {{ $index + 1 }}

                                        </td>

                                        <td class="p-4 font-semibold text-green-600">

                                            TZS {{ number_format($payment->amount, 2) }}

                                        </td>

                                        <td class="p-4">

                                            {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}

                                        </td>

                                        <td class="p-4">

                                            {{ $payment->payment_date }}

                                        </td>

                                        <td class="p-4">

                                            {{ $payment->notes ?? '-' }}

                                        </td>

                                        <td class="p-4">

                                            <form method="POST" action="{{ route('loan-payments.destroy', $payment->id) }}">

                                                @csrf
                                                @method('DELETE')

                                                <button onclick="return confirm('Delete payment?')" class="bg-red-500 hover:bg-red-600
                                                               text-white px-3 py-1 rounded-md text-sm">

                                                    Delete

                                                </button>

                                            </form>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td colspan="6" class="p-6 text-center text-gray-500">

                                            No payments found

                                        </td>

                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection