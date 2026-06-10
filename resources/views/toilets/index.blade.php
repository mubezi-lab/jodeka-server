<x-app-layout>

    <div class="min-h-screen bg-gray-100 p-4 md:p-6">

        {{-- =========================================
            PAGE HEADER
        ========================================== --}}

        <div class="mb-8">

            <h1 class="text-3xl md:text-4xl font-bold text-gray-900">

                Toilets Dashboard

            </h1>

            <p class="text-gray-500 mt-2">

                Revenue, expenses and balance overview

            </p>

        </div>



        {{-- =========================================
            TODAY SUMMARY
        ========================================== --}}

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 mb-6 overflow-hidden">

            {{-- HEADER --}}

            <div class="px-6 py-5 border-b bg-gray-50">

                <h2 class="text-xl font-bold text-gray-800">

                    Today Summary

                </h2>

            </div>

            {{-- CONTENT --}}

            <div class="grid grid-cols-1 md:grid-cols-4">

                {{-- REVENUE --}}
                <div class="p-6 border-b md:border-b-0 md:border-r border-gray-100">

                    <p class="text-sm text-gray-500 mb-3">

                        Total Revenue

                    </p>

                    <h3 class="text-3xl font-bold text-green-600">

                        TZS {{ number_format($todayRevenue ?? 0) }}

                    </h3>

                    <div class="mt-4 space-y-2 text-sm">

                        <div class="flex items-center justify-between">

                            <span class="text-gray-500">
                                Sokoni
                            </span>

                            <span class="font-semibold text-green-600">
                                TZS {{ number_format($sokoniRevenue ?? 0) }}
                            </span>

                        </div>

                        <div class="flex items-center justify-between">

                            <span class="text-gray-500">
                                Stendi
                            </span>

                            <span class="font-semibold text-blue-600">
                                TZS {{ number_format($stendiRevenue ?? 0) }}
                            </span>

                        </div>

                    </div>

                </div>

                {{-- EXPENSES --}}
                <div class="p-6 border-b md:border-b-0 md:border-r border-gray-100">

                    <p class="text-sm text-gray-500 mb-3">

                        Total Expenses

                    </p>

                    <h3 class="text-3xl font-bold text-red-500">

                        TZS {{ number_format($todayExpenses ?? 0) }}

                    </h3>

                    <div class="mt-4 space-y-2 text-sm">

                        <div class="flex items-center justify-between">

                            <span class="text-gray-500">
                                Sokoni
                            </span>

                            <span class="font-semibold text-red-500">
                                TZS {{ number_format($sokoniExpenses ?? 0) }}
                            </span>

                        </div>

                        <div class="flex items-center justify-between">

                            <span class="text-gray-500">
                                Stendi
                            </span>

                            <span class="font-semibold text-red-500">
                                TZS {{ number_format($stendiExpenses ?? 0) }}
                            </span>

                        </div>

                    </div>

                </div>

                {{-- POS SUMMARY --}}
                <div class="p-6 border-b md:border-b-0 md:border-r border-gray-100">

                    <p class="text-sm text-gray-500 mb-3">
                        POS Summary
                    </p>

                    <h3 class="text-3xl font-bold text-orange-600">
                        TZS {{ number_format($sokoniPosAmount ?? 0) }}
                    </h3>

                    <div class="mt-4 space-y-2 text-sm">

                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">
                                POS Collections
                            </span>

                            <span class="font-semibold text-orange-600">
                                TZS {{ number_format($sokoniPosAmount ?? 0) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">
                                Your 60%
                            </span>

                            <span class="font-semibold text-green-600">
                                TZS {{ number_format($sokoniYourShare ?? 0) }}
                            </span>
                        </div>

                    </div>

                </div>

                {{-- TOTAL PROFIT --}}
                <div class="p-6 border-b md:border-b-0 md:border-r border-gray-100">

                    <p class="text-sm text-gray-500 mb-3">

                        Total Profit

                    </p>

                    <h3 class="text-3xl font-bold text-purple-600">

                        TZS {{ number_format($totalProfit ?? 0) }}

                    </h3>

                    <div class="mt-4 space-y-2 text-sm">

                        <div class="flex items-center justify-between">

                            <span class="text-gray-500">
                                Sokoni
                            </span>

                            <span class="font-semibold text-purple-600">
                                TZS {{ number_format($sokoniNet ?? 0) }}
                            </span>

                        </div>

                        <div class="flex items-center justify-between">

                            <span class="text-gray-500">
                                Stendi
                            </span>

                            <span class="font-semibold text-blue-600">
                                TZS {{ number_format($stendiNet ?? 0) }}
                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        {{-- =========================================
            WEEK SUMMARY
        ========================================== --}}

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 mb-6 overflow-hidden">

            {{-- HEADER --}}

            <div class="px-6 py-5 border-b bg-gray-50">

                <h2 class="text-xl font-bold text-gray-800">

                    Weekly Summary

                </h2>

            </div>

            {{-- CONTENT --}}

            <div class="grid grid-cols-1 md:grid-cols-3">

                {{-- REVENUE --}}
                <div class="p-6 border-b md:border-b-0 md:border-r border-gray-100">

                    <p class="text-sm text-gray-500 mb-3">

                        Revenue

                    </p>

                    <h3 class="text-3xl font-bold text-green-600">

                        TZS {{ number_format($weeklyRevenue ?? 0) }}

                    </h3>

                </div>

                {{-- EXPENSES --}}
                <div class="p-6 border-b md:border-b-0 md:border-r border-gray-100">

                    <p class="text-sm text-gray-500 mb-3">

                        Expenses

                    </p>

                    <h3 class="text-3xl font-bold text-red-500">

                        TZS {{ number_format($weeklyExpenses ?? 0) }}

                    </h3>

                </div>

                {{-- NET --}}
                <div class="p-6">

                    <p class="text-sm text-gray-500 mb-3">

                        Net Balance

                    </p>

                    <h3 class="text-3xl font-bold text-blue-600">

                        TZS {{ number_format($weeklyNet ?? 0) }}

                    </h3>

                </div>

            </div>

        </div>



        {{-- =========================================
            MONTH SUMMARY
        ========================================== --}}

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 mb-6 overflow-hidden">

            {{-- HEADER --}}

            <div class="px-6 py-5 border-b bg-gray-50">

                <h2 class="text-xl font-bold text-gray-800">

                    Monthly Summary

                </h2>

            </div>

            {{-- CONTENT --}}

            <div class="grid grid-cols-1 md:grid-cols-3">

                {{-- REVENUE --}}
                <div class="p-6 border-b md:border-b-0 md:border-r border-gray-100">

                    <p class="text-sm text-gray-500 mb-3">

                        Revenue

                    </p>

                    <h3 class="text-3xl font-bold text-green-600">

                        TZS {{ number_format($monthlyRevenue ?? 0) }}

                    </h3>

                </div>

                {{-- EXPENSES --}}
                <div class="p-6 border-b md:border-b-0 md:border-r border-gray-100">

                    <p class="text-sm text-gray-500 mb-3">

                        Expenses

                    </p>

                    <h3 class="text-3xl font-bold text-red-500">

                        TZS {{ number_format($monthlyExpenses ?? 0) }}

                    </h3>

                </div>

                {{-- NET --}}
                <div class="p-6">

                    <p class="text-sm text-gray-500 mb-3">

                        Net Balance

                    </p>

                    <h3 class="text-3xl font-bold text-blue-600">

                        TZS {{ number_format($monthlyNet ?? 0) }}

                    </h3>

                </div>

            </div>

        </div>



        {{-- =========================================
            RECENT REPORTS TABLE
        ========================================== --}}

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">

            {{-- HEADER --}}
            <div class="px-6 py-5 border-b">

                <h2 class="text-2xl font-bold text-gray-800">

                    Recent Reports

                </h2>

                <p class="text-sm text-gray-500 mt-1">

                    Latest toilet reports and balances

                </p>

            </div>

            {{-- TABLE --}}
            <div class="overflow-x-auto">

                <table class="w-full min-w-[900px]">

                    <thead class="bg-gray-50">

                        <tr>

                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                Date
                            </th>

                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                Toilet
                            </th>

                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                Revenue
                            </th>

                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                Expenses
                            </th>

                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                Balance
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($recentReports as $report)

                            <tr class="border-t hover:bg-gray-50">

                                <td class="px-6 py-5 text-sm">

                                    {{ \Carbon\Carbon::parse($report->entry_date)->format('d M Y') }}

                                </td>

                                <td class="px-6 py-5 font-semibold">

                                    {{ $report->toilet->name ?? 'N/A' }}

                                </td>

                                <td class="px-6 py-5 text-green-600 font-bold">

                                    TZS {{ number_format($report->total_revenue ?? 0) }}

                                </td>

                                <td class="px-6 py-5 text-red-500 font-bold">

                                    TZS {{ number_format($report->expenses->sum('amount') ?? 0) }}

                                </td>

                                <td class="px-6 py-5 text-blue-600 font-bold">

                                    TZS {{
                                        number_format(
                                            ($report->total_revenue ?? 0)
                                            -
                                            ($report->expenses->sum('amount') ?? 0)
                                        )
                                    }}

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5"
                                    class="px-6 py-10 text-center text-gray-400">

                                    No reports available

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</x-app-layout>