<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Businesses
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Success Message --}}
            @if(session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Add Button --}}
            <a href="{{ route('businesses.create') }}" 
               class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700">
               + Add Business
            </a>

            {{-- Table --}}
            <div class="mt-4 bg-white shadow sm:rounded-lg p-4 overflow-x-auto">
                <table class="w-full border border-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border p-2 text-left">ID</th>
                            <th class="border p-2 text-left">Name</th>
                            <th class="border p-2 text-left">Type</th>
                            <th class="border p-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($businesses as $business)
                            <tr class="hover:bg-gray-50">
                                <td class="border p-2">{{ $business->id }}</td>
                                <td class="border p-2">{{ $business->name }}</td>
                                <td class="border p-2">{{ $business->type }}</td>
                                <td class="border p-2 text-center space-x-2">

                                    {{-- Edit --}}
                                    <a href="{{ route('businesses.edit', $business->id) }}" 
                                       class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                                        Edit
                                    </a>

                                    {{-- Delete --}}
                                    <form action="{{ route('businesses.destroy', $business->id) }}" 
                                          method="POST" 
                                          class="inline-block"
                                          onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')

                                        <button class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                            Delete
                                        </button>
                                    </form>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center p-4 text-gray-500">
                                    No businesses found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>