@extends('layouts.admin')

@section('title', 'Customer Debts')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div><h1 class="text-2xl font-semibold">Customer Debts</h1><p class="text-sm text-gray-500">Madeni ya wateja katika branches zote.</p></div>
        <a href="{{ route('debts.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg w-fit">+ Add Debt</a>
    </div>
    @if(session('success'))<div class="bg-green-100 text-green-700 p-3 rounded mb-5">{{ session('success') }}</div>@endif

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <div class="bg-red-50 p-5 rounded-xl"><p class="text-sm text-red-600">Total Outstanding</p><p class="text-2xl font-bold text-red-700">TZS {{ number_format($totalOutstanding, 2) }}</p></div>
        <div class="bg-yellow-50 p-5 rounded-xl"><p class="text-sm text-yellow-700">Unpaid</p><p class="text-2xl font-bold">{{ $unpaidCount }}</p></div>
        <div class="bg-blue-50 p-5 rounded-xl"><p class="text-sm text-blue-700">Partial</p><p class="text-2xl font-bold">{{ $partialCount }}</p></div>
        <div class="bg-orange-50 p-5 rounded-xl"><p class="text-sm text-orange-700">Overdue</p><p class="text-2xl font-bold">{{ $overdueCount }}</p></div>
    </div>

    <form method="GET" class="bg-white p-4 rounded-xl shadow mb-5 grid grid-cols-1 md:grid-cols-4 gap-3">
        <input name="search" value="{{ request('search') }}" placeholder="Customer, phone or reference" class="border rounded-lg p-2">
        <select name="business_id" class="border rounded-lg p-2"><option value="">All branches</option>@foreach($businesses as $business)<option value="{{ $business->id }}" @selected(request('business_id') == $business->id)>{{ $business->name }}</option>@endforeach</select>
        <select name="status" class="border rounded-lg p-2"><option value="">All statuses</option>@foreach(['unpaid','partial','paid'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
        <button class="bg-gray-800 text-white rounded-lg px-4 py-2">Filter</button>
    </form>

    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full min-w-[1100px]">
            <thead class="bg-gray-100"><tr><th class="p-3 text-left">Reference</th><th class="p-3 text-left">Customer</th><th class="p-3 text-left">Branch</th><th class="p-3 text-left">Original</th><th class="p-3 text-left">Balance</th><th class="p-3 text-left">Debt Date</th><th class="p-3 text-left">Due Date</th><th class="p-3 text-left">Status</th><th class="p-3">Action</th></tr></thead>
            <tbody>
                @forelse($debts as $debt)
                    @php $overdue = $debt->balance > 0 && $debt->due_date && $debt->due_date->isBefore(today()); @endphp
                    <tr class="border-t">
                        <td class="p-3">{{ $debt->reference }}</td><td class="p-3 font-medium">{{ $debt->customer->name }}</td><td class="p-3">{{ $debt->business->name }}</td>
                        <td class="p-3">TZS {{ number_format($debt->original_amount, 2) }}</td><td class="p-3 font-semibold text-red-700">TZS {{ number_format($debt->balance, 2) }}</td>
                        <td class="p-3">{{ $debt->debt_date->format('d/m/Y') }}</td><td class="p-3">{{ $debt->due_date?->format('d/m/Y') ?? '-' }}</td>
                        <td class="p-3"><span class="px-2 py-1 rounded text-xs {{ $overdue ? 'bg-red-100 text-red-700' : 'bg-gray-100' }}">{{ $overdue ? 'Overdue' : ucfirst($debt->status) }}</span></td>
                        <td class="p-3 text-center"><a href="{{ route('debts.show', $debt) }}" class="bg-indigo-600 text-white px-3 py-1.5 rounded">View</a></td>
                    </tr>
                @empty<tr><td colspan="9" class="p-8 text-center text-gray-500">Hakuna deni lililoandikwa.</td></tr>@endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $debts->links() }}</div>
</div>
@endsection
