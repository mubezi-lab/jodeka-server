@extends('layouts.admin')

@section('title')
    <div class="flex items-center gap-3">

        <div class="w-11 h-11 rounded-xl
                            bg-blue-100 text-blue-600
                            flex items-center justify-center
                            text-xl">

            <i class="fa-solid fa-people-group"></i>

        </div>

        <div>

            <div class="text-2xl font-bold text-gray-900 leading-tight">
                Bagambakamo
            </div>

            <div class="text-sm font-normal text-gray-500 mt-1">
                Member contributions, payments, events and group finances
            </div>

        </div>

    </div>
@endsection


@section('content')

    <div class="space-y-5">


        {{-- ========================================================= --}}
        {{-- ALERTS --}}
        {{-- ========================================================= --}}

        @if(session('success'))

            <div id="successAlert" class="rounded-xl border border-green-200
                                        bg-green-50 px-4 py-3
                                        text-green-700">

                <i class="fa-solid fa-circle-check mr-2"></i>

                {{ session('success') }}

            </div>

        @endif


        @if(session('error'))

            <div class="rounded-xl border border-red-200
                                        bg-red-50 px-4 py-3
                                        text-red-700">

                <i class="fa-solid fa-circle-exclamation mr-2"></i>

                {{ session('error') }}

            </div>

        @endif


        @if($errors->any())

            <div class="rounded-xl border border-red-200
                                        bg-red-50 px-4 py-3
                                        text-red-700">

                <i class="fa-solid fa-triangle-exclamation mr-2"></i>

                {{ $errors->first() }}

            </div>

        @endif


        {{-- ========================================================= --}}
        {{-- PENDING TRANSACTION ALERT --}}
        {{-- ========================================================= --}}

        @if(($pendingTransactionsCount ?? 0) > 0)

            <div class="rounded-2xl
                                        border border-orange-200
                                        bg-orange-50
                                        px-5 py-4
                                        flex flex-col
                                        md:flex-row
                                        md:items-center
                                        md:justify-between
                                        gap-4">

                <div class="flex items-center gap-3">

                    <div class="w-11 h-11 rounded-xl
                                                bg-orange-100
                                                text-orange-600
                                                flex items-center
                                                justify-center
                                                text-xl">

                        <i class="fa-solid fa-bell"></i>

                    </div>

                    <div>

                        <div class="font-bold text-orange-900">
                            Pending M-Koba Transaction
                        </div>

                        <div class="text-sm text-orange-700 mt-1">
                            {{ $pendingTransactionsCount }}
                            {{ $pendingTransactionsCount === 1 ? 'transaction requires' : 'transactions require' }}
                            confirmation.
                        </div>

                    </div>

                </div>

                @if(isset($pendingTransactions) && $pendingTransactions->isNotEmpty())

                    <button type="button"
                        onclick="openBagambakamoModal('pendingTransactionModal{{ $pendingTransactions->first()->id }}')" class="inline-flex items-center justify-center gap-2
                                                       px-5 py-2.5
                                                       rounded-xl
                                                       bg-orange-600
                                                       text-white
                                                       font-semibold
                                                       hover:bg-orange-700
                                                       transition">

                        <i class="fa-solid fa-eye"></i>

                        Review Transaction

                    </button>

                @endif

            </div>

        @endif


        {{-- ========================================================= --}}
        {{-- ACTIONS --}}
        {{-- ========================================================= --}}

        <div class="flex flex-wrap items-center gap-3">

            <button type="button" onclick="openBagambakamoModal('memberModal')" class="inline-flex items-center justify-center gap-2
                               px-5 py-3 rounded-xl
                               bg-blue-600 text-white
                               font-semibold
                               hover:bg-blue-700 transition">

                <i class="fa-solid fa-plus"></i>

                Add Member

            </button>


            <button type="button" onclick="openBagambakamoModal('paymentModal')" class="inline-flex items-center justify-center gap-2
                               px-5 py-3 rounded-xl
                               bg-green-600 text-white
                               font-semibold
                               hover:bg-green-700 transition">

                <i class="fa-solid fa-money-bill-wave"></i>

                Add Payment

            </button>


            <button type="button" onclick="openBagambakamoModal('eventModal')" class="inline-flex items-center justify-center gap-2
                               px-5 py-3 rounded-xl
                               bg-yellow-500 text-gray-900
                               font-semibold
                               hover:bg-yellow-600 transition">

                <i class="fa-solid fa-calendar-plus"></i>

                Add Event

            </button>


            <a href="{{ route('bagambakamo.members.index') }}" class="inline-flex items-center justify-center gap-2
                               px-5 py-3 rounded-xl
                               bg-white border border-blue-300
                               text-blue-600 font-semibold
                               hover:bg-blue-50 transition">

                <i class="fa-solid fa-users"></i>

                View Members

            </a>


            <a href="{{ route('bagambakamo.report.pdf') }}" target="_blank" class="inline-flex items-center justify-center gap-2
                               px-5 py-3 rounded-xl
                               bg-white border border-gray-300
                               text-gray-800 font-semibold
                               hover:bg-gray-50 transition">

                <i class="fa-solid fa-file-pdf"></i>

                Download Report

            </a>

        </div>


        {{-- ========================================================= --}}
        {{-- SUMMARY CARDS --}}
        {{-- ========================================================= --}}

        <div class="grid grid-cols-1
                            sm:grid-cols-2
                            lg:grid-cols-3
                            2xl:grid-cols-6
                            gap-4">


            {{-- TOTAL MEMBERS --}}
            <div class="rounded-2xl
                                border border-blue-100
                                bg-blue-50 p-4">

                <div class="flex items-start gap-3">

                    <div class="w-11 h-11 rounded-xl
                                        bg-blue-100 text-blue-600
                                        flex items-center justify-center
                                        text-xl shrink-0">

                        <i class="fa-solid fa-users"></i>

                    </div>

                    <div>

                        <div class="text-sm text-gray-600">
                            Total Members
                        </div>

                        <div class="text-2xl font-bold text-gray-900">
                            {{ number_format($totalMembers) }}
                        </div>

                    </div>

                </div>

                <div class="mt-3 text-sm text-blue-600">
                    Members with ≥ TSH 80,000
                </div>

            </div>


            {{-- TOTAL PAYMENTS --}}
            <div class="rounded-2xl
                                border border-green-100
                                bg-green-50 p-4">

                <div class="flex items-start gap-3">

                    <div class="w-11 h-11 rounded-xl
                                        bg-green-100 text-green-600
                                        flex items-center justify-center
                                        text-xl shrink-0">

                        <i class="fa-solid fa-money-bill-wave"></i>

                    </div>

                    <div>

                        <div class="text-sm text-gray-600">
                            Total Payments
                        </div>

                        <div class="text-xl font-bold text-gray-900">
                            TSH {{ number_format($totalPayments) }}
                        </div>

                    </div>

                </div>

                <div class="mt-3 text-sm text-green-600">
                    All payments received
                </div>

            </div>


            {{-- THIS MONTH --}}
            <div class="rounded-2xl
                                border border-yellow-100
                                bg-yellow-50 p-4">

                <div class="flex items-start gap-3">

                    <div class="w-11 h-11 rounded-xl
                                        bg-yellow-100 text-yellow-600
                                        flex items-center justify-center
                                        text-xl shrink-0">

                        <i class="fa-solid fa-chart-column"></i>

                    </div>

                    <div>

                        <div class="text-sm text-gray-600">
                            This Month
                        </div>

                        <div class="text-xl font-bold text-gray-900">
                            TSH {{ number_format($monthlyPayments) }}
                        </div>

                    </div>

                </div>

                <div class="mt-3 text-sm text-yellow-700">
                    Payments in {{ now()->format('F') }}
                </div>

            </div>


            {{-- GROUP BALANCE --}}
            <div class="rounded-2xl
                                border border-purple-100
                                bg-purple-50 p-4">

                <div class="flex items-start gap-3">

                    <div class="w-11 h-11 rounded-xl
                                        bg-purple-100 text-purple-600
                                        flex items-center justify-center
                                        text-xl shrink-0">

                        <i class="fa-solid fa-wallet"></i>

                    </div>

                    <div>

                        <div class="text-sm text-gray-600">
                            Group Balance
                        </div>

                        <div class="text-xl font-bold text-gray-900">
                            TSH {{ number_format($groupBalance) }}
                        </div>

                    </div>

                </div>

                <div class="mt-3 text-sm text-purple-600">
                    Incoming − outgoing
                </div>

            </div>


            {{-- EVENTS --}}
            <div class="rounded-2xl
                                border border-red-100
                                bg-red-50 p-4">

                <div class="flex items-start gap-3">

                    <div class="w-11 h-11 rounded-xl
                                        bg-red-100 text-red-600
                                        flex items-center justify-center
                                        text-xl shrink-0">

                        <i class="fa-solid fa-calendar-days"></i>

                    </div>

                    <div>

                        <div class="text-sm text-gray-600">
                            Events Given
                        </div>

                        <div class="text-xl font-bold text-gray-900">
                            TSH {{ number_format($totalEventsGiven) }}
                        </div>

                    </div>

                </div>

                <div class="mt-3 text-sm text-red-600">
                    Group event support
                </div>

            </div>


            {{-- DEBTS --}}
            <div class="rounded-2xl
                                border border-cyan-100
                                bg-cyan-50 p-4">

                <div class="flex items-start gap-3">

                    <div class="w-11 h-11 rounded-xl
                                        bg-cyan-100 text-cyan-600
                                        flex items-center justify-center
                                        text-xl shrink-0">

                        <i class="fa-solid fa-triangle-exclamation"></i>

                    </div>

                    <div>

                        <div class="text-sm text-gray-600">
                            Total Debts
                        </div>

                        <div class="text-xl font-bold text-gray-900">
                            TSH {{ number_format($totalDebts) }}
                        </div>

                    </div>

                </div>

                <div class="mt-3 text-sm text-cyan-700">
                    Members balance
                </div>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- RECENT PAYMENTS + EVENTS --}}
        {{-- ========================================================= --}}

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">


            {{-- RECENT PAYMENTS --}}
            <div class="bg-white rounded-2xl
                                border border-gray-200
                                shadow-sm overflow-hidden">

                <div class="px-5 py-4 border-b
                                    flex items-center justify-between">

                    <h3 class="font-bold text-lg text-gray-800">

                        <i class="fa-solid fa-money-bill-wave mr-2"></i>

                        Recent Payments

                    </h3>


                    <a href="{{ route('bagambakamo.members.index') }}" class="text-blue-600 font-semibold hover:underline">

                        View All

                    </a>

                </div>


                <div class="overflow-x-auto">

                    <table class="w-full text-sm">

                        <thead class="bg-gray-50">

                            <tr class="text-gray-600">

                                <th class="px-4 py-3 text-left">#</th>
                                <th class="px-4 py-3 text-left">Member</th>
                                <th class="px-4 py-3 text-left">Type</th>
                                <th class="px-4 py-3 text-right">Amount (TSH)</th>
                                <th class="px-4 py-3 text-left">Date</th>
                                <th class="px-4 py-3 text-left">Description</th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-gray-100">

                            @forelse($recentPayments as $index => $payment)

                                <tr class="hover:bg-gray-50">

                                    <td class="px-4 py-3">
                                        {{ $index + 1 }}
                                    </td>

                                    <td class="px-4 py-3 font-medium">
                                        {{ $payment->member?->full_name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">

                                        @if($payment->type === 'monthly')

                                            <span class="inline-flex px-2 py-1
                                                                                         rounded-lg
                                                                                         bg-blue-100 text-blue-700
                                                                                         text-xs font-semibold">

                                                Monthly

                                            </span>

                                        @else

                                            <span class="inline-flex px-2 py-1
                                                                                         rounded-lg
                                                                                         bg-purple-100 text-purple-700
                                                                                         text-xs font-semibold">

                                                Mchango

                                            </span>

                                        @endif

                                    </td>

                                    <td class="px-4 py-3 text-right
                                                               text-green-600 font-bold">

                                        {{ number_format($payment->amount) }}

                                    </td>

                                    <td class="px-4 py-3">

                                        {{ optional($payment->payment_date)->format('d/m/Y') }}

                                    </td>

                                    <td class="px-4 py-3">

                                        {{ $payment->description ?: '-' }}

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="6" class="px-4 py-12
                                                               text-center text-gray-400">

                                        No payments found.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>


            {{-- RECENT EVENTS --}}
            <div class="bg-white rounded-2xl
                                border border-gray-200
                                shadow-sm overflow-hidden">

                <div class="px-5 py-4 border-b
                                    flex items-center justify-between">

                    <h3 class="font-bold text-lg text-gray-800">

                        <i class="fa-solid fa-calendar-days mr-2"></i>

                        Recent Events

                    </h3>

                </div>


                <div class="overflow-x-auto">

                    <table class="w-full text-sm">

                        <thead class="bg-gray-50">

                            <tr class="text-gray-600">

                                <th class="px-4 py-3 text-left">#</th>
                                <th class="px-4 py-3 text-left">Beneficiary</th>
                                <th class="px-4 py-3 text-left">Type</th>
                                <th class="px-4 py-3 text-right">Amount (TSH)</th>
                                <th class="px-4 py-3 text-left">Date</th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-gray-100">

                            @forelse($recentEvents as $index => $event)

                                <tr class="hover:bg-gray-50">

                                    <td class="px-4 py-3">
                                        {{ $index + 1 }}
                                    </td>

                                    <td class="px-4 py-3 font-medium">
                                        {{ $event->member?->full_name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">

                                        <span class="inline-flex px-2 py-1
                                                                             rounded-lg
                                                                             bg-red-100 text-red-700
                                                                             text-xs font-semibold">

                                            {{ ucfirst($event->type) }}

                                        </span>

                                    </td>

                                    <td class="px-4 py-3 text-right
                                                               text-red-600 font-bold">

                                        {{ number_format($event->amount) }}

                                    </td>

                                    <td class="px-4 py-3">

                                        {{ optional($event->event_date)->format('d/m/Y') }}

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="5" class="px-4 py-12
                                                               text-center text-gray-400">

                                        No events found.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- CHART + QUICK INFO --}}
        {{-- ========================================================= --}}

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

            <div class="xl:col-span-2
                                bg-white rounded-2xl
                                border border-gray-200
                                shadow-sm p-5">

                <h3 class="font-bold text-lg text-gray-800 mb-5">

                    <i class="fa-solid fa-chart-column mr-2"></i>

                    Payments vs Events — {{ now()->year }}

                </h3>

                <div class="h-72">

                    <canvas id="bagambakamoChart"></canvas>

                </div>

            </div>


            <div class="bg-white rounded-2xl
                                border border-gray-200
                                shadow-sm p-5">

                <h3 class="font-bold text-lg text-gray-800">

                    <i class="fa-solid fa-circle-info mr-2"></i>

                    Quick Info

                </h3>


                <div class="mt-5
                                    rounded-2xl
                                    bg-green-50
                                    border border-green-100
                                    p-7 text-center">

                    <i class="fa-solid fa-people-group
                                      text-green-600
                                      text-4xl mb-4"></i>

                    <div class="text-green-800
                                        text-lg font-bold">

                        “Umoja ni nguvu, pamoja tunaweza.”

                    </div>

                    <div class="text-green-600 mt-3">
                        — Bagambakamo —
                    </div>

                </div>

            </div>

        </div>

    </div>



    {{-- ============================================================= --}}
    {{-- MEMBER MODAL --}}
    {{-- ============================================================= --}}

    <div id="memberModal" class="hidden fixed inset-0 z-[100]
                       bg-black/50 p-4
                       items-center justify-center">

        <div class="bg-white rounded-2xl
                            shadow-2xl w-full max-w-lg">

            <div class="flex items-center justify-between
                                px-6 py-4 border-b">

                <h3 class="text-lg font-bold">

                    <i class="fa-solid fa-users mr-2"></i>

                    Add Member

                </h3>

                <button type="button" onclick="closeBagambakamoModal('memberModal')"
                    class="text-gray-400 hover:text-gray-700">

                    <i class="fa-solid fa-xmark text-xl"></i>

                </button>

            </div>


            <form method="POST" action="{{ route('bagambakamo.members.store') }}">

                @csrf

                <div class="p-6 space-y-5">

                    <div>

                        <label class="block mb-2
                                              text-sm font-semibold">

                            Full Name
                            <span class="text-red-500">*</span>

                        </label>

                        <input type="text" name="full_name" value="{{ old('full_name') }}" class="w-full rounded-xl
                                           border-gray-300" placeholder="Enter full name" required>

                    </div>


                    <div>

                        <label class="block mb-2
                                              text-sm font-semibold">

                            Phone

                        </label>

                        <input type="text" name="phone" value="{{ old('phone') }}" class="w-full rounded-xl
                                           border-gray-300" placeholder="Enter phone number">

                    </div>

                </div>


                <div class="px-6 py-4 border-t
                                    flex justify-end gap-3">

                    <button type="button" onclick="closeBagambakamoModal('memberModal')" class="px-5 py-2.5
                                       rounded-xl
                                       bg-gray-500 text-white">

                        Cancel

                    </button>

                    <button type="submit" class="px-5 py-2.5
                                       rounded-xl
                                       bg-blue-600 text-white
                                       font-semibold">

                        <i class="fa-solid fa-floppy-disk mr-2"></i>

                        Save Member

                    </button>

                </div>

            </form>

        </div>

    </div>



    {{-- ============================================================= --}}
    {{-- PAYMENT MODAL --}}
    {{-- ============================================================= --}}

    <div id="paymentModal" class="hidden fixed inset-0 z-[100]
                       bg-black/50 p-4
                       items-center justify-center">

        <div class="bg-white rounded-2xl
                            shadow-2xl w-full max-w-2xl">

            <div class="flex items-center justify-between
                                px-6 py-4 border-b">

                <h3 class="text-lg font-bold">

                    <i class="fa-solid fa-money-bill-wave
                                      mr-2 text-green-600"></i>

                    Add Payment

                </h3>

                <button type="button" onclick="closeBagambakamoModal('paymentModal')"
                    class="text-gray-400 hover:text-gray-700">

                    <i class="fa-solid fa-xmark text-xl"></i>

                </button>

            </div>


            <form method="POST" action="{{ route('bagambakamo.payments.store') }}">

                @csrf

                <div class="p-6
                                    grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div class="md:col-span-2">

                        <label class="block mb-2
                                              text-sm font-semibold">

                            Member
                            <span class="text-red-500">*</span>

                        </label>

                        <select name="member_id" class="w-full rounded-xl border-gray-300" required>

                            <option value="">
                                Select member
                            </option>

                            @foreach(
                                    \App\Models\Bagambakamo\Member::orderBy('full_name')->get()
                                    as $member
                                )

                                <option value="{{ $member->id }}" @selected(old('member_id') == $member->id)>

                                    {{ $member->full_name }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div>

                        <label class="block mb-2 text-sm font-semibold">

                            Amount (TSH)
                            <span class="text-red-500">*</span>

                        </label>

                        <input type="number" name="amount" value="{{ old('amount') }}" min="0" step="0.01"
                            class="w-full rounded-xl border-gray-300" placeholder="Enter amount" required>

                    </div>


                    <div>

                        <label class="block mb-2 text-sm font-semibold">

                            Payment Type
                            <span class="text-red-500">*</span>

                        </label>

                        <select name="type" class="w-full rounded-xl border-gray-300" required>

                            <option value="">
                                Select type
                            </option>

                            <option value="monthly" @selected(old('type') === 'monthly')>

                                Monthly

                            </option>

                            <option value="mchango" @selected(old('type') === 'mchango')>

                                Mchango

                            </option>

                        </select>

                    </div>


                    <div class="md:col-span-2">

                        <label class="block mb-2 text-sm font-semibold">
                            Description
                        </label>

                        <input type="text" name="description" value="{{ old('description') }}"
                            class="w-full rounded-xl border-gray-300" placeholder="Description (optional)">

                    </div>


                    <div class="md:col-span-2">

                        <label class="block mb-2 text-sm font-semibold">

                            Payment Date
                            <span class="text-red-500">*</span>

                        </label>

                        <input type="date" name="payment_date" value="{{ old('payment_date', now()->format('Y-m-d')) }}"
                            class="w-full rounded-xl border-gray-300" required>

                    </div>

                </div>


                <div class="px-6 py-4 border-t
                                    flex justify-end gap-3">

                    <button type="button" onclick="closeBagambakamoModal('paymentModal')" class="px-5 py-2.5
                                       rounded-xl
                                       bg-gray-500 text-white">

                        Cancel

                    </button>

                    <button type="submit" class="px-5 py-2.5
                                       rounded-xl
                                       bg-green-600 text-white
                                       font-semibold">

                        <i class="fa-solid fa-floppy-disk mr-2"></i>

                        Save Payment

                    </button>

                </div>

            </form>

        </div>

    </div>



    {{-- ============================================================= --}}
    {{-- EVENT MODAL --}}
    {{-- ============================================================= --}}

    <div id="eventModal" class="hidden fixed inset-0 z-[100]
                       bg-black/50 p-4
                       items-center justify-center">

        <div class="bg-white rounded-2xl
                            shadow-2xl w-full max-w-2xl">

            <div class="flex items-center justify-between
                                px-6 py-4 border-b">

                <h3 class="text-lg font-bold">

                    <i class="fa-solid fa-calendar-days
                                      mr-2 text-red-600"></i>

                    Add Event

                </h3>

                <button type="button" onclick="closeBagambakamoModal('eventModal')"
                    class="text-gray-400 hover:text-gray-700">

                    <i class="fa-solid fa-xmark text-xl"></i>

                </button>

            </div>


            <form method="POST" action="{{ route('bagambakamo.events.store') }}">

                @csrf

                <div class="p-6
                                    grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div class="md:col-span-2">

                        <label class="block mb-2 text-sm font-semibold">

                            Beneficiary
                            <span class="text-red-500">*</span>

                        </label>

                        <select name="member_id" class="w-full rounded-xl border-gray-300" required>

                            <option value="">
                                Select beneficiary
                            </option>

                            @foreach(
                                    \App\Models\Bagambakamo\Member::orderBy('full_name')->get()
                                    as $member
                                )

                                <option value="{{ $member->id }}">
                                    {{ $member->full_name }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div>

                        <label class="block mb-2 text-sm font-semibold">

                            Event Type
                            <span class="text-red-500">*</span>

                        </label>

                        <input type="text" name="type" value="{{ old('type') }}" class="w-full rounded-xl border-gray-300"
                            placeholder="Msiba / Sherehe" required>

                    </div>


                    <div>

                        <label class="block mb-2 text-sm font-semibold">

                            Amount Given
                            <span class="text-red-500">*</span>

                        </label>

                        <input type="number" name="amount" value="{{ old('amount') }}" min="0" step="0.01"
                            class="w-full rounded-xl border-gray-300" placeholder="Enter amount" required>

                    </div>


                    <div>

                        <label class="block mb-2 text-sm font-semibold">

                            Contribution per Member
                            <span class="text-red-500">*</span>

                        </label>

                        <input type="number" name="contribution_per_member"
                            value="{{ old('contribution_per_member', 10000) }}" min="0" step="0.01"
                            class="w-full rounded-xl border-gray-300" required>

                    </div>


                    <div>

                        <label class="block mb-2 text-sm font-semibold">

                            Event Date
                            <span class="text-red-500">*</span>

                        </label>

                        <input type="date" name="event_date" value="{{ old('event_date', now()->format('Y-m-d')) }}"
                            class="w-full rounded-xl border-gray-300" required>

                    </div>

                </div>


                <div class="px-6 py-4 border-t
                                    flex justify-end gap-3">

                    <button type="button" onclick="closeBagambakamoModal('eventModal')" class="px-5 py-2.5
                                       rounded-xl
                                       bg-gray-500 text-white">

                        Cancel

                    </button>

                    <button type="submit" class="px-5 py-2.5
                                       rounded-xl
                                       bg-yellow-500 text-gray-900
                                       font-semibold">

                        <i class="fa-solid fa-floppy-disk mr-2"></i>

                        Save Event

                    </button>

                </div>

            </form>

        </div>

    </div>



    {{-- ============================================================= --}}
    {{-- PENDING M-KOBA TRANSACTION MODALS --}}
    {{-- ============================================================= --}}

    @if(isset($pendingTransactions))

        @foreach($pendingTransactions as $pendingTransaction)

            <div id="pendingTransactionModal{{ $pendingTransaction->id }}" class="hidden fixed inset-0 z-[120]
                                               bg-black/60 p-4
                                               items-center justify-center
                                               overflow-y-auto">

                <div class="bg-white rounded-2xl
                                                    shadow-2xl
                                                    w-full max-w-3xl
                                                    my-auto">

                    {{-- HEADER --}}
                    <div class="px-6 py-5 border-b
                                                        flex items-start justify-between
                                                        gap-4">

                        <div class="flex items-center gap-3">

                            <div class="w-12 h-12 rounded-xl
                                                                bg-orange-100
                                                                text-orange-600
                                                                flex items-center justify-center
                                                                text-xl shrink-0">

                                <i class="fa-solid fa-money-bill-transfer"></i>

                            </div>

                            <div>

                                <h3 class="text-xl font-bold text-gray-900">
                                    M-Koba Transaction Confirmation
                                </h3>

                                <div class="text-sm text-gray-500 mt-1">
                                    Confirm what this outgoing transaction was used for.
                                </div>

                            </div>

                        </div>

                        <span class="inline-flex
                                                             px-3 py-1.5
                                                             rounded-full
                                                             bg-orange-100
                                                             text-orange-700
                                                             text-xs font-bold">

                            Pending

                        </span>

                    </div>


                    {{-- AUTO-FILLED TRANSACTION DETAILS --}}
                    <div class="px-6 pt-6">

                        <div class="rounded-2xl
                                                            border border-gray-200
                                                            bg-gray-50
                                                            p-5">

                            <div class="grid grid-cols-1
                                                                sm:grid-cols-2
                                                                gap-5">

                                <div>

                                    <div class="text-xs font-semibold
                                                                        uppercase tracking-wide
                                                                        text-gray-500">

                                        Beneficiary

                                    </div>

                                    <div class="mt-1 text-base
                                                                        font-bold text-gray-900">

                                        {{ $pendingTransaction->member?->full_name
                        ?? $pendingTransaction->recipient_name
                        ?? '-' }}

                                    </div>

                                </div>


                                <div>

                                    <div class="text-xs font-semibold
                                                                        uppercase tracking-wide
                                                                        text-gray-500">

                                        Amount Given

                                    </div>

                                    <div class="mt-1 text-xl
                                                                        font-bold text-red-600">

                                        TSH {{ number_format($pendingTransaction->amount) }}

                                    </div>

                                </div>


                                <div>

                                    <div class="text-xs font-semibold
                                                                        uppercase tracking-wide
                                                                        text-gray-500">

                                        Transaction Date

                                    </div>

                                    <div class="mt-1 font-semibold text-gray-900">

                                        {{ optional($pendingTransaction->transaction_date)->format('d/m/Y H:i') }}

                                    </div>

                                </div>


                                <div>

                                    <div class="text-xs font-semibold
                                                                        uppercase tracking-wide
                                                                        text-gray-500">

                                        M-Koba Reference

                                    </div>

                                    <div class="mt-1 font-semibold
                                                                        text-gray-900">

                                        {{ $pendingTransaction->reference }}

                                    </div>

                                </div>


                                <div>

                                    <div class="text-xs font-semibold
                                                                        uppercase tracking-wide
                                                                        text-gray-500">

                                        Phone

                                    </div>

                                    <div class="mt-1 text-gray-900">

                                        {{ $pendingTransaction->recipient_phone ?: '-' }}

                                    </div>

                                </div>


                                <div>

                                    <div class="text-xs font-semibold
                                                                        uppercase tracking-wide
                                                                        text-gray-500">

                                        M-Koba Balance After Transaction

                                    </div>

                                    <div class="mt-1 font-semibold
                                                                        text-gray-900">

                                        @if($pendingTransaction->account_balance !== null)

                                            TSH {{ number_format($pendingTransaction->account_balance) }}

                                        @else

                                            -

                                        @endif

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- SELECT PURPOSE --}}
                    <div class="px-6 pt-5">

                        <div class="text-sm font-bold text-gray-800 mb-3">
                            What was this payment for?
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                            <button type="button" id="pendingEventButton{{ $pendingTransaction->id }}"
                                onclick="showPendingEventForm({{ $pendingTransaction->id }})" class="pending-purpose-button
                                                               w-full rounded-xl
                                                               border-2 border-blue-500
                                                               bg-blue-50
                                                               text-blue-700
                                                               px-4 py-4
                                                               font-bold
                                                               flex items-center justify-center gap-2
                                                               transition">

                                <i class="fa-solid fa-calendar-days"></i>

                                Event

                            </button>


                            <button type="button" id="pendingExpenseButton{{ $pendingTransaction->id }}"
                                onclick="showPendingExpenseForm({{ $pendingTransaction->id }})" class="pending-purpose-button
                                                               w-full rounded-xl
                                                               border border-gray-300
                                                               bg-white
                                                               text-gray-700
                                                               px-4 py-4
                                                               font-bold
                                                               flex items-center justify-center gap-2
                                                               hover:bg-gray-50
                                                               transition">

                                <i class="fa-solid fa-receipt"></i>

                                Expenditure

                            </button>

                        </div>

                    </div>


                    {{-- ===================================================== --}}
                    {{-- EVENT FORM --}}
                    {{-- ===================================================== --}}

                    <div id="pendingEventForm{{ $pendingTransaction->id }}" class="px-6 py-6">

                        <form method="POST" action="{{ route(
                        'bagambakamo.pending-transactions.event',
                        $pendingTransaction
                    ) }}">

                            @csrf


                            {{-- ================================================= --}}
                            {{-- VALUES FROM M-KOBA TRANSACTION --}}
                            {{-- ================================================= --}}

                            <input type="hidden" name="member_id" value="{{ $pendingTransaction->member_id }}">

                            <input type="hidden" name="event_amount" value="{{ $pendingTransaction->amount }}">

                            <input type="hidden" name="event_date"
                                value="{{ optional($pendingTransaction->transaction_date)->format('Y-m-d') }}">


                            <div class="rounded-2xl
                                                                border border-blue-100
                                                                bg-blue-50/40
                                                                p-5">

                                <div class="flex items-center gap-2 mb-5">

                                    <i class="fa-solid fa-calendar-check
                                                                      text-blue-600"></i>

                                    <h4 class="font-bold text-gray-900">
                                        Event Details
                                    </h4>

                                </div>


                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">


                                    <div>

                                        <label class="block mb-2
                                                                              text-sm font-semibold">

                                            Event Type
                                            <span class="text-red-500">*</span>

                                        </label>

                                        <select name="type" class="w-full rounded-xl
                                                                           border-gray-300" required>

                                            <option value="">
                                                Select event type
                                            </option>

                                            <option value="msiba">
                                                Msiba
                                            </option>

                                            <option value="sherehe">
                                                Sherehe
                                            </option>

                                        </select>

                                    </div>


                                    <div>

                                        <label class="block mb-2
                                                                              text-sm font-semibold">

                                            Contribution per Member
                                            <span class="text-red-500">*</span>

                                        </label>

                                        <input type="number" name="contribution_per_member" value="10000" min="0" step="0.01" class="w-full rounded-xl
                                                                           border-gray-300" required>

                                    </div>


                                    <div>

                                        <label class="block mb-2
                                                                              text-sm font-semibold">

                                            Beneficiary

                                        </label>

                                        <input type="text" value="{{ $pendingTransaction->member?->full_name
                        ?? $pendingTransaction->recipient_name }}" class="w-full rounded-xl
                                                                           border-gray-200
                                                                           bg-gray-100
                                                                           text-gray-700" readonly>

                                    </div>


                                    <div>

                                        <label class="block mb-2
                                                                              text-sm font-semibold">

                                            Amount Given

                                        </label>

                                        <input type="text" value="TSH {{ number_format($pendingTransaction->amount) }}" class="w-full rounded-xl
                                                                           border-gray-200
                                                                           bg-gray-100
                                                                           text-gray-700" readonly>

                                    </div>


                                    <div class="md:col-span-2">

                                        <label class="block mb-2
                                                                              text-sm font-semibold">

                                            Event Date

                                        </label>

                                        <input type="text"
                                            value="{{ optional($pendingTransaction->transaction_date)->format('d/m/Y') }}" class="w-full rounded-xl
                                                                           border-gray-200
                                                                           bg-gray-100
                                                                           text-gray-700" readonly>

                                    </div>

                                </div>

                            </div>


                            <div class="mt-5
                                                                flex flex-col-reverse
                                                                sm:flex-row
                                                                sm:justify-end
                                                                gap-3">

                                <button type="button" onclick="closeBagambakamoModal(
                                                                'pendingTransactionModal{{ $pendingTransaction->id }}'
                                                            )" class="px-5 py-2.5
                                                                   rounded-xl
                                                                   bg-gray-100
                                                                   text-gray-700
                                                                   font-semibold
                                                                   hover:bg-gray-200">

                                    Review Later

                                </button>

                                <button type="submit" class="px-5 py-2.5
                                                                   rounded-xl
                                                                   bg-blue-600
                                                                   text-white
                                                                   font-semibold
                                                                   hover:bg-blue-700">

                                    <i class="fa-solid fa-floppy-disk mr-2"></i>

                                    Save Event

                                </button>

                            </div>

                        </form>

                    </div>


                    {{-- ===================================================== --}}
                    {{-- EXPENDITURE FORM --}}
                    {{-- ===================================================== --}}

                    <div id="pendingExpenseForm{{ $pendingTransaction->id }}" class="hidden px-6 py-6">

                        <form method="POST" action="{{ route(
                        'bagambakamo.pending-transactions.expense',
                        $pendingTransaction
                    ) }}">

                            @csrf

                            <div class="rounded-2xl
                                                                border border-purple-100
                                                                bg-purple-50/40
                                                                p-5">

                                <div class="flex items-center gap-2 mb-5">

                                    <i class="fa-solid fa-receipt
                                                                      text-purple-600"></i>

                                    <h4 class="font-bold text-gray-900">
                                        Expenditure Details
                                    </h4>

                                </div>


                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">


                                    <div>

                                        <label class="block mb-2
                                                                              text-sm font-semibold">

                                            Expense Category
                                            <span class="text-red-500">*</span>

                                        </label>

                                        <input type="text" name="category" class="w-full rounded-xl
                                                                           border-gray-300"
                                            placeholder="e.g. Transport, Food, Office" required>

                                    </div>


                                    <div>

                                        <label class="block mb-2
                                                                              text-sm font-semibold">

                                            Beneficiary

                                        </label>

                                        <input type="text" value="{{ $pendingTransaction->member?->full_name
                        ?? $pendingTransaction->recipient_name }}" class="w-full rounded-xl
                                                                           border-gray-200
                                                                           bg-gray-100
                                                                           text-gray-700" readonly>

                                    </div>


                                    <div>

                                        <label class="block mb-2
                                                                              text-sm font-semibold">

                                            Amount

                                        </label>

                                        <input type="text" value="TSH {{ number_format($pendingTransaction->amount) }}" class="w-full rounded-xl
                                                                           border-gray-200
                                                                           bg-gray-100
                                                                           text-gray-700" readonly>

                                    </div>


                                    <div>

                                        <label class="block mb-2
                                                                              text-sm font-semibold">

                                            Expense Date

                                        </label>

                                        <input type="text"
                                            value="{{ optional($pendingTransaction->transaction_date)->format('d/m/Y') }}" class="w-full rounded-xl
                                                                           border-gray-200
                                                                           bg-gray-100
                                                                           text-gray-700" readonly>

                                    </div>


                                    <div class="md:col-span-2">

                                        <label class="block mb-2
                                                                              text-sm font-semibold">

                                            Description

                                        </label>

                                        <input type="text" name="description" class="w-full rounded-xl
                                                                           border-gray-300" placeholder="Describe the expenditure">

                                    </div>


                                    <div class="md:col-span-2">

                                        <label class="block mb-2
                                                                              text-sm font-semibold">

                                            Notes

                                        </label>

                                        <textarea name="notes" rows="3" class="w-full rounded-xl
                                                                           border-gray-300"
                                            placeholder="Additional notes (optional)"></textarea>

                                    </div>

                                </div>

                            </div>


                            <div class="mt-5
                                                                flex flex-col-reverse
                                                                sm:flex-row
                                                                sm:justify-end
                                                                gap-3">

                                <button type="button" onclick="closeBagambakamoModal(
                                                                'pendingTransactionModal{{ $pendingTransaction->id }}'
                                                            )" class="px-5 py-2.5
                                                                   rounded-xl
                                                                   bg-gray-100
                                                                   text-gray-700
                                                                   font-semibold
                                                                   hover:bg-gray-200">

                                    Review Later

                                </button>

                                <button type="submit" class="px-5 py-2.5
                                                                   rounded-xl
                                                                   bg-purple-600
                                                                   text-white
                                                                   font-semibold
                                                                   hover:bg-purple-700">

                                    <i class="fa-solid fa-floppy-disk mr-2"></i>

                                    Save Expenditure

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        @endforeach

    @endif



    {{-- ============================================================= --}}
    {{-- CHART + MODAL JS --}}
    {{-- ============================================================= --}}

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>

        /*
        |--------------------------------------------------------------------------
        | OPEN MODAL
        |--------------------------------------------------------------------------
        */

        function openBagambakamoModal(id) {

            const modal =
                document.getElementById(id);

            if (!modal) {
                return;
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            document.body.classList.add('overflow-hidden');
        }


        /*
        |--------------------------------------------------------------------------
        | CLOSE MODAL
        |--------------------------------------------------------------------------
        */

        function closeBagambakamoModal(id) {

            const modal =
                document.getElementById(id);

            if (!modal) {
                return;
            }

            modal.classList.remove('flex');
            modal.classList.add('hidden');

            document.body.classList.remove('overflow-hidden');
        }


        /*
        |--------------------------------------------------------------------------
        | PENDING TRANSACTION - EVENT FORM
        |--------------------------------------------------------------------------
        */

        function showPendingEventForm(id) {

            const eventForm =
                document.getElementById(
                    'pendingEventForm' + id
                );

            const expenseForm =
                document.getElementById(
                    'pendingExpenseForm' + id
                );

            const eventButton =
                document.getElementById(
                    'pendingEventButton' + id
                );

            const expenseButton =
                document.getElementById(
                    'pendingExpenseButton' + id
                );


            if (eventForm) {
                eventForm.classList.remove('hidden');
            }

            if (expenseForm) {
                expenseForm.classList.add('hidden');
            }


            if (eventButton) {

                eventButton.classList.remove(
                    'border-gray-300',
                    'bg-white',
                    'text-gray-700'
                );

                eventButton.classList.add(
                    'border-blue-500',
                    'bg-blue-50',
                    'text-blue-700'
                );
            }


            if (expenseButton) {

                expenseButton.classList.remove(
                    'border-purple-500',
                    'bg-purple-50',
                    'text-purple-700'
                );

                expenseButton.classList.add(
                    'border-gray-300',
                    'bg-white',
                    'text-gray-700'
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | PENDING TRANSACTION - EXPENDITURE FORM
        |--------------------------------------------------------------------------
        */

        function showPendingExpenseForm(id) {

            const eventForm =
                document.getElementById(
                    'pendingEventForm' + id
                );

            const expenseForm =
                document.getElementById(
                    'pendingExpenseForm' + id
                );

            const eventButton =
                document.getElementById(
                    'pendingEventButton' + id
                );

            const expenseButton =
                document.getElementById(
                    'pendingExpenseButton' + id
                );


            if (eventForm) {
                eventForm.classList.add('hidden');
            }

            if (expenseForm) {
                expenseForm.classList.remove('hidden');
            }


            if (expenseButton) {

                expenseButton.classList.remove(
                    'border-gray-300',
                    'bg-white',
                    'text-gray-700'
                );

                expenseButton.classList.add(
                    'border-purple-500',
                    'bg-purple-50',
                    'text-purple-700'
                );
            }


            if (eventButton) {

                eventButton.classList.remove(
                    'border-blue-500',
                    'bg-blue-50',
                    'text-blue-700'
                );

                eventButton.classList.add(
                    'border-gray-300',
                    'bg-white',
                    'text-gray-700'
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | CLOSE STANDARD MODALS WHEN CLICKING BACKDROP
        |--------------------------------------------------------------------------
        */

        document.querySelectorAll(
            '#memberModal, #paymentModal, #eventModal'
        ).forEach(function (modal) {

            modal.addEventListener(
                'click',
                function (event) {

                    if (event.target === modal) {

                        closeBagambakamoModal(
                            modal.id
                        );
                    }
                }
            );

        });


        /*
        |--------------------------------------------------------------------------
        | AUTO OPEN FIRST PENDING TRANSACTION
        |--------------------------------------------------------------------------
        |
        | Every time the admin opens Bagambakamo, if an outgoing M-Koba
        | transaction is waiting, the oldest pending transaction opens.
        |
        */

        document.addEventListener(
            'DOMContentLoaded',
            function () {

                @if(
                        isset($pendingTransactions)
                        &&
                        $pendingTransactions->isNotEmpty()
                    )

                    openBagambakamoModal(
                        'pendingTransactionModal{{ $pendingTransactions->first()->id }}'
                    );

                @endif

                }
        );


        /*
        |--------------------------------------------------------------------------
        | CHART
        |--------------------------------------------------------------------------
        */

        const chartCanvas =
            document.getElementById(
                'bagambakamoChart'
            );


        if (chartCanvas) {

            new Chart(
                chartCanvas,
                {
                    type: 'line',

                    data: {

                        labels: [
                            'Jan',
                            'Feb',
                            'Mar',
                            'Apr',
                            'May',
                            'Jun',
                            'Jul',
                            'Aug',
                            'Sep',
                            'Oct',
                            'Nov',
                            'Dec'
                        ],

                        datasets: [

                            {
                                label: 'Payments',
                                data: @json($monthlyPaymentChart),
                                borderColor: '#16a34a',
                                backgroundColor: 'rgba(22,163,74,0.10)',
                                borderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                tension: 0.35,
                                fill: true
                            },

                            {
                                label: 'Events',
                                data: @json($monthlyEventChart),
                                borderColor: '#dc2626',
                                backgroundColor: 'rgba(220,38,38,0.08)',
                                borderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                tension: 0.35,
                                fill: true
                            }

                        ]

                    },

                    options: {

                        responsive: true,

                        maintainAspectRatio: false,

                        interaction: {
                            mode: 'index',
                            intersect: false
                        },

                        plugins: {

                            legend: {
                                position: 'top'
                            }

                        },

                        scales: {

                            y: {

                                beginAtZero: true,

                                ticks: {

                                    callback: function (value) {

                                        return new Intl.NumberFormat(
                                            'en-US'
                                        ).format(value);
                                    }

                                }

                            }

                        }

                    }

                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | SUCCESS ALERT AUTO REMOVE
        |--------------------------------------------------------------------------
        */

        setTimeout(
            function () {

                const alert =
                    document.getElementById(
                        'successAlert'
                    );

                if (alert) {
                    alert.remove();
                }

            },
            4000
        );

    </script>

@endsection