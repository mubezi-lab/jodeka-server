<x-app-layout>

    <div class="py-8">

        <h1 class="text-xl font-semibold mb-6 px-6">Add User</h1>

        <div class="max-w-xl mx-auto">
            <div class="bg-white shadow rounded-xl p-6">

                {{-- ERRORS --}}
                @if ($errors->any())
                    <div class="bg-red-100 text-red-600 p-3 rounded mb-4 text-sm">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('users.store') }}">
                    @csrf

                    {{-- NAME --}}
                    <div class="mb-4">
                        <label class="block text-sm mb-1">Name</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="w-full border rounded-lg px-3 py-2">
                    </div>

                    {{-- EMAIL --}}
                    <div class="mb-4">
                        <label class="block text-sm mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="w-full border rounded-lg px-3 py-2">
                    </div>

                    {{-- PASSWORD --}}
                    <div class="mb-4">
                        <label class="block text-sm mb-1">Password</label>
                        <input type="password" name="password" class="w-full border rounded-lg px-3 py-2">

                        @error('password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- ROLE --}}
                    <div class="mb-4">
                        <label class="block text-sm mb-1">Role</label>
                        <select name="role" class="w-full border rounded-lg px-3 py-2">
                            <option value="">-- Select Role --</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- BUSINESS --}}
                    <div class="mb-6">
                        <label class="block text-sm mb-1">Business</label>
                        <select name="business_id" class="w-full border rounded-lg px-3 py-2">
                            <option value="">-- Select Business --</option>
                            @foreach ($businesses as $business)
                                <option value="{{ $business->id }}">
                                    {{ $business->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button class="bg-blue-600 text-white px-5 py-2 rounded-lg">
                        Save User
                    </button>

                </form>

            </div>
        </div>

    </div>

</x-app-layout>