<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Add Livestock
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            {{-- Errors --}}
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow rounded-lg p-6">

                <form action="{{ route('livestocks.store') }}" method="POST">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Name --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Batch Name
                            </label>
                            <input type="text" name="name"
                                   value="{{ old('name') }}"
                                   class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200">
                        </div>

                        {{-- Type --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Type
                            </label>
                            <input type="text" name="type"
                                   value="{{ old('type') }}"
                                   placeholder="e.g kuku"
                                   class="mt-1 w-full border-gray-300 rounded-lg shadow-sm">
                        </div>

                        {{-- Category --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Category
                            </label>
                            <select name="category"
                                    class="mt-1 w-full border-gray-300 rounded-lg shadow-sm">

                                <option value="">Select Category</option>

                                <option value="layers" {{ old('category') == 'layers' ? 'selected' : '' }}>
                                    Layers
                                </option>

                                <option value="broilers" {{ old('category') == 'broilers' ? 'selected' : '' }}>
                                    Broilers
                                </option>

                            </select>
                        </div>

                        {{-- Quantity --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Quantity
                            </label>
                            <input type="number" name="quantity"
                                   value="{{ old('quantity') }}"
                                   min="1"
                                   class="mt-1 w-full border-gray-300 rounded-lg shadow-sm">
                        </div>

                        {{-- Start Date --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Start Date
                            </label>
                            <input type="date" name="start_date"
                                   value="{{ old('start_date') }}"
                                   class="mt-1 w-full border-gray-300 rounded-lg shadow-sm">
                        </div>

                    </div>

                    {{-- Buttons --}}
                    <div class="mt-6 flex justify-end space-x-3">

                        <a href="{{ route('livestocks.index') }}"
                           class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                            Cancel
                        </a>

                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Save Livestock
                        </button>

                    </div>

                </form>

            </div>

        </div>
    </div>
</x-app-layout>