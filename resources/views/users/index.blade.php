<x-app-layout>

    <div class="max-w-7xl mx-auto py-6 px-6">

        <h1 class="text-2xl font-semibold mb-4">Users</h1>

        {{-- SUCCESS MESSAGE --}}
        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <a href="{{ route('users.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded mb-4 inline-block">
            + Add User
        </a>

        <div class="bg-white shadow rounded-xl overflow-hidden">

            <table class="w-full">

                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3">#</th>
                        <th class="p-3">Name</th>
                        <th class="p-3">Email</th>
                        <th class="p-3">Role</th>
                        <th class="p-3">Business</th>
                        <th class="p-3">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($users as $index => $user)
                        <tr class="border-t">

                            <td class="p-3">{{ $index + 1 }}</td>
                            <td class="p-3">{{ $user->name }}</td>
                            <td class="p-3">{{ $user->email }}</td>
                            <td class="p-3">{{ $user->role->name ?? '-' }}</td>
                            <td class="p-3">{{ $user->business->name ?? '-' }}</td>

                            <td class="p-3">

                                <div class="flex items-center gap-2">

                                    {{-- EDIT --}}
                                    <a href="{{ route('users.edit', $user->id) }}"
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-md text-sm font-medium shadow-sm">
                                        Edit
                                    </a>

                                    {{-- DELETE --}}
                                    <form action="{{ route('users.destroy', $user->id) }}"
                                        method="POST">
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-sm font-medium shadow-sm">
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