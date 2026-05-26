<x-app-layout>

    <x-slot name="header">

        <h2 class="font-semibold text-xl text-gray-800 leading-tight">

            Add Entry

        </h2>

    </x-slot>

    <div class="py-6">

        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow rounded-lg p-6">

                @if(session('success'))

                    <div class="mb-5 bg-green-100 text-green-700 px-4 py-3 rounded-lg">

                        {{ session('success') }}

                    </div>

                @endif

                <form method="POST" action="{{
    strtolower($toilet->name) === 'stendi'
    ? route('stendi.entry.store')
    : route('sokoni.entry.store')
                    }}" class="space-y-5">
                    @csrf

                    <!-- STENDI ONLY -->
                    @if(strtolower($toilet->name) === 'stendi')

                        <div>

                            <label class="block mb-2 font-semibold text-gray-700">

                                Opening Balance (Morning Change)

                            </label>

                            <input type="number" step="0.01" name="opening_balance" value="{{ old('opening_balance') }}"
                                required class="w-full border-gray-300 rounded-lg" placeholder="Enter opening balance">

                            @error('opening_balance')

                                <p class="text-red-500 text-sm mt-1">

                                    {{ $message }}

                                </p>

                            @enderror

                        </div>

                    @endif

                    <!-- EXPENSES -->
                    <div>

                        <label class="block mb-2 font-semibold text-gray-700">

                            Expenses Amount

                        </label>

                        <input type="number" step="0.01" name="expenses" value="{{ old('expenses') }}" required
                            class="w-full border-gray-300 rounded-lg" placeholder="Enter total expenses">

                        @error('expenses')

                            <p class="text-red-500 text-sm mt-1">

                                {{ $message }}

                            </p>

                        @enderror

                    </div>

                    <!-- CLOSING BALANCE -->
                    <div>

                        <label class="block mb-2 font-semibold text-gray-700">

                            Closing Balance (Cash Remaining)

                        </label>

                        <input type="number" step="0.01" name="closing_balance" value="{{ old('closing_balance') }}"
                            required class="w-full border-gray-300 rounded-lg" placeholder="Enter remaining cash">

                        @error('closing_balance')

                            <p class="text-red-500 text-sm mt-1">

                                {{ $message }}

                            </p>

                        @enderror

                    </div>

                    <!-- NOTE -->
                    <div>

                        <label class="block mb-2 font-semibold text-gray-700">

                            Note (Optional)

                        </label>

                        <textarea name="note" rows="4" class="w-full border-gray-300 rounded-lg"
                            placeholder="Write note here...">{{ old('note') }}</textarea>

                        @error('note')

                            <p class="text-red-500 text-sm mt-1">

                                {{ $message }}

                            </p>

                        @enderror

                    </div>

                    <!-- SUBMIT -->
                    <div class="pt-2">

                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                            Save Entry
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</x-app-layout>