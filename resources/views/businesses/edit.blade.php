<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Business
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            <form method="POST" action="{{ route('businesses.update', $business->id) }}" 
                  class="bg-white p-6 shadow rounded">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block">Name</label>
                    <input type="text" name="name" value="{{ $business->name }}"
                           class="w-full border rounded p-2" required>
                </div>

                <div class="mb-4">
                    <label class="block">Type</label>
                    <input type="text" name="type" value="{{ $business->type }}"
                           class="w-full border rounded p-2">
                </div>

                <div class="mb-4">
                    <label class="block">Description</label>
                    <textarea name="description" 
                              class="w-full border rounded p-2">{{ $business->description }}</textarea>
                </div>

                <button class="bg-yellow-500 text-white px-4 py-2 rounded">
                    Update
                </button>
            </form>

        </div>
    </div>
</x-app-layout>