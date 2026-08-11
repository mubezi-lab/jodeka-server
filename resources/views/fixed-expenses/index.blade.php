@extends('layouts.admin')

@section('title', 'Fixed Expenses')

@section('content')

<div class="space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">
            Fixed Expenses
        </h1>

        <a href="{{ route('fixed-expenses.create') }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
            + Add Fixed Expense
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">

            <table class="w-full text-sm text-left">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-4 py-3">Business</th>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">

                    @forelse($fixedExpenses as $expense)
                        <tr>
                            <td class="px-4 py-3">
                                {{ $expense->business->name ?? 'Personal' }}
                            </td>

                            <td class="px-4 py-3 font-medium">
                                {{ $expense->name }}
                            </td>

                            <td class="px-4 py-3">
                                TZS {{ number_format($expense->default_amount) }}
                            </td>

                            <td class="px-4 py-3">
                                @if($expense->is_active)
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs">
                                        Active
                                    </span>
                                @else
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('fixed-expenses.edit', $expense) }}"
                                   class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded">
                                    Edit
                                </a>

                                <form action="{{ route('fixed-expenses.destroy', $expense) }}"
                                      method="POST"
                                      class="inline-block"
                                      onsubmit="return confirm('Una uhakika unataka kufuta?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                Hakuna fixed expenses bado.
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>

        </div>
    </div>

</div>

@endsection