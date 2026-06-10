<x-app-layout>

    {{-- =========================================
    MAIN CONTAINER
    ========================================== --}}

    <div class="py-4 md:py-8 bg-gray-100 min-h-screen">

        <div class="max-w-7xl mx-auto sm:px-4 lg:px-8">

            {{-- =========================================
            SUCCESS MESSAGE
            ========================================== --}}

            @if(session('success'))

                <div class="mb-6 bg-green-100 border border-green-300 text-green-700 px-4 py-4 rounded-2xl text-sm">

                    {{ session('success') }}

                </div>

            @endif

            {{-- =========================================
            ERROR MESSAGE
            ========================================== --}}

            @if(session('error'))

                <div class="mb-6 bg-red-100 border border-red-300 text-red-700 px-4 py-4 rounded-2xl text-sm">

                    {{ session('error') }}

                </div>

            @endif

            {{-- =========================================
            DAILY SUMMARY CARD
            ========================================== --}}

            {{-- =========================================
            TODAY REPORT BAR
            ========================================== --}}

            <div class="bg-white shadow-sm rounded-3xl px-5 py-4 mb-6 border border-gray-100">

                <div class="flex items-center justify-between">

                    {{-- LEFT BUTTON --}}

                    <button onclick="openTodayReport()"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-2xl text-[13px] font-semibold shadow-sm">

                        Today Report

                    </button>

                    {{-- RIGHT DATE --}}

                    <div class="bg-gray-100 text-gray-700 px-4 py-2 rounded-2xl text-[13px] font-semibold">

                        {{ \Carbon\Carbon::parse($entry->entry_date)->format('d M Y') }}

                    </div>

                </div>

            </div>

            {{-- =========================================
            ADD EXPENSE FORM
            ========================================== --}}

            <div class="bg-white shadow-sm rounded-3xl p-5 md:p-6 mb-6 border border-gray-100">

                {{-- FORM HEADER --}}

                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">

                    <h3 class="text-lg font-bold">

                        Add Expense

                    </h3>

                    {{-- DATE BADGE --}}

                    <div class="bg-blue-100 text-blue-700 px-4 py-2 rounded-2xl font-semibold w-fit text-[13px]">

                        Date:
                        {{ \Carbon\Carbon::parse($entry->entry_date)->format('d M Y') }}

                    </div>

                </div>

                {{-- FORM --}}

                <form method="POST" action="{{ route('expense.store') }}"
                    class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">

                    @csrf

                    {{-- HIDDEN FIELDS --}}

                    <input type="hidden" name="entry_id" value="{{ $entry->id }}">

                    <input type="hidden" name="entry_date"
                        value="{{ \Carbon\Carbon::parse($entry->entry_date)->format('Y-m-d') }}">

                    <input type="hidden" name="toilet_id" value="{{ $toilet->id }}">

                    {{-- EXPENSE NAME --}}

                    <div>

                        <label class="block mb-2 font-medium text-[13px]">

                            Expense Name

                        </label>

                        <input type="text" name="expense_name"
                            class="w-full border-gray-300 rounded-2xl h-11 text-[13px] px-4" required>

                    </div>

                    {{-- AMOUNT --}}

                    <div>

                        <label class="block mb-2 font-medium text-[13px]">

                            Amount

                        </label>

                        <input type="number" step="0.01" name="amount"
                            class="w-full border-gray-300 rounded-2xl h-11 text-[13px] px-4" required>

                    </div>

                    {{-- NOTE --}}

                    <div>

                        <label class="block mb-2 font-medium text-[13px]">

                            Note

                        </label>

                        <input type="text" name="note" class="w-full border-gray-300 rounded-2xl h-11 text-[13px] px-4">

                    </div>

                    {{-- SUBMIT BUTTON --}}

                    <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white h-11 rounded-2xl text-[13px] font-semibold shadow-md">

                        Save Expense

                    </button>

                </form>

            </div>

            {{-- =========================================
DAILY ENTRY TABLE
========================================== --}}

<div class="bg-white shadow-sm rounded-3xl overflow-hidden border border-gray-100 mb-6">

    {{-- TABLE HEADER --}}

    <div class="px-5 py-4 border-b border-gray-100">

        <h3 class="text-lg font-bold text-gray-800">

            Daily Summary

        </h3>

    </div>

    {{-- TABLE --}}

    <div class="overflow-x-auto">

        <table class="w-full min-w-[700px]">

            {{-- TABLE HEAD --}}

            <thead class="bg-gray-50">

                <tr>

                    <th class="px-5 py-4 text-left text-xs font-bold text-gray-500 uppercase">

                        Opening

                    </th>

                    <th class="px-5 py-4 text-left text-xs font-bold text-gray-500 uppercase">

                        Closing

                    </th>

                    <th class="px-5 py-4 text-left text-xs font-bold text-gray-500 uppercase">

                        Revenue

                    </th>

                    <th class="px-5 py-4 text-left text-xs font-bold text-gray-500 uppercase">

                        Expenses

                    </th>

                    <th class="px-5 py-4 text-center text-xs font-bold text-gray-500 uppercase">

                        Actions

                    </th>

                </tr>

            </thead>

            {{-- TABLE BODY --}}

            <tbody>

                <tr class="border-t border-gray-100 hover:bg-gray-50 transition">

                    {{-- OPENING --}}

                    <td class="px-5 py-5 text-sm font-semibold text-gray-700">

                        TZS {{ number_format($entry->opening_balance ?? 0) }}

                    </td>

                    {{-- CLOSING --}}

                    <td class="px-5 py-5 text-sm font-semibold text-blue-600">

                        TZS {{ number_format($entry->closing_balance ?? 0) }}

                    </td>

                    {{-- REVENUE --}}

                    <td class="px-5 py-5 text-sm font-semibold text-green-600">

                        TZS {{ number_format($entry->total_revenue ?? 0) }}

                    </td>

                    {{-- EXPENSES --}}

                    <td class="px-5 py-5 text-sm font-semibold text-red-500">

                        TZS {{ number_format($entry->total_expenses ?? 0) }}

                    </td>

                    {{-- ACTIONS --}}

                    <td class="px-5 py-5">

                        <div class="flex items-center justify-center">

                            <button
                                onclick="openDailyEditModal(
                                    {{ $entry->id }},
                                    '{{ \Carbon\Carbon::parse($entry->entry_date)->format('Y-m-d') }}',
                                    {{ $entry->opening_balance ?? 0 }},
                                    {{ $entry->pos_amount ?? 0 }},
                                    {{ $entry->closing_balance ?? 0 }}
                                )"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-xs font-semibold shadow-sm transition">

                                Edit

                            </button>

                        </div>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</div>


        </div>

    </div>


    {{-- =========================================
    EDIT MODAL
    ========================================== --}}

    <div id="editModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">

        <div class="bg-white w-full max-w-md rounded-3xl p-6">

            {{-- MODAL HEADER --}}

            <div class="flex items-center justify-between mb-6">

                <h3 class="text-xl font-bold">

                    Edit Expense

                </h3>

                <button onclick="closeEditModal()" class="text-gray-400 text-3xl leading-none">

                    &times;

                </button>

            </div>

            {{-- EDIT FORM --}}

            <form id="editForm" method="POST">

                @csrf
                @method('PUT')

                {{-- EXPENSE NAME --}}

                <div class="mb-4">

                    <label class="block mb-2 font-semibold">

                        Expense Name

                    </label>

                    <input type="text" id="editExpenseName" name="expense_name"
                        class="w-full border-gray-300 rounded-2xl h-12 px-4" required>

                </div>

                {{-- AMOUNT --}}

                <div class="mb-4">

                    <label class="block mb-2 font-semibold">

                        Amount

                    </label>

                    <input type="number" id="editAmount" name="amount"
                        class="w-full border-gray-300 rounded-2xl h-12 px-4" required>

                </div>

                {{-- NOTE --}}

                <div class="mb-6">

                    <label class="block mb-2 font-semibold">

                        Note

                    </label>

                    <input type="text" id="editNote" name="note" class="w-full border-gray-300 rounded-2xl h-12 px-4">

                </div>

                {{-- SUBMIT BUTTON --}}

                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white h-12 rounded-2xl font-semibold">

                    Update Expense

                </button>

            </form>

        </div>

    </div>

    {{-- =========================================
DAILY ENTRY EDIT MODAL
========================================== --}}

<div id="dailyEditModal"
    class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">

    <div class="bg-white w-full max-w-md rounded-3xl p-6">

        {{-- HEADER --}}

        <div class="flex items-center justify-between mb-6">

            <h3 class="text-xl font-bold">

                Edit Daily Entry

            </h3>

            <button onclick="closeDailyEditModal()"
                class="text-gray-400 hover:text-red-500 text-3xl leading-none">

                &times;

            </button>

        </div>

        {{-- FORM --}}

<form id="dailyEditForm" method="POST">

    @csrf
    @method('PUT')

    {{-- DATE --}}

    <div class="mb-4">

        <label class="block mb-2 font-semibold text-sm">

            Date

        </label>

        <input
            type="date"
            id="editEntryDate"
            name="entry_date"
            class="w-full border-gray-300 rounded-2xl h-12 px-4 text-sm"
            required>

    </div>

    {{-- OPENING --}}

    <div class="mb-4">

        <label class="block mb-2 font-semibold text-sm">

            Opening Balance

        </label>

        <input
            type="number"
            step="0.01"
            id="editOpeningBalance"
            name="opening_balance"
            class="w-full border-gray-300 rounded-2xl h-12 px-4 text-sm"
            required>

    </div>

    <div class="mb-4">

    <label class="block mb-2 font-semibold text-sm">
        POS Amount
    </label>

    <input
        type="number"
        step="0.01"
        id="editPosAmount"
        name="pos_amount"
        class="w-full border-gray-300 rounded-2xl h-12 px-4 text-sm">

</div>

    {{-- CLOSING --}}

    <div class="mb-6">

        <label class="block mb-2 font-semibold text-sm">

            Closing Balance

        </label>

        <input
            type="number"
            step="0.01"
            id="editClosingBalance"
            name="closing_balance"
            class="w-full border-gray-300 rounded-2xl h-12 px-4 text-sm"
            required>

    </div>

    {{-- SUBMIT --}}

    <button
        type="submit"
        class="w-full bg-blue-600 hover:bg-blue-700 text-white h-12 rounded-2xl font-semibold text-sm">

        Update Entry

    </button>

</form>

    </div>

</div>


    {{-- =========================================
    TODAY REPORT MODAL
    ========================================== --}}

    {{-- =========================================
    TODAY REPORT MODAL
    ========================================== --}}

    <div id="todayReportModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-end md:items-center justify-center z-50">

        <div
            class="bg-white w-full md:max-w-lg rounded-t-[35px] md:rounded-[35px] shadow-2xl overflow-hidden animate-fadeInUp">

            {{-- =========================================
            HEADER
            ========================================== --}}

            <div class="px-6 pt-6 pb-4">

                <div class="flex items-start justify-between">

                    <div>

                        <p class="text-sm text-gray-400 font-medium">

                            Daily Expense Report

                        </p>

                        <h2 class="text-2xl font-bold text-gray-800 mt-1">

                            Receipt
                        </h2>

                    </div>

                    {{-- CLOSE BUTTON --}}

                    <button onclick="closeTodayReport()" class="text-gray-400 hover:text-red-500 text-2xl leading-none">

                        ×

                    </button>

                </div>

            </div>

            {{-- =========================================
            RECEIPT BODY
            ========================================== --}}

            <div class="px-6 pb-6 overflow-y-auto max-h-[75vh]">

                {{-- DATE --}}

                <div class="mb-6">

                    <p class="text-sm text-gray-400">

                        {{ \Carbon\Carbon::parse($entry->entry_date)->format('d M Y') }}

                    </p>

                </div>

                {{-- =========================================
                EXPENSE ITEMS
                ========================================== --}}

                <div class="space-y-4">

                    @php
                        $counter = 1;
                    @endphp

                    @forelse($entry->expenses as $expense)

                                        <div>

                                            <div class="flex items-start justify-between gap-3">

                                                {{-- LEFT SIDE --}}

                                                <div class="flex gap-3 flex-1">

                                                    {{-- NUMBER --}}

                                                    <span class="text-gray-400 text-sm mt-0.5">

                                                        {{ $counter++ }}

                                                    </span>

                                                    {{-- DETAILS --}}

                                                    <div>

                                                        {{-- EXPENSE NAME --}}

                                                        <h4 class="font-semibold text-gray-700 text-sm">

                                                            {{ $expense->expense_name }}

                                                        </h4>

                                                        {{-- NOTE --}}

                                                        @if($expense->note)

                                                            <p class="text-xs text-gray-400 mt-1">

                                                                {{ $expense->note }}

                                                            </p>

                                                        @endif

                                                    </div>

                                                </div>

                                                {{-- RIGHT SIDE --}}

                                                <div class="text-right">

                                                    {{-- AMOUNT --}}

                                                    <div class="font-bold text-gray-700 text-sm">

                                                        TZS {{ number_format($expense->amount) }}

                                                    </div>

                                                    {{-- EDIT BUTTON --}}

                                                    <button onclick="openExpenseEditFromReport(
                            {{ $expense->id }},
                            '{{ $expense->expense_name }}',
                            {{ $expense->amount }},
                            '{{ $expense->note }}'
                        )" class="mt-2 text-blue-500 hover:text-blue-700 text-xs font-semibold">

                                                        Edit

                                                    </button>

                                                </div>

                                            </div>

                                            {{-- DASHED LINE --}}

                                            <div class="border-b border-dashed border-gray-200 mt-4">

                                            </div>

                                        </div>

                    @empty

                        {{-- EMPTY STATE --}}

                        <div class="text-center py-10">

                            <p class="text-gray-400 text-sm">

                                No expenses available

                            </p>

                        </div>

                    @endforelse

                </div>

                {{-- =========================================
                TOTAL SECTION
                ========================================== --}}

                <div class="mt-6">

                    {{-- REVENUE --}}

                    <div class="flex items-center justify-between mb-3">

                        <span class="font-medium text-gray-500 text-sm">

                            Revenue

                        </span>

                        <span class="font-bold text-green-600 text-sm">

                            TZS {{ number_format($entry->total_revenue) }}

                        </span>

                    </div>

                    {{-- EXPENSES --}}

                    <div class="flex items-center justify-between mb-3">

                        <span class="font-medium text-gray-500 text-sm">

                            Expenses

                        </span>

                        <span class="font-bold text-red-500 text-sm">

                            TZS {{ number_format($entry->total_expenses) }}

                        </span>

                    </div>

                    {{-- YELLOW LINE --}}

                    <div class="h-[2px] bg-yellow-400 rounded-full my-4"></div>

                    {{-- BALANCE --}}

                    <div class="flex items-center justify-between">

                        <span class="font-bold text-gray-800 text-lg">

                            TOTAL

                        </span>

                        <span class="font-bold text-blue-600 text-lg">

                            TZS {{ number_format($entry->closing_balance) }}

                        </span>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- =========================================
    JAVASCRIPT
    ========================================== --}}

<script>

    function openEditModal(id, name, amount, note) {

        const modal = document.getElementById('editModal');

        if (!modal) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.getElementById('editExpenseName').value = name;
        document.getElementById('editAmount').value = amount;
        document.getElementById('editNote').value = note ?? '';

        document.getElementById('editForm').action =
            '/expense/update/' + id;
    }

    function closeEditModal() {

        const modal = document.getElementById('editModal');

        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openTodayReport() {

        const modal = document.getElementById('todayReportModal');

        if (!modal) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeTodayReport() {

        const modal = document.getElementById('todayReportModal');

        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openExpenseEditFromReport(
        id,
        name,
        amount,
        note
    ) {

        closeTodayReport();

        setTimeout(() => {

            openEditModal(
                id,
                name,
                amount,
                note
            );

        }, 200);
    }

    /*
    |--------------------------------------------------------------------------
    | DAILY ENTRY EDIT MODAL
    |--------------------------------------------------------------------------
    */

function openDailyEditModal(
    id,
    date,
    opening,
    pos,
    closing
) {

    const modal =
        document.getElementById(
            'dailyEditModal'
        );

    if (!modal) return;

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    const dateField =
        document.getElementById(
            'editEntryDate'
        );

    if (dateField) {
        dateField.value = date;
    }

    const openingField =
        document.getElementById(
            'editOpeningBalance'
        );

    if (openingField) {
        openingField.value = opening;
    }

    const posField =
        document.getElementById(
            'editPosAmount'
        );

    if (posField) {
        posField.value = pos;
    }

    const closingField =
        document.getElementById(
            'editClosingBalance'
        );

    if (closingField) {
        closingField.value = closing;
    }

    document.getElementById(
        'dailyEditForm'
    ).action =
        '{{ url("/daily-entry/update") }}/' + id;
}

    function closeDailyEditModal() {

        const modal =
            document.getElementById(
                'dailyEditModal'
            );

        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

</script>

    {{-- =========================================
    PAGE STYLES
    ========================================== --}}

    <style>
        body {
            background: #f3f4f6;
        }

        input,
        textarea,
        button {
            -webkit-tap-highlight-color: transparent;
        }
    </style>

</x-app-layout>