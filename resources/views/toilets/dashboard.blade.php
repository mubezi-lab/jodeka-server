<x-app-layout>

    {{-- =======================================================
        CHART JS
    ======================================================== --}}

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- =======================================================
        PAGE CONTAINER
    ======================================================== --}}

    <div class="py-4 md:py-6 bg-gray-100 min-h-screen">

        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">

            {{-- =======================================================
                SUCCESS MESSAGE
            ======================================================== --}}

            @if(session('success'))

                <div
                    class="mb-5 bg-green-100 text-green-700 px-4 py-3 rounded-2xl text-sm">

                    {{ session('success') }}

                </div>

            @endif

            {{-- =======================================================
                ERROR MESSAGE
            ======================================================== --}}

            @if(session('error'))

                <div
                    class="mb-5 bg-red-100 text-red-700 px-4 py-3 rounded-2xl text-sm">

                    {{ session('error') }}

                </div>

            @endif

            {{-- =======================================================
                TOP BUTTONS
            ======================================================== --}}

            <div class="flex gap-2 mb-5">

                {{-- ADD CASH ENTRY BUTTON --}}

                <button onclick="openCashModal()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-medium shadow-sm">

                    + Add Cash Entry

                </button>

                {{-- DAILY REPORTS BUTTON --}}

                <a href="{{
                    strtolower($toilet->name) === 'stendi'
                    ? route('stendi.reports')
                    : route('sokoni.reports')
                }}"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl text-sm font-medium text-center shadow-sm">

                    Daily Reports

                </a>

            </div>

            {{-- =======================================================
                SOKONI SUMMARY CARDS
            ======================================================== --}}

            @if(strtolower($toilet->name) === 'sokoni')

                <div class="mb-4">

                    <label class="block text-sm font-medium mb-2">
                        POS Amount
                    </label>

                    <input
                        type="number"
                        name="pos_amount"
                        step="0.01"
                        min="0"
                        value="0"
                        required
                        class="w-full border border-gray-300 rounded-2xl px-4 py-3 text-sm">

                </div>

            @endif

            {{-- =======================================================
                WEEKLY CHART
            ======================================================== --}}

                {{-- =========================================
    WEEKLY CHART CARD
========================================== --}}

<div
    class="bg-white shadow-sm rounded-[28px] p-4 md:p-6 mb-6 border border-gray-100">

    {{-- =========================================
        CHART HEADER
    ========================================== --}}

    <div class="flex items-center justify-between mb-4 md:mb-6">

        {{-- TITLE --}}

        <div>

            <h3
                class="text-[17px] md:text-xl font-bold text-gray-900 leading-tight">

                Weekly Revenue Chart

            </h3>

            <p class="text-[11px] md:text-sm text-gray-400 mt-1">

                Revenue • Expenses • Balance

            </p>

        </div>

        {{-- RANGE --}}

        <div
            class="bg-gray-100 text-gray-500 text-[11px] md:text-sm px-3 py-1.5 rounded-2xl font-medium">

            Last 7 Days

        </div>

    </div>

    {{-- =========================================
        CHART WRAPPER
    ========================================== --}}

    <div
        class="relative w-full h-[220px] md:h-[380px] rounded-2xl overflow-hidden">

        <canvas id="weeklyChart"></canvas>

    </div>

</div>

            {{-- =======================================================
                LAST 7 DAYS TABLE
            ======================================================== --}}

            <div class="bg-white shadow-sm rounded-3xl overflow-hidden">

                {{-- TABLE HEADER --}}

                <div class="px-4 md:px-6 py-4 border-b">

                    <h3 class="text-lg md:text-xl font-bold">

                        Last 7 Days

                    </h3>

                </div>

                {{-- TABLE WRAPPER --}}

                <div class="overflow-x-auto">

                    <table class="w-full min-w-[720px]">

                        {{-- TABLE HEAD --}}

                        <thead class="bg-gray-100">

                            <tr>

                                <th class="px-4 py-3 text-left text-sm whitespace-nowrap">

                                    Date

                                </th>

                                <th
                                    class="hidden md:table-cell px-4 py-3 text-left text-sm whitespace-nowrap">

                                    Opening

                                </th>

                                <th class="px-4 py-3 text-left text-sm whitespace-nowrap">

                                    Closing

                                </th>

                                <th class="px-4 py-3 text-left text-sm whitespace-nowrap">

                                    Expenses

                                </th>

                                <th class="px-4 py-3 text-left text-sm whitespace-nowrap">

                                    Revenue

                                </th>

                                <th
                                    class="hidden md:table-cell px-4 py-3 text-left text-sm whitespace-nowrap">

                                    Status

                                </th>

                                <th class="px-4 py-3 text-center text-sm whitespace-nowrap">

                                    Actions

                                </th>

                            </tr>

                        </thead>

                        {{-- TABLE BODY --}}

                        <tbody>

                            @forelse($entries->take(7) as $entry)

                                <tr class="border-t hover:bg-gray-50">

                                    {{-- DATE --}}

                                    <td class="px-4 py-4 text-sm whitespace-nowrap">

                                        {{ \Carbon\Carbon::parse($entry->entry_date)->format('d M Y') }}

                                    </td>

                                    {{-- OPENING --}}

                                    <td class="hidden md:table-cell px-4 py-4 text-sm whitespace-nowrap">

                                        TZS {{ number_format($entry->opening_balance) }}

                                    </td>

                                    {{-- CLOSING --}}

                                    <td class="px-4 py-4 text-sm whitespace-nowrap">

                                        TZS {{ number_format($entry->closing_balance) }}

                                    </td>

                                    {{-- EXPENSES --}}

                                    <td class="px-4 py-4 text-red-500 font-semibold text-sm whitespace-nowrap">

                                        TZS {{ number_format($entry->total_expenses) }}

                                    </td>

                                    {{-- REVENUE --}}

                                    <td class="px-4 py-4 text-green-600 font-bold text-sm whitespace-nowrap">

                                        TZS {{ number_format($entry->total_revenue) }}

                                    </td>

                                    {{-- STATUS --}}

                                    <td class="hidden md:table-cell px-4 py-4 whitespace-nowrap">

                                        @if($entry->is_closed)

                                            <span
                                                class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs">

                                                Closed

                                            </span>

                                        @else

                                            <span
                                                class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs">

                                                Open

                                            </span>

                                        @endif

                                    </td>

                                    {{-- ACTION BUTTON --}}

                                    <td class="px-4 py-4 text-center whitespace-nowrap">

                                        <a href="{{ url(
                                            strtolower($toilet->name) === 'stendi'
                                            ? '/stendi/expenses?entry_date=' . $entry->entry_date
                                            : '/sokoni/expenses?entry_date=' . $entry->entry_date
                                        ) }}"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-xs inline-block">

                                            Manage

                                        </a>

                                    </td>

                                </tr>

                            @empty

                                {{-- EMPTY STATE --}}

                                <tr>

                                    <td colspan="7"
                                        class="px-4 py-6 text-center text-gray-500 text-sm">

                                        No entries found.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

    {{-- =======================================================
        CASH ENTRY MODAL
    ======================================================== --}}

    <div id="cashEntryModal"
        class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">

        <div class="bg-white rounded-3xl w-full max-w-md p-6 relative">

            {{-- CLOSE BUTTON --}}

            <button onclick="closeCashModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-red-500">

                ✕

            </button>

            {{-- MODAL TITLE --}}

            <h2 class="text-xl font-bold mb-5">

                Add Cash Entry

            </h2>

            {{-- FORM --}}

            <form method="POST"
                action="{{
                    strtolower($toilet->name) === 'stendi'
                    ? route('stendi.entry.store')
                    : route('sokoni.entry.store')
                }}">

                @csrf

                {{-- DATE --}}

                <div class="mb-4">

                    <label class="block text-sm font-medium mb-2">

                        Date

                    </label>

                    <input type="date"
                        name="entry_date"
                        value="{{ now()->format('Y-m-d') }}"
                        required
                        class="w-full border border-gray-300 rounded-2xl px-4 py-3 text-sm">

                </div>

                {{-- OPENING BALANCE --}}

                @if(strtolower($toilet->name) === 'stendi')

                    <div class="mb-4">

                        <label class="block text-sm font-medium mb-2">

                            Opening Balance

                        </label>

                        <input type="number"
                            name="opening_balance"
                            required
                            class="w-full border border-gray-300 rounded-2xl px-4 py-3 text-sm">

                    </div>

                @endif

                {{-- POS AMOUNT (SOKONI ONLY) --}}

                @if(strtolower($toilet->name) === 'sokoni')

                    <div class="mb-4">

                        <label class="block text-sm font-medium mb-2">

                            POS Amount

                        </label>

                        <input
                            type="number"
                            name="pos_amount"
                            step="0.01"
                            min="0"
                            value="0"
                            required
                            class="w-full border border-gray-300 rounded-2xl px-4 py-3 text-sm">

                    </div>

                @endif

                {{-- CLOSING BALANCE --}}

                <div class="mb-5">

                    <label class="block text-sm font-medium mb-2">

                        Closing Balance

                    </label>

                    <input type="number"
                        name="closing_balance"
                        required
                        class="w-full border border-gray-300 rounded-2xl px-4 py-3 text-sm">

                </div>

                {{-- SUBMIT BUTTON --}}

                <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-2xl font-semibold">

                    Save Entry

                </button>

            </form>

        </div>

    </div>

    {{-- =======================================================
        CHART SCRIPT
    ======================================================== --}}

    <script>

        const weeklyChart = document.getElementById('weeklyChart');

        if (weeklyChart) {

            const isMobile = window.innerWidth < 768;

            new Chart(weeklyChart, {

                type: 'line',

                data: {

                    labels: [

                        @foreach($entries->take(7)->reverse() as $entry)
                            '{{ \Carbon\Carbon::parse($entry->entry_date)->format('D') }}',
                        @endforeach

                    ],

                    datasets: [

                        {
                            label: 'Revenue',

                            data: [
                                @foreach($entries->take(7)->reverse() as $entry)
                                    {{ $entry->total_revenue }},
                                @endforeach
                            ],

                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37,99,235,0.08)',
                            fill: false,
                            tension: 0.4,
                            borderWidth: 3,
                            pointRadius: isMobile ? 0 : 4
                        },

                        {
                            label: 'Expenses',

                            data: [
                                @foreach($entries->take(7)->reverse() as $entry)
                                    {{ $entry->total_expenses }},
                                @endforeach
                            ],

                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239,68,68,0.08)',
                            fill: false,
                            tension: 0.4,
                            borderWidth: 3,
                            pointRadius: isMobile ? 0 : 4
                        },

                        {
                            label: 'Balance',

                            data: [
                                @foreach($entries->take(7)->reverse() as $entry)
                                    {{ $entry->closing_balance }},
                                @endforeach
                            ],

                            borderColor: '#16a34a',
                            backgroundColor: 'rgba(22,163,74,0.08)',
                            fill: false,
                            tension: 0.4,
                            borderWidth: 3,
                            pointRadius: isMobile ? 0 : 4
                        }

                    ]
                },

                options: {

                    responsive: true,
                    maintainAspectRatio: false,

                    plugins: {

                        legend: {

                            display: true,

                            labels: {

                                boxWidth: 10,

                                font: {
                                    size: isMobile ? 10 : 12
                                }
                            }
                        }
                    },

                    scales: {

                        y: {

                            display: !isMobile,
                            beginAtZero: true
                        },

                        x: {

                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        {{-- OPEN MODAL --}}

        function openCashModal() {

            const modal = document.getElementById('cashEntryModal');

            modal.classList.remove('hidden');

            modal.classList.add('flex');
        }

        {{-- CLOSE MODAL --}}

        function closeCashModal() {

            const modal = document.getElementById('cashEntryModal');

            modal.classList.remove('flex');

            modal.classList.add('hidden');
        }

    </script>

    {{-- =======================================================
        LOGO SPIN ANIMATION
    ======================================================== --}}

    <style>

        @keyframes slowSpin {

            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .animate-spin-slow {

            animation: slowSpin 8s linear infinite;
        }

    </style>

</x-app-layout>