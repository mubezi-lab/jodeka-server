@extends('layouts.admin')

@section('title', 'Cash Handovers')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-6">
    <div class="mb-6"><h1 class="text-2xl font-semibold">Cash Handovers</h1><p class="text-sm text-gray-500">Muhudumu anatuma handover; manager/admin anathibitisha kiasi alichopokea.</p></div>
    @if(session('success'))<div class="bg-green-100 text-green-700 p-3 rounded mb-5">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="bg-red-100 text-red-700 p-3 rounded mb-5">{{ $errors->first() }}</div>@endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow p-6 h-fit">
            <h2 class="font-semibold text-lg mb-4">Record Handover</h2>
            @if($accounts->count() < 2)<div class="bg-yellow-100 text-yellow-800 p-3 rounded">Tengeneza angalau financial accounts mbili kwanza.</div>@else
            <form method="POST" action="{{ route('financial-account-transfers.store') }}" class="space-y-4">
                @csrf
                <div><label class="block text-sm mb-1">From Account *</label><select name="from_financial_account_id" required class="w-full border rounded-lg p-2"><option value="">-- Select source --</option>@foreach($accounts as $account)<option value="{{ $account->id }}">{{ $account->name }} — {{ $account->business?->name ?? 'Main' }} (TZS {{ number_format($account->current_balance, 2) }})</option>@endforeach</select></div>
                <div><label class="block text-sm mb-1">To Account *</label><select name="to_financial_account_id" required class="w-full border rounded-lg p-2"><option value="">-- Select destination --</option>@foreach($accounts as $account)<option value="{{ $account->id }}">{{ $account->name }} — {{ $account->business?->name ?? 'Main' }}</option>@endforeach</select></div>
                <div><label class="block text-sm mb-1">Amount *</label><input type="number" min="1" step="0.01" name="amount" required class="w-full border rounded-lg p-2"></div>
                <div><label class="block text-sm mb-1">Transfer Date *</label><input type="date" name="transfer_date" value="{{ now()->toDateString() }}" required class="w-full border rounded-lg p-2"></div>
                <div><label class="block text-sm mb-1">Reference</label><input name="external_reference" class="w-full border rounded-lg p-2"></div>
                <div><label class="block text-sm mb-1">Notes</label><textarea name="notes" class="w-full border rounded-lg p-2"></textarea></div>
                <button class="w-full bg-green-600 text-white px-5 py-2 rounded-lg">Submit Handover</button>
            </form>@endif
        </div>

        <div class="xl:col-span-2 bg-white rounded-xl shadow overflow-x-auto">
            <table class="w-full min-w-[1250px]"><thead class="bg-gray-100"><tr><th class="p-3 text-left">Transfer No.</th><th class="p-3 text-left">Date</th><th class="p-3 text-left">From</th><th class="p-3 text-left">To</th><th class="p-3 text-left">Submitted</th><th class="p-3 text-left">Received</th><th class="p-3 text-left">Variance</th><th class="p-3 text-left">Status</th><th class="p-3 text-left">By</th><th class="p-3 text-left">Review</th></tr></thead>
                <tbody>@forelse($transfers as $transfer)<tr class="border-t align-top">
                    <td class="p-3">{{ $transfer->transfer_number }}</td><td class="p-3">{{ $transfer->transfer_date->format('d/m/Y') }}</td><td class="p-3">{{ $transfer->fromAccount->name }}</td><td class="p-3">{{ $transfer->toAccount->name }}</td>
                    <td class="p-3 font-semibold">TZS {{ number_format($transfer->amount, 2) }}</td><td class="p-3">{{ $transfer->confirmed_amount !== null ? 'TZS '.number_format($transfer->confirmed_amount, 2) : '-' }}</td>
                    <td class="p-3 {{ $transfer->variance != 0 ? 'text-red-700 font-semibold' : '' }}">{{ $transfer->variance !== null ? 'TZS '.number_format($transfer->variance, 2) : '-' }}</td>
                    <td class="p-3"><span class="px-2 py-1 rounded text-xs {{ $transfer->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : ($transfer->status === 'confirmed' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') }}">{{ ucfirst($transfer->status) }}</span></td>
                    <td class="p-3">{{ $transfer->submitter?->name ?? '-' }}</td>
                    <td class="p-3 min-w-[260px]">
                        @if($transfer->status === 'pending' && in_array(auth()->user()->role?->name, ['admin','manager']))
                            <form method="POST" action="{{ route('financial-account-transfers.confirm', $transfer) }}" class="space-y-2 mb-2">@csrf
                                <input type="number" min="1" step="0.01" name="confirmed_amount" value="{{ $transfer->amount }}" required class="w-full border rounded p-1.5" aria-label="Confirmed amount">
                                <input name="review_notes" placeholder="Explanation if different" class="w-full border rounded p-1.5">
                                <button class="bg-green-600 text-white px-3 py-1.5 rounded">Confirm Received</button>
                            </form>
                            <form method="POST" action="{{ route('financial-account-transfers.reject', $transfer) }}" class="flex gap-2">@csrf
                                <input name="review_notes" required minlength="3" placeholder="Reason for rejection" class="border rounded p-1.5">
                                <button class="bg-red-600 text-white px-3 py-1.5 rounded">Reject</button>
                            </form>
                        @else
                            <span class="text-sm">{{ $transfer->reviewer?->name ?? '-' }}<br>{{ $transfer->review_notes }}</span>
                        @endif
                    </td>
                </tr>@empty<tr><td colspan="10" class="p-8 text-center text-gray-500">Hakuna handover bado.</td></tr>@endforelse</tbody>
            </table>
            <div class="p-4">{{ $transfers->links() }}</div>
        </div>
    </div>
</div>
@endsection
