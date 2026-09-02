@extends('layouts.admin')

@section('title', 'Customers')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-6" x-data="{ open: false }">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Customers</h1>
            <p class="text-sm text-gray-500">Wateja wa branches zote na salio la madeni yao.</p>
        </div>
        <button @click="open = true" class="bg-blue-600 text-white px-4 py-2 rounded-lg">+ Add Customer</button>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-5">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full min-w-[850px]">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-left">Number</th><th class="p-3 text-left">Name</th>
                    <th class="p-3 text-left">Phone</th><th class="p-3 text-left">Credit Limit</th>
                    <th class="p-3 text-left">Outstanding</th><th class="p-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr class="border-t">
                        <td class="p-3">{{ $customer->customer_number }}</td>
                        <td class="p-3 font-medium">{{ $customer->name }}</td>
                        <td class="p-3">{{ $customer->phone ?? '-' }}</td>
                        <td class="p-3">{{ $customer->credit_limit !== null ? 'TZS '.number_format($customer->credit_limit, 2) : 'No limit' }}</td>
                        <td class="p-3 font-semibold text-red-700">TZS {{ number_format($customer->outstanding_balance ?? 0, 2) }}</td>
                        <td class="p-3">{{ $customer->is_active ? 'Active' : 'Inactive' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-8 text-center text-gray-500">Hakuna mteja bado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $customers->links() }}</div>

    <div x-show="open" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div @click.outside="open = false" class="bg-white rounded-xl shadow-xl p-6 w-full max-w-lg">
            <div class="flex justify-between mb-5"><h2 class="text-xl font-semibold">Add Customer</h2><button @click="open=false">✕</button></div>
            <form method="POST" action="{{ route('customers.store') }}" class="space-y-4">
                @csrf
                <div><label class="block text-sm mb-1">Name *</label><input name="name" value="{{ old('name') }}" required class="w-full border rounded-lg p-2"></div>
                <div><label class="block text-sm mb-1">Phone</label><input name="phone" value="{{ old('phone') }}" class="w-full border rounded-lg p-2"></div>
                <div><label class="block text-sm mb-1">Email</label><input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded-lg p-2"></div>
                <div><label class="block text-sm mb-1">Address</label><textarea name="address" class="w-full border rounded-lg p-2">{{ old('address') }}</textarea></div>
                <div><label class="block text-sm mb-1">Credit Limit (optional)</label><input type="number" min="0" step="0.01" name="credit_limit" value="{{ old('credit_limit') }}" class="w-full border rounded-lg p-2"></div>
                @if($errors->any())<div class="text-red-600 text-sm">{{ $errors->first() }}</div>@endif
                <button class="bg-blue-600 text-white px-5 py-2 rounded-lg">Save Customer</button>
            </form>
        </div>
    </div>
</div>
@endsection
