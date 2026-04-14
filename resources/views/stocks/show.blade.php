<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            Stock Details
        </h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto">

        <div class="bg-white p-6 shadow rounded">

            <p><strong>Date:</strong> {{ $stock->date }}</p>
            <p><strong>Business:</strong> {{ $stock->business->name }}</p>
            <p><strong>Product:</strong> {{ $stock->product->name }}</p>

            <hr class="my-3">

            <p><strong>Opening:</strong> {{ $stock->opening_stock }}</p>
            <p><strong>Purchased:</strong> {{ $stock->purchased }}</p>
            <p><strong>Total:</strong> {{ $stock->total_stock }}</p>
            <p><strong>Closing:</strong> {{ $stock->closing_stock }}</p>
            <p><strong>Sold:</strong> {{ $stock->sold }}</p>

            <hr class="my-3">

            <p><strong>Sales:</strong> {{ number_format($stock->sales_amount, 2) }}</p>
            <p><strong>Profit:</strong> {{ number_format($stock->profit, 2) }}</p>

            <a href="{{ route('stocks.index') }}" 
               class="mt-4 inline-block bg-gray-600 text-white px-4 py-2 rounded">
                Back
            </a>

        </div>

    </div>
</x-app-layout>