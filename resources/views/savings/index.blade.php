@extends('layouts.admin')

@section('title', 'Savings')

@section('content')

    <div class="space-y-6">

        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row
                    sm:items-center sm:justify-between gap-4">

            <div>

                <h1 class="text-2xl font-bold text-gray-800">

                    Savings Management

                </h1>

                <p class="text-gray-500 text-sm mt-1">

                    Manage all savings accounts

                </p>

            </div>

            <a href="{{ route('savings.create') }}" class="bg-indigo-600 hover:bg-indigo-700
                      text-white px-5 py-2 rounded-lg
                      text-sm transition">

                + Add Saving

            </a>

        </div>

        {{-- SUCCESS --}}
        @if(session('success'))

            <div class="bg-green-100 text-green-700
                            px-4 py-3 rounded-lg">

                {{ session('success') }}

            </div>

        @endif

        {{-- ANALYTICS --}}
        <div class="grid grid-cols-1
                    md:grid-cols-3 gap-4">

            {{-- TOTAL SAVINGS --}}
            <div class="bg-white shadow rounded-2xl p-5">

                <p class="text-sm text-gray-500">

                    Total Savings

                </p>

                <h2 class="text-2xl font-bold mt-2 text-indigo-600">

                    TZS {{ number_format($totalSavings, 2) }}

                </h2>

            </div>

            {{-- ACTIVE --}}
            <div class="bg-white shadow rounded-2xl p-5">

                <p class="text-sm text-gray-500">

                    Active Savings

                </p>

                <h2 class="text-2xl font-bold mt-2 text-green-600">

                    {{ $activeSavings }}

                </h2>

            </div>

            {{-- COMPLETED --}}
            <div class="bg-white shadow rounded-2xl p-5">

                <p class="text-sm text-gray-500">

                    Completed Savings

                </p>

                <h2 class="text-2xl font-bold mt-2 text-blue-600">

                    {{ $completedSavings }}

                </h2>

            </div>

        </div>

        {{-- TABLE --}}
        <div class="bg-white shadow rounded-2xl overflow-hidden">

            <div class="overflow-x-auto">

                <table class="w-full min-w-[1000px]">

                    <thead class="bg-gray-100">

                        <tr>

                            <th class="p-4 text-left text-sm">

                                #

                            </th>

                            <th class="p-4 text-left text-sm">

                                Saving Name

                            </th>

                            <th class="p-4 text-left text-sm">

                                Type

                            </th>

                            <th class="p-4 text-left text-sm">

                                Business

                            </th>

                            <th class="p-4 text-left text-sm">

                                Balance

                            </th>

                            <th class="p-4 text-left text-sm">

                                Target

                            </th>

                            <th class="p-4 text-left text-sm">

                                Status

                            </th>

                            <th class="p-4 text-left text-sm">

                                Actions

                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($savings as $index => $saving)

                            <tr class="border-t">

                                <td class="p-4">

                                    {{ $index + 1 }}

                                </td>

                                <td class="p-4 font-medium">

                                    {{ $saving->name }}

                                </td>

                                <td class="p-4">

                                    <span class="capitalize">

                                        {{ $saving->type }}

                                    </span>

                                </td>

                                <td class="p-4">

                                    {{ $saving->business->name ?? '-' }}

                                </td>

                                <td class="p-4 font-semibold text-green-600">

                                    TZS {{ number_format($saving->balance, 2) }}

                                </td>

                                <td class="p-4">

                                    @if($saving->target_amount)

                                        TZS {{ number_format($saving->target_amount, 2) }}

                                    @else

                                        -

                                    @endif

                                </td>

                                <td class="p-4">

                                    @if($saving->status == 'active')

                                        <span class="bg-green-100 text-green-700
                                                             px-3 py-1 rounded-full text-xs">

                                            Active

                                        </span>

                                    @elseif($saving->status == 'completed')

                                        <span class="bg-blue-100 text-blue-700
                                                             px-3 py-1 rounded-full text-xs">

                                            Completed

                                        </span>

                                    @else

                                        <span class="bg-red-100 text-red-700
                                                             px-3 py-1 rounded-full text-xs">

                                            Closed

                                        </span>

                                    @endif

                                </td>

                                <td class="p-4">

                                    <div class="flex items-center gap-2">

                                        {{-- VIEW --}}
                                        <a href="{{ route('savings.show', $saving->id) }}" class="bg-indigo-500 hover:bg-indigo-600
                                                      text-white px-3 py-1 rounded-md text-sm">

                                            View

                                        </a>

                                        {{-- EDIT --}}
                                        <a href="{{ route('savings.edit', $saving->id) }}" class="bg-yellow-500 hover:bg-yellow-600
                                                      text-white px-3 py-1 rounded-md text-sm">

                                            Edit

                                        </a>

                                        {{-- DELETE --}}
                                        <form action="{{ route('savings.destroy', $saving->id) }}" method="POST">

                                            @csrf
                                            @method('DELETE')

                                            <button onclick="return confirm('Delete this saving?')" class="bg-red-500 hover:bg-red-600
                                                           text-white px-3 py-1 rounded-md text-sm">

                                                Delete

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="8" class="p-6 text-center text-gray-500">

                                    No savings found

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

@endsection