@extends('layouts.admin')

@section('title')
    <div class="flex items-center gap-3">

        <div class="w-11 h-11 rounded-xl
                    bg-blue-100 text-blue-600
                    flex items-center justify-center
                    text-xl">

            <i class="fa-solid fa-users"></i>

        </div>

        <div>

            <div class="text-2xl font-bold text-gray-900 leading-tight">
                Bagambakamo Members
            </div>

            <div class="text-sm font-normal text-gray-500 mt-1">
                Member payments, balances and contribution records
            </div>

        </div>

    </div>
@endsection


@section('content')

    @php
        $membersCount = $members->count();

        $totalPaid = $members->sum(function ($member) {
            return $member->total_paid;
        });

        $totalBalance = $members->sum(function ($member) {
            return $member->balance_amount;
        });
    @endphp


    <div class="space-y-5">

        {{-- ========================================================= --}}
        {{-- ALERTS --}}
        {{-- ========================================================= --}}

        @if(session('success'))

            <div id="successAlert" class="rounded-xl
                            border border-green-200
                            bg-green-50
                            px-4 py-3
                            text-green-700">

                <i class="fa-solid fa-circle-check mr-2"></i>

                {{ session('success') }}

            </div>

        @endif


        @if(session('error'))

            <div class="rounded-xl
                            border border-red-200
                            bg-red-50
                            px-4 py-3
                            text-red-700">

                <i class="fa-solid fa-circle-exclamation mr-2"></i>

                {{ session('error') }}

            </div>

        @endif


        {{-- ========================================================= --}}
        {{-- SUMMARY CARDS --}}
        {{-- ========================================================= --}}

        <div class="grid grid-cols-1
                    md:grid-cols-3
                    gap-4">

            {{-- MEMBERS --}}
            <div class="rounded-2xl
                        border border-blue-100
                        bg-blue-50
                        p-5">

                <div class="flex items-center gap-4">

                    <div class="w-14 h-14
                                rounded-2xl
                                bg-blue-100
                                text-blue-600
                                flex items-center
                                justify-center
                                text-2xl">

                        <i class="fa-solid fa-users"></i>

                    </div>

                    <div>

                        <p class="text-gray-600">
                            Members
                        </p>

                        <h3 class="text-3xl
                                   font-bold
                                   text-gray-900">

                            {{ number_format($membersCount) }}

                        </h3>

                        <p class="text-sm
                                  text-blue-600
                                  mt-2">

                            Active members in Bagambakamo

                        </p>

                    </div>

                </div>

            </div>


            {{-- TOTAL PAID --}}
            <div class="rounded-2xl
                        border border-green-100
                        bg-green-50
                        p-5">

                <div class="flex items-center gap-4">

                    <div class="w-14 h-14
                                rounded-2xl
                                bg-green-100
                                text-green-600
                                flex items-center
                                justify-center
                                text-2xl">

                        <i class="fa-solid fa-money-bill-wave"></i>

                    </div>

                    <div>

                        <p class="text-gray-600">
                            Total Paid
                        </p>

                        <h3 class="text-2xl
                                   font-bold
                                   text-gray-900">

                            TSH {{ number_format($totalPaid) }}

                        </h3>

                        <p class="text-sm
                                  text-green-600
                                  mt-2">

                            All member payments received

                        </p>

                    </div>

                </div>

            </div>


            {{-- TOTAL BALANCE --}}
            <div class="rounded-2xl
                        border border-red-100
                        bg-red-50
                        p-5">

                <div class="flex items-center gap-4">

                    <div class="w-14 h-14
                                rounded-2xl
                                bg-red-100
                                text-red-600
                                flex items-center
                                justify-center
                                text-2xl">

                        <i class="fa-solid fa-triangle-exclamation"></i>

                    </div>

                    <div>

                        <p class="text-gray-600">
                            Total Balance
                        </p>

                        <h3 class="text-2xl
                                   font-bold
                                   text-gray-900">

                            TSH {{ number_format($totalBalance) }}

                        </h3>

                        <p class="text-sm
                                  text-red-600
                                  mt-2">

                            Outstanding members balance

                        </p>

                    </div>

                </div>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- SEARCH + ACTIONS --}}
        {{-- ========================================================= --}}

        <div class="bg-white
                    rounded-2xl
                    border border-gray-200
                    shadow-sm
                    p-4">

            <div class="flex flex-col
                        xl:flex-row
                        xl:items-center
                        xl:justify-between
                        gap-4">

                {{-- SEARCH --}}
                <div class="relative
                            w-full
                            xl:max-w-xl">

                    <div class="absolute
                                inset-y-0 left-0
                                pl-4
                                flex items-center
                                pointer-events-none
                                text-gray-400">

                        <i class="fa-solid fa-magnifying-glass"></i>

                    </div>

                    <input id="memberSearch" type="text" placeholder="Search member by name or phone..." class="w-full
                               rounded-xl
                               border-gray-300
                               pl-11 pr-4 py-3
                               focus:border-blue-500
                               focus:ring-blue-500">

                </div>


                {{-- ACTION BUTTONS --}}
                <div class="flex
                            flex-wrap
                            gap-3">

                    <form method="POST" action="{{ route('bagambakamo.sms.debtors') }}">

                        @csrf

                        <button type="submit" onclick="return confirm('Tuma SMS kwa wadaiwa?')" class="inline-flex
                                   items-center
                                   gap-2
                                   px-5 py-3
                                   rounded-xl
                                   bg-yellow-500
                                   text-gray-900
                                   font-semibold
                                   hover:bg-yellow-600
                                   transition">

                            <i class="fa-solid fa-envelope"></i>

                            Notify Debtors

                        </button>

                    </form>


                    <a href="{{ route('bagambakamo.report.pdf') }}" target="_blank" class="inline-flex
                               items-center
                               gap-2
                               px-5 py-3
                               rounded-xl
                               bg-red-600
                               text-white
                               font-semibold
                               hover:bg-red-700
                               transition">

                        <i class="fa-solid fa-file-pdf"></i>

                        PDF Report

                    </a>

                </div>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- MEMBERS LIST --}}
        {{-- ========================================================= --}}

        <div class="bg-white
                    rounded-2xl
                    border border-gray-200
                    shadow-sm
                    overflow-hidden">

            {{-- TABLE HEADER --}}
            <div class="px-5 py-4
                        border-b
                        flex flex-col
                        md:flex-row
                        md:items-center
                        md:justify-between
                        gap-3">

                <div>

                    <h3 class="text-lg
                               font-bold
                               text-gray-900">

                        <i class="fa-solid
                                  fa-users
                                  mr-2"></i>

                        Members List

                    </h3>

                    <p class="text-sm
                              text-gray-500
                              mt-1">

                        Bagambakamo member financial records

                    </p>

                </div>


                <div id="membersCounter" class="text-sm text-gray-500">

                    Showing
                    <span id="visibleStart">1</span>–<span id="visibleEnd">10</span>
                    of
                    <span id="visibleTotal">{{ $membersCount }}</span>
                    members

                </div>

            </div>


            {{-- TABLE --}}
            <div class="overflow-x-auto">

                <table class="w-full text-sm">

                    <thead class="bg-gray-50">

                        <tr class="text-gray-600">

                            <th class="px-4 py-3 text-left">
                                #
                            </th>

                            <th class="px-4 py-3 text-left">
                                Member
                            </th>

                            <th class="px-4 py-3 text-left">
                                Phone
                            </th>

                            <th class="px-4 py-3 text-right">
                                Expected
                            </th>

                            <th class="px-4 py-3 text-right">
                                Events
                            </th>

                            <th class="px-4 py-3 text-right">
                                Total Paid
                            </th>

                            <th class="px-4 py-3 text-right">
                                Balance
                            </th>

                            <th class="px-4 py-3 text-center">
                                Status
                            </th>

                            <th class="px-4 py-3 text-center">
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody id="membersTableBody" class="divide-y divide-gray-100">

                        @foreach($members as $index => $member)

                                        <tr class="member-row
                                                       hover:bg-gray-50
                                                       transition" data-name="{{ strtolower($member->full_name) }}"
                                            data-phone="{{ strtolower($member->phone ?? '') }}" data-index="{{ $index }}">


                                            {{-- NUMBER --}}
                                            <td class="px-4 py-4">

                                                {{ $index + 1 }}

                                            </td>


                                            {{-- MEMBER --}}
                                            <td class="px-4 py-4">

                                                <div class="flex
                                                                items-center
                                                                gap-3">

                                                    <div class="w-9 h-9
                                                                    rounded-full
                                                                    bg-blue-100
                                                                    text-blue-600
                                                                    flex
                                                                    items-center
                                                                    justify-center
                                                                    font-bold">

                                                        {{
                            strtoupper(
                                substr(
                                    trim($member->full_name),
                                    0,
                                    1
                                )
                            )
                                                            }}

                                                    </div>

                                                    <span class="font-medium
                                                                     text-gray-900
                                                                     whitespace-nowrap">

                                                        {{ $member->full_name }}

                                                    </span>

                                                </div>

                                            </td>


                                            {{-- PHONE --}}
                                            <td class="px-4 py-4
                                                           whitespace-nowrap">

                                                @if($member->phone)

                                                    <i class="fa-solid
                                                                      fa-phone
                                                                      text-gray-400
                                                                      mr-2"></i>

                                                    {{ $member->phone }}

                                                @else

                                                    <span class="text-gray-400">
                                                        -
                                                    </span>

                                                @endif

                                            </td>


                                            {{-- EXPECTED --}}
                                            <td class="px-4 py-4
                                                           text-right
                                                           whitespace-nowrap">

                                                TSH
                                                {{ number_format($member->expected_amount) }}

                                            </td>


                                            {{-- EVENTS --}}
                                            <td class="px-4 py-4
                                                           text-right
                                                           whitespace-nowrap">

                                                TSH
                                                {{ number_format($member->total_events) }}

                                            </td>


                                            {{-- TOTAL PAID --}}
                                            <td class="px-4 py-4
                                                           text-right
                                                           whitespace-nowrap
                                                           font-bold
                                                           text-green-600">

                                                TSH
                                                {{ number_format($member->total_paid) }}

                                            </td>


                                            {{-- BALANCE --}}
                                            <td class="px-4 py-4
                                                           text-right
                                                           whitespace-nowrap
                                                           font-semibold
                                                           {{
                            $member->balance_amount > 0
                            ? 'text-red-600'
                            : 'text-gray-700'
                                                           }}">

                                                TSH
                                                {{ number_format($member->balance_amount) }}

                                            </td>


                                            {{-- STATUS --}}
                                            <td class="px-4 py-4
                                                           text-center">

                                                @if($member->balance_amount <= 0)

                                                    <span class="inline-flex
                                                                         items-center
                                                                         gap-1.5
                                                                         px-3 py-1
                                                                         rounded-full
                                                                         bg-green-100
                                                                         text-green-700
                                                                         font-medium">

                                                        <i class="fa-solid
                                                                          fa-circle-check"></i>

                                                        Paid

                                                    </span>

                                                @else

                                                    <span class="inline-flex
                                                                         items-center
                                                                         gap-1.5
                                                                         px-3 py-1
                                                                         rounded-full
                                                                         bg-red-100
                                                                         text-red-700
                                                                         font-medium">

                                                        <i class="fa-solid
                                                                          fa-triangle-exclamation"></i>

                                                        Owes

                                                    </span>

                                                @endif

                                            </td>


                                            {{-- ACTION --}}
                                            <td class="px-4 py-4">

                                                <div class="flex
                                                                items-center
                                                                justify-center
                                                                gap-2">

                                                    {{-- VIEW --}}
                                                    <button type="button" onclick="openMemberModal('memberModal{{ $member->id }}')" class="w-10 h-9
                                                                   rounded-lg
                                                                   bg-blue-100
                                                                   text-blue-600
                                                                   hover:bg-blue-600
                                                                   hover:text-white
                                                                   transition" title="View">

                                                        <i class="fa-solid fa-eye"></i>

                                                    </button>


                                                    {{-- DELETE --}}
                                                    <form method="POST" action="{{
                            route(
                                'bagambakamo.members.destroy',
                                $member->id
                            )
                                                            }}">

                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="submit"
                                                            onclick="return confirm('Una uhakika unataka kumfuta {{ $member->full_name }}?')"
                                                            class="w-10 h-9
                                                                       rounded-lg
                                                                       bg-red-100
                                                                       text-red-600
                                                                       hover:bg-red-600
                                                                       hover:text-white
                                                                       transition" title="Delete">

                                                            <i class="fa-solid fa-trash"></i>

                                                        </button>

                                                    </form>

                                                </div>

                                            </td>

                                        </tr>

                        @endforeach


                        {{-- EMPTY SEARCH ROW --}}
                        <tr id="noSearchResults" class="hidden">

                            <td colspan="9" class="px-4 py-16
                                       text-center
                                       text-gray-400">

                                <i class="fa-solid
                                          fa-magnifying-glass
                                          text-4xl
                                          mb-3
                                          block"></i>

                                No matching member found.

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>


            {{-- ===================================================== --}}
            {{-- FOOTER / PAGINATION --}}
            {{-- ===================================================== --}}

            <div class="px-5 py-4
                        border-t
                        flex flex-col
                        md:flex-row
                        md:items-center
                        md:justify-between
                        gap-4">


                {{-- PER PAGE --}}
                <div class="flex
                            items-center
                            gap-2
                            text-sm
                            text-gray-600">

                    <span>
                        Show
                    </span>

                    <select id="membersPerPage" class="rounded-lg
                               border-gray-300
                               py-2
                               pr-8">

                        <option value="10" selected>
                            10
                        </option>

                        <option value="20">
                            20
                        </option>

                        <option value="50">
                            50
                        </option>

                        <option value="100">
                            100
                        </option>

                    </select>

                    <span>
                        entries
                    </span>

                </div>


                {{-- PAGE BUTTONS --}}
                <div class="flex
                            items-center
                            gap-2">

                    <button id="previousPage" type="button" class="w-10 h-10
                               rounded-lg
                               border
                               border-gray-300
                               text-gray-600
                               hover:bg-gray-50">

                        <i class="fa-solid
                                  fa-chevron-left"></i>

                    </button>


                    <div id="pageNumbers" class="flex
                               items-center
                               gap-2">
                    </div>


                    <button id="nextPage" type="button" class="w-10 h-10
                               rounded-lg
                               border
                               border-gray-300
                               text-gray-600
                               hover:bg-gray-50">

                        <i class="fa-solid
                                  fa-chevron-right"></i>

                    </button>

                </div>

            </div>

        </div>

    </div>



    {{-- ============================================================= --}}
    {{-- MEMBER DETAIL MODALS --}}
    {{-- ============================================================= --}}

    @foreach($members as $member)

        <div id="memberModal{{ $member->id }}" class="hidden
                   fixed inset-0
                   z-[100]
                   bg-black/50
                   p-4
                   items-center
                   justify-center">

            <div class="bg-white
                        rounded-2xl
                        shadow-2xl
                        w-full
                        max-w-5xl
                        max-h-[90vh]
                        overflow-y-auto">


                {{-- HEADER --}}
                <div class="sticky top-0
                            z-10
                            bg-white
                            border-b
                            px-6 py-4
                            flex
                            items-center
                            justify-between">

                    <div class="flex
                                items-center
                                gap-3">

                        <div class="w-12 h-12
                                    rounded-full
                                    bg-blue-100
                                    text-blue-600
                                    flex
                                    items-center
                                    justify-center
                                    font-bold
                                    text-lg">

                            {{
                strtoupper(
                    substr(
                        trim($member->full_name),
                        0,
                        1
                    )
                )
                            }}

                        </div>

                        <div>

                            <h3 class="text-xl
                                       font-bold
                                       text-gray-900">

                                {{ $member->full_name }}

                            </h3>

                            <p class="text-sm
                                      text-gray-500">

                                {{ $member->phone ?: 'No phone number' }}

                            </p>

                        </div>

                    </div>


                    <button type="button" onclick="closeMemberModal('memberModal{{ $member->id }}')" class="w-10 h-10
                               rounded-lg
                               text-gray-400
                               hover:text-gray-700
                               hover:bg-gray-100">

                        <i class="fa-solid
                                  fa-xmark
                                  text-xl"></i>

                    </button>

                </div>


                <div class="p-6
                            space-y-6">


                    {{-- SUMMARY --}}
                    <div class="grid
                                grid-cols-1
                                sm:grid-cols-2
                                xl:grid-cols-4
                                gap-4">

                        <div class="rounded-xl
                                    bg-blue-50
                                    border
                                    border-blue-100
                                    p-4">

                            <p class="text-sm
                                      text-gray-500">
                                Expected
                            </p>

                            <h4 class="text-lg
                                       font-bold
                                       mt-1">

                                TSH
                                {{ number_format($member->expected_amount) }}

                            </h4>

                        </div>


                        <div class="rounded-xl
                                    bg-purple-50
                                    border
                                    border-purple-100
                                    p-4">

                            <p class="text-sm
                                      text-gray-500">
                                Events
                            </p>

                            <h4 class="text-lg
                                       font-bold
                                       mt-1">

                                TSH
                                {{ number_format($member->total_events) }}

                            </h4>

                        </div>


                        <div class="rounded-xl
                                    bg-green-50
                                    border
                                    border-green-100
                                    p-4">

                            <p class="text-sm
                                      text-gray-500">
                                Total Paid
                            </p>

                            <h4 class="text-lg
                                       font-bold
                                       text-green-700
                                       mt-1">

                                TSH
                                {{ number_format($member->total_paid) }}

                            </h4>

                        </div>


                        <div class="rounded-xl
                                    {{
                $member->balance_amount > 0
                ? 'bg-red-50 border-red-100'
                : 'bg-green-50 border-green-100'
                                    }}
                                    border
                                    p-4">

                            <p class="text-sm
                                      text-gray-500">
                                Balance
                            </p>

                            <h4 class="text-lg
                                       font-bold
                                       mt-1
                                       {{
                $member->balance_amount > 0
                ? 'text-red-700'
                : 'text-green-700'
                                       }}">

                                TSH
                                {{ number_format($member->balance_amount) }}

                            </h4>

                        </div>

                    </div>


                    {{-- PAYMENT HISTORY --}}
                    <div class="rounded-2xl
                                border
                                border-gray-200
                                overflow-hidden">

                        <div class="bg-gray-50
                                    border-b
                                    px-5 py-4">

                            <h4 class="font-bold
                                       text-gray-900">

                                <i class="fa-solid
                                          fa-money-bill-wave
                                          text-green-600
                                          mr-2"></i>

                                Payment History

                            </h4>

                        </div>


                        <div class="overflow-x-auto">

                            <table class="w-full
                                          text-sm">

                                <thead>

                                    <tr class="text-gray-500">

                                        <th class="px-4 py-3
                                                   text-left">
                                            Date
                                        </th>

                                        <th class="px-4 py-3
                                                   text-left">
                                            Type
                                        </th>

                                        <th class="px-4 py-3
                                                   text-left">
                                            Description
                                        </th>

                                        <th class="px-4 py-3
                                                   text-right">
                                            Amount
                                        </th>

                                    </tr>

                                </thead>


                                <tbody class="divide-y
                                              divide-gray-100">

                                    @forelse(
                                                            $member->payments
                                                                ->sortByDesc('payment_date')
                                                            as $payment
                                                        )

                                                        <tr>

                                                            <td class="px-4 py-3
                                                                           whitespace-nowrap">

                                                                {{
                                        optional(
                                            $payment->payment_date
                                        )->format('d/m/Y')
                                                                    }}

                                                            </td>

                                                            <td class="px-4 py-3">

                                                                @if($payment->type === 'monthly')

                                                                    <span class="inline-flex
                                                                                         px-2 py-1
                                                                                         rounded-lg
                                                                                         bg-blue-100
                                                                                         text-blue-700
                                                                                         text-xs
                                                                                         font-semibold">

                                                                        Monthly

                                                                    </span>

                                                                @else

                                                                    <span class="inline-flex
                                                                                         px-2 py-1
                                                                                         rounded-lg
                                                                                         bg-purple-100
                                                                                         text-purple-700
                                                                                         text-xs
                                                                                         font-semibold">

                                                                        Mchango

                                                                    </span>

                                                                @endif

                                                            </td>

                                                            <td class="px-4 py-3">

                                                                {{ $payment->description ?: '-' }}

                                                            </td>

                                                            <td class="px-4 py-3
                                                                           text-right
                                                                           font-bold
                                                                           text-green-600">

                                                                TSH
                                                                {{ number_format($payment->amount) }}

                                                            </td>

                                                        </tr>

                                    @empty

                                        <tr>

                                            <td colspan="4" class="px-4 py-10
                                                           text-center
                                                           text-gray-400">

                                                No payments found.

                                            </td>

                                        </tr>

                                    @endforelse

                                </tbody>

                            </table>

                        </div>

                    </div>


                    {{-- EVENT HISTORY --}}
                    <div class="rounded-2xl
                                border
                                border-gray-200
                                overflow-hidden">

                        <div class="bg-gray-50
                                    border-b
                                    px-5 py-4">

                            <h4 class="font-bold
                                       text-gray-900">

                                <i class="fa-solid
                                          fa-calendar-days
                                          text-red-600
                                          mr-2"></i>

                                Events

                            </h4>

                        </div>


                        <div class="overflow-x-auto">

                            <table class="w-full
                                          text-sm">

                                <thead>

                                    <tr class="text-gray-500">

                                        <th class="px-4 py-3
                                                   text-left">
                                            Date
                                        </th>

                                        <th class="px-4 py-3
                                                   text-left">
                                            Type
                                        </th>

                                        <th class="px-4 py-3
                                                   text-right">
                                            Amount
                                        </th>

                                        <th class="px-4 py-3
                                                   text-right">
                                            Contribution / Member
                                        </th>

                                    </tr>

                                </thead>


                                <tbody class="divide-y
                                              divide-gray-100">

                                    @forelse(
                                                            $member->events
                                                                ->sortByDesc('event_date')
                                                            as $event
                                                        )

                                                        <tr>

                                                            <td class="px-4 py-3">

                                                                {{
                                        optional(
                                            $event->event_date
                                        )->format('d/m/Y')
                                                                    }}

                                                            </td>

                                                            <td class="px-4 py-3">

                                                                <span class="inline-flex
                                                                                 px-2 py-1
                                                                                 rounded-lg
                                                                                 bg-red-100
                                                                                 text-red-700
                                                                                 text-xs
                                                                                 font-semibold">

                                                                    {{ ucfirst($event->type) }}

                                                                </span>

                                                            </td>

                                                            <td class="px-4 py-3
                                                                           text-right
                                                                           font-bold">

                                                                TSH
                                                                {{ number_format($event->amount) }}

                                                            </td>

                                                            <td class="px-4 py-3
                                                                           text-right">

                                                                TSH
                                                                {{
                                        number_format(
                                            $event->contribution_per_member
                                        )
                                                                    }}

                                                            </td>

                                                        </tr>

                                    @empty

                                        <tr>

                                            <td colspan="4" class="px-4 py-10
                                                           text-center
                                                           text-gray-400">

                                                No events found.

                                            </td>

                                        </tr>

                                    @endforelse

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>


                {{-- FOOTER --}}
                <div class="px-6 py-4
                            border-t
                            flex justify-end">

                    <button type="button" onclick="closeMemberModal('memberModal{{ $member->id }}')" class="px-5 py-2.5
                               rounded-xl
                               bg-gray-800
                               text-white
                               hover:bg-gray-700">

                        Close

                    </button>

                </div>

            </div>

        </div>

    @endforeach



    <script>

        /*
        |--------------------------------------------------------------------------
        | MEMBERS TABLE
        |--------------------------------------------------------------------------
        */

        const memberRows =
            Array.from(
                document.querySelectorAll('.member-row')
            );

        const memberSearch =
            document.getElementById('memberSearch');

        const perPageSelect =
            document.getElementById('membersPerPage');

        const previousPage =
            document.getElementById('previousPage');

        const nextPage =
            document.getElementById('nextPage');

        const pageNumbers =
            document.getElementById('pageNumbers');

        const noSearchResults =
            document.getElementById('noSearchResults');

        let currentPage = 1;
        let perPage = 10;
        let filteredRows = [...memberRows];


        function renderMembersTable() {

            const total =
                filteredRows.length;

            const totalPages =
                Math.max(
                    1,
                    Math.ceil(total / perPage)
                );


            if (currentPage > totalPages) {
                currentPage = totalPages;
            }


            memberRows.forEach(function (row) {
                row.style.display = 'none';
            });


            const start =
                (currentPage - 1) * perPage;

            const end =
                Math.min(
                    start + perPage,
                    total
                );


            filteredRows
                .slice(start, end)
                .forEach(function (row) {

                    row.style.display = '';

                });


            /*
            | EMPTY SEARCH
            */

            if (noSearchResults) {

                if (total === 0) {

                    noSearchResults.classList.remove(
                        'hidden'
                    );

                } else {

                    noSearchResults.classList.add(
                        'hidden'
                    );

                }

            }


            /*
            | COUNTER
            */

            document.getElementById(
                'visibleStart'
            ).textContent =
                total === 0
                    ? 0
                    : start + 1;


            document.getElementById(
                'visibleEnd'
            ).textContent =
                end;


            document.getElementById(
                'visibleTotal'
            ).textContent =
                total;


            /*
            | PAGE NUMBERS
            */

            pageNumbers.innerHTML = '';


            for (
                let page = 1;
                page <= totalPages;
                page++
            ) {

                const button =
                    document.createElement('button');


                button.type = 'button';

                button.textContent = page;


                button.className =
                    page === currentPage
                        ? 'w-10 h-10 rounded-lg bg-blue-600 text-white font-semibold'
                        : 'w-10 h-10 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50';


                button.addEventListener(
                    'click',
                    function () {

                        currentPage = page;

                        renderMembersTable();

                    }
                );


                pageNumbers.appendChild(button);

            }


            previousPage.disabled =
                currentPage <= 1;

            nextPage.disabled =
                currentPage >= totalPages;


            previousPage.classList.toggle(
                'opacity-40',
                currentPage <= 1
            );


            nextPage.classList.toggle(
                'opacity-40',
                currentPage >= totalPages
            );

        }


        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        memberSearch.addEventListener(
            'input',
            function () {

                const keyword =
                    this.value
                        .toLowerCase()
                        .trim();


                filteredRows =
                    memberRows.filter(
                        function (row) {

                            const name =
                                row.dataset.name || '';

                            const phone =
                                row.dataset.phone || '';


                            return (
                                name.includes(keyword)
                                ||
                                phone.includes(keyword)
                            );

                        }
                    );


                currentPage = 1;

                renderMembersTable();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | ENTRIES PER PAGE
        |--------------------------------------------------------------------------
        */

        perPageSelect.addEventListener(
            'change',
            function () {

                perPage =
                    parseInt(
                        this.value,
                        10
                    );

                currentPage = 1;

                renderMembersTable();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | PREVIOUS / NEXT
        |--------------------------------------------------------------------------
        */

        previousPage.addEventListener(
            'click',
            function () {

                if (currentPage > 1) {

                    currentPage--;

                    renderMembersTable();

                }

            }
        );


        nextPage.addEventListener(
            'click',
            function () {

                const totalPages =
                    Math.max(
                        1,
                        Math.ceil(
                            filteredRows.length
                            /
                            perPage
                        )
                    );


                if (currentPage < totalPages) {

                    currentPage++;

                    renderMembersTable();

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | MEMBER DETAIL MODALS
        |--------------------------------------------------------------------------
        */

        function openMemberModal(id) {

            const modal =
                document.getElementById(id);

            if (!modal) {
                return;
            }

            modal.classList.remove(
                'hidden'
            );

            modal.classList.add(
                'flex'
            );

        }


        function closeMemberModal(id) {

            const modal =
                document.getElementById(id);

            if (!modal) {
                return;
            }

            modal.classList.remove(
                'flex'
            );

            modal.classList.add(
                'hidden'
            );

        }


        document
            .querySelectorAll(
                '[id^="memberModal"]'
            )
            .forEach(
                function (modal) {

                    modal.addEventListener(
                        'click',
                        function (event) {

                            if (
                                event.target
                                === modal
                            ) {

                                modal.classList.remove(
                                    'flex'
                                );

                                modal.classList.add(
                                    'hidden'
                                );

                            }

                        }
                    );

                }
            );


        /*
        |--------------------------------------------------------------------------
        | INITIAL RENDER
        |--------------------------------------------------------------------------
        */

        renderMembersTable();


        /*
        |--------------------------------------------------------------------------
        | SUCCESS ALERT
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