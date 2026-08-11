@extends('layouts.admin')

@section('title', 'Edit Hotspot Profile')

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow p-6">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold">
            Edit Hotspot Profile
        </h2>

        <a href="{{ route('hotspot-profiles.index') }}"
           class="text-gray-600 hover:text-gray-900">
            Back
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-4 bg-red-100 text-red-800 px-4 py-3 rounded">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('hotspot-profiles.update', $hotspotProfile) }}"
          method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Router
            </label>

            <input type="text"
                   value="{{ $hotspotProfile->router->name ?? '-' }}"
                   disabled
                   class="w-full rounded border-gray-300 bg-gray-100">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                MikroTik Profile
            </label>

            <input type="text"
                   value="{{ $hotspotProfile->mikrotik_profile }}"
                   disabled
                   class="w-full rounded border-gray-300 bg-gray-100">
        </div>

        <div class="mb-4">
            <label for="name"
                   class="block text-sm font-medium text-gray-700 mb-1">
                Display Name
            </label>

            <input type="text"
                   id="name"
                   name="name"
                   value="{{ old('name', $hotspotProfile->name) }}"
                   required
                   class="w-full rounded border-gray-300">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="price"
                       class="block text-sm font-medium text-gray-700 mb-1">
                    Price
                </label>

                <input type="number"
                       id="price"
                       name="price"
                       min="0"
                       step="1"
                       value="{{ old('price', $hotspotProfile->price) }}"
                       required
                       class="w-full rounded border-gray-300">
            </div>

            <div>
                <label for="validity_value"
                    class="block text-sm font-medium text-gray-700 mb-1">
                    Validity
                </label>

                <input type="number"
                    id="validity_value"
                    name="validity_value"
                    min="1"
                    value="{{ old('validity_value', $hotspotProfile->validity_value) }}"
                    required
                    class="w-full rounded border-gray-300">
            </div>

            <div>
                <label for="validity_unit"
                    class="block text-sm font-medium text-gray-700 mb-1">
                    Unit
                </label>

                <select id="validity_unit"
                        name="validity_unit"
                        class="w-full rounded border-gray-300">

                    <option value="minutes" @selected(old('validity_unit', $hotspotProfile->validity_unit) == 'minutes')>
                        Minutes
                    </option>

                    <option value="hours" @selected(old('validity_unit', $hotspotProfile->validity_unit) == 'hours')>
                        Hours
                    </option>

                    <option value="days" @selected(old('validity_unit', $hotspotProfile->validity_unit) == 'days')>
                        Days
                    </option>

                    <option value="weeks" @selected(old('validity_unit', $hotspotProfile->validity_unit) == 'weeks')>
                        Weeks
                    </option>

                    <option value="months" @selected(old('validity_unit', $hotspotProfile->validity_unit) == 'months')>
                        Months
                    </option>

                </select>
            </div>
        </div>

        <div class="mb-4">
            <label for="voucher_prefix"
                class="block text-sm font-medium text-gray-700 mb-1">
                Voucher Prefix
            </label>

            <input type="text"
                id="voucher_prefix"
                name="voucher_prefix"
                value="{{ old('voucher_prefix', $hotspotProfile->voucher_prefix) }}"
                placeholder="Mfano: JDK2"
                required
                class="w-full rounded border-gray-300">
        </div>

        <div class="mb-4">
            <label for="description"
                   class="block text-sm font-medium text-gray-700 mb-1">
                Description
            </label>

            <textarea id="description"
                      name="description"
                      rows="4"
                      class="w-full rounded border-gray-300">{{ old('description', $hotspotProfile->description) }}</textarea>
        </div>

        <div class="mb-6">
            <label class="inline-flex items-center">
                <input type="checkbox"
                       name="enabled"
                       value="1"
                       @checked(old('enabled', $hotspotProfile->enabled))
                       class="rounded border-gray-300 text-blue-600">

                <span class="ml-2 text-sm text-gray-700">
                    Enabled
                </span>
            </label>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded">
                Save Changes
            </button>
        </div>
    </form>

</div>
@endsection