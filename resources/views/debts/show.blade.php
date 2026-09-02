@extends('layouts.admin')

@section('title', 'Debt Statement')

@section('content')
<div class="max-w-6xl mx-auto py-6 px-6">
    <div class="flex items-center justify-between mb-6"><div><h1 class="text-2xl font-semibold">Debt Statement</h1><p class="text-gray-500">{{ $debt->reference }}</p></div><a href="{{ route('debts.index') }}" class="bg-gray-200 px-4 py-2 rounded-lg">Back</a></div>
    @if(session('success'))<div class="bg-green-100 text-green-700 p-3 rounded mb-5">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="bg-red-100 text-red-700 p-3 rounded mb-5">{{ $errors->first() }}</div>@endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow p-6 grid grid-cols-2 md:grid-cols-4 gap-5">
                <div><p class="text-xs text-gray-500">Customer</p><p class="font-semibold">{{ $debt->customer->name }}</p><p class="text-sm">{{ $debt->customer->phone }}</p></div>
                <div><p class="text-xs text-gray-500">Branch</p><p class="font-semibold">{{ $debt->business->name }}</p></div>
                <div><p class="text-xs text-gray-500">Original Debt</p><p class="font-semibold">TZS {{ number_format($debt->original_amount, 2) }}</p></div>
                <div><p class="text-xs text-gray-500">Balance</p><p class="font-bold text-red-700">TZS {{ number_format($debt->balance, 2) }}</p></div>
                <div><p class="text-xs text-gray-500">Debt Date</p><p>{{ $debt->debt_date->format('d/m/Y') }}</p></div>
                <div><p class="text-xs text-gray-500">Due Date</p><p>{{ $debt->due_date?->format('d/m/Y') ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-500">Status</p><p>{{ ucfirst($debt->status) }}</p></div>
                <div><p class="text-xs text-gray-500">Description</p><p>{{ $debt->description ?? '-' }}</p></div>
            </div>

            <div class="bg-white rounded-xl shadow overflow-x-auto">
                <div class="p-5 border-b"><h2 class="font-semibold text-lg">Payment History</h2></div>
                <table class="w-full min-w-[750px]"><thead class="bg-gray-100"><tr><th class="p-3 text-left">Payment No.</th><th class="p-3 text-left">Date</th><th class="p-3 text-left">Method</th><th class="p-3 text-left">Account</th><th class="p-3 text-left">Reference</th><th class="p-3 text-left">Amount</th></tr></thead>
                    <tbody>@forelse($debt->payments as $payment)<tr class="border-t"><td class="p-3">{{ $payment->payment_number }}</td><td class="p-3">{{ $payment->payment_date->format('d/m/Y') }}</td><td class="p-3">{{ strtoupper($payment->payment_method) }}</td><td class="p-3">{{ $payment->financialAccount?->name ?? '-' }}</td><td class="p-3">{{ $payment->external_reference ?? '-' }}</td><td class="p-3 font-semibold">TZS {{ number_format($payment->amount, 2) }}</td></tr>@empty<tr><td colspan="6" class="p-6 text-center text-gray-500">Hakuna malipo bado.</td></tr>@endforelse</tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-6 h-fit">
            <h2 class="font-semibold text-lg mb-4">Receive Payment</h2>
            @if($debt->status === 'paid')
                <div class="bg-green-100 text-green-700 p-3 rounded">Deni hili limelipwa lote.</div>
            @else
                <form method="POST" action="{{ route('debts.payments.store', $debt) }}" class="space-y-4">
                    @csrf
                    <div><label class="block text-sm mb-1">Amount *</label><input type="number" min="1" max="{{ $debt->balance }}" step="0.01" name="amount" required class="w-full border rounded-lg p-2"></div>
                    <div><label class="block text-sm mb-1">Payment Date *</label><input type="date" name="payment_date" value="{{ now()->toDateString() }}" required class="w-full border rounded-lg p-2"></div>
                    <div><label class="block text-sm mb-1">Method *</label><select name="payment_method" required class="w-full border rounded-lg p-2">@foreach(['cash'=>'Cash','bank'=>'Bank','mpesa'=>'M-Pesa','airtel_money'=>'Airtel Money','mixx'=>'Mixx by Yas','halopesa'=>'HaloPesa','pos'=>'POS'] as $value=>$label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
                    <div><label class="block text-sm mb-1">Financial Account</label><select name="financial_account_id" class="w-full border rounded-lg p-2"><option value="">-- Not configured --</option>@foreach($accounts as $account)<option value="{{ $account->id }}">{{ $account->name }}</option>@endforeach</select></div>
                    <div><label class="block text-sm mb-1">Transaction Reference</label><input name="external_reference" class="w-full border rounded-lg p-2"></div>
                    <div><label class="block text-sm mb-1">Notes</label><textarea name="notes" class="w-full border rounded-lg p-2"></textarea></div>
                    <button class="w-full bg-green-600 text-white px-5 py-2 rounded-lg">Save Payment</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
