@extends('layouts.admin')

@section('title', 'Add Fixed Expense')

@section('content')

<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">
            Add Fixed Expense
        </h1>

        <a href="{{ route('fixed-expenses.index') }}"
           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
            Back
        </a>
    </div>

    @if($errors->any())
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow p-6">

        <form action="{{ route('fixed-expenses.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                    Business
                </label>

                <select name="business_id"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">General / Personal</option>

                    @foreach($businesses as $business)
                        <option value="{{ $business->id }}"
                            {{ old('business_id') == $business->id ? 'selected' : '' }}>
                            {{ $business->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                    Expense Name
                </label>

                <input type="text"
                       name="name"
                       value="{{ old('name') }}"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="Mfano: Mishahara, Kodi, Internet"
                       required>
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                    Amount
                </label>

                <input type="number"
                       name="default_amount"
                       value="{{ old('default_amount') }}"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="Mfano: 250000"
                       required>
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                    Notes
                </label>

                <textarea name="notes"
                          rows="3"
                          class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox"
                       name="is_active"
                       id="is_active"
                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                       checked>

                <label for="is_active" class="text-sm text-gray-700">
                    Active
                </label>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('fixed-expenses.index') }}"
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    Cancel
                </a>

                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
                    Save
                </button>
            </div>

        </form>

    </div>

</div>

@endsection