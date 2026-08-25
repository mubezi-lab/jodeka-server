@extends('layouts.admin')

@section('title', 'Create Hotspot Voucher')

@section('content')
    <div class="max-w-3xl mx-auto bg-white rounded-xl shadow p-6">

        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold">
                Create Hotspot Voucher
            </h2>

            <a href="{{ route('hotspot-vouchers.index') }}" class="text-gray-600 hover:underline">
                Back
            </a>
        </div>

        {{-- Error message --}}
        @if(session('error'))
            <div class="mb-5 bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        {{-- Validation errors --}}
        @if($errors->any())
            <div class="mb-5 bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('hotspot-vouchers.store') }}">

            @csrf

            {{-- Hotspot Package --}}
            <div class="mb-5">
                <label class="block text-sm font-medium mb-2">
                    Hotspot Package
                </label>

                <select name="hotspot_profile_id" class="w-full border rounded-lg px-3 py-2" required>

                    <option value="">
                        Select package
                    </option>

                    @foreach($profiles as $profile)
                        <option value="{{ $profile->id }}" {{ old('hotspot_profile_id') == $profile->id ? 'selected' : '' }}>

                            {{ $profile->name }}
                            - TZS {{ number_format($profile->price) }}
                            - {{ $profile->validity_value }}
                            {{ ucfirst($profile->validity_unit) }}

                        </option>
                    @endforeach

                </select>
            </div>

            {{-- Customer / Comment --}}
            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">
                    Customer / Comment
                </label>

                <input type="text" name="comment" value="{{ old('comment') }}" class="w-full border rounded-lg px-3 py-2"
                    placeholder="Optional customer name or note">
            </div>

            {{-- Generate Button --}}
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg">
                    Generate Voucher
                </button>
            </div>

        </form>

    </div>
@endsection