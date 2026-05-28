@extends('layouts.admin')

@section('title', 'Add Saving')

@section('content')

    <div class="max-w-3xl mx-auto">

        {{-- HEADER --}}
        <div class="flex items-center justify-between mb-6">

            <div>

                <h1 class="text-2xl font-bold text-gray-800">

                    Create Saving

                </h1>

                <p class="text-sm text-gray-500 mt-1">

                    Add new saving account

                </p>

            </div>

            <a href="{{ route('savings.index') }}" class="bg-gray-200 hover:bg-gray-300
                      px-4 py-2 rounded-lg text-sm">

                Back

            </a>

        </div>

        {{-- ERRORS --}}
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

        {{-- FORM --}}
        <div class="bg-white shadow rounded-2xl p-6">

            <form action="{{ route('savings.store') }}" method="POST" class="space-y-5">

                @csrf

                {{-- BUSINESS --}}
                <div>

                    <label class="block text-sm font-medium mb-2">

                        Business

                    </label>

                    <select name="business_id" class="w-full border rounded-xl px-4 py-3">

                        <option value="">

                            Select Business

                        </option>

                        @foreach($businesses as $business)

                            <option value="{{ $business->id }}">

                                {{ $business->name }}

                            </option>

                        @endforeach

                    </select>

                </div>

                {{-- TYPE --}}
                <div>

                    <label class="block text-sm font-medium mb-2">

                        Saving Type

                    </label>

                    <select name="type" required class="w-full border rounded-xl px-4 py-3">

                        <option value="">

                            Select Type

                        </option>

                        <option value="personal">

                            Personal

                        </option>

                        <option value="business">

                            Business

                        </option>

                        <option value="group">

                            Group / Kibati

                        </option>

                        <option value="emergency">

                            Emergency

                        </option>

                        <option value="investment">

                            Investment

                        </option>

                    </select>

                </div>

                {{-- NAME --}}
                <div>

                    <label class="block text-sm font-medium mb-2">

                        Saving Name

                    </label>

                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Example: Emergency Fund" required
                        class="w-full border rounded-xl px-4 py-3">

                </div>

                {{-- TARGET --}}
                <div>

                    <label class="block text-sm font-medium mb-2">

                        Target Amount

                    </label>

                    <input type="number" step="0.01" name="target_amount" value="{{ old('target_amount') }}"
                        placeholder="0.00" class="w-full border rounded-xl px-4 py-3">

                </div>

                {{-- START DATE --}}
                <div>

                    <label class="block text-sm font-medium mb-2">

                        Start Date

                    </label>

                    <input type="date" name="start_date" value="{{ old('start_date') }}"
                        class="w-full border rounded-xl px-4 py-3">

                </div>

                {{-- MATURITY DATE --}}
                <div>

                    <label class="block text-sm font-medium mb-2">

                        Maturity Date

                    </label>

                    <input type="date" name="maturity_date" value="{{ old('maturity_date') }}"
                        class="w-full border rounded-xl px-4 py-3">

                </div>

                {{-- DESCRIPTION --}}
                <div>

                    <label class="block text-sm font-medium mb-2">

                        Description

                    </label>

                    <textarea name="description" rows="4" placeholder="Saving details..."
                        class="w-full border rounded-xl px-4 py-3">{{ old('description') }}</textarea>

                </div>

                {{-- BUTTON --}}
                <div>

                    <button class="bg-indigo-600 hover:bg-indigo-700
                               text-white px-6 py-3 rounded-xl">

                        Save Saving

                    </button>

                </div>

            </form>

        </div>

    </div>

@endsection