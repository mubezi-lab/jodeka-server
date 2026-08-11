<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Add Network Router
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                <form method="POST" action="{{ route('network-routers.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="block font-medium">Router Name</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="w-full border-gray-300 rounded mt-1" required>
                        @error('name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium">Host / IP Address</label>
                        <input type="text" name="host" value="{{ old('host') }}"
                               class="w-full border-gray-300 rounded mt-1" required>
                        @error('host') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium">API Port</label>
                        <input type="number" name="api_port" value="{{ old('api_port', 8728) }}"
                               class="w-full border-gray-300 rounded mt-1" required>
                        @error('api_port') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium">API Username</label>
                        <input type="text" name="username" value="{{ old('username') }}"
                               class="w-full border-gray-300 rounded mt-1" required>
                        @error('username') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium">API Password</label>
                        <input type="password" name="password"
                               class="w-full border-gray-300 rounded mt-1" required>
                        @error('password') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium">Location</label>
                        <input type="text" name="location" value="{{ old('location') }}"
                               class="w-full border-gray-300 rounded mt-1">
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium">Description</label>
                        <textarea name="description"
                                  class="w-full border-gray-300 rounded mt-1">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-4 flex gap-6">
                        <label>
                            <input type="checkbox" name="use_ssl" value="1">
                            Use SSL
                        </label>

                        <label>
                            <input type="checkbox" name="enabled" value="1" checked>
                            Enabled
                        </label>
                    </div>

                    <div class="flex justify-between">
                        <a href="{{ route('network-routers.index') }}"
                           class="bg-gray-500 text-white px-4 py-2 rounded">
                            Back
                        </a>

                        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                            Save Router
                        </button>
                    </div>

                </form>

            </div>

        </div>
    </div>
</x-app-layout>