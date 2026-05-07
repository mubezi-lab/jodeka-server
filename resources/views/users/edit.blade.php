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

                            <option value="1" {{ $user->role_id == 1 ? 'selected' : '' }}>Admin</option>
                            <option value="2" {{ $user->role_id == 2 ? 'selected' : '' }}>Manager</option>
                            <option value="3" {{ $user->role_id == 3 ? 'selected' : '' }}>Employee</option>

                        </select>
                    </div>

                    <!-- BUSINESS -->
                    <div class="mb-4">
                        <label class="block text-sm mb-1">Business</label>
                        <select name="business_id" class="w-full border rounded p-2">

                            <option value="">-- Select Business --</option>

                            @foreach($businesses as $business)
                                <option value="{{ $business->id }}"
                                    {{ $user->business_id == $business->id ? 'selected' : '' }}>
                                    {{ $business->name }}
                                </option>
                            @endforeach

                        </select>
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