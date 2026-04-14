<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Livestock Report - {{ $livestock->name }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- SUCCESS --}}
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- 🔥 SUMMARY --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                <div class="bg-white p-4 rounded shadow">
                    <h4 class="text-sm text-gray-500">Stock</h4>
                    <p class="text-2xl font-bold text-blue-600">
                        {{ $livestock->quantity }}
                    </p>
                </div>

                <div class="bg-white p-4 rounded shadow">
                    <h4 class="text-sm text-gray-500">Age</h4>
                    <p class="text-2xl font-bold">
                        {{ $livestock->age_in_days }} days
                    </p>
                </div>

                <div class="bg-white p-4 rounded shadow">
                    <h4 class="text-sm text-gray-500">Week</h4>
                    <p class="text-2xl font-bold">
                        {{ $livestock->current_week }}
                    </p>
                </div>

                <div class="bg-white p-4 rounded shadow">
                    <h4 class="text-sm text-gray-500">Today's Feed</h4>
                    <p class="text-2xl font-bold text-green-600">
                        {{ number_format($livestock->getDailyFeed(now()), 2) }} kg
                    </p>
                </div>

            </div>

            {{-- 🔥 COST SUMMARY --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">

                <div class="bg-white p-4 rounded shadow">
                    <h4 class="text-sm text-gray-500">Total Cost</h4>
                    <p class="text-2xl font-bold text-red-600">
                        {{ number_format($livestock->totalCost(), 2) }} TZS
                    </p>
                </div>

                <div class="bg-white p-4 rounded shadow">
                    <h4 class="text-sm text-gray-500">Cost per Alive</h4>
                    <p class="text-xl font-bold text-blue-600">
                        {{ number_format($livestock->costPerAlive(), 2) }} TZS
                    </p>
                </div>

                <div class="bg-white p-4 rounded shadow">
                    <h4 class="text-sm text-gray-500">Cost Dead</h4>
                    <p class="text-xl font-bold text-red-600">
                        {{ number_format($livestock->costPerDead(), 2) }} TZS
                    </p>
                </div>

            </div>

            {{-- 🔥 COST BREAKDOWN --}}
            <div class="bg-white mt-6 p-6 rounded shadow">
                <h3 class="font-bold mb-4">Cost Breakdown</h3>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                    <div>
                        <p class="text-sm text-gray-500">Chicks</p>
                        <p class="font-bold">
                            {{ number_format($livestock->chicksCost(), 2) }} TZS
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Feed</p>
                        <p class="font-bold text-green-600">
                            {{ number_format($livestock->feedCost(), 2) }} TZS
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Medicine</p>
                        <p class="font-bold">
                            {{ number_format($livestock->medicineCost(), 2) }} TZS
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Vaccine</p>
                        <p class="font-bold">
                            {{ number_format($livestock->vaccineCost(), 2) }} TZS
                        </p>
                    </div>

                </div>
            </div>

            {{-- 🔥 MORTALITY --}}
            <div class="bg-white mt-6 p-6 rounded shadow">
                <h3 class="font-bold mb-4">Mortality</h3>

                <p>
                    Total Dead:
                    <span class="font-bold text-red-600">
                        {{ $livestock->totalMortality() }}
                    </span>
                </p>
            </div>

            {{-- 🔥 HISTORY TABLE --}}
            <div class="bg-white mt-6 p-6 rounded shadow">

                <h3 class="font-bold mb-4">History</h3>

                <table class="w-full border">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2">Date</th>
                            <th class="p-2">Type</th>
                            <th class="p-2">Category</th>
                            <th class="p-2">Qty</th>
                            <th class="p-2">Amount</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($livestock->logs as $log)
                            <tr class="border-t">
                                <td class="p-2">{{ $log->date }}</td>
                                <td class="p-2">{{ $log->type }}</td>
                                <td class="p-2">{{ $log->category ?? '-' }}</td>
                                <td class="p-2">{{ $log->quantity ?? '-' }}</td>
                                <td class="p-2">
                                    {{ $log->amount ? number_format($log->amount) : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-gray-500">
                                    No records found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>

            </div>

        </div>
    </div>
</x-app-layout>