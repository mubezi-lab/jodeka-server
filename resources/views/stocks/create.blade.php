<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Add Stock
        </h2>
    </x-slot>

    <script>
        window.stockDataUrl = "{{ route('stocks.data') }}";
    </script>

    <div class="py-6">
        <div class="max-w-5xl mx-auto">

            {{-- 🔥 ERROR DISPLAY --}}
            @if ($errors->any())
                <div class="bg-red-100 text-red-700 p-4 mb-4 rounded">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('stocks.store') }}" class="bg-white p-6 shadow rounded">
                @csrf

                <label>Date</label>
                <input type="date" name="date" value="{{ old('date') }}" class="w-full border p-2 mb-3" required>

                <label>Business</label>
                <select name="business_id" class="w-full border p-2 mb-3" required>
                    <option value="">Choose Business</option>
                    @foreach($businesses as $business)
                        <option value="{{ $business->id }}" {{ old('business_id') == $business->id ? 'selected' : '' }}>
                            {{ $business->name }}
                        </option>
                    @endforeach
                </select>

                <label>Product</label>
                <select name="product_id" class="w-full border p-2 mb-3" required>
                    <option value="">Choose Product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>

                <label>Opening Stock</label>
                <input type="number" step="1" name="opening_stock" value="{{ old('opening_stock') }}"
                    class="w-full border p-2 mb-3" required>

                <label>Purchased</label>
                <input type="number" step="0.01" name="purchased" value="{{ old('purchased') }}"
                    class="w-full border p-2 mb-3" required>

                <label>Closing Stock</label>
                <input type="number" step="1" name="closing_stock" value="{{ old('closing_stock') }}"
                    class="w-full border p-2 mb-3" required>

                <small class="text-gray-500">
                    Closing stock must be ≤ Opening + Purchased
                </small>

                <label class="mt-2 block">Selling Price</label>
                <input type="number" step="0.01" name="price" value="{{ old('price') }}" class="w-full border p-2 mb-3">

                <button class="bg-green-600 text-white px-4 py-2 rounded">
                    Save
                </button>
            </form>

        </div>
    </div>
</x-app-layout>