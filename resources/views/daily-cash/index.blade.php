@extends('layouts.admin')

@section('title', 'Daily Cash Records')

@section('content')

<div
    x-data="{
        openModal: {{ $errors->any() ? 'true' : 'false' }},

        manualOpeningBalance: @js((float) old('manual_opening_balance', 0)),

        yas: @js((float) old('yas', 0)),
        voda: @js((float) old('voda', 0)),
        halotel: @js((float) old('halotel', 0)),
        airtel: @js((float) old('airtel', 0)),
        token: @js((float) old('token', 0)),
        noti: @js((float) old('noti', 0)),

        expensesTotal: @js((float) old('expenses_total', 0)),
        manualExternalTotal: @js((float) old('manual_external_total', 0)),

        get closingTotal() {
            return Number(this.yas || 0)
                + Number(this.voda || 0)
                + Number(this.halotel || 0)
                + Number(this.airtel || 0)
                + Number(this.token || 0)
                + Number(this.noti || 0);
        },

        get estimatedHqSales() {
            return Number(this.closingTotal || 0)
                + Number(this.expensesTotal || 0)
                - Number(this.manualOpeningBalance || 0)
                - Number(this.manualExternalTotal || 0);
        },

        formatAmount(amount) {
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            }).format(Number(amount || 0));
        },

        resetForm() {
            this.manualOpeningBalance = 0;

            this.yas = 0;
            this.voda = 0;
            this.halotel = 0;
            this.airtel = 0;
            this.token = 0;
            this.noti = 0;

            this.expensesTotal = 0;
            this.manualExternalTotal = 0;
        },

        closeModal() {
            this.openModal = false;
        }
    }"
    class="max-w-7xl mx-auto space-y-6"
>

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

        <div>

            <h1 class="text-2xl font-bold text-gray-800">
                Daily Cash Records
            </h1>

            <p class="text-sm text-gray-500 mt-1">
                Orodha ya taarifa za kufunga siku za HQ.
            </p>

        </div>

        <button
            type="button"
            @click="openModal = true"
            class="inline-flex items-center justify-center
                   bg-green-600 hover:bg-green-700
                   text-white px-5 py-2.5 rounded-lg
                   font-semibold transition"
        >
            + Add Daily Cash
        </button>

    </div>

    {{-- SUCCESS MESSAGE --}}
    @if (session('success'))

        <div class="bg-green-100 border border-green-300
                    text-green-700 px-4 py-3 rounded-lg">

            {{ session('success') }}

        </div>

    @endif

    {{-- ERROR MESSAGE --}}
    @if ($errors->any())

        <div class="bg-red-100 border border-red-300
                    text-red-700 px-4 py-3 rounded-lg">

            <p class="font-semibold">
                Taarifa haijahifadhiwa.
            </p>

            <p class="text-sm mt-1">
                Rekebisha sehemu zilizoonyeshwa kwenye popup.
            </p>

        </div>

    @endif

    {{-- TABLE --}}
    <div class="bg-white shadow rounded-xl overflow-hidden">

        <div class="overflow-x-auto">

            <table class="w-full min-w-[1050px]">

                <thead class="bg-gray-100">

                    <tr>

                        <th class="px-4 py-3 text-left text-sm whitespace-nowrap">
                            Date
                        </th>

                        <th class="px-4 py-3 text-right text-sm whitespace-nowrap">
                            Opening
                        </th>

                        <th class="px-4 py-3 text-right text-sm whitespace-nowrap">
                            Yas
                        </th>

                        <th class="px-4 py-3 text-right text-sm whitespace-nowrap">
                            Voda
                        </th>

                        <th class="px-4 py-3 text-right text-sm whitespace-nowrap">
                            Halotel
                        </th>

                        <th class="px-4 py-3 text-right text-sm whitespace-nowrap">
                            Airtel
                        </th>

                        <th class="px-4 py-3 text-right text-sm whitespace-nowrap">
                            Token
                        </th>

                        <th class="px-4 py-3 text-right text-sm whitespace-nowrap">
                            Noti
                        </th>

                        <th class="px-4 py-3 text-right text-sm whitespace-nowrap">
                            Closing
                        </th>

                        <th class="px-4 py-3 text-right text-sm whitespace-nowrap">
                            Expenses
                        </th>

                        <th class="px-4 py-3 text-right text-sm whitespace-nowrap">
                            External
                        </th>

                        <th class="px-4 py-3 text-right text-sm whitespace-nowrap">
                            HQ Sales
                        </th>

                        <th class="px-4 py-3 text-left text-sm whitespace-nowrap">
                            Entered By
                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-gray-200">

                    @forelse ($entries as $entry)

                        <tr class="hover:bg-gray-50">

                            <td class="px-4 py-3 text-sm whitespace-nowrap">
                                {{ $entry->entry_date->format('d/m/Y') }}
                            </td>

                            <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                {{ number_format($entry->opening_balance, 0) }}
                            </td>

                            <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                {{ number_format($entry->yas, 0) }}
                            </td>

                            <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                {{ number_format($entry->voda, 0) }}
                            </td>

                            <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                {{ number_format($entry->halotel, 0) }}
                            </td>

                            <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                {{ number_format($entry->airtel, 0) }}
                            </td>

                            <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                {{ number_format($entry->token, 0) }}
                            </td>

                            <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                {{ number_format($entry->noti, 0) }}
                            </td>

                            <td class="px-4 py-3 text-sm text-right
                                       font-semibold whitespace-nowrap">
                                {{ number_format($entry->closing_balance, 0) }}
                            </td>

                            <td class="px-4 py-3 text-sm text-right
                                       text-red-600 whitespace-nowrap">
                                {{ number_format($entry->expenses_total, 0) }}
                            </td>

                            <td class="px-4 py-3 text-sm text-right
                                       text-blue-600 whitespace-nowrap">
                                {{ number_format($entry->external_total, 0) }}
                            </td>

                            <td class="px-4 py-3 text-sm text-right
                                       font-bold text-green-700 whitespace-nowrap">
                                {{ number_format($entry->shop_income, 0) }}
                            </td>

                            <td class="px-4 py-3 text-sm whitespace-nowrap">
                                {{ $entry->creator?->name ?? '-' }}
                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="13"
                                class="px-4 py-10 text-center text-gray-500"
                            >
                                Hakuna taarifa za Daily Cash bado.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if ($entries->hasPages())

            <div class="px-4 py-4 border-t">
                {{ $entries->links() }}
            </div>

        @endif

    </div>

    {{-- POPUP MODAL --}}
    <div
        x-show="openModal"
        x-cloak
        x-transition.opacity
        @keydown.escape.window="closeModal()"
        class="fixed inset-0 z-50 flex items-center justify-center
               bg-black/60 px-4 py-6"
    >

        <div
            @click.outside="closeModal()"
            x-transition
            class="bg-white w-full max-w-5xl max-h-[95vh]
                   overflow-y-auto rounded-2xl shadow-2xl"
        >

            {{-- MODAL HEADER --}}
            <div class="sticky top-0 z-20 flex items-center
                        justify-between bg-gray-900 px-6 py-4">

                <div>

                    <h2 class="text-xl font-bold text-white">
                        Add Daily Cash
                    </h2>

                    <p class="text-sm text-gray-300 mt-1">
                        Weka taarifa za kufunga siku za HQ.
                    </p>

                </div>

                <button
                    type="button"
                    @click="closeModal()"
                    class="text-gray-300 hover:text-white
                           text-3xl leading-none"
                >
                    &times;
                </button>

            </div>

            {{-- FORM --}}
            <form
                method="POST"
                action="{{ route('daily-cash.store') }}"
                class="p-6 space-y-6"
            >

                @csrf

                {{-- DATE AND OPENING BALANCE --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- DATE --}}
                    <div>

                        <label
                            for="entry_date"
                            class="block text-sm font-semibold
                                   text-gray-700 mb-1"
                        >
                            Tarehe
                        </label>

                        <input
                            type="date"
                            id="entry_date"
                            name="entry_date"
                            value="{{ old('entry_date', now()->toDateString()) }}"
                            required
                            class="w-full border border-gray-300 rounded-lg
                                   px-3 py-2 focus:ring-2
                                   focus:ring-green-500
                                   focus:border-green-500"
                        >

                        @error('entry_date')

                            <p class="text-sm text-red-600 mt-1">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>

                    {{-- MANUAL OPENING BALANCE --}}
                    <div>

                        <label
                            class="block text-sm font-semibold
                                   text-gray-700 mb-1"
                        >
                            Opening Balance ya Kuanzia
                        </label>

                        <input
                            type="number"
                            name="manual_opening_balance"
                            min="0"
                            step="0.01"
                            x-model.number="manualOpeningBalance"
                            class="w-full border border-gray-300
                                   rounded-lg px-3 py-2
                                   focus:ring-2 focus:ring-green-500
                                   focus:border-green-500"
                        >

                        <p class="text-xs text-gray-500 mt-1">
                            Jaza kwa record ya kwanza tu. Siku zinazofuata
                            mfumo utatumia Closing Balance ya siku iliyopita.
                        </p>

                        @error('manual_opening_balance')

                            <p class="text-sm text-red-600 mt-1">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>

                </div>

                {{-- CLOSING BALANCE --}}
                <div class="border border-gray-200 rounded-xl p-5">

                    <div class="mb-4">

                        <h3 class="text-lg font-bold text-gray-800">
                            Pesa za Kufungia Jioni
                        </h3>

                        <p class="text-sm text-gray-500 mt-1">
                            Jaza kiasi kilichobaki kwenye kila sehemu.
                        </p>

                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2
                                lg:grid-cols-3 gap-4">

                        {{-- YAS --}}
                        <div>

                            <label class="block text-sm font-medium
                                          text-gray-700 mb-1">
                                Yas
                            </label>

                            <input
                                type="number"
                                name="yas"
                                min="0"
                                step="0.01"
                                x-model.number="yas"
                                class="w-full border border-gray-300
                                       rounded-lg px-3 py-2
                                       focus:ring-2 focus:ring-green-500"
                            >

                            @error('yas')

                                <p class="text-sm text-red-600 mt-1">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                        {{-- VODA --}}
                        <div>

                            <label class="block text-sm font-medium
                                          text-gray-700 mb-1">
                                Voda
                            </label>

                            <input
                                type="number"
                                name="voda"
                                min="0"
                                step="0.01"
                                x-model.number="voda"
                                class="w-full border border-gray-300
                                       rounded-lg px-3 py-2
                                       focus:ring-2 focus:ring-green-500"
                            >

                            @error('voda')

                                <p class="text-sm text-red-600 mt-1">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                        {{-- HALOTEL --}}
                        <div>

                            <label class="block text-sm font-medium
                                          text-gray-700 mb-1">
                                Halotel
                            </label>

                            <input
                                type="number"
                                name="halotel"
                                min="0"
                                step="0.01"
                                x-model.number="halotel"
                                class="w-full border border-gray-300
                                       rounded-lg px-3 py-2
                                       focus:ring-2 focus:ring-green-500"
                            >

                            @error('halotel')

                                <p class="text-sm text-red-600 mt-1">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                        {{-- AIRTEL --}}
                        <div>

                            <label class="block text-sm font-medium
                                          text-gray-700 mb-1">
                                Airtel
                            </label>

                            <input
                                type="number"
                                name="airtel"
                                min="0"
                                step="0.01"
                                x-model.number="airtel"
                                class="w-full border border-gray-300
                                       rounded-lg px-3 py-2
                                       focus:ring-2 focus:ring-green-500"
                            >

                            @error('airtel')

                                <p class="text-sm text-red-600 mt-1">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                        {{-- TOKEN --}}
                        <div>

                            <label class="block text-sm font-medium
                                          text-gray-700 mb-1">
                                Token
                            </label>

                            <input
                                type="number"
                                name="token"
                                min="0"
                                step="0.01"
                                x-model.number="token"
                                class="w-full border border-gray-300
                                       rounded-lg px-3 py-2
                                       focus:ring-2 focus:ring-green-500"
                            >

                            @error('token')

                                <p class="text-sm text-red-600 mt-1">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                        {{-- NOTI --}}
                        <div>

                            <label class="block text-sm font-medium
                                          text-gray-700 mb-1">
                                Noti
                            </label>

                            <input
                                type="number"
                                name="noti"
                                min="0"
                                step="0.01"
                                x-model.number="noti"
                                class="w-full border border-gray-300
                                       rounded-lg px-3 py-2
                                       focus:ring-2 focus:ring-green-500"
                            >

                            @error('noti')

                                <p class="text-sm text-red-600 mt-1">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                    </div>

                </div>

                {{-- CLOSING TOTAL --}}
                <div class="bg-green-50 border border-green-200
                            rounded-xl p-5">

                    <p class="text-sm font-medium text-green-700">
                        Jumla ya Kufungia Jioni
                    </p>

                    <p class="text-3xl font-bold text-green-800 mt-1">

                        TZS

                        <span x-text="formatAmount(closingTotal)">
                            0
                        </span>

                    </p>

                    <p class="text-xs text-green-700 mt-2">
                        Yas + Voda + Halotel + Airtel + Token + Noti
                    </p>

                </div>

                {{-- HQ EXPENSES AND EXTERNAL --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- HQ EXPENSES --}}
                    <div>

                        <label class="block text-sm font-semibold
                                      text-gray-700 mb-1">
                            Matumizi ya HQ
                        </label>

                        <input
                            type="number"
                            name="expenses_total"
                            min="0"
                            step="0.01"
                            x-model.number="expensesTotal"
                            class="w-full border border-gray-300
                                   rounded-lg px-3 py-2
                                   focus:ring-2 focus:ring-green-500"
                        >

                        <p class="text-xs text-gray-500 mt-1">
                            Jumla ya matumizi yaliyolipwa na HQ siku hiyo.
                        </p>

                        @error('expenses_total')

                            <p class="text-sm text-red-600 mt-1">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>

                    {{-- MANUAL EXTERNAL --}}
                    <div>

                        <label class="block text-sm font-semibold
                                      text-gray-700 mb-1">
                            Pesa Nyingine Zilizoingia HQ
                        </label>

                        <input
                            type="number"
                            name="manual_external_total"
                            min="0"
                            step="0.01"
                            x-model.number="manualExternalTotal"
                            class="w-full border border-gray-300
                                   rounded-lg px-3 py-2
                                   focus:ring-2 focus:ring-green-500"
                        >

                        <p class="text-xs text-gray-500 mt-1">
                            Mfano pesa kutoka Admin au chanzo cha mara moja.
                        </p>

                        @error('manual_external_total')

                            <p class="text-sm text-red-600 mt-1">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>

                </div>

                {{-- EXTERNAL DESCRIPTION --}}
                <div>

                    <label class="block text-sm font-semibold
                                  text-gray-700 mb-1">
                        Maelezo ya Pesa Nyingine
                    </label>

                    <textarea
                        name="external_description"
                        rows="3"
                        placeholder="Mfano: Pesa kutoka kwa Admin..."
                        class="w-full border border-gray-300
                               rounded-lg px-3 py-2
                               focus:ring-2 focus:ring-green-500"
                    >{{ old('external_description') }}</textarea>

                    @error('external_description')

                        <p class="text-sm text-red-600 mt-1">
                            {{ $message }}
                        </p>

                    @enderror

                </div>

                {{-- LIVE SUMMARY --}}
                <div class="border border-gray-200 rounded-xl overflow-hidden">

                    <div class="bg-gray-100 px-5 py-3">

                        <h3 class="font-bold text-gray-800">
                            Muhtasari wa Hesabu
                        </h3>

                    </div>

                    <div class="p-5 grid grid-cols-1 sm:grid-cols-2
                                lg:grid-cols-4 gap-4">

                        <div>

                            <p class="text-xs text-gray-500">
                                Opening ya Manual
                            </p>

                            <p class="text-lg font-bold text-gray-800">

                                TZS

                                <span x-text="formatAmount(manualOpeningBalance)">
                                    0
                                </span>

                            </p>

                        </div>

                        <div>

                            <p class="text-xs text-gray-500">
                                Closing Balance
                            </p>

                            <p class="text-lg font-bold text-green-700">

                                TZS

                                <span x-text="formatAmount(closingTotal)">
                                    0
                                </span>

                            </p>

                        </div>

                        <div>

                            <p class="text-xs text-gray-500">
                                Matumizi ya HQ
                            </p>

                            <p class="text-lg font-bold text-red-600">

                                TZS

                                <span x-text="formatAmount(expensesTotal)">
                                    0
                                </span>

                            </p>

                        </div>

                        <div>

                            <p class="text-xs text-gray-500">
                                Pesa Nyingine Manual
                            </p>

                            <p class="text-lg font-bold text-blue-600">

                                TZS

                                <span x-text="formatAmount(manualExternalTotal)">
                                    0
                                </span>

                            </p>

                        </div>

                    </div>

                    <div class="border-t bg-yellow-50 px-5 py-4">

                        <p class="text-xs text-yellow-700">
                            Makadirio kabla ya Stendi na Sokoni
                        </p>

                        <p class="text-2xl font-bold text-yellow-800 mt-1">

                            TZS

                            <span x-text="formatAmount(estimatedHqSales)">
                                0
                            </span>

                        </p>

                        <p class="text-xs text-yellow-700 mt-1">
                            Mfumo utaondoa Stendi na Sokoni wakati wa kuhifadhi.
                        </p>

                    </div>

                </div>

                {{-- AUTOMATIC SOURCES --}}
                <div class="bg-blue-50 border border-blue-200
                            rounded-lg p-4">

                    <h3 class="font-semibold text-blue-800">
                        Vyanzo vya Automatic
                    </h3>

                    <p class="text-sm text-blue-700 mt-1">
                        Stendi na Sokoni vitachukuliwa moja kwa moja
                        kutoka kwenye taarifa zao za tarehe uliyochagua.
                        Usiviongeze tena kwenye pesa nyingine.
                    </p>

                </div>

                {{-- ACTIONS --}}
                <div class="flex flex-col-reverse sm:flex-row
                            sm:justify-between gap-3 border-t pt-5">

                    <button
                        type="button"
                        @click="resetForm()"
                        class="px-6 py-2.5 rounded-lg
                               bg-yellow-100 hover:bg-yellow-200
                               text-yellow-800 font-semibold transition"
                    >
                        Clear Form
                    </button>

                    <div class="flex flex-col-reverse sm:flex-row gap-3">

                        <button
                            type="button"
                            @click="closeModal()"
                            class="px-6 py-2.5 rounded-lg
                                   bg-gray-200 hover:bg-gray-300
                                   text-gray-800 font-semibold transition"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="px-6 py-2.5 rounded-lg
                                   bg-green-600 hover:bg-green-700
                                   text-white font-semibold transition"
                        >
                            Save Daily Cash
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection