<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Network Management
            </h2>

            <a href="{{ route('network-routers.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                <i class="fa fa-plus"></i> Add Router
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 bg-green-100 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 text-red-800 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-3 text-left">Name</th>
                            <th class="p-3 text-left">Host</th>
                            <th class="p-3 text-left">Port</th>
                            <th class="p-3 text-left">Location</th>
                            <th class="p-3 text-left">Status</th>
                            <th class="p-3 text-right">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($routers as $router)
                            <tr class="border-t">
                                <td class="p-3">{{ $router->name }}</td>
                                <td class="p-3">{{ $router->host }}</td>
                                <td class="p-3">{{ $router->api_port }}</td>
                                <td class="p-3">{{ $router->location ?? '-' }}</td>
                                <td class="p-3">
                                    @if($router->enabled)
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
                                    <form action="{{ route('network-routers.test', $router) }}"
                                        method="POST"
                                        class="inline-block mr-3">
                                        @csrf

                                        <button type="submit"
                                                class="text-green-600 hover:underline">
                                            Test
                                        </button>
                                    </form>

                                    <form action="{{ route('network-routers.sync-profiles', $router) }}"
                                        method="POST"
                                        class="inline-block mr-3">
                                        @csrf

                                        <button type="submit"
                                                class="text-purple-600 hover:underline">
                                            Sync Profiles
                                        </button>
                                    </form>

                                    <a href="{{ route('network-routers.edit', $router) }}"
                                       class="text-blue-600 hover:underline">
                                        Edit
                                    </a>

                                    <form action="{{ route('network-routers.destroy', $router) }}"
                                          method="POST"
                                          class="inline-block ml-3"
                                          onsubmit="return confirm('Delete this router?')">
                                        @csrf
                                        @method('DELETE')

                                        <button class="text-red-600 hover:underline">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-5 text-center text-gray-500">
                                    No routers found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>