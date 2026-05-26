<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Toilet Attendant</title>

    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100">

    <div class="min-h-screen flex">

        <!-- SIDEBAR -->
        <aside class="w-64 bg-blue-900 text-white min-h-screen">

            <div class="p-5 text-2xl font-bold border-b border-blue-800">

                TOILET PANEL

            </div>

            <nav class="p-4 space-y-2">

                <a href="/toilet-attendant"
                   class="block px-4 py-3 rounded hover:bg-blue-800">

                    Dashboard

                </a>

            </nav>

        </aside>

        <!-- CONTENT -->
        <main class="flex-1 p-6">

            <!-- TOP -->
            <div class="bg-white shadow rounded-xl p-4 mb-6 flex justify-between">

                <h1 class="text-xl font-bold">

                    Toilet Attendant Dashboard

                </h1>

                <div class="flex items-center gap-4">

                    <span class="font-medium">

                        {{ auth()->user()->name }}

                    </span>

                    <form method="POST"
                          action="{{ route('logout') }}">

                        @csrf

                        <button class="bg-red-500 text-white px-4 py-2 rounded">

                            Logout

                        </button>

                    </form>

                </div>

            </div>

            <!-- PAGE CONTENT -->
            @yield('content')

        </main>

    </div>

</body>

</html>