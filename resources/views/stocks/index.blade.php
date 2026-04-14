<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Stock Reports
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

            <a href="{{ route('stocks.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
                + Add Stock
            </a>

            <div class="mt-4 bg-white shadow p-4 rounded overflow-x-auto">
                <table class="w-full border text-sm">

                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border p-2">Date</th>
                            <th class="border p-2">Product</th>
                            <th class="border p-2">Opening</th>
                            <th class="border p-2">Purchased</th>
                            <th class="border p-2">Total</th>
                            <th class="border p-2">Closing</th>
                            <th class="border p-2">Sold</th>
                            <th class="border p-2">Sales</th>
                            <th class="border p-2">Profit</th>
                            <th class="border p-2 text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($stocks as $stock)
                            <tr class="hover:bg-gray-50">
                                <td class="border p-2">
                                    {{ \Carbon\Carbon::parse($stock->date)->format('d/m/Y') }}
                                </td>

                                <td class="border p-2">
                                    {{ $stock->product->name ?? '-' }}
                                </td>

                                <td class="border p-2">{{ $stock->opening_stock }}</td>
                                <td class="border p-2">{{ $stock->purchased }}</td>

                                <td class="border p-2 font-semibold">
                                    {{ $stock->total_stock }}
                                </td>

                                <td class="border p-2">{{ $stock->closing_stock }}</td>

                                <td class="border p-2 text-red-600 font-semibold">
                                    {{ $stock->sold }}
                                </td>

                                {{-- 🔥 SALES --}}
                                <td class="border p-2 font-semibold">
                                    @if($stock->sales_amount > 0)
                                        <span class="text-blue-600">
                                            {{ number_format($stock->sales_amount, 2) }}
                                        </span>
                                    @else
                                        <span class="text-red-500">
                                            0.00 ⚠
                                        </span>
                                    @endif
                                </td>

                                {{-- 🔥 PROFIT --}}
                                <td class="border p-2 font-semibold">
                                    @if($stock->profit >= 0)
                                        <span class="text-green-600">
                                            {{ number_format($stock->profit, 2) }}
                                        </span>
                                    @else
                                        <span class="text-red-600">
                                            {{ number_format($stock->profit, 2) }}
                                        </span>
                                    @endif
                                </td>

                                <td class="border p-2 text-center space-x-1">

                                    <a href="{{ route('stocks.edit', $stock->id) }}"
                                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded text-xs">
                                        Edit
                                    </a>

                                    <form action="{{ route('stocks.destroy', $stock->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')

                                        <button onclick="return confirm('Delete this record?')"
                                            class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs">
                                            Delete
                                        </button>
                                    </form>

                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="10" class="text-center p-4 text-gray-500">
                                    No stock records found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>
</x-app-layout>