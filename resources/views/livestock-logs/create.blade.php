<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">
            Add Livestock Record
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto">

            {{-- Errors --}}
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>- {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white p-6 rounded shadow">

                <form action="{{ route('livestock-logs.store') }}" method="POST">
                    @csrf

                    {{-- LIVESTOCK --}}
                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Livestock Batch</label>

                        <select name="livestock_id" class="w-full border rounded p-2">
                            @foreach($livestocks as $item)
                                <option value="{{ $item->id }}" {{ (old('livestock_id', $selectedLivestock ?? '') == $item->id) ? 'selected' : '' }}>
                                    {{ $item->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- TYPE --}}
                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Type</label>

                        <select name="type" id="type" class="w-full border rounded p-2">
                            <option value="expense" {{ old('type', $type ?? 'expense') == 'expense' ? 'selected' : '' }}>
                                Expense
                            </option>

                            <option value="mortality" {{ old('type', $type ?? '') == 'mortality' ? 'selected' : '' }}>
                                Mortality
                            </option>
                        </select>
                    </div>

                    {{-- CATEGORY --}}
                    <div class="mb-4" id="categoryBox">
                        <label class="block mb-1 font-medium">Category</label>

                        <select name="category" class="w-full border rounded p-2">

                            <option value="">Select Category</option>

                            <option value="chicks" {{ old('category') == 'chicks' ? 'selected' : '' }}>
                                Chicks
                            </option>

                            <option value="feed" {{ old('category') == 'feed' ? 'selected' : '' }}>
                                Feed
                            </option>

                            <option value="vaccine" {{ old('category') == 'vaccine' ? 'selected' : '' }}>
                                Vaccine
                            </option>

                            <option value="medicine" {{ old('category') == 'medicine' ? 'selected' : '' }}>
                                Medicine
                            </option>

                        </select>
                    </div>

                    {{-- QUANTITY --}}
                    <div class="mb-4" id="quantityBox">
                        <label class="block mb-1 font-medium">Quantity</label>

                        <input type="number" name="quantity" value="{{ old('quantity') }}"
                            class="w-full border rounded p-2">
                    </div>

                    {{-- AMOUNT --}}
                    <div class="mb-4" id="amountBox">
                        <label class="block mb-1 font-medium">Amount (TZS)</label>

                        <input type="number" name="amount" value="{{ old('amount') }}"
                            class="w-full border rounded p-2">
                    </div>

                    {{-- DATE --}}
                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Date</label>

                        <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}"
                            class="w-full border rounded p-2">
                    </div>

                    {{-- NOTE --}}
                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Note</label>

                        <input type="text" name="note" value="{{ old('note') }}" class="w-full border rounded p-2">
                    </div>

                    {{-- BUTTON --}}
                    <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Save Record
                    </button>

                </form>

            </div>
        </div>
    </div>

    {{-- 🔥 SMART UI --}}
    <script>
        const type = document.getElementById('type');
        const amountBox = document.getElementById('amountBox');
        const quantityBox = document.getElementById('quantityBox');
        const categoryBox = document.getElementById('categoryBox');

        function toggleFields() {
            if (type.value === 'mortality') {
                amountBox.style.display = 'none';
                categoryBox.style.display = 'none';
                quantityBox.style.display = 'block';
            } else {
                amountBox.style.display = 'block';
                categoryBox.style.display = 'block';
                quantityBox.style.display = 'block';
            }
        }

        type.addEventListener('change', toggleFields);
        window.onload = toggleFields;
    </script>

</x-app-layout>