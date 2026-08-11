@extends('layouts.admin')

@section('title', 'Daily Cash Entry')

@section('content')

<div class="max-w-5xl mx-auto space-y-6">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                Daily Cash Entry
            </h1>

            <p class="text-sm text-gray-500 mt-1">
                Ingiza salio la jioni, matumizi ya HQ na pesa nyingine zilizoingia dukani.
            </p>
        </div>

        <a href="{{ route('daily-cash.index') }}"
           class="inline-flex items-center justify-center
                  bg-gray-700 hover:bg-gray-800
                  text-white px-4 py-2 rounded-lg transition">

            View Records

        </a>

    </div>

    {{-- ERRORS --}}
    @if ($errors->any())

        <div class="bg-red-100 border border-red-300
                    text-red-700 p-4 rounded-lg">

            <ul class="list-disc pl-5 space-y-1">

                @foreach ($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    {{-- AUTOMATIC SUMMARY --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- OPENING BALANCE --}}
        <div class="bg-white rounded-xl shadow p-5">

            <p class="text-sm text-gray-500">
                Opening Balance
            </p>

            <p class="text-2xl font-bold text-gray-800 mt-2">

                TZS {{ number_format($openingBalance, 0) }}

            </p>

            <p class="text-xs text-gray-400 mt-1">
                Closing balance ya record iliyopita
            </p>

        </div>

        {{-- STENDI --}}
        <div class="bg-white rounded-xl shadow p-5">

            <p class="text-sm text-gray-500">
                Stendi
            </p>

            <p class="text-2xl font-bold text-blue-700 mt-2">

                TZS {{ number_format($stendiAmount, 0) }}

            </p>

            <p class="text-xs text-gray-400 mt-1">
                Automatic baada ya matumizi
            </p>

        </div>

        {{-- SOKONI --}}
        <div class="bg-white rounded-xl shadow p-5">

            <p class="text-sm text-gray-500">
                Sokoni
            </p>

            <p class="text-2xl font-bold text-purple-700 mt-2">

                TZS {{ number_format($sokoniAmount, 0) }}

            </p>

            <p class="text-xs text-gray-400 mt-1">
                Automatic baada ya matumizi
            </p>

        </div>

        {{-- AUTOMATIC TOTAL --}}
        <div class="bg-white rounded-xl shadow p-5">

            <p class="text-sm text-gray-500">
                Automatic External Total
            </p>

            <p class="text-2xl font-bold text-green-700 mt-2">

                TZS {{ number_format($automaticExternalTotal, 0) }}

            </p>

            <p class="text-xs text-gray-400 mt-1">
                Stendi + Sokoni
            </p>

        </div>

    </div>

    {{-- DATE FILTER --}}
    <div class="bg-white shadow rounded-xl p-5">

        <form method="GET"
              action="{{ route('daily-cash.create') }}"
              class="flex flex-col sm:flex-row sm:items-end gap-4">

            <div class="flex-1">

                <label class="block text-sm font-medium text-gray-700 mb-1">

                    Tarehe ya taarifa

                </label>

                <input type="date"
                       name="entry_date"
                       value="{{ $entryDate }}"
                       class="w-full border border-gray-300
                              rounded-lg px-3 py-2
                              focus:ring-2 focus:ring-indigo-500
                              focus:border-indigo-500">

            </div>

            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700
                           text-white px-5 py-2 rounded-lg transition">

                Load Date

            </button>

        </form>

    </div>

    {{-- TERMINAL FORM --}}
    <div class="bg-white shadow rounded-xl overflow-hidden">

        <div class="bg-gray-900 px-6 py-4">

            <h2 class="text-white font-semibold text-lg">
                HQ Cash Terminal
            </h2>

            <p class="text-gray-400 text-sm mt-1">
                Andika taarifa moja kwa kila mstari.
            </p>

        </div>

        <form method="POST"
              action="{{ route('daily-cash.store') }}"
              class="p-6">

            @csrf

            <input type="hidden"
                   name="entry_date"
                   value="{{ $entryDate }}">

            <div class="mb-4">

                <label class="block text-sm font-medium text-gray-700 mb-2">

                    Terminal Input

                </label>

                <textarea
                    name="raw_input"
                    rows="18"
                    required
                    spellcheck="false"
                    placeholder="yas 10000
voda 15000
halotel 20000
airtel 5000
token 3000
noti 30000

matumizi usafiri 5000
matumizi umeme 8000

nje admin 50000"
                    class="w-full bg-gray-950 text-green-400
                           font-mono text-sm
                           border border-gray-700
                           rounded-lg p-4
                           focus:ring-2 focus:ring-green-500
                           focus:border-green-500">{{ old('raw_input') }}</textarea>

            </div>

            {{-- GUIDE --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

                <div class="bg-gray-50 border rounded-lg p-4">

                    <h3 class="font-semibold text-gray-700 mb-2">
                        Closing Balance
                    </h3>

                    <div class="font-mono text-sm text-gray-600 space-y-1">

                        <p>yas 10000</p>
                        <p>voda 15000</p>
                        <p>halotel 20000</p>
                        <p>airtel 5000</p>
                        <p>token 3000</p>
                        <p>noti 30000</p>

                    </div>

                </div>

                <div class="bg-gray-50 border rounded-lg p-4">

                    <h3 class="font-semibold text-gray-700 mb-2">
                        Matumizi ya HQ
                    </h3>

                    <div class="font-mono text-sm text-gray-600 space-y-1">

                        <p>matumizi usafiri 5000</p>
                        <p>matumizi umeme 8000</p>
                        <p>expense chakula 10000</p>

                    </div>

                </div>

                <div class="bg-gray-50 border rounded-lg p-4">

                    <h3 class="font-semibold text-gray-700 mb-2">
                        Pesa za Nje Manual
                    </h3>

                    <div class="font-mono text-sm text-gray-600 space-y-1">

                        <p>nje admin 50000</p>
                        <p>nje nyumba seuta 100000</p>
                        <p>external mtaji 200000</p>

                    </div>

                    <p class="text-xs text-red-500 mt-3">
                        Usiongeze Stendi au Sokoni hapa.
                    </p>

                </div>

            </div>

            {{-- FORMULA --}}
            <div class="bg-blue-50 border border-blue-200
                        rounded-lg p-4 mb-6">

                <h3 class="font-semibold text-blue-800 mb-2">

                    Mfumo wa hesabu

                </h3>

                <p class="text-sm text-blue-700">

                    Mauzo ya HQ =
                    Closing Balance
                    + Matumizi ya HQ
                    − Opening Balance
                    − Pesa zote zilizoingia kutoka nje ya duka.

                </p>

            </div>

            <div class="flex flex-col sm:flex-row gap-3">

                <button type="submit"
                        class="bg-green-600 hover:bg-green-700
                               text-white px-6 py-3
                               rounded-lg font-semibold transition">

                    Save Daily Cash

                </button>

                <a href="{{ route('daily-cash.index') }}"
                   class="inline-flex items-center justify-center
                          bg-gray-200 hover:bg-gray-300
                          text-gray-800 px-6 py-3
                          rounded-lg font-semibold transition">

                    Cancel

                </a>

            </div>

        </form>

    </div>

</div>

@endsection