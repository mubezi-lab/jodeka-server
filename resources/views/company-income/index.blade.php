@extends('layouts.admin')

@section('title', 'Company Incomes')

@section('buttons')

    <a href="{{ route('company-incomes.create') }}" class="bg-green-600 hover:bg-green-700
              text-white px-4 py-2 rounded-lg shadow-sm transition">

        + Add Income

    </a>

@endsection

@section('content')

    <div class="bg-white shadow rounded-xl overflow-x-auto">

        <table class="w-full min-w-[1000px]">

            <thead class="bg-gray-100">

                <tr>

                    <th class="p-4 text-left">#</th>

                    <th class="p-4 text-left">Business</th>

                    <th class="p-4 text-left">Category</th>

                    <th class="p-4 text-left">Amount</th>

                    <th class="p-4 text-left">Payment</th>

                    <th class="p-4 text-left">Date</th>

                    <th class="p-4 text-left">Created By</th>

                    <th class="p-4 text-left">Actions</th>

                </tr>

            </thead>

            <tbody>

                @forelse($incomes as $income)

                    <tr class="border-t hover:bg-gray-50 transition">

                        <td class="p-4">

                            {{ $loop->iteration }}

                        </td>

                        <td class="p-4 whitespace-nowrap">

                            {{ $income->business->name ?? '-' }}

                        </td>

                        <td class="p-4 whitespace-nowrap">

                            {{ $income->category }}

                        </td>

                        <td class="p-4 text-green-600 font-semibold whitespace-nowrap">

                            {{ number_format($income->amount, 2) }}

                        </td>

                        <td class="p-4 whitespace-nowrap">

                            {{ ucfirst(str_replace('_', ' ', $income->payment_method)) }}

                        </td>

                        <td class="p-4 whitespace-nowrap">

                            {{ $income->transaction_date }}

                        </td>

                        <td class="p-4 whitespace-nowrap">

                            {{ $income->creator->name ?? '-' }}

                        </td>

                        <td class="p-4">

                            <div class="flex items-center gap-2">

                                {{-- EDIT --}}
                                <a href="{{ route('company-incomes.edit', $income->id) }}" class="bg-yellow-500 hover:bg-yellow-600
                                              text-white px-3 py-1.5 rounded-md
                                              text-sm font-medium shadow-sm transition">

                                    Edit

                                </a>

                                {{-- DELETE --}}
                                <form action="{{ route('company-incomes.destroy', $income->id) }}" method="POST">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" onclick="return confirm('Delete this income?')" class="bg-red-500 hover:bg-red-600
                                                   text-white px-3 py-1.5 rounded-md
                                                   text-sm font-medium shadow-sm transition">

                                        Delete

                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="8" class="p-6 text-center text-gray-500">

                            No incomes found

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

@endsection