@extends('layouts.admin')

@section('title', 'Deposit Money')

@section('content')

    <div class="max-w-2xl mx-auto">

        {{-- HEADER --}}
        <div class="flex items-center justify-between mb-6">

            <div>

                <h1 class="text-2xl font-bold text-gray-800">

                    Deposit Money

                </h1>

                <p class="text-sm text-gray-500 mt-1">

                    Add money to saving account

                </p>

            </div>

            <a href="{{ route('savings.show', $saving->id) }}" class="bg-gray-200 hover:bg-gray-300
                      px-4 py-2 rounded-lg text-sm">

                Back

            </a>

        </div>

        {{-- ERROR --}}
        @if ($errors->any())

            <div class="bg-red-100 text-red-700
                            p-4 rounded-lg mb-6">

                <ul class="list-disc pl-5 space-y-1">

                    @foreach ($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

            </div>

        @endif

        {{-- CARD --}}
        <div class="bg-white shadow rounded-2xl p-6">

            {{-- SAVING INFO --}}
            <div class="mb-6">

                <h2 class="font-semibold text-lg">

                    {{ $saving->name }}

                </h2>

                <p class="text-sm text-gray-500 mt-1">

                    Current Balance:
                    <span class="font-semibold text-green-600">

                        TZS {{ number_format($saving->balance, 2) }}

                    </span>

                </p>

            </div>

            {{-- FORM --}}
            <form action="{{ route('savings.deposit.store', $saving->id) }}" method="POST" class="space-y-5">

                @csrf

                {{-- AMOUNT --}}
                <div>

                    <label class="block text-sm font-medium mb-2">

                        Deposit Amount

                    </label>

                    <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" required placeholder="0.00"
                        class="w-full border rounded-xl px-4 py-3">

                </div>

                {{-- PAYMENT METHOD --}}
                <div>

                    <label class="block text-sm font-medium mb-2">

                        Payment Method

                    </label>

                    <select name="payment_method" required class="w-full border rounded-xl px-4 py-3">

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

                {{-- DATE --}}
                <div>

                    <label class="block text-sm font-medium mb-2">

                        Transaction Date

                    </label>

                    <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required
                        class="w-full border rounded-xl px-4 py-3">

                </div>

                {{-- DESCRIPTION --}}
                <div>

                    <label class="block text-sm font-medium mb-2">

                        Description

                    </label>

                    <textarea name="description" rows="4" placeholder="Deposit details..."
                        class="w-full border rounded-xl px-4 py-3">{{ old('description') }}</textarea>

                </div>

                {{-- BUTTON --}}
                <div>

                    <button class="bg-green-600 hover:bg-green-700
                               text-white px-6 py-3 rounded-xl">

                        Save Deposit

                    </button>

                </div>

            </form>

        </div>

    </div>

@endsection