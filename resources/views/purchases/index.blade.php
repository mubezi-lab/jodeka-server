<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Purchases
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- SUCCESS MESSAGE --}}
            @if(session('success'))
                <div class="bg-green-100 text-green-800 p-3 mb-4 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- ADD BUTTON --}}
            <a href="{{ route('purchases.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
                + Add Purchase
            </a>

            {{-- TABLE --}}
            <div class="mt-4 bg-white shadow p-4 rounded overflow-x-auto">
                <table class="w-full border text-sm">

                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border p-2">Date</th>
                            <th class="border p-2">Business</th>
                            <th class="border p-2">Product</th>
                            <th class="border p-2">Quantity</th>
                            <th class="border p-2">Unit Cost</th>
                            <th class="border p-2">Total Cost</th>
                            <th class="border p-2 text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($purchases as $purchase)

                            @php
                                $unitsPerPackage = $purchase->product->units_per_package ?? 0;

                                // calculate packages safely
                                $packages = $unitsPerPackage > 0
                                    ? $purchase->quantity / $unitsPerPackage
                                    : 0;
                            @endphp

                            <tr class="hover:bg-gray-50">

                                {{-- Date --}}
                                <td class="border p-2">
                                    {{ \Carbon\Carbon::parse($purchase->date)->format('d M Y') }}
                                </td>

                                {{-- Business --}}
                                <td class="border p-2">
                                    {{ $purchase->business->name ?? '-' }}
                                </td>

                                {{-- Product --}}
                                <td class="border p-2">
                                    {{ $purchase->product->name ?? '-' }}
                                </td>

                                {{-- Quantity --}}
                                <td class="border p-2">
                                    {{ number_format($packages, 2) }} pkg
                                    <br>
                                    <small class="text-gray-500">
                                        ({{ number_format($purchase->quantity) }} units)
                                    </small>
                                </td>

                                {{-- Unit Cost --}}
                                <td class="border p-2">
                                    {{ number_format($purchase->unit_cost, 2) }}
                                </td>

                                {{-- Total Cost --}}
                                <td class="border p-2 font-semibold text-green-700">
                                    {{ number_format($purchase->total_cost, 2) }}
                                </td>

                                {{-- Actions --}}
                                <td class="border p-2 text-center space-x-1">

                                    {{-- EDIT --}}
                                    <a href="{{ route('purchases.edit', $purchase->id) }}"
                                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded text-xs">
                                        Edit
                                    </a>

                                    {{-- DELETE --}}
                                    <form action="{{ route('purchases.destroy', $purchase->id) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        @method('DELETE')

                                        <button onclick="return confirm('Delete this purchase?')"
                                            class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs">
                                            Delete
                                        </button>
                                    </form>

                                </td>

                            </tr>

                        @empty
                            <tr>
                                <td colspan="7" class="text-center p-4 text-gray-500">
                                    No purchases found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>
</x-app-layout>