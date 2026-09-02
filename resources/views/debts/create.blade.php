@extends('layouts.admin')

@section('title', 'Add Customer Debt')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-6">
    <div class="mb-6"><h1 class="text-2xl font-semibold">Add Customer Debt</h1><p class="text-sm text-gray-500">Andika deni la mteja katika branch husika.</p></div>
    <div class="bg-white rounded-xl shadow p-6">
        @if($errors->any())<div class="bg-red-100 text-red-700 p-3 rounded mb-5"><ul class="list-disc ml-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @if($customers->isEmpty())<div class="bg-yellow-100 text-yellow-800 p-3 rounded mb-5">Ongeza mteja kwanza kwenye <a class="underline" href="{{ route('customers.index') }}">Customers</a>.</div>@endif
        <form method="POST" action="{{ route('debts.store') }}" class="space-y-5">
            @csrf
            <div><label class="block text-sm mb-1">Customer *</label><select name="customer_id" required class="w-full border rounded-lg p-2"><option value="">-- Select customer --</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->name }} {{ $customer->phone ? '— '.$customer->phone : '' }}</option>@endforeach</select></div>
            <div><label class="block text-sm mb-1">Business / Branch *</label><select name="business_id" required class="w-full border rounded-lg p-2"><option value="">-- Select branch --</option>@foreach($businesses as $business)<option value="{{ $business->id }}" @selected(old('business_id') == $business->id)>{{ $business->name }}</option>@endforeach</select></div>
            <div><label class="block text-sm mb-1">Debt Amount (TZS) *</label><input type="number" min="1" step="0.01" name="original_amount" value="{{ old('original_amount') }}" required class="w-full border rounded-lg p-2"></div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="block text-sm mb-1">Debt Date *</label><input type="date" name="debt_date" value="{{ old('debt_date', now()->toDateString()) }}" required class="w-full border rounded-lg p-2"></div>
                <div><label class="block text-sm mb-1">Due Date</label><input type="date" name="due_date" value="{{ old('due_date') }}" class="w-full border rounded-lg p-2"></div>
            </div>
            <div><label class="block text-sm mb-1">Description</label><textarea name="description" rows="3" class="w-full border rounded-lg p-2" placeholder="Mfano: Trays 5 za mayai">{{ old('description') }}</textarea></div>
            <div class="flex gap-3"><button @disabled($customers->isEmpty()) class="bg-blue-600 disabled:bg-gray-400 text-white px-5 py-2 rounded-lg">Save Debt</button><a href="{{ route('debts.index') }}" class="bg-gray-200 px-5 py-2 rounded-lg">Cancel</a></div>
        </form>
    </div>
</div>
@endsection
