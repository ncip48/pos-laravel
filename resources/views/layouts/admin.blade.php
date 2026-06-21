<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name', 'POS') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <style>
        body {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
        }

        .font-mono-num {
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-variant-numeric: tabular-nums;
        }
    </style>
    @stack('styles')
</head>

<body class="h-full text-slate-900 antialiased">
    <div class="flex h-full min-h-screen">

        {{-- Sidebar --}}
        <aside class="hidden lg:flex lg:flex-col w-64 shrink-0 bg-[#11132B] text-slate-300">
            <div class="flex items-center gap-2 px-6 h-16 border-b border-white/10">
                <div
                    class="h-8 w-8 rounded-md bg-indigo-500 flex items-center justify-center font-mono-num font-semibold text-white text-sm">
                    PO</div>
                <span class="font-semibold text-white tracking-tight">{{ config('app.name', 'POS System') }}</span>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6 text-sm">
                @can('dashboard.view')
                    <div>
                        <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Overview</p>
                        <x-nav-link href="{{ route('admin.dashboard') }}" icon="chart-bar"
                            :active="request()->routeIs('admin.dashboard')">Dashboard</x-nav-link>
                    </div>
                @endcan

                @canany(['products.view', 'categories.manage', 'units.manage'])
                    <div>
                        <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Catalog</p>
                        @can('products.view')
                            <x-nav-link href="{{ route('admin.products.index') }}" icon="cube"
                                :active="request()->routeIs('admin.products.*')">Products</x-nav-link>
                        @endcan
                        @can('categories.manage')
                            <x-nav-link href="{{ route('admin.categories.index') }}" icon="tag"
                                :active="request()->routeIs('admin.categories.*')">Categories</x-nav-link>
                        @endcan
                        @can('units.manage')
                            <x-nav-link href="{{ route('admin.units.index') }}" icon="scale"
                                :active="request()->routeIs('admin.units.*')">Units</x-nav-link>
                        @endcan
                    </div>
                @endcanany

                @canany(['suppliers.view', 'purchases.view', 'stock-adjustments.view', 'stock-movements.view'])
                    <div>
                        <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Inventory</p>
                        @can('suppliers.view')
                            <x-nav-link href="{{ route('admin.suppliers.index') }}" icon="truck"
                                :active="request()->routeIs('admin.suppliers.*')">Suppliers</x-nav-link>
                        @endcan
                        @can('purchases.view')
                            <x-nav-link href="{{ route('admin.purchases.index') }}" icon="clipboard-list"
                                :active="request()->routeIs('admin.purchases.*')">Purchases</x-nav-link>
                        @endcan
                        @can('stock-adjustments.view')
                            <x-nav-link href="{{ route('admin.stock-adjustments.index') }}" icon="adjustments"
                                :active="request()->routeIs('admin.stock-adjustments.*')">Stock Adjustments</x-nav-link>
                        @endcan
                        @can('stock-movements.view')
                            <x-nav-link href="{{ route('admin.stock-movements.index') }}" icon="clock"
                                :active="request()->routeIs('admin.stock-movements.index')">Stock Movements</x-nav-link>
                        @endcan
                    </div>
                @endcanany

                @canany(['customers.view', 'sales.view', 'sales.view-all', 'reports.sales', 'reports.profit',
                    'reports.inventory'])
                    <div>
                        <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Sales</p>
                        @can('customers.view')
                            <x-nav-link href="{{ route('admin.customers.index') }}" icon="users"
                                :active="request()->routeIs('admin.customers.*')">Customers</x-nav-link>
                        @endcan
                        @canany(['sales.view', 'sales.view-all'])
                            <x-nav-link href="{{ route('admin.sales.index') }}" icon="receipt"
                                :active="request()->routeIs('admin.sales.*')">Transactions</x-nav-link>
                        @endcanany
                        @canany(['reports.sales', 'reports.profit', 'reports.inventory', 'reports.export'])
                            <x-nav-link href="{{ route('admin.reports.sales') }}" icon="document-report"
                                :active="request()->routeIs('admin.reports.*')">Reports</x-nav-link>
                        @endcanany
                    </div>
                @endcanany

                @canany(['users.view', 'roles.manage', 'activity-logs.view'])
                    <div>
                        <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">System</p>
                        @role('admin')
                            <div>
                                <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">System
                                </p>

                                <x-nav-link href="{{ route('admin.settings.edit') }}" icon="cog"
                                    :active="request()->routeIs('admin.settings.*')">Settings</x-nav-link>

                                <x-nav-link href="{{ route('admin.registers.index') }}" icon="shield-check"
                                    :active="request()->routeIs('admin.registers.*')">POS Registers</x-nav-link>
                            </div>
                        @endrole
                        @canany(['users.view', 'roles.manage'])
                            <x-nav-link href="{{ route('admin.users.index') }}" icon="shield-check" :active="request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*')">Users &
                                Roles</x-nav-link>
                        @endcanany
                        @can('activity-logs.view')
                            <x-nav-link href="{{ route('admin.activity-log.index') }}" icon="clock"
                                :active="request()->routeIs('admin.activity-log.*')">Activity Log</x-nav-link>
                        @endcan
                    </div>
                @endcanany
            </nav>

            @can('pos.access')
                <div class="border-t border-white/10 px-4 py-4">
                    <a href="{{ route('pos.register') ?? '#' }}"
                        class="flex items-center justify-center gap-2 rounded-lg bg-indigo-500 hover:bg-indigo-400 transition px-3 py-2.5 text-sm font-medium text-white">
                        Open POS Register
                    </a>
                </div>
            @endcan
        </aside>

        {{-- Main column --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Topbar --}}
            <header
                class="sticky top-0 z-20 flex items-center justify-between h-16 px-4 sm:px-6 bg-white border-b border-slate-200">
                <div class="flex items-center gap-3">
                    <button id="mobile-menu-btn" class="lg:hidden p-2 -ml-2 text-slate-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 class="text-lg font-semibold text-slate-900">@yield('page-title', 'Dashboard')</h1>
                </div>

                <div class="flex items-center gap-4">
                    @auth
                        <div class="relative">
                            <button id="user-menu-btn" class="flex items-center gap-2 text-sm">
                                <img src="{{ auth()->user()->avatarUrl() }}" class="w-8 h-8 rounded-full" alt="">
                                <span class="hidden sm:block font-medium text-slate-700">{{ auth()->user()->name }}</span>
                            </button>
                            <div id="user-menu"
                                class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg ring-1 ring-slate-200 py-1 text-sm">
                                <a href="{{ route('profile.edit') ?? '#' }}"
                                    class="block px-4 py-2 text-slate-700 hover:bg-slate-50">Profile</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50">Log out</button>
                                </form>
                            </div>
                        </div>
                    @endauth
                </div>
            </header>

            {{-- Flash messages --}}
            <div class="px-4 sm:px-6">
                @if (session('success'))
                    <div
                        class="mt-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mt-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                        {{ session('error') }}
                    </div>
                @endif
            </div>

            <main class="flex-1 px-4 sm:px-6 py-6">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
