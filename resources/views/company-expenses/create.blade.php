@extends('layouts.admin')

@section('title', 'Add Expense')

@section('content')

<div class="max-w-2xl mx-auto">

    <div class="bg-white shadow rounded-xl p-6">

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

        <form method="POST"
              action="{{ route('company-expenses.store') }}">

            @csrf

            {{-- BUSINESS --}}
            <div class="mb-4">

                <label class="block text-sm mb-1">

                    Business

                </label>

                <select name="business_id"
                        class="w-full border rounded-lg px-3 py-2">

                    <option value="">
                        -- Select Business --
                    </option>

                    @foreach($businesses as $business)

                        <option value="{{ $business->id }}">

                            {{ $business->name }}

                        </option>

                    @endforeach

                </select>

            </div>

            {{-- CATEGORY --}}
            <div class="mb-4">

                <label class="block text-sm mb-1">

                    Expense Category

                </label>

                <input type="text"
                       name="category"
                       value="{{ old('category') }}"
                       placeholder="Example: Transport"
                       class="w-full border rounded-lg px-3 py-2">

            </div>

            {{-- AMOUNT --}}
            <div class="mb-4">

                <label class="block text-sm mb-1">

                    Amount

                </label>

                <input type="number"
                       step="0.01"
                       name="amount"
                       value="{{ old('amount') }}"
                       class="w-full border rounded-lg px-3 py-2">

            </div>

            {{-- PAYMENT METHOD --}}
            <div class="mb-4">

                <label class="block text-sm mb-1">

                    Payment Method

                </label>

                <select name="payment_method"
                        class="w-full border rounded-lg px-3 py-2">

                    <option value="cash">Cash</option>

                    <option value="bank">Bank</option>

                    <option value="mpesa">M-Pesa</option>

                    <option value="airtel_money">Airtel Money</option>

                    <option value="mix">Mixed</option>

                </select>

            </div>

            {{-- DATE --}}
            <div class="mb-4">

                <label class="block text-sm mb-1">

                    Expense Date

                </label>

                <input type="date"
                       name="transaction_date"
                       value="{{ date('Y-m-d') }}"
                       class="w-full border rounded-lg px-3 py-2">

            </div>

            {{-- DESCRIPTION --}}
            <div class="mb-6">

                <label class="block text-sm mb-1">

                    Description

                </label>

                <textarea
                    name="description"
                    rows="4"
                    class="w-full border rounded-lg px-3 py-2">{{ old('description') }}</textarea>

            </div>

            <button
                class="bg-red-600 hover:bg-red-700
                       text-white px-5 py-2 rounded-lg transition">

                Save Expense

            </button>

        </form>

    </div>

</div>

@endsection