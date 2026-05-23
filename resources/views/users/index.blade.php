<x-app-layout>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6">

        <h1 class="text-2xl font-semibold mb-4">Users</h1>

        {{-- SUCCESS MESSAGE --}}
        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        {{-- ADD BUTTON --}}
        <a href="{{ route('users.create') }}"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg mb-4 inline-block shadow-sm transition">

            + Add User

        </a>

        {{-- TABLE --}}
        <div class="bg-white shadow rounded-xl overflow-x-auto">

            <table class="w-full min-w-[900px]">

                <thead class="bg-gray-100">

                    <tr>

                        <th class="p-3 text-left">#</th>

                        <th class="p-3 text-left">Name</th>

                        <th class="p-3 text-left">Email</th>

                        <th class="p-3 text-left">Role</th>

                        <th class="p-3 text-left">
                            Business
                        </th>

                        <th class="p-3 text-left">Actions</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach ($users as $index => $user)

                        <tr class="border-t hover:bg-gray-50 transition">

                            <td class="p-3">
                                {{ $index + 1 }}
                            </td>

                            <td class="p-3 whitespace-nowrap">
                                {{ $user->name }}
                            </td>

                            <td class="p-3 whitespace-nowrap">
                                {{ $user->email }}
                            </td>

                            <td class="p-3 whitespace-nowrap">
                                {{ $user->role->name ?? '-' }}
                            </td>

                            <td class="p-3 whitespace-nowrap">
                                {{ $user->business->name ?? '-' }}
                            </td>

                            <td class="p-3">

                                <div class="flex items-center gap-2 whitespace-nowrap">

                                    {{-- EDIT --}}
                                    <a href="{{ route('users.edit', $user->id) }}" class="bg-yellow-500 hover:bg-yellow-600
                                                          text-white px-3 py-1.5 rounded-md
                                                          text-sm font-medium shadow-sm transition">

                                        Edit

                                    </a>

                                    {{-- DELETE --}}
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                            onclick="return confirm('Are you sure you want to delete this user?')" class="bg-red-500 hover:bg-red-600
                                                               text-white px-3 py-1.5 rounded-md
                                                               text-sm font-medium shadow-sm transition">

                                            Delete

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

</x-app-layout>