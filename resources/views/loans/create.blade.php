@extends('layouts.admin')

@section('title', 'Add Loan')

@section('content')

<div class="max-w-3xl mx-auto">

    <div class="bg-white shadow rounded-2xl p-6">

        {{-- TITLE --}}
        <div class="mb-6">

            <h1 class="text-2xl font-semibold">

                Add Loan

            </h1>

        </div>

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
              action="{{ route('loans.store') }}">

            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- TYPE --}}
                <div>

                    <label class="block text-sm font-medium mb-2">

                        Loan Type

                    </label>

                    <select name="type"
                            class="w-full border rounded-xl px-4 py-3">

                        <option value="">
                            -- Select Type --
                        </option>

                        <option value="receivable"
                            {{ old('type') == 'receivable' ? 'selected' : '' }}>

                            Receivable (Someone owes you)

                        </option>

                        <option value="payable"
                            {{ old('type') == 'payable' ? 'selected' : '' }}>

                            Payable (You owe someone)

                        </option>

                    </select>

                </div>

                {{-- BUSINESS --}}
                <div>

                    <label class="block text-sm font-medium mb-2">

                        Business

                    </label>

                    <select name="business_id"
                            class="w-full border rounded-xl px-4 py-3">

                        <option value="">
                            -- Select Business --
                        </option>

                        @foreach($businesses as $business)

                            <option value="{{ $business->id }}"
                                {{ old('business_id') == $business->id ? 'selected' : '' }}>

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

                    <input type="text"
                           name="name"
                           value="{{ old('name') }}"
                           class="w-full border rounded-xl px-4 py-3">

                </div>

                {{-- PHONE --}}
                <div>

                    <label class="block text-sm font-medium mb-2">

                        Phone Number

                    </label>

                    <input type="text"
                           name="phone"
                           value="{{ old('phone') }}"
                           class="w-full border rounded-xl px-4 py-3">

                </div>

                {{-- AMOUNT --}}
                <div>

                    <label class="block text-sm font-medium mb-2">

                        Loan Amount

                    </label>

                    <input type="number"
                           step="0.01"
                           name="amount"
                           value="{{ old('amount') }}"
                           class="w-full border rounded-xl px-4 py-3">

                </div>

                {{-- LOAN DATE --}}
                <div>

                    <label class="block text-sm font-medium mb-2">

                        Loan Date

                    </label>

                    <input type="date"
                           name="loan_date"
                           value="{{ old('loan_date', date('Y-m-d')) }}"
                           class="w-full border rounded-xl px-4 py-3">

                </div>

                {{-- DUE DATE --}}
                <div>

                    <label class="block text-sm font-medium mb-2">

                        Due Date

                    </label>

                    <input type="date"
                           name="due_date"
                           value="{{ old('due_date') }}"
                           class="w-full border rounded-xl px-4 py-3">

                </div>

            </div>

            {{-- DESCRIPTION --}}
            <div class="mt-6">

                <label class="block text-sm font-medium mb-2">

                    Description

                </label>

                <textarea
                    name="description"
                    rows="5"
                    class="w-full border rounded-xl px-4 py-3">{{ old('description') }}</textarea>

            </div>

            {{-- BUTTON --}}
            <div class="mt-6">

                <button
                    class="bg-blue-600 hover:bg-blue-700
                           text-white px-6 py-3 rounded-xl transition">

                    Save Loan

                </button>

            </div>

        </form>

    </div>

</div>

@endsection