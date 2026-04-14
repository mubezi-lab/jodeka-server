<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Livestock Management
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Success Message --}}
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            {{-- 🔥 TOP ACTIONS --}}
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">All Livestock Batches</h3>

                <div class="space-x-2">

                    {{-- Add Livestock --}}
                    <a href="{{ route('livestocks.create') }}"
                       class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        + Add Livestock
                    </a>

                    {{-- Add Expense --}}
                    <a href="{{ route('livestock-logs.create') }}"
                       class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        + Add Expense
                    </a>

                    {{-- Record Mortality --}}
                    <a href="{{ route('livestock-logs.create', ['type' => 'mortality']) }}"
                       class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                        + Mortality
                    </a>

                </div>
            </div>

            {{-- TABLE --}}
            <div class="bg-white shadow rounded-lg overflow-x-auto">
                <table class="min-w-full border border-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 text-left">#</th>
                            <th class="px-4 py-2 text-left">Name</th>
                            <th class="px-4 py-2 text-left">Type</th>
                            <th class="px-4 py-2 text-left">Category</th>
                            <th class="px-4 py-2 text-left">Stock</th>
                            <th class="px-4 py-2 text-left">Age</th>
                            <th class="px-4 py-2 text-left">Week</th>
                            <th class="px-4 py-2 text-left">Expected Feed (kg)</th>
                            <th class="px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($livestocks as $livestock)
                            <tr class="border-t hover:bg-gray-50">

                                <td class="px-4 py-2">{{ $loop->iteration }}</td>

                                <td class="px-4 py-2 font-semibold">
                                    {{ $livestock->name }}
                                </td>

                                <td class="px-4 py-2">
                                    {{ $livestock->type }}
                                </td>

                                <td class="px-4 py-2">
                                    <span class="px-2 py-1 bg-gray-200 rounded text-sm">
                                        {{ $livestock->category }}
                                    </span>
                                </td>

                                <td class="px-4 py-2 font-bold text-blue-600">
                                    {{ $livestock->quantity }}
                                </td>

                                <td class="px-4 py-2">
                                    {{ $livestock->age_in_days }} days
                                </td>

                                <td class="px-4 py-2">
                                    Week {{ $livestock->current_week }}
                                </td>

                                <td class="px-4 py-2 text-green-600 font-semibold">
                                    {{ number_format($livestock->expected_feed, 2) }}
                                </td>

                                {{-- 🔥 ACTIONS --}}
                                <td class="px-4 py-2 space-x-2">

                                    {{-- View --}}
                                    <a href="{{ route('livestocks.show', $livestock->id) }}"
                                       class="text-blue-600 hover:underline">
                                        View
                                    </a>

                                    {{-- Edit --}}
                                    <a href="{{ route('livestocks.edit', $livestock->id) }}"
                                       class="text-yellow-600 hover:underline">
                                        Edit
                                    </a>

                                    {{-- Expense --}}
                                    <a href="{{ route('livestock-logs.create', ['livestock_id' => $livestock->id]) }}"
                                       class="text-green-600 hover:underline">
                                        Expense
                                    </a>

                                    {{-- Mortality --}}
                                    <a href="{{ route('livestock-logs.create', [
                                            'livestock_id' => $livestock->id,
                                            'type' => 'mortality'
                                        ]) }}"
                                       class="text-red-600 hover:underline">
                                        Mortality
                                    </a>

                                    {{-- Delete --}}
                                    <form action="{{ route('livestocks.destroy', $livestock->id) }}"
                                          method="POST"
                                          class="inline"
                                          onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')

                                        <button class="text-red-700 hover:underline">
                                            Delete
                                        </button>
                                    </form>

                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-gray-500">
                                    No livestock found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>