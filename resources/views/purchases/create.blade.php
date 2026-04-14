<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Add Purchase</h2>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto">

        @if(session('error'))
            <div class="bg-red-100 text-red-700 p-3 mb-4 rounded">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('purchases.store') }}" class="bg-white p-6 shadow rounded">
            @csrf

            {{-- Business --}}
            <label>Business</label>
            <select name="business_id" class="w-full border p-2 mb-3" required>
                <option value="">Select a business</option>
                @foreach($businesses as $business)
                    <option value="{{ $business->id }}">{{ $business->name }}</option>
                @endforeach
            </select>

            {{-- Product --}}
            <label>Product</label>
            <select id="productSelect" name="product_id" class="w-full border p-2 mb-3" required>
                <option value="">Select a product</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" data-units="{{ $product->units_per_package }}">
                        {{ $product->name }}
                    </option>
                @endforeach
            </select>

            {{-- Units per package --}}
            <label>Units per Package</label>
            <input type="text" id="unitsPerPackage" class="w-full border p-2 mb-3 bg-gray-100" readonly>

            {{-- Packages --}}
            <label>Quantity (Packages)</label>
            <input type="number" id="packages" name="quantity" class="w-full border p-2 mb-3" min="1" required>

            {{-- Price per package --}}
            <label>Package Cost</label>
            <input type="number" step="0.01" id="packageCost" name="unit_cost" class="w-full border p-2 mb-3" required>

            {{-- Total Units --}}
            <label>Total Units</label>
            <input type="text" id="totalUnits" class="w-full border p-2 mb-3 bg-gray-100" readonly>

            {{-- Total Cost --}}
            <label>Total Cost</label>
            <input type="text" id="totalCost" class="w-full border p-2 mb-3 bg-gray-100" readonly>

            {{-- Date --}}
            <label>Date</label>
            <input type="date" name="date" class="w-full border p-2 mb-3" required>

            {{-- Supplier --}}
            <label>Supplier</label>
            <input type="text" name="supplier" class="w-full border p-2 mb-3">

            {{-- Notes --}}
            <label>Notes</label>
            <textarea name="notes" class="w-full border p-2 mb-3"></textarea>

            <button class="bg-green-600 text-white px-4 py-2 rounded">
                Save Purchase
            </button>
        </form>

    </div>

    {{-- 🔥 AUTO CALCULATION SCRIPT --}}
    <script>
        const productSelect = document.getElementById('productSelect');
        const unitsPerPackageInput = document.getElementById('unitsPerPackage');
        const packagesInput = document.getElementById('packages');
        const packageCostInput = document.getElementById('packageCost');
        const totalUnitsInput = document.getElementById('totalUnits');
        const totalCostInput = document.getElementById('totalCost');

        let unitsPerPackage = 0;

        productSelect.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            unitsPerPackage = parseFloat(selected.getAttribute('data-units')) || 0;

            unitsPerPackageInput.value = unitsPerPackage;
            calculate();
        });

        packagesInput.addEventListener('input', calculate);
        packageCostInput.addEventListener('input', calculate);

        function calculate() {
            const packages = parseFloat(packagesInput.value) || 0;
            const cost = parseFloat(packageCostInput.value) || 0;

            const totalUnits = packages * unitsPerPackage;
            const totalCost = packages * cost;

            totalUnitsInput.value = totalUnits;
            totalCostInput.value = totalCost.toFixed(2);
        }
    </script>

</x-app-layout>