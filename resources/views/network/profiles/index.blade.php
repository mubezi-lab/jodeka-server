@extends('layouts.admin')

@section('title', 'Hotspot Profiles')

@section('content')
    <div class="bg-white rounded-xl shadow p-6">

        <h2 class="text-xl font-bold mb-4">
            Hotspot Profiles
        </h2>

        <table class="w-full border-collapse">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-left">Router</th>
                    <th class="p-3 text-left">Profile Name</th>
                    <th class="p-3 text-left">MikroTik Profile</th>
                    <th class="p-3 text-left">Price</th>
                    <th class="p-3 text-left">Validity</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-right">Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($profiles as $profile)
                    <tr class="border-t">

                        <td class="p-3">
                            {{ $profile->router->name ?? '-' }}
                        </td>

                        <td class="p-3">
                            {{ $profile->name }}
                        </td>

                        <td class="p-3">
                            {{ $profile->mikrotik_profile }}
                        </td>

                        <td class="p-3">
                            {{ number_format($profile->price) }}
                        </td>

                        <td class="p-3">
                            {{ $profile->validity_value }} {{ ucfirst($profile->validity_unit) }}
                        </td>

                        <td class="p-3">
                            @if($profile->enabled)
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-sm">
                                    Enabled
                                </span>
                            @else
                                <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-sm">
                                    Disabled
                                </span>
                            @endif
                        </td>

                        <td class="p-3 text-right">
                            <a href="{{ route('hotspot-profiles.edit', $profile) }}" class="text-blue-600 hover:underline">
                                Edit
                            </a>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-5 text-center text-gray-500">
                            No hotspot profiles found. Sync profiles first.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>
@endsection