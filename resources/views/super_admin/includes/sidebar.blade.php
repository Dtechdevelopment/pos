@php
    $user = auth()->user();
    $initials = collect(explode(' ', $user->name ?? 'Super Admin'))
        ->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
@endphp

<aside id="sidebar"
    class="flex flex-col flex-shrink-0 transition-all duration-300 ease-in-out overflow-hidden"
    style="width: 260px; background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);">

    <div class="flex items-center justify-between h-16 px-4 flex-shrink-0"
        style="border-bottom: 1px solid rgba(255,255,255,0.07)">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center shadow-md flex-shrink-0">
                <i class="fas fa-crown text-white text-sm"></i>
            </div>
            <div class="sidebar-text">
                <p class="text-white font-bold text-sm leading-none">NESPOS</p>
                <p class="text-slate-400 text-xs leading-none mt-0.5">Super Admin</p>
            </div>
        </div>
        <button id="sidebarToggle"
            class="text-slate-400 hover:text-white transition-colors focus:outline-none w-7 h-7 flex items-center justify-center rounded-md hover:bg-white/10 flex-shrink-0">
            <i class="fas fa-bars text-sm"></i>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto overflow-x-hidden py-3 px-2.5 space-y-0.5"
        style="scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.1) transparent;">

        <a href="{{ route('super_admin.dashboard') }}"
            class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                {{ request()->routeIs('super_admin.dashboard')
                    ? 'bg-gradient-to-r from-purple-500 to-indigo-500 text-white shadow-lg shadow-purple-500/20'
                    : 'text-slate-300 hover:text-white hover:bg-white/8' }}">
            <i class="fas fa-house w-4 text-center flex-shrink-0
                {{ request()->routeIs('super_admin.dashboard') ? 'text-white' : 'text-slate-400' }}"></i>
            <span class="sidebar-text whitespace-nowrap">Dashboard</span>
        </a>

        <p class="sidebar-text text-xs font-semibold text-slate-500 uppercase tracking-widest px-3 pt-4 pb-1.5">Restaurant Management</p>

        <a href="{{ route('super_admin.restaurants.index') }}"
            class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                {{ request()->routeIs('super_admin.restaurants.*')
                    ? 'bg-gradient-to-r from-white/10 to-white/5 text-white'
                    : 'text-slate-300 hover:text-white hover:bg-white/8' }}">
            <i class="fas fa-store w-4 text-center flex-shrink-0
                {{ request()->routeIs('super_admin.restaurants.*') ? 'text-emerald-400' : 'text-slate-400' }}"></i>
            <span class="sidebar-text whitespace-nowrap">Restaurants</span>
        </a>

        <a href="{{ route('super_admin.managers.index') }}"
            class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                {{ request()->routeIs('super_admin.managers.*')
                    ? 'bg-gradient-to-r from-white/10 to-white/5 text-white'
                    : 'text-slate-300 hover:text-white hover:bg-white/8' }}">
            <i class="fas fa-user-tie w-4 text-center flex-shrink-0
                {{ request()->routeIs('super_admin.managers.*') ? 'text-blue-400' : 'text-slate-400' }}"></i>
            <span class="sidebar-text whitespace-nowrap">Managers</span>
        </a>

        <div class="pb-4"></div>
    </nav>

    <div class="flex-shrink-0 px-3 py-3" style="border-top: 1px solid rgba(255,255,255,0.07)">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-400 to-indigo-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow">
                {{ $initials }}
            </div>
            <div class="sidebar-text flex-1 min-w-0">
                <p class="text-white text-xs font-semibold truncate">{{ $user->name ?? 'Super Admin' }}</p>
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
    #sidebar nav::-webkit-scrollbar { width: 3px; }
    #sidebar nav::-webkit-scrollbar-track { background: transparent; }
    #sidebar nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
    #sidebar.collapsed { width: 64px !important; }
    #sidebar.collapsed .sidebar-text { display: none !important; }
    #sidebar.collapsed .nav-item { justify-content: center; padding-left: 0; padding-right: 0; }
</style>
