<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Products
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Success Message --}}
            @if(session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Add Button --}}
            <div class="flex justify-between items-center">
                <a href="{{ route('products.create') }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700">
                    + Add Product
                </a>
            </div>

            {{-- Table --}}
            <div class="mt-4 bg-white shadow sm:rounded-lg p-4 overflow-x-auto">
                <table class="w-full border border-gray-200 text-sm">

                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border p-2">#</th>
                            <th class="border p-2 text-left">Name</th>
                            <th class="border p-2 text-left">Category</th>
                            <th class="border p-2 text-left">Package</th>
                            <th class="border p-2 text-left">Unit Price</th>
                            <th class="border p-2 text-left">Package Price</th>
                            <th class="border p-2 text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($products as $product)
                            <tr class="hover:bg-gray-50">

                                {{-- Serial Number --}}
                                <td class="border p-2">
                                    {{ $loop->iteration }}
                                </td>

                                {{-- Name --}}
                                <td class="border p-2 font-medium">
                                    {{ $product->name }}
                                </td>

                                {{-- Category --}}
                                <td class="border p-2">
                                    {{ $product->category ?? '-' }}
                                </td>

                                {{-- Package --}}
                                <td class="border p-2">
                                    {{ $product->package_type }}
                                    ({{ $product->units_per_package }} pcs)
                                </td>

                                {{-- Unit Prices --}}
                                <td class="border p-2">
                                    <span class="block text-gray-600">
                                        Buy: {{ number_format($product->buy_price_per_unit, 2) }}
                                    </span>
                                    <span class="block text-green-600 font-medium">
                                        Sell: {{ number_format($product->sell_price_per_unit, 2) }}
                                    </span>
                                </td>

                                {{-- Package Prices --}}
                                <td class="border p-2">
                                    <span class="block text-gray-600">
                                        Buy: {{ number_format($product->buy_price_per_package, 2) }}
                                    </span>
                                    <span class="block text-green-600 font-medium">
                                        Sell: {{ number_format($product->sell_price_per_package, 2) }}
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td class="border p-2 text-center space-x-2">

                                    <a href="{{ route('products.edit', $product->id) }}"
                                        class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                                        Edit
                                    </a>

                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST"
                                        class="inline-block" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')

                                        <button class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                            Delete
                                        </button>
                                    </form>

                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center p-4 text-gray-500">
                                    No products found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>
</x-app-layout>