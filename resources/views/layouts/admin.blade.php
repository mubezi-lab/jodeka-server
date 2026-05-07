<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100">

    <div class="flex min-h-screen">

        <!-- SIDEBAR -->
        <aside class="w-64 bg-gray-900 text-gray-200">

            <div class="p-4 text-lg font-bold border-b border-gray-800">
                JODEKA
            </div>

            <nav class="p-4 space-y-2 text-sm">

                <a href="{{ route('dashboard') }}" class="block px-4 py-2 rounded transition
               {{ request()->routeIs('dashboard') ? 'bg-gray-800 text-white' : 'hover:bg-gray-800' }}">
                    Dashboard
                </a>

                <a href="{{ route('businesses.index') }}" class="block px-4 py-2 rounded transition
               {{ request()->routeIs('businesses.*') ? 'bg-gray-800 text-white' : 'hover:bg-gray-800' }}">
                    Businesses
                </a>

                <a href="{{ route('products.index') }}" class="block px-4 py-2 rounded transition
               {{ request()->routeIs('products.*') ? 'bg-gray-800 text-white' : 'hover:bg-gray-800' }}">
                    Products
                </a>

                <a href="{{ route('stocks.index') }}" class="block px-4 py-2 rounded transition
               {{ request()->routeIs('stocks.*') ? 'bg-gray-800 text-white' : 'hover:bg-gray-800' }}">
                    Stock
                </a>

                <a href="{{ route('purchases.index') }}" class="block px-4 py-2 rounded transition
               {{ request()->routeIs('purchases.*') ? 'bg-gray-800 text-white' : 'hover:bg-gray-800' }}">
                    Purchases
                </a>

                <a href="{{ route('livestocks.index') }}" class="block px-4 py-2 rounded transition
               {{ request()->routeIs('livestocks.*') ? 'bg-gray-800 text-white' : 'hover:bg-gray-800' }}">
                    Livestock
                </a>

            </nav>

        </aside>

        <!-- MAIN -->
        <div class="flex-1 flex flex-col">

            <!-- TOPBAR -->
            <div class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">

                <!-- TITLE -->
                <h1 class="text-lg font-semibold text-gray-800">
                    @yield('title')
                </h1>

                <div class="flex items-center gap-4">

                    <!-- NOTIFICATIONS -->
                    <div class="relative">
                        <button onclick="toggleNotif()" class="relative text-lg">
                            🔔
                            <span class="absolute -top-1 -right-2 bg-red-500 text-white text-xs px-1 rounded-full">
                                3
                            </span>
                        </button>

                        <div id="notifBox"
                            class="hidden absolute right-0 mt-2 w-64 bg-white border rounded shadow z-50">

                            <div class="p-3 text-sm font-semibold border-b">
                                Notifications
                            </div>

                            <div class="p-3 text-sm text-gray-600">
                                No new notifications
                            </div>

                        </div>
                    </div>

                    <!-- USER DROPDOWN (MATCHES x-app-layout STYLE) -->
                    <div class="relative">

                        <button onclick="toggleUserMenu()"
                            class="flex items-center text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none">

                            <span>{{ auth()->user()->name }}</span>

                            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>

                        </button>

                        <div id="userMenu"
                            class="hidden absolute right-0 mt-2 w-40 bg-white border rounded shadow z-50">

                            <a href="{{ route('profile.edit') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Profile
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Logout
                                </button>
                            </form>

                        </div>

                    </div>

                </div>

            </div>

            <!-- CONTENT -->
            <div class="p-6">
                @yield('content')
            </div>

        </div>

    </div>

    <!-- JS -->
    <script>
        function toggleNotif() {
            document.getElementById('notifBox').classList.toggle('hidden');
        }

        function toggleUserMenu() {
            document.getElementById('userMenu').classList.toggle('hidden');
        }
    </script>

</body>

</html>