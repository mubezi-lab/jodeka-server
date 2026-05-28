<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard</title>

    @vite('resources/css/app.css')

</head>

<body class="bg-gray-100 overflow-x-hidden">

    <div class="flex min-h-screen">

        {{-- ========================================= --}}
        {{-- MOBILE OVERLAY --}}
        {{-- ========================================= --}}
        <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden" onclick="toggleSidebar()">
        </div>

        {{-- ========================================= --}}
        {{-- SIDEBAR --}}
        {{-- ========================================= --}}
        <aside id="sidebar" class="fixed lg:static inset-y-0 left-0
                      z-50 w-72 bg-gray-900 text-white
                      transform -translate-x-full lg:translate-x-0
                      transition-transform duration-300
                      overflow-y-auto">

            {{-- LOGO --}}
            <div class="flex items-center justify-between
                        p-5 border-b border-gray-800">

                <h1 class="text-2xl font-bold">

                    JODEKA

                </h1>

                {{-- MOBILE CLOSE --}}
                <button onclick="toggleSidebar()" class="lg:hidden text-2xl">

                    ✕

                </button>

            </div>

            {{-- ========================================= --}}
            {{-- NAVIGATION --}}
            {{-- ========================================= --}}
            <nav class="p-4 space-y-3">

                {{-- ========================================= --}}
                {{-- DASHBOARD --}}
                {{-- ========================================= --}}
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3
                          px-4 py-3 rounded-lg transition

                          {{ request()->routeIs('dashboard')
    ? 'bg-indigo-600 text-white'
    : 'hover:bg-gray-800' }}">

                    <span>🏠</span>

                    <span>Dashboard</span>

                </a>

                {{-- ========================================= --}}
                {{-- BUSINESS MANAGEMENT --}}
                {{-- ========================================= --}}
                <div>

                    <button onclick="toggleMenu('businessMenu', 'businessArrow')" class="w-full flex items-center justify-between
                                   px-4 py-3 rounded-lg
                                   bg-gray-800 hover:bg-gray-700 transition">

                        <span>

                            🏢 Business Management

                        </span>

                        <span id="businessArrow" class="transition-transform duration-300">

                            ▶

                        </span>

                    </button>

                    {{-- HIDDEN BY DEFAULT --}}
                    <div id="businessMenu" class="hidden mt-2 space-y-2 pl-3">

                        {{-- BUSINESSES --}}
                        <a href="{{ route('businesses.index') }}" class="block px-4 py-2 rounded-lg transition

                           {{ request()->routeIs('businesses.*')
    ? 'bg-indigo-600 text-white'
    : 'hover:bg-gray-800' }}">

                            Businesses

                        </a>

                        {{-- USERS --}}
                        <a href="{{ route('users.index') }}" class="block px-4 py-2 rounded-lg transition

                           {{ request()->routeIs('users.*')
    ? 'bg-indigo-600 text-white'
    : 'hover:bg-gray-800' }}">

                            Users

                        </a>

                    </div>

                </div>

                {{-- ========================================= --}}
                {{-- INVENTORY --}}
                {{-- ========================================= --}}
                <div>

                    <button onclick="toggleMenu('inventoryMenu', 'inventoryArrow')" class="w-full flex items-center justify-between
                                   px-4 py-3 rounded-lg
                                   bg-gray-800 hover:bg-gray-700 transition">

                        <span>

                            📦 Inventory

                        </span>

                        <span id="inventoryArrow" class="transition-transform duration-300">

                            ▶

                        </span>

                    </button>

                    <div id="inventoryMenu" class="hidden mt-2 space-y-2 pl-3">

                        {{-- PRODUCTS --}}
                        <a href="{{ route('products.index') }}" class="block px-4 py-2 rounded-lg transition

                           {{ request()->routeIs('products.*')
    ? 'bg-indigo-600 text-white'
    : 'hover:bg-gray-800' }}">

                            Products

                        </a>

                        {{-- STOCK --}}
                        <a href="{{ route('stocks.index') }}" class="block px-4 py-2 rounded-lg transition

                           {{ request()->routeIs('stocks.*')
    ? 'bg-indigo-600 text-white'
    : 'hover:bg-gray-800' }}">

                            Stock

                        </a>

                        {{-- PURCHASES --}}
                        <a href="{{ route('purchases.index') }}" class="block px-4 py-2 rounded-lg transition

                           {{ request()->routeIs('purchases.*')
    ? 'bg-indigo-600 text-white'
    : 'hover:bg-gray-800' }}">

                            Purchases

                        </a>

                    </div>

                </div>

                {{-- ========================================= --}}
                {{-- FARM --}}
                {{-- ========================================= --}}
                <div>

                    <button onclick="toggleMenu('farmMenu', 'farmArrow')" class="w-full flex items-center justify-between
                                   px-4 py-3 rounded-lg
                                   bg-gray-800 hover:bg-gray-700 transition">

                        <span>

                            🐔 Farm

                        </span>

                        <span id="farmArrow" class="transition-transform duration-300">

                            ▶

                        </span>

                    </button>

                    <div id="farmMenu" class="hidden mt-2 space-y-2 pl-3">

                        {{-- LIVESTOCK --}}
                        <a href="{{ route('livestocks.index') }}" class="block px-4 py-2 rounded-lg transition

                           {{ request()->routeIs('livestocks.*')
    ? 'bg-indigo-600 text-white'
    : 'hover:bg-gray-800' }}">

                            Livestock

                        </a>

                    </div>

                </div>

                {{-- ========================================= --}}
                {{-- TOILETS --}}
                {{-- ========================================= --}}
                <div>

                    <button onclick="toggleMenu('toiletMenu', 'toiletArrow')" class="w-full flex items-center justify-between
                                   px-4 py-3 rounded-lg
                                   bg-gray-800 hover:bg-gray-700 transition">

                        <span>

                            🚻 Toilets

                        </span>

                        <span id="toiletArrow" class="transition-transform duration-300">

                            ▶

                        </span>

                    </button>

                    <div id="toiletMenu" class="hidden mt-2 space-y-2 pl-3">

                        <a href="{{ route('toilets.index') }}" class="block px-4 py-2 rounded-lg transition

                           {{ request()->routeIs('toilets.*')
    ? 'bg-indigo-600 text-white'
    : 'hover:bg-gray-800' }}">

                            Toilets

                        </a>

                    </div>

                </div>

                {{-- ========================================= --}}
                {{-- FINANCE --}}
                {{-- ========================================= --}}
                <div>

                    <button onclick="toggleMenu('financeMenu', 'financeArrow')" class="w-full flex items-center justify-between
                                   px-4 py-3 rounded-lg
                                   bg-gray-800 hover:bg-gray-700 transition">

                        <span>

                            💰 Finance

                        </span>

                        <span id="financeArrow" class="transition-transform duration-300">

                            ▶

                        </span>

                    </button>

                    <div id="financeMenu" class="hidden mt-2 space-y-2 pl-3">

                        {{-- COMPANY EXPENSES --}}
                        <a href="{{ route('company-expenses.index') }}" class="block px-4 py-2 rounded-lg transition

                           {{ request()->routeIs('company-expenses.*')
    ? 'bg-indigo-600 text-white'
    : 'hover:bg-gray-800' }}">

                            Company Expenses

                        </a>

                        {{-- COMPANY INCOMES --}}
                        <a href="{{ route('company-incomes.index') }}" class="block px-4 py-2 rounded-lg transition

                           {{ request()->routeIs('company-incomes.*')
    ? 'bg-indigo-600 text-white'
    : 'hover:bg-gray-800' }}">

                            Company Incomes

                        </a>

                        {{-- SAVINGS --}}
                        <a href="{{ route('savings.index') }}" class="block px-4 py-2 rounded-lg transition

                           {{ request()->routeIs('savings.*')
    ? 'bg-indigo-600 text-white'
    : 'hover:bg-gray-800' }}">

                            Savings

                        </a>

                        {{-- LOANS --}}
                        <a href="{{ route('loans.index') }}" class="block px-4 py-2 rounded-lg transition

                           {{ request()->routeIs('loans.*')
    ? 'bg-indigo-600 text-white'
    : 'hover:bg-gray-800' }}">

                            Loans

                        </a>

                        {{-- REPORTS --}}
                        <a href="{{ route('reports.monthly') }}" class="block px-4 py-2 rounded-lg transition

                           {{ request()->routeIs('reports.*')
    ? 'bg-indigo-600 text-white'
    : 'hover:bg-gray-800' }}">

                            Reports

                        </a>

                    </div>

                </div>

            </nav>

        </aside>

        {{-- ========================================= --}}
        {{-- MAIN CONTENT --}}
        {{-- ========================================= --}}
        <div class="flex-1 flex flex-col">

            {{-- TOPBAR --}}
            <header class="bg-white shadow-sm px-4 lg:px-6 py-4">

                <div class="flex items-center justify-between">

                    {{-- LEFT --}}
                    <div class="flex items-center gap-4">

                        {{-- MOBILE MENU --}}
                        <button onclick="toggleSidebar()" class="lg:hidden text-2xl text-gray-700">

                            ☰

                        </button>

                        <h2 class="text-lg lg:text-2xl
                                   font-bold text-gray-800">

                            @yield('title')

                        </h2>

                    </div>

                    {{-- RIGHT --}}
                    <div class="flex items-center gap-4">

                        {{-- NOTIFICATIONS --}}
                        <div class="relative">

                            <button onclick="toggleNotif()" class="relative text-2xl">

                                🔔

                                <span class="absolute -top-1 -right-1
                                             bg-red-500 text-white
                                             text-xs rounded-full
                                             px-1">

                                    3

                                </span>

                            </button>

                            {{-- NOTIFICATION DROPDOWN --}}
                            <div id="notifBox" class="hidden absolute right-0 mt-2
                                        w-64 bg-white border rounded-xl
                                        shadow-lg z-50">

                                <div class="p-4 border-b font-semibold">

                                    Notifications

                                </div>

                                <div class="p-4 text-sm text-gray-500">

                                    No notifications

                                </div>

                            </div>

                        </div>

                        {{-- USER --}}
                        <div class="relative">

                            <button onclick="toggleUserMenu()" class="flex items-center gap-2
                                           text-sm font-medium">

                                <span>

                                    {{ auth()->user()->name }}

                                </span>

                                <span>

                                    ▼

                                </span>

                            </button>

                            {{-- USER DROPDOWN --}}
                            <div id="userMenu" class="hidden absolute right-0 mt-2
                                        w-48 bg-white border rounded-xl
                                        shadow-lg z-50">

                                <a href="{{ route('profile.edit') }}" class="block px-4 py-3
                                          hover:bg-gray-100">

                                    Profile

                                </a>

                                <form method="POST" action="{{ route('logout') }}">

                                    @csrf

                                    <button type="submit" class="w-full text-left
                                                   px-4 py-3
                                                   hover:bg-gray-100">

                                        Logout

                                    </button>

                                </form>

                            </div>

                        </div>

                    </div>

                </div>

            </header>

            {{-- PAGE CONTENT --}}
            <main class="flex-1 p-4 lg:p-6">

                @yield('content')

            </main>

        </div>

    </div>

    {{-- ========================================= --}}
    {{-- JAVASCRIPT --}}
    {{-- ========================================= --}}
    <script>

        /*
        |--------------------------------------------------------------------------
        | SIDEBAR
        |--------------------------------------------------------------------------
        */
        function toggleSidebar() {

            document.getElementById('sidebar')
                .classList.toggle('-translate-x-full');

            document.getElementById('sidebarOverlay')
                .classList.toggle('hidden');
        }

        /*
        |--------------------------------------------------------------------------
        | USER MENU
        |--------------------------------------------------------------------------
        */
        function toggleUserMenu() {

            document.getElementById('userMenu')
                .classList.toggle('hidden');
        }

        /*
        |--------------------------------------------------------------------------
        | NOTIFICATIONS
        |--------------------------------------------------------------------------
        */
        function toggleNotif() {

            document.getElementById('notifBox')
                .classList.toggle('hidden');
        }

        /*
        |--------------------------------------------------------------------------
        | DROPDOWN MENUS
        |--------------------------------------------------------------------------
        */
        function toggleMenu(menuId, arrowId) {

            const menu = document.getElementById(menuId);

            const arrow = document.getElementById(arrowId);

            menu.classList.toggle('hidden');

            arrow.classList.toggle('rotate-90');
        }

    </script>

</body>

</html>