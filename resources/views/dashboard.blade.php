@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

    <div class="space-y-6">

        <!-- ACTION BUTTONS -->
        <div class="flex flex-wrap gap-3">

            <a href="{{ route('users.create') }}"
                class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 text-sm">
                + Add User
            </a>

            <a href="{{ route('products.create') }}"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
                + Add Product
            </a>

            <a href="{{ route('purchases.create') }}"
                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">
                + Add Purchase
            </a>

            <a href="{{ route('livestocks.create') }}"
                class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 text-sm">
                + Add Livestock
            </a>

            <a href="{{ route('businesses.create') }}"
                class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">
                + Add Business
            </a>

        </div>

        <!-- SUMMARY CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            <div class="bg-white p-4 rounded-xl shadow">
                <p class="text-sm text-gray-500">Products</p>
                <h2 class="text-2xl font-bold mt-1">0</h2>
            </div>

            <div class="bg-white p-4 rounded-xl shadow">
                <p class="text-sm text-gray-500">Stock Items</p>
                <h2 class="text-2xl font-bold mt-1">0</h2>
            </div>

            <div class="bg-white p-4 rounded-xl shadow">
                <p class="text-sm text-gray-500">Purchases</p>
                <h2 class="text-2xl font-bold mt-1">TZS 0</h2>
            </div>

            <div class="bg-white p-4 rounded-xl shadow">
                <p class="text-sm text-gray-500">Livestock</p>
                <h2 class="text-2xl font-bold mt-1">0</h2>
            </div>

        </div>

    </div>

@endsection