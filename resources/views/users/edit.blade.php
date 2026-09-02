<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit User
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-xl mx-auto">

            <div class="bg-white p-6 rounded-xl shadow">

                <form method="POST" action="{{ route('users.update', $user->id) }}">
                    @csrf
                    @method('PUT')

                    <!-- NAME -->
                    <div class="mb-4">
                        <label class="block text-sm mb-1">Name</label>
                        <input type="text" name="name"
                               value="{{ $user->name }}"
                               class="w-full border rounded p-2" required>
                    </div>

                    <!-- EMAIL -->
                    <div class="mb-4">
                        <label class="block text-sm mb-1">Email</label>
                        <input type="email" name="email"
                               value="{{ $user->email }}"
                               class="w-full border rounded p-2" required>
                    </div>

                    <!-- PASSWORD -->
                    <div class="mb-4">
                        <label class="block text-sm mb-1">
                            Password (leave blank to keep current)
                        </label>
                        <input type="password" name="password"
                               class="w-full border rounded p-2">
                    </div>

                    <!-- ROLE -->
                    <div class="mb-4">
                        <label class="block text-sm mb-1">Role</label>
                        <select name="role" class="w-full border rounded p-2" required>

                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @selected(old('role', $user->role_id) == $role->id)>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- BUSINESSES -->
                    <div class="mb-4">
                        <label class="block text-sm mb-1">Businesses / Branches</label>
                        @php
                            $selectedBusinesses = old(
                                'business_ids',
                                $user->businesses->pluck('id')->all() ?: array_filter([$user->business_id])
                            );
                        @endphp
                        <div class="border rounded p-3 space-y-2">
                            @foreach($businesses as $business)
                                <label class="flex items-center gap-2">
                                    <input type="checkbox"
                                           name="business_ids[]"
                                           value="{{ $business->id }}"
                                           @checked(in_array($business->id, $selectedBusinesses))
                                           class="rounded border-gray-300">
                                    <span>{{ $business->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            The first selected branch is used as primary by older modules.
                        </p>
                    </div>

                    <!-- BUTTON -->
                    <div class="mt-6">
                        <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                            Update User
                        </button>
                    </div>

                </form>

            </div>

        </div>
    </div>

</x-app-layout>
