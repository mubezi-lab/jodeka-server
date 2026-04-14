<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            Edit Stock
        </h2>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto">

        <form method="POST" action="{{ route('stocks.update', $stock->id) }}" 
              class="bg-white p-6 shadow rounded">
            @csrf
            @method('PUT')

            <label>Date</label>
            <input type="date" name="date" value="{{ $stock->date }}" class="w-full border p-2 mb-3">

            <label>Business</label>
            <select name="business_id" class="w-full border p-2 mb-3">
                @foreach($businesses as $business)
                    <option value="{{ $business->id }}" 
                        {{ $stock->business_id == $business->id ? 'selected' : '' }}>
                        {{ $business->name }}
                    </option>
                @endforeach
            </select>

            <label>Product</label>
            <select name="product_id" class="w-full border p-2 mb-3">
                @foreach($products as $product)
                    <option value="{{ $product->id }}"
                        {{ $stock->product_id == $product->id ? 'selected' : '' }}>
                        {{ $product->name }}
                    </option>
                @endforeach
            </select>

            <label>Opening Stock</label>
            <input type="number" name="opening_stock" value="{{ $stock->opening_stock }}" class="w-full border p-2 mb-3">

            <label>Purchased</label>
            <input type="number" name="purchased" value="{{ $stock->purchased }}" class="w-full border p-2 mb-3">

            <label>Closing Stock</label>
            <input type="number" name="closing_stock" value="{{ $stock->closing_stock }}" class="w-full border p-2 mb-3">

            <label>Selling Price</label>
            <input type="number" name="price" step="0.01" value="{{ $stock->product->price }}" class="w-full border p-2 mb-3">

            <button class="bg-blue-600 text-white px-4 py-2 rounded">
                Update Stock
            </button>

        </form>

    </div>
</x-app-layout>