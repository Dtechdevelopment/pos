<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'NESPOS Super Admin')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50:'#f3f0ff', 100:'#e9e3ff', 200:'#d4c9ff', 300:'#b39dff', 400:'#8b6cff', 500:'#6b3fff', 600:'#5a1fff', 700:'#4a148c', 800:'#3a1070', 900:'#2d0d57' }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.15); border-radius: 10px; }
    </style>
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: false }">

    {{-- Mobile overlay --}}
    <div x-show="sidebarOpen" x-cloak x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-black/50 z-40 lg:hidden"></div>

    {{-- Sidebar --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-50 w-[260px] flex flex-col transition-transform duration-300 ease-in-out lg:translate-x-0"
           style="background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);">

        {{-- Logo --}}
        <div class="flex items-center justify-between h-16 px-4 flex-shrink-0" style="border-bottom: 1px solid rgba(255,255,255,0.07)">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center shadow-md">
                    <i class="fas fa-crown text-white text-sm"></i>
                </div>
                <div>
                    <p class="text-white font-bold text-sm leading-none">NESPOS</p>
                    <p class="text-slate-400 text-xs leading-none mt-0.5">Super Admin</p>
                </div>
            </div>
            <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto py-3 px-2.5 space-y-1">
            <a href="{{ route('super_admin.dashboard') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                    {{ request()->routeIs('super_admin.dashboard')
                        ? 'bg-gradient-to-r from-purple-500 to-indigo-500 text-white shadow-lg shadow-purple-500/20'
                        : 'text-slate-300 hover:text-white hover:bg-white/8' }}">
                <i class="fas fa-house w-5 text-center {{ request()->routeIs('super_admin.dashboard') ? 'text-white' : 'text-slate-400' }}"></i>
                <span>Dashboard</span>
            </a>

            <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest px-3 pt-5 pb-2">Restaurant Management</p>

            <a href="{{ route('super_admin.restaurants.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                    {{ request()->routeIs('super_admin.restaurants.*')
                        ? 'bg-white/10 text-white' : 'text-slate-300 hover:text-white hover:bg-white/8' }}">
                <i class="fas fa-store w-5 text-center {{ request()->routeIs('super_admin.restaurants.*') ? 'text-emerald-400' : 'text-slate-400' }}"></i>
                <span>Restaurants</span>
            </a>

            <a href="{{ route('super_admin.managers.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                    {{ request()->routeIs('super_admin.managers.*')
                        ? 'bg-white/10 text-white' : 'text-slate-300 hover:text-white hover:bg-white/8' }}">
                <i class="fas fa-user-tie w-5 text-center {{ request()->routeIs('super_admin.managers.*') ? 'text-blue-400' : 'text-slate-400' }}"></i>
                <span>Managers</span>
            </a>
        </nav>

        {{-- User footer --}}
        <div class="flex-shrink-0 px-3 py-3" style="border-top: 1px solid rgba(255,255,255,0.07)">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-400 to-indigo-500 flex items-center justify-center text-white text-xs font-bold shadow">
                    {{ strtoupper(substr(auth()->user()->name ?? 'SA', 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-xs font-semibold truncate">{{ auth()->user()->name ?? 'Super Admin' }}</p>
                    <p class="text-slate-400 text-xs truncate">{{ auth()->user()->email ?? '' }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition" title="Logout">
                        <i class="fas fa-arrow-right-from-bracket text-xs"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main content --}}
    <div class="flex-1 flex flex-col lg:ml-[260px] min-h-screen">
        {{-- Header --}}
        <header class="sticky top-0 z-30 flex items-center justify-between h-14 px-4 bg-white border-b border-gray-200 shadow-sm">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="lg:hidden w-9 h-9 flex items-center justify-center rounded-lg hover:bg-gray-100 transition">
                    <i class="fas fa-bars text-gray-600"></i>
                </button>
                <h1 class="text-base font-semibold text-gray-800">@yield('header', 'Dashboard')</h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-400 hidden sm:block">{{ now()->format('D, M d, Y') }}</span>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 p-4 sm:p-6">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
