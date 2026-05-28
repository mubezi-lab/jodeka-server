@extends('layouts.admin')

@section('title', 'Edit Income')

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

            <form method="POST" action="{{ route('company-incomes.update', $income->id) }}">

                @csrf
                @method('PUT')

                {{-- BUSINESS --}}
                <div class="mb-4">

                    <label class="block text-sm mb-1">

                        Business

                    </label>

                    <select name="business_id" class="w-full border rounded-lg px-3 py-2">

                        <option value="">
                            -- Select Business --
                        </option>

                        @foreach($businesses as $business)

                            <option value="{{ $business->id }}" {{ $income->business_id == $business->id ? 'selected' : '' }}>

                                {{ $business->name }}

                            </option>

                        @endforeach

                    </select>

                </div>

                {{-- CATEGORY --}}
                <div class="mb-4">

                    <label class="block text-sm mb-1">

                        Income Category

                    </label>

                    <input type="text" name="category" value="{{ old('category', $income->category) }}"
                        class="w-full border rounded-lg px-3 py-2">

                </div>

                {{-- AMOUNT --}}
                <div class="mb-4">

                    <label class="block text-sm mb-1">

                        Amount

                    </label>

                    <input type="number" step="0.01" name="amount" value="{{ old('amount', $income->amount) }}"
                        class="w-full border rounded-lg px-3 py-2">

                </div>

                {{-- PAYMENT METHOD --}}
                <div class="mb-4">

                    <label class="block text-sm mb-1">

                        Payment Method

                    </label>

                    <select name="payment_method" class="w-full border rounded-lg px-3 py-2">

                        <option value="cash" {{ $income->payment_method == 'cash' ? 'selected' : '' }}>
                            Cash
                        </option>

                        <option value="bank" {{ $income->payment_method == 'bank' ? 'selected' : '' }}>
                            Bank
                        </option>

                        <option value="mpesa" {{ $income->payment_method == 'mpesa' ? 'selected' : '' }}>
                            M-Pesa
                        </option>

                        <option value="airtel_money" {{ $income->payment_method == 'airtel_money' ? 'selected' : '' }}>
                            Airtel Money
                        </option>

                        <option value="mix" {{ $income->payment_method == 'mix' ? 'selected' : '' }}>
                            Mixed
                        </option>

                    </select>

                </div>

                {{-- DATE --}}
                <div class="mb-4">

                    <label class="block text-sm mb-1">

                        Income Date

                    </label>

                    <input type="date" name="transaction_date" value="{{ $income->transaction_date }}"
                        class="w-full border rounded-lg px-3 py-2">

                </div>

                {{-- DESCRIPTION --}}
                <div class="mb-6">

                    <label class="block text-sm mb-1">

                        Description

                    </label>

                    <textarea name="description" rows="4"
                        class="w-full border rounded-lg px-3 py-2">{{ old('description', $income->description) }}</textarea>

                </div>

                <button class="bg-yellow-500 hover:bg-yellow-600
                           text-white px-5 py-2 rounded-lg transition">

                    Update Income

                </button>

            </form>

        </div>

    </div>

@endsection