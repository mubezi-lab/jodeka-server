<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Add Product
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            <form method="POST" action="{{ route('products.store') }}" 
                  class="bg-white p-6 shadow rounded space-y-6">
                @csrf

                {{-- Product Info --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- Product Name --}}
                    <div>
                        <label class="block font-medium">Product Name</label>
                        <input type="text" name="name" 
                               value="{{ old('name') }}" 
                               placeholder="e.g. Soda, Jobo"
                               class="w-full border rounded p-2">
                        @error('name')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Category --}}
                    <div>
                        <label class="block font-medium">Category</label>
                        <input type="text" name="category" 
                               value="{{ old('category') }}" 
                               placeholder="e.g. Soft Drinks"
                               class="w-full border rounded p-2">
                        @error('category')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                {{-- Package Info --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- Package Type --}}
                    <div>
                        <label class="block font-medium">Package Type</label>
                        <select name="package_type" class="w-full border rounded p-2">
                            <option value="">Select Package</option>
                            <option value="Crate" {{ old('package_type') == 'Crate' ? 'selected' : '' }}>Crate</option>
                            <option value="Box" {{ old('package_type') == 'Box' ? 'selected' : '' }}>Box</option>
                            <option value="Tray" {{ old('package_type') == 'Tray' ? 'selected' : '' }}>Tray</option>
                            <option value="Bundle" {{ old('package_type') == 'Bundle' ? 'selected' : '' }}>Bundle</option>
                            <option value="Bag" {{ old('package_type') == 'Bag' ? 'selected' : '' }}>Bag</option>
                            <option value="Packet" {{ old('package_type') == 'Packet' ? 'selected' : '' }}>Packet</option>
                        </select>
                        @error('package_type')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Units per Package --}}
                    <div>
                        <label class="block font-medium">
                            Units per Package
                        </label>
                        <input type="number" name="units_per_package" 
                               value="{{ old('units_per_package') }}"
                               placeholder="e.g. 24, 30"
                               class="w-full border rounded p-2">
                        @error('units_per_package')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                {{-- Pricing --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- Buy Price (PACKAGE) --}}
                    <div>
                        <label class="block font-medium">
                            Buy Price (per package)
                        </label>
                        <input type="number" step="0.01" 
                               name="buy_price_per_package"
                               value="{{ old('buy_price_per_package') }}"
                               placeholder="e.g. 44000"
                               class="w-full border rounded p-2">
                        @error('buy_price_per_package')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Sell Price (UNIT) --}}
                    <div>
                        <label class="block font-medium">
                            Sell Price (per unit)
                        </label>
                        <input type="number" step="0.01" 
                               name="sell_price_per_unit"
                               value="{{ old('sell_price_per_unit') }}"
                               placeholder="e.g. 2000"
                               class="w-full border rounded p-2">
                        @error('sell_price_per_unit')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                {{-- Submit --}}
                <div class="pt-4">
                    <button class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 shadow">
                        Save Product
                    </button>
                </div>

            </form>

        </div>
    </div>
</x-app-layout>