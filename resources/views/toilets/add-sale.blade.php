<!DOCTYPE html>
<html>
<head>

    <title>Add Sale</title>

    @vite(['resources/css/app.css'])

</head>

<body class="bg-gray-100">

    <div class="max-w-2xl mx-auto py-10">

        <div class="bg-white p-6 rounded-xl shadow">

            <h1 class="text-3xl font-bold mb-6">
                Add Toilet Sale
            </h1>

            @if(session('success'))

                <div class="bg-green-100 text-green-700 p-3 rounded mb-4">

                    {{ session('success') }}

                </div>

            @endif

            <form
                action="{{ route('toilet.store.sale') }}"
                method="POST"
            >
                @csrf

                <div class="mb-4">

                    <label class="block mb-2 font-semibold">

                        Amount

                    </label>

                    <input
                        type="number"
                        name="amount"
                        class="w-full border rounded-lg p-3"
                        required
                    >

                </div>

                <div class="mb-4">

                    <label class="block mb-2 font-semibold">

                        Customers

                    </label>

                    <input
                        type="number"
                        name="customers"
                        value="1"
                        class="w-full border rounded-lg p-3"
                        required
                    >

                </div>

                <div class="mb-4">

                    <label class="block mb-2 font-semibold">

                        Note

                    </label>

                    <textarea
                        name="note"
                        class="w-full border rounded-lg p-3"
                    ></textarea>

                </div>

                <button
                    type="submit"
                    class="bg-blue-600 text-white px-6 py-3 rounded-lg"
                >
                    Save Sale
                </button>

            </form>

        </div>

    </div>

</body>
</html>