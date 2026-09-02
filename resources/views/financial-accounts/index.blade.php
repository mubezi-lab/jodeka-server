@extends('layouts.admin')

@section('title', 'Financial Accounts')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-6">
    <div class="mb-6"><h1 class="text-2xl font-semibold">Financial Accounts</h1><p class="text-sm text-gray-500">Akaunti kuu na akaunti za makusanyo ya kila branch.</p></div>
    @if(session('success'))<div class="bg-green-100 text-green-700 p-3 rounded mb-5">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="bg-red-100 text-red-700 p-3 rounded mb-5">{{ $errors->first() }}</div>@endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow p-6 h-fit">
            <h2 class="font-semibold text-lg mb-4">Add Financial Account</h2>
            <form method="POST" action="{{ route('financial-accounts.store') }}" class="space-y-4">
                @csrf
                <div><label class="block text-sm mb-1">Scope *</label><select name="business_id" class="w-full border rounded-lg p-2"><option value="">Main / Company Account</option>@foreach($businesses as $business)<option value="{{ $business->id }}" @selected(old('business_id') == $business->id)>{{ $business->name }}</option>@endforeach</select></div>
                <div><label class="block text-sm mb-1">Account Name *</label><input name="name" value="{{ old('name') }}" placeholder="Mfano: Main Cash" required class="w-full border rounded-lg p-2"></div>
                <div><label class="block text-sm mb-1">Type *</label><select name="account_type" required class="w-full border rounded-lg p-2">@foreach(['cash'=>'Cash','bank'=>'Bank','mobile_money'=>'Mobile Money','pos'=>'POS','clearing'=>'Pending Handover'] as $value=>$label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
                <div><label class="block text-sm mb-1">Provider</label><input name="provider" value="{{ old('provider') }}" placeholder="NMB, M-Pesa, Mixx..." class="w-full border rounded-lg p-2"></div>
                <div><label class="block text-sm mb-1">Account / Phone Number</label><input name="account_number" value="{{ old('account_number') }}" class="w-full border rounded-lg p-2"></div>
                <div><label class="block text-sm mb-1">Opening Balance *</label><input type="number" step="0.01" name="opening_balance" value="{{ old('opening_balance', 0) }}" required class="w-full border rounded-lg p-2"></div>
                <div><label class="block text-sm mb-1">Balance Date</label><input type="date" name="opening_balance_date" value="{{ old('opening_balance_date', now()->toDateString()) }}" class="w-full border rounded-lg p-2"></div>
                <button class="w-full bg-blue-600 text-white px-5 py-2 rounded-lg">Save Account</button>
            </form>
        </div>

        <div class="xl:col-span-2 bg-white rounded-xl shadow overflow-x-auto">
            <table class="w-full min-w-[850px]">
                <thead class="bg-gray-100"><tr><th class="p-3 text-left">Account</th><th class="p-3 text-left">Scope</th><th class="p-3 text-left">Type</th><th class="p-3 text-left">Provider</th><th class="p-3 text-left">Opening</th><th class="p-3 text-left">Current Balance</th><th class="p-3 text-left">Status</th><th class="p-3"></th></tr></thead>
                <tbody>@forelse($accounts as $account)<tr class="border-t {{ $account->is_active ? '' : 'opacity-50' }}">
                    <td class="p-3 font-medium">{{ $account->name }}</td><td class="p-3">{{ $account->business?->name ?? 'Main Company' }}</td><td class="p-3">{{ ucwords(str_replace('_', ' ', $account->account_type)) }}</td><td class="p-3">{{ $account->provider ?? '-' }}</td>
                    <td class="p-3">TZS {{ number_format($account->opening_balance, 2) }}</td><td class="p-3 font-semibold {{ $account->current_balance < 0 ? 'text-red-700' : 'text-green-700' }}">TZS {{ number_format($account->current_balance, 2) }}</td><td class="p-3">{{ $account->is_active ? 'Active' : 'Inactive' }}</td>
                    <td class="p-3"><form method="POST" action="{{ route('financial-accounts.toggle', $account) }}">@csrf @method('PATCH')<button class="px-3 py-1.5 rounded {{ $account->is_active ? 'bg-yellow-500' : 'bg-green-600 text-white' }}">{{ $account->is_active ? 'Disable' : 'Enable' }}</button></form></td>
                </tr>@empty<tr><td colspan="8" class="p-8 text-center text-gray-500">Hakuna financial account bado.</td></tr>@endforelse</tbody>
            </table>
        </div>
    </div>
</div>
@endsection
