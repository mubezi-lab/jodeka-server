<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Add Business
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            <form method="POST" action="{{ route('businesses.store') }}" 
                class="bg-white p-6 shadow rounded border">
                @csrf

                <div class="mb-4">
                    <label class="block">Name</label>
                    <input type="text" name="name" class="w-full border rounded p-2" required>
                </div>

                <div class="mb-4">
                    <label class="block">Type</label>
                    <input type="text" name="type" class="w-full border rounded p-2">
                </div>

                <div class="mb-4">
                    <label class="block">Description</label>
                    <textarea name="description" class="w-full border rounded p-2"></textarea>
                </div>

                <div class="mt-4">
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded shadow hover:bg-green-700">
                        Save Business
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>