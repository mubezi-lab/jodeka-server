<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Edit Purchase</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto">

            @if(session('error'))
                <div class="bg-red-100 text-red-700 p-3 mb-4 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('purchases.update', $purchase->id) }}"
                class="bg-white p-6 shadow rounded">
                @csrf
                @method('PUT')

                {{-- Business --}}
                <div class="mb-4">
                    <label>Business</label>
                    <select name="business_id" class="w-full border p-2 rounded">
                        @foreach($businesses as $business)
                            <option value="{{ $business->id }}" {{ $purchase->business_id == $business->id ? 'selected' : '' }}>
                                {{ $business->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Product --}}
                <div class="mb-4">
                    <label>Product</label>
                    <select name="product_id" class="w-full border p-2 rounded">
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ $purchase->product_id == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @php
                    $unitsPerPackage = $purchase->product->units_per_package ?? 0;

                    // packages (safe)
                    $packages = $unitsPerPackage > 0
                        ? $purchase->quantity / $unitsPerPackage
                        : 0;

                    // 🔥 FIX: rounding
                    $packageCost = $unitsPerPackage > 0
                        ? round($purchase->unit_cost * $unitsPerPackage, 2)
                        : 0;
                @endphp

                {{-- Quantity (Packages) --}}
                <div class="mb-4">
                    <label>Quantity (Packages)</label>
                    <input type="number" step="0.01" name="quantity" value="{{ $packages }}"
                        class="w-full border p-2 rounded" min="1" required>
                </div>

                {{-- Cost per Package --}}
                <div class="mb-4">
                    <label>Cost per Package</label>
                    <input type="number" step="0.01" name="unit_cost" value="{{ $packageCost }}"
                        class="w-full border p-2 rounded" min="0" required>
                </div>

                {{-- Date --}}
                <div class="mb-4">
                    <label>Date</label>
                    <input type="date" name="date" value="{{ \Carbon\Carbon::parse($purchase->date)->format('Y-m-d') }}"
                        class="w-full border p-2 rounded" required>
                </div>

                {{-- Supplier --}}
                <div class="mb-4">
                    <label>Supplier</label>
                    <input type="text" name="supplier" value="{{ $purchase->supplier }}"
                        class="w-full border p-2 rounded">
                </div>

                {{-- Notes --}}
                <div class="mb-4">
                    <label>Notes</label>
                    <textarea name="notes" class="w-full border p-2 rounded">{{ $purchase->notes }}</textarea>
                </div>

                <button class="bg-blue-600 text-white px-4 py-2 rounded">
                    Update Purchase
                </button>

            </form>

        </div>
    </div>
</x-app-layout>