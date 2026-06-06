@php
    $user = auth()->user();
    $initials = collect(explode(' ', $user->name ?? 'Admin User'))
        ->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
@endphp

<aside id="sidebar"
    class="flex flex-col flex-shrink-0 transition-all duration-300 ease-in-out overflow-hidden"
    style="width: 260px; background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);">

    {{-- ── Logo ─────────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between h-16 px-4 flex-shrink-0"
        style="border-bottom: 1px solid rgba(255,255,255,0.07)">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center shadow-md flex-shrink-0">
                <i class="fas fa-utensils text-white text-sm"></i>
            </div>
            <div class="sidebar-text">
                <p class="text-white font-bold text-sm leading-none">POS Admin</p>
                <p class="text-slate-400 text-xs leading-none mt-0.5">Restaurant System</p>
            </div>
        </div>
        <button id="sidebarToggle"
            class="text-slate-400 hover:text-white transition-colors focus:outline-none w-7 h-7 flex items-center justify-center rounded-md hover:bg-white/10 flex-shrink-0">
            <i class="fas fa-bars text-sm"></i>
        </button>
    </div>

    {{-- ── Nav ──────────────────────────────────────────────────────────── --}}
    <nav class="flex-1 overflow-y-auto overflow-x-hidden py-3 px-2.5 space-y-0.5"
        style="scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.1) transparent;">

        {{-- Dashboard --}}
        <a href="{{ route('admin.dashboard') }}"
            class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                {{ request()->routeIs('admin.dashboard')
                    ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg shadow-orange-500/20'
                    : 'text-slate-300 hover:text-white hover:bg-white/8' }}">
            <i class="fas fa-house w-4 text-center flex-shrink-0
                {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-slate-400' }}"></i>
            <span class="sidebar-text whitespace-nowrap">Dashboard</span>
        </a>

        {{-- Section: Management --}}
        <p class="sidebar-text text-xs font-semibold text-slate-500 uppercase tracking-widest px-3 pt-4 pb-1.5">Management</p>

        {{-- User Management --}}
        <div x-data="{ open: {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                class="nav-item w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                    {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*')
                        ? 'text-white bg-white/10'
                        : 'text-slate-300 hover:text-white hover:bg-white/8' }}">
                <span class="flex items-center gap-3">
                    <i class="fas fa-users w-4 text-center flex-shrink-0
                        {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                    <span class="sidebar-text whitespace-nowrap">User Management</span>
                </span>
                <i class="fas fa-chevron-right text-xs text-slate-500 transition-transform duration-200 sidebar-text flex-shrink-0"
                    :class="{ 'rotate-90': open }"></i>
            </button>
            <ul x-show="open" x-collapse class="mt-0.5 space-y-0.5 pl-4">
                @foreach([
                    ['admin.users.index',       'admin.users.*',       'fa-user',    'Users'],
                    ['admin.roles.index',        'admin.roles.*',       'fa-shield-halved', 'Roles'],
                    ['admin.permissions.index',  'admin.permissions.*', 'fa-key',     'Permissions'],
                ] as [$route, $match, $icon, $label])
                <li>
                    <a href="{{ route($route) }}"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all
                            {{ request()->routeIs($match)
                                ? 'text-white bg-white/10 border-l-2 border-blue-400'
                                : 'text-slate-400 hover:text-white hover:bg-white/8 border-l-2 border-transparent' }}">
                        <i class="fas {{ $icon }} w-3.5 text-center flex-shrink-0"></i>
                        <span class="sidebar-text">{{ $label }}</span>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>

        {{-- Restaurant --}}
        <div x-data="{ open: {{ request()->routeIs('admin.branches.*') || request()->routeIs('admin.tables.*') || request()->routeIs('admin.menu.*') || request()->routeIs('admin.categories.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                class="nav-item w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                    {{ request()->routeIs('admin.branches.*') || request()->routeIs('admin.tables.*') || request()->routeIs('admin.menu.*') || request()->routeIs('admin.categories.*')
                        ? 'text-white bg-white/10'
                        : 'text-slate-300 hover:text-white hover:bg-white/8' }}">
                <span class="flex items-center gap-3">
                    <i class="fas fa-store w-4 text-center flex-shrink-0
                        {{ request()->routeIs('admin.branches.*') || request()->routeIs('admin.tables.*') || request()->routeIs('admin.menu.*') || request()->routeIs('admin.categories.*') ? 'text-emerald-400' : 'text-slate-400' }}"></i>
                    <span class="sidebar-text whitespace-nowrap">Restaurant</span>
                </span>
                <i class="fas fa-chevron-right text-xs text-slate-500 transition-transform duration-200 sidebar-text flex-shrink-0"
                    :class="{ 'rotate-90': open }"></i>
            </button>
            <ul x-show="open" x-collapse class="mt-0.5 space-y-0.5 pl-4">
                @foreach([
                    ['admin.branches.index',   'admin.branches.*',   'fa-building',  'Branches'],
                    ['admin.tables.index',     'admin.tables.*',     'fa-chair',     'Tables'],
                    ['admin.menu.index',       'admin.menu.*',       'fa-utensils',  'Menu'],
                    ['admin.categories.index', 'admin.categories.*', 'fa-tags',      'Categories'],
                ] as [$route, $match, $icon, $label])
                <li>
                    <a href="{{ route($route) }}"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all
                            {{ request()->routeIs($match)
                                ? 'text-white bg-white/10 border-l-2 border-emerald-400'
                                : 'text-slate-400 hover:text-white hover:bg-white/8 border-l-2 border-transparent' }}">
                        <i class="fas {{ $icon }} w-3.5 text-center flex-shrink-0"></i>
                        <span class="sidebar-text">{{ $label }}</span>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>

        {{-- Section: Operations --}}
        <p class="sidebar-text text-xs font-semibold text-slate-500 uppercase tracking-widest px-3 pt-4 pb-1.5">Operations</p>

        @foreach([
            ['admin.orders.index',       'admin.orders.*',       'fa-clipboard-list',  'Orders',       'text-orange-400'],
            ['admin.kitchen.index',      'admin.kitchen.*',      'fa-fire-burner',     'Kitchen',      'text-red-400'],
            ['admin.billing.index',      'admin.billing.*',      'fa-file-invoice',    'Billing',      'text-blue-400'],
            ['admin.payments.index',     'admin.payments.*',     'fa-credit-card',     'Payments',     'text-teal-400'],
            ['admin.inventory.index',    'admin.inventory.*',    'fa-boxes-stacked',   'Inventory',    'text-lime-400'],
            ['admin.customers.index',    'admin.customers.*',    'fa-users-gear',      'Customers',    'text-amber-400'],
        ] as [$route, $match, $icon, $label, $activeColor])
        <a href="{{ route($route) }}"
            class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                {{ request()->routeIs($match)
                    ? 'bg-gradient-to-r from-white/10 to-white/5 text-white'
                    : 'text-slate-300 hover:text-white hover:bg-white/8' }}">
            <i class="fas {{ $icon }} w-4 text-center flex-shrink-0
                {{ request()->routeIs($match) ? $activeColor : 'text-slate-400' }}"></i>
            <span class="sidebar-text whitespace-nowrap">{{ $label }}</span>
            @if(request()->routeIs($match))
                <span class="sidebar-text ml-auto w-1.5 h-1.5 rounded-full {{ str_replace('text-', 'bg-', $activeColor) }} flex-shrink-0"></span>
            @endif
        </a>
        @endforeach

        {{-- Section: Analytics --}}
        <p class="sidebar-text text-xs font-semibold text-slate-500 uppercase tracking-widest px-3 pt-4 pb-1.5">Analytics</p>

        {{-- Reports --}}
        <div x-data="{ open: {{ request()->routeIs('admin.reports.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                class="nav-item w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                    {{ request()->routeIs('admin.reports.*')
                        ? 'text-white bg-white/10'
                        : 'text-slate-300 hover:text-white hover:bg-white/8' }}">
                <span class="flex items-center gap-3">
                    <i class="fas fa-chart-bar w-4 text-center flex-shrink-0
                        {{ request()->routeIs('admin.reports.*') ? 'text-purple-400' : 'text-slate-400' }}"></i>
                    <span class="sidebar-text whitespace-nowrap">Reports</span>
                </span>
                <i class="fas fa-chevron-right text-xs text-slate-500 transition-transform duration-200 sidebar-text flex-shrink-0"
                    :class="{ 'rotate-90': open }"></i>
            </button>
            <ul x-show="open" x-collapse class="mt-0.5 space-y-0.5 pl-4">
                @foreach([
                    ['admin.reports.sales',          'admin.reports.sales',          'fa-chart-line',       'Sales'],
                    ['admin.reports.kitchen',        'admin.reports.kitchen',        'fa-utensils',         'Kitchen'],
                    ['admin.reports.financial',      'admin.reports.financial',      'fa-coins',            'Financial'],
                    ['admin.reports.waiter',         'admin.reports.waiter',         'fa-user',             'Waiter'],
                    ['admin.reports.reconciliation', 'admin.reports.reconciliation', 'fa-scale-balanced',   'Reconciliation'],
                ] as [$route, $match, $icon, $label])
                <li>
                    <a href="{{ route($route) }}"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all
                            {{ request()->routeIs($match)
                                ? 'text-white bg-white/10 border-l-2 border-purple-400'
                                : 'text-slate-400 hover:text-white hover:bg-white/8 border-l-2 border-transparent' }}">
                        <i class="fas {{ $icon }} w-3.5 text-center flex-shrink-0"></i>
                        <span class="sidebar-text">{{ $label }}</span>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>

        @foreach([
            ['admin.reconciliation.index', 'admin.reconciliation.*', 'fa-scale-balanced', 'Reconciliation', 'text-indigo-400'],
            ['admin.notifications.index',  'admin.notifications.*',  'fa-bell',            'Notifications',  'text-yellow-400'],
            ['admin.audit.index',          'admin.audit.*',          'fa-clock-rotate-left','Audit Logs',     'text-slate-400'],
        ] as [$route, $match, $icon, $label, $activeColor])
        <a href="{{ route($route) }}"
            class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                {{ request()->routeIs($match)
                    ? 'bg-gradient-to-r from-white/10 to-white/5 text-white'
                    : 'text-slate-300 hover:text-white hover:bg-white/8' }}">
            <i class="fas {{ $icon }} w-4 text-center flex-shrink-0
                {{ request()->routeIs($match) ? $activeColor : 'text-slate-400' }}"></i>
            <span class="sidebar-text whitespace-nowrap">{{ $label }}</span>
            @if(request()->routeIs($match))
                <span class="sidebar-text ml-auto w-1.5 h-1.5 rounded-full {{ str_replace('text-', 'bg-', $activeColor) }} flex-shrink-0"></span>
            @endif
        </a>
        @endforeach

        {{-- Section: System --}}
        <p class="sidebar-text text-xs font-semibold text-slate-500 uppercase tracking-widest px-3 pt-4 pb-1.5">System</p>

        {{-- Settings --}}
        <div x-data="{ open: {{ request()->routeIs('admin.settings.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                class="nav-item w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                    {{ request()->routeIs('admin.settings.*')
                        ? 'text-white bg-white/10'
                        : 'text-slate-300 hover:text-white hover:bg-white/8' }}">
                <span class="flex items-center gap-3">
                    <i class="fas fa-gear w-4 text-center flex-shrink-0
                        {{ request()->routeIs('admin.settings.*') ? 'text-sky-400' : 'text-slate-400' }}"></i>
                    <span class="sidebar-text whitespace-nowrap">Settings</span>
                </span>
                <i class="fas fa-chevron-right text-xs text-slate-500 transition-transform duration-200 sidebar-text flex-shrink-0"
                    :class="{ 'rotate-90': open }"></i>
            </button>
            <ul x-show="open" x-collapse class="mt-0.5 space-y-0.5 pl-4">
                @foreach([
                    ['admin.settings.index',    'admin.settings.index',    'fa-sliders', 'General'],
                    ['admin.settings.security', 'admin.settings.security', 'fa-lock',    'Security'],
                ] as [$route, $match, $icon, $label])
                <li>
                    <a href="{{ route($route) }}"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all
                            {{ request()->routeIs($match)
                                ? 'text-white bg-white/10 border-l-2 border-sky-400'
                                : 'text-slate-400 hover:text-white hover:bg-white/8 border-l-2 border-transparent' }}">
                        <i class="fas {{ $icon }} w-3.5 text-center flex-shrink-0"></i>
                        <span class="sidebar-text">{{ $label }}</span>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>

        <div class="pb-4"></div>
    </nav>

    {{-- ── User Footer ──────────────────────────────────────────────────── --}}
    <div class="flex-shrink-0 px-3 py-3" style="border-top: 1px solid rgba(255,255,255,0.07)">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow">
                {{ $initials }}
            </div>
            <div class="sidebar-text flex-1 min-w-0">
                <p class="text-white text-xs font-semibold truncate">{{ $user->name ?? 'Admin User' }}</p>
                <p class="text-slate-400 text-xs truncate">{{ $user->email ?? '' }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="sidebar-text flex-shrink-0">
                @csrf
                <button type="submit"
                    class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition"
                    title="Logout">
                    <i class="fas fa-arrow-right-from-bracket text-xs"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

<style>
    /* Scrollbar styling */
    #sidebar nav::-webkit-scrollbar { width: 3px; }
    #sidebar nav::-webkit-scrollbar-track { background: transparent; }
    #sidebar nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }

    /* Collapsed state */
    #sidebar.collapsed { width: 64px !important; }
    #sidebar.collapsed .sidebar-text { display: none !important; }
    #sidebar.collapsed .nav-item { justify-content: center; padding-left: 0; padding-right: 0; }
</style>
