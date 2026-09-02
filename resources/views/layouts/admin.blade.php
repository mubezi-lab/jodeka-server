<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard</title>

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

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

            {{-- ========================================= --}}
            {{-- LOGO --}}
            {{-- ========================================= --}}
            <div class="flex items-center justify-between
                        px-5 py-5 border-b border-gray-800">

                <h1 class="text-2xl font-bold tracking-wide">
                    JODEKA
                </h1>

                {{-- MOBILE CLOSE --}}
                <button onclick="toggleSidebar()" class="lg:hidden text-xl
                           text-gray-300 hover:text-white transition">

                    <i class="fa-solid fa-xmark"></i>

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
    ? 'bg-indigo-600 text-white shadow'
    : 'bg-gray-800 hover:bg-gray-700' }}">

                    <i class="fa-solid fa-house
                              w-5 text-center"></i>

                    <span>
                        Dashboard
                    </span>

                </a>


                {{-- ========================================= --}}
                {{-- BUSINESS MANAGEMENT --}}
                {{-- ========================================= --}}
                <div>

                    <button onclick="toggleMenu('businessMenu', 'businessArrow')" class="w-full flex items-center justify-between
                               px-4 py-3 rounded-lg
                               bg-gray-800 hover:bg-gray-700 transition">

                        <span class="flex items-center gap-3">

                            <i class="fa-solid fa-building
                                      w-5 text-center"></i>

                            <span>
                                Business Management
                            </span>

                        </span>

                        <i id="businessArrow" class="fa-solid fa-chevron-right
                                   text-xs transition-transform duration-300">
                        </i>

                    </button>


                    <div id="businessMenu" class="{{ request()->routeIs(
    'businesses.*',
    'users.*'
) ? '' : 'hidden' }}
                        mt-2 space-y-2 pl-3">


                        {{-- BUSINESSES --}}
                        <a href="{{ route('businesses.index') }}" class="flex items-center gap-3
                                   px-4 py-2 rounded-lg transition
                                   {{ request()->routeIs('businesses.*')
    ? 'bg-indigo-600 text-white'
    : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">

                            <i class="fa-solid fa-briefcase
                                      w-5 text-center text-sm"></i>

                            <span>
                                Businesses
                            </span>

                        </a>


                        {{-- USERS --}}
                        <a href="{{ route('users.index') }}" class="flex items-center gap-3
                                   px-4 py-2 rounded-lg transition
                                   {{ request()->routeIs('users.*')
    ? 'bg-indigo-600 text-white'
    : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">

                            <i class="fa-solid fa-user-gear
                                      w-5 text-center text-sm"></i>

                            <span>
                                Users
                            </span>

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

                        <span class="flex items-center gap-3">

                            <i class="fa-solid fa-boxes-stacked
                                      w-5 text-center"></i>

                            <span>
                                Inventory
                            </span>

                        </span>

                        <i id="inventoryArrow" class="fa-solid fa-chevron-right
                                   text-xs transition-transform duration-300">
                        </i>

                    </button>


                    <div id="inventoryMenu" class="{{ request()->routeIs(
    'products.*',
    'stocks.*',
    'purchases.*'
) ? '' : 'hidden' }}
                        mt-2 space-y-2 pl-3">


                        {{-- PRODUCTS --}}
                        <a href="{{ route('products.index') }}" class="flex items-center gap-3
                                   px-4 py-2 rounded-lg transition
                                   {{ request()->routeIs('products.*')
    ? 'bg-indigo-600 text-white'
    : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">

                            <i class="fa-solid fa-box
                                      w-5 text-center text-sm"></i>

                            <span>
                                Products
                            </span>

                        </a>


                        {{-- STOCK --}}
                        <a href="{{ route('stocks.index') }}" class="flex items-center gap-3
                                   px-4 py-2 rounded-lg transition
                                   {{ request()->routeIs('stocks.*')
    ? 'bg-indigo-600 text-white'
    : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">

                            <i class="fa-solid fa-warehouse
                                      w-5 text-center text-sm"></i>

                            <span>
                                Stock
                            </span>

                        </a>


                        {{-- PURCHASES --}}
                        <a href="{{ route('purchases.index') }}" class="flex items-center gap-3
                                   px-4 py-2 rounded-lg transition
                                   {{ request()->routeIs('purchases.*')
    ? 'bg-indigo-600 text-white'
    : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">

                            <i class="fa-solid fa-cart-shopping
                                      w-5 text-center text-sm"></i>

                            <span>
                                Purchases
                            </span>

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

                        <span class="flex items-center gap-3">

                            <i class="fa-solid fa-cow
                                      w-5 text-center"></i>

                            <span>
                                Farm
                            </span>

                        </span>

                        <i id="farmArrow" class="fa-solid fa-chevron-right
                                   text-xs transition-transform duration-300">
                        </i>

                    </button>


                    <div id="farmMenu" class="{{ request()->routeIs('livestocks.*')
    ? ''
    : 'hidden' }}
                            mt-2 space-y-2 pl-3">


                        {{-- LIVESTOCK --}}
                        <a href="{{ route('livestocks.index') }}" class="flex items-center gap-3
                                   px-4 py-2 rounded-lg transition
                                   {{ request()->routeIs('livestocks.*')
    ? 'bg-indigo-600 text-white'
    : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">

                            <i class="fa-solid fa-paw
                                      w-5 text-center text-sm"></i>

                            <span>
                                Livestock
                            </span>

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

                        <span class="flex items-center gap-3">

                            <i class="fa-solid fa-restroom
                                      w-5 text-center"></i>

                            <span>
                                Toilets
                            </span>

                        </span>

                        <i id="toiletArrow" class="fa-solid fa-chevron-right
                                   text-xs transition-transform duration-300">
                        </i>

                    </button>


                    <div id="toiletMenu" class="{{ request()->routeIs('toilets.*')
    ? ''
    : 'hidden' }}
                            mt-2 space-y-2 pl-3">


                        <a href="{{ route('toilets.index') }}" class="flex items-center gap-3
                                   px-4 py-2 rounded-lg transition
                                   {{ request()->routeIs('toilets.*')
    ? 'bg-indigo-600 text-white'
    : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">

                            <i class="fa-solid fa-toilet
                                      w-5 text-center text-sm"></i>

                            <span>
                                Toilets
                            </span>

                        </a>

                    </div>

                </div>


                {{-- ========================================= --}}
                {{-- HOTSPOT --}}
                {{-- ========================================= --}}
                <div>

                    <button onclick="toggleMenu('hotspotMenu', 'hotspotArrow')" class="w-full flex items-center justify-between
                               px-4 py-3 rounded-lg
                               bg-gray-800 hover:bg-gray-700 transition">

                        <span class="flex items-center gap-3">

                            <i class="fa-solid fa-wifi
                                      w-5 text-center"></i>

                            <span>
                                Hotspot
                            </span>

                        </span>

                        <i id="hotspotArrow" class="fa-solid fa-chevron-right
                                   text-xs transition-transform duration-300">
                        </i>

                    </button>


                    <div id="hotspotMenu" class="{{ request()->routeIs(
    'network-routers.*',
    'hotspot-profiles.*',
    'hotspot-vouchers.*'
) ? '' : 'hidden' }}
                        mt-2 space-y-2 pl-3">


                        {{-- ROUTERS --}}
                        <a href="{{ route('network-routers.index') }}" class="flex items-center gap-3
                                   px-4 py-2 rounded-lg transition
                                   {{ request()->routeIs('network-routers.*')
    ? 'bg-indigo-600 text-white'
    : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">

                            <i class="fa-solid fa-router
                                      w-5 text-center text-sm"></i>

                            <span>
                                Routers
                            </span>

                        </a>


                        {{-- HOTSPOT PROFILES --}}
                        <a href="{{ route('hotspot-profiles.index') }}" class="flex items-center gap-3
                                   px-4 py-2 rounded-lg transition
                                   {{ request()->routeIs('hotspot-profiles.*')
    ? 'bg-indigo-600 text-white'
    : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">

                            <i class="fa-solid fa-sliders
                                      w-5 text-center text-sm"></i>

                            <span>
                                Profiles
                            </span>

                        </a>


                        {{-- HOTSPOT VOUCHERS --}}
                        <a href="{{ route('hotspot-vouchers.index') }}" class="flex items-center gap-3
                                   px-4 py-2 rounded-lg transition
                                   {{ request()->routeIs('hotspot-vouchers.*')
    ? 'bg-indigo-600 text-white'
    : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">

                            <i class="fa-solid fa-ticket
                                      w-5 text-center text-sm"></i>

                            <span>
                                Vouchers
                            </span>

                        </a>

                    </div>

                </div>


                {{-- ========================================= --}}
                {{-- BAGAMBAKAMO --}}
                {{-- ========================================= --}}
                <div>

                    <button onclick="toggleMenu('bagambakamoMenu', 'bagambakamoArrow')" class="w-full flex items-center justify-between
                               px-4 py-3 rounded-lg
                               bg-gray-800 hover:bg-gray-700 transition">

                        <span class="flex items-center gap-3">

                            <i class="fa-solid fa-people-group
                                      w-5 text-center"></i>

                            <span>
                                Bagambakamo
                            </span>

                        </span>

                        <i id="bagambakamoArrow" class="fa-solid fa-chevron-right
                                   text-xs transition-transform duration-300">
                        </i>

                    </button>


                    <div id="bagambakamoMenu" class="{{ request()->is('bagambakamo*')
    ? ''
    : 'hidden' }}
                            mt-2 space-y-2 pl-3">


                        {{-- DASHBOARD --}}
                        <a href="{{ url('/bagambakamo') }}" class="flex items-center gap-3
                                   px-4 py-2 rounded-lg transition
                                   {{ request()->is('bagambakamo')
    ? 'bg-indigo-600 text-white'
    : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">

                            <i class="fa-solid fa-gauge-high
                                      w-5 text-center text-sm"></i>

                            <span>
                                Dashboard
                            </span>

                        </a>


                        {{-- MEMBERS --}}
                        <a href="{{ url('/bagambakamo/members') }}" class="flex items-center gap-3
                                   px-4 py-2 rounded-lg transition
                                   {{ request()->is('bagambakamo/members*')
    ? 'bg-indigo-600 text-white'
    : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">

                            <i class="fa-solid fa-users
                                      w-5 text-center text-sm"></i>

                            <span>
                                Members
                            </span>

                        </a>


                        {{-- SMS REPORTS --}}
                        <a href="{{ url('/bagambakamo/sms-reports') }}" class="flex items-center gap-3
                                   px-4 py-2 rounded-lg transition
                                   {{ request()->is('bagambakamo/sms-reports*')
    ? 'bg-indigo-600 text-white'
    : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">

                            <i class="fa-solid fa-comments
                                      w-5 text-center text-sm"></i>

                            <span>
                                SMS Reports
                            </span>

                        </a>


                        {{-- REPORTS --}}
                        <a href="{{ url('/bagambakamo/reports') }}" class="flex items-center gap-3
                                   px-4 py-2 rounded-lg transition
                                   {{ request()->is('bagambakamo/reports*')
    ? 'bg-indigo-600 text-white'
    : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">

                            <i class="fa-solid fa-chart-column
                                      w-5 text-center text-sm"></i>

                            <span>
                                Reports
                            </span>

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

                        <span class="flex items-center gap-3">

                            <i class="fa-solid fa-wallet
                                      w-5 text-center"></i>

                            <span>
                                Finance
                            </span>

                        </span>

                        <i id="financeArrow" class="fa-solid fa-chevron-right
                                   text-xs transition-transform duration-300">
                        </i>

                    </button>


                    <div id="financeMenu" class="{{ request()->routeIs(
    'company-expenses.*',
    'fixed-expenses.*',
    'fixed-expense-payments.*',
    'company-incomes.*',
    'savings.*',
    'loans.*',
    'customers.*',
    'debts.*',
    'financial-accounts.*',
    'financial-account-transfers.*',
    'reports.*'
) ? '' : 'hidden' }}
                        mt-2 space-y-2 pl-3">


                        {{-- COMPANY EXPENSES --}}
                        <a href="{{ route('company-expenses.index') }}" class="flex items-center gap-3
                                   px-4 py-2 rounded-lg transition
                                   {{ request()->routeIs('company-expenses.*')
    ? 'bg-indigo-600 text-white'
    : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">

                            <i class="fa-solid fa-money-bill-transfer
                                      w-5 text-center text-sm"></i>

                            <span>
                                Company Expenses
                            </span>

                        </a>


                        {{-- FIXED EXPENSES --}}
                        <a href="{{ route('fixed-expenses.index') }}" class="flex items-center gap-3
                                   px-4 py-2 rounded-lg transition
                                   {{ request()->routeIs('fixed-expenses.*')
    ? 'bg-indigo-600 text-white'
    : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">

                            <i class="fa-solid fa-file-invoice-dollar
                                      w-5 text-center text-sm"></i>

                            <span>
                                Fixed Expenses
                            </span>

                        </a>


                        {{-- FIXED EXPENSE PAYMENTS --}}
                        <a href="{{ route('fixed-expense-payments.index') }}" class="flex items-center gap-3
                                   px-4 py-2 rounded-lg transition
                                   {{ request()->routeIs('fixed-expense-payments.*')
    ? 'bg-indigo-600 text-white'
    : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">

                            <i class="fa-solid fa-money-check-dollar
                                      w-5 text-center text-sm"></i>

                            <span>
                                Fixed Expense Payments
                            </span>

                        </a>


                        {{-- COMPANY INCOMES --}}
                        <a href="{{ route('company-incomes.index') }}" class="flex items-center gap-3
                                   px-4 py-2 rounded-lg transition
                                   {{ request()->routeIs('company-incomes.*')
    ? 'bg-indigo-600 text-white'
    : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">

                            <i class="fa-solid fa-coins
                                      w-5 text-center text-sm"></i>

                            <span>
                                Company Incomes
                            </span>

                        </a>


                        {{-- SAVINGS --}}
                        <a href="{{ route('savings.index') }}" class="flex items-center gap-3
                                   px-4 py-2 rounded-lg transition
                                   {{ request()->routeIs('savings.*')
    ? 'bg-indigo-600 text-white'
    : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">

                            <i class="fa-solid fa-piggy-bank
                                      w-5 text-center text-sm"></i>

                            <span>
                                Savings
                            </span>

                        </a>


                        {{-- LOANS --}}
                        <a href="{{ route('loans.index') }}" class="flex items-center gap-3
                                   px-4 py-2 rounded-lg transition
                                   {{ request()->routeIs('loans.*')
    ? 'bg-indigo-600 text-white'
    : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">

                            <i class="fa-solid fa-hand-holding-dollar
                                      w-5 text-center text-sm"></i>

                            <span>
                                Loans
                            </span>

                        </a>

                        {{-- CUSTOMERS --}}
                        <a href="{{ route('customers.index') }}" class="flex items-center gap-3
                                   px-4 py-2 rounded-lg transition
                                   {{ request()->routeIs('customers.*')
    ? 'bg-indigo-600 text-white'
    : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">

                            <i class="fa-solid fa-users w-5 text-center text-sm"></i>
                            <span>Customers</span>
                        </a>

                        {{-- GENERAL DEBTS --}}
                        <a href="{{ route('debts.index') }}" class="flex items-center gap-3
                                   px-4 py-2 rounded-lg transition
                                   {{ request()->routeIs('debts.*')
    ? 'bg-indigo-600 text-white'
    : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">

                            <i class="fa-solid fa-file-invoice-dollar w-5 text-center text-sm"></i>
                            <span>Customer Debts</span>
                        </a>

                        {{-- FINANCIAL ACCOUNTS --}}
                        <a href="{{ route('financial-accounts.index') }}" class="flex items-center gap-3
                                   px-4 py-2 rounded-lg transition
                                   {{ request()->routeIs('financial-accounts.*')
    ? 'bg-indigo-600 text-white'
    : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                            <i class="fa-solid fa-building-columns w-5 text-center text-sm"></i>
                            <span>Financial Accounts</span>
                        </a>

                        {{-- CASH HANDOVERS --}}
                        <a href="{{ route('financial-account-transfers.index') }}" class="flex items-center gap-3
                                   px-4 py-2 rounded-lg transition
                                   {{ request()->routeIs('financial-account-transfers.*')
    ? 'bg-indigo-600 text-white'
    : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                            <i class="fa-solid fa-right-left w-5 text-center text-sm"></i>
                            <span>Cash Handovers</span>
                        </a>


                        {{-- REPORTS --}}
                        <a href="{{ route('reports.monthly') }}" class="flex items-center gap-3
                                   px-4 py-2 rounded-lg transition
                                   {{ request()->routeIs('reports.*')
    ? 'bg-indigo-600 text-white'
    : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">

                            <i class="fa-solid fa-chart-line
                                      w-5 text-center text-sm"></i>

                            <span>
                                Reports
                            </span>

                        </a>

                    </div>

                </div>

            </nav>

        </aside>


        {{-- ========================================= --}}
        {{-- MAIN CONTENT --}}
        {{-- ========================================= --}}
        <div class="flex-1 min-w-0 flex flex-col">


            {{-- ========================================= --}}
            {{-- TOPBAR --}}
            {{-- ========================================= --}}
            <header class="bg-white shadow-sm px-4 lg:px-6 py-4">

                <div class="flex items-center justify-between">


                    {{-- LEFT --}}
                    <div class="flex items-center gap-4">

                        {{-- MOBILE MENU --}}
                        <button onclick="toggleSidebar()" class="lg:hidden text-2xl text-gray-700">

                            <i class="fa-solid fa-bars"></i>

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

                            <button onclick="toggleNotif()" class="relative text-xl
                                       text-gray-700 hover:text-indigo-600 transition">

                                <i class="fa-solid fa-bell"></i>

                                <span class="absolute -top-2 -right-2
                                             bg-red-500 text-white
                                             text-xs rounded-full
                                             min-w-5 h-5
                                             flex items-center justify-center">

                                    3

                                </span>

                            </button>


                            {{-- NOTIFICATION DROPDOWN --}}
                            <div id="notifBox" class="hidden absolute right-0 mt-3
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
                                       text-sm font-medium
                                       text-gray-700 hover:text-indigo-600 transition">

                                <i class="fa-solid fa-circle-user text-xl"></i>

                                <span>
                                    {{ auth()->user()->name }}
                                </span>

                                <i class="fa-solid fa-chevron-down text-xs"></i>

                            </button>


                            {{-- USER DROPDOWN --}}
                            <div id="userMenu" class="hidden absolute right-0 mt-3
                                       w-48 bg-white border rounded-xl
                                       shadow-lg z-50 overflow-hidden">

                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3
                                           px-4 py-3 hover:bg-gray-100">

                                    <i class="fa-solid fa-user"></i>

                                    <span>
                                        Profile
                                    </span>

                                </a>


                                <form method="POST" action="{{ route('logout') }}">

                                    @csrf

                                    <button type="submit" class="w-full flex items-center gap-3
                                               px-4 py-3
                                               text-left hover:bg-gray-100">

                                        <i class="fa-solid fa-right-from-bracket"></i>

                                        <span>
                                            Logout
                                        </span>

                                    </button>

                                </form>

                            </div>

                        </div>

                    </div>

                </div>

            </header>


            {{-- ========================================= --}}
            {{-- PAGE CONTENT --}}
            {{-- ========================================= --}}
            <main class="flex-1 min-w-0 p-4 lg:p-6">

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


        /*
        |--------------------------------------------------------------------------
        | ROTATE ARROWS FOR MENUS ALREADY OPEN
        |--------------------------------------------------------------------------
        */
        document.addEventListener('DOMContentLoaded', function () {

            const menus = [
                ['businessMenu', 'businessArrow'],
                ['inventoryMenu', 'inventoryArrow'],
                ['farmMenu', 'farmArrow'],
                ['toiletMenu', 'toiletArrow'],
                ['hotspotMenu', 'hotspotArrow'],
                ['bagambakamoMenu', 'bagambakamoArrow'],
                ['financeMenu', 'financeArrow']
            ];

            menus.forEach(function (item) {

                const menu = document.getElementById(item[0]);
                const arrow = document.getElementById(item[1]);

                if (menu && arrow && !menu.classList.contains('hidden')) {

                    arrow.classList.add('rotate-90');

                }

            });

        });

    </script>

</body>

</html>
