@php $user = auth()->user(); @endphp

<header class="bg-white border-b border-gray-100 h-16 flex items-center justify-between px-5 flex-shrink-0 shadow-sm z-10">

    {{-- Left: Toggle + Breadcrumb --}}
    <div class="flex items-center gap-3">
        <button id="sidebarToggleBtn"
            class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none transition">
            <i class="fas fa-bars-staggered text-base"></i>
        </button>

        {{-- Breadcrumb --}}
        <div class="hidden sm:flex items-center gap-1.5 text-sm">
            <a href="{{ route('admin.dashboard') }}" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-house text-xs"></i>
            </a>
            <i class="fas fa-chevron-right text-gray-300 text-[10px]"></i>
            <span class="text-gray-700 font-semibold">@yield('title', 'Dashboard')</span>
        </div>
    </div>

    {{-- Right: Actions --}}
    <div class="flex items-center gap-2">

        {{-- Search --}}
        <div class="hidden md:flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-sm text-gray-400 w-48 hover:border-gray-300 transition cursor-pointer">
            <i class="fas fa-search text-xs"></i>
            <span class="text-xs">Quick search...</span>
            <span class="ml-auto text-[10px] bg-gray-200 text-gray-500 px-1 rounded font-mono">⌘K</span>
        </div>

        {{-- Notifications --}}
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open"
                class="relative w-9 h-9 flex items-center justify-center rounded-xl text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition focus:outline-none">
                <i class="fas fa-bell text-base"></i>
                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
            </button>

            <div x-show="open" x-cloak @click.outside="open = false"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 top-11 w-80 bg-white rounded-2xl shadow-xl border border-gray-100 z-50 overflow-hidden">

                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <h3 class="font-semibold text-gray-800 text-sm">Notifications</h3>
                        <span class="text-xs bg-red-100 text-red-600 font-semibold px-1.5 py-0.5 rounded-full">3</span>
                    </div>
                    <a href="{{ route('admin.notifications.index') }}"
                        class="text-xs text-blue-600 hover:underline font-medium">Mark all read</a>
                </div>

                <div class="divide-y divide-gray-50">
                    <a href="#" class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition">
                        <div class="w-8 h-8 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-receipt text-blue-600 text-xs"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-700 leading-snug">New order <span class="font-semibold">#1234</span> received</p>
                            <p class="text-xs text-gray-400 mt-0.5">2 minutes ago</p>
                        </div>
                        <span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0 mt-1.5"></span>
                    </a>
                    <a href="#" class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition">
                        <div class="w-8 h-8 bg-yellow-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-triangle-exclamation text-yellow-600 text-xs"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-700 leading-snug">Low stock alert: <span class="font-semibold">Rice</span></p>
                            <p class="text-xs text-gray-400 mt-0.5">15 minutes ago</p>
                        </div>
                        <span class="w-2 h-2 rounded-full bg-yellow-500 flex-shrink-0 mt-1.5"></span>
                    </a>
                    <a href="#" class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition">
                        <div class="w-8 h-8 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-circle-check text-green-600 text-xs"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-700 leading-snug">Payment received for <span class="font-semibold">Invoice #567</span></p>
                            <p class="text-xs text-gray-400 mt-0.5">1 hour ago</p>
                        </div>
                    </a>
                </div>

                <a href="{{ route('admin.notifications.index') }}"
                    class="block text-center text-xs text-blue-600 hover:text-blue-800 font-medium py-3 border-t border-gray-100 hover:bg-gray-50 transition">
                    View all notifications →
                </a>
            </div>
        </div>

        {{-- User Menu --}}
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open"
                class="flex items-center gap-2 pl-1 pr-3 py-1 rounded-xl hover:bg-gray-100 transition focus:outline-none">
                @php
                    $initials = collect(explode(' ', $user->name ?? 'Admin User'))
                        ->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
                @endphp
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                    {{ $initials }}
                </div>
                <div class="hidden sm:block text-left">
                    <p class="text-sm font-semibold text-gray-700 leading-none">{{ $user->name ?? 'Admin' }}</p>
                    <p class="text-xs text-gray-400 leading-none mt-0.5">{{ ucfirst($user->roles->first()?->name ?? 'Administrator') }}</p>
                </div>
                <i class="fas fa-chevron-down text-xs text-gray-400 hidden sm:block"></i>
            </button>

            <div x-show="open" x-cloak @click.outside="open = false"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 top-11 w-56 bg-white rounded-2xl shadow-xl border border-gray-100 z-50 overflow-hidden">

                {{-- Profile header --}}
                <div class="flex items-center gap-3 px-4 py-3.5 border-b border-gray-100 bg-gray-50">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center text-white text-sm font-bold shadow">
                        {{ $initials }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $user->name ?? 'Admin' }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $user->email ?? '' }}</p>
                    </div>
                </div>

                <div class="py-1.5">
                    <a href="{{ route('admin.profile.edit') }}"
                        class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="fas fa-user-pen w-4 text-gray-400 text-center"></i> Profile
                    </a>
                    <a href="{{ route('admin.settings.index') }}"
                        class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="fas fa-gear w-4 text-gray-400 text-center"></i> Settings
                    </a>
                    <a href="{{ route('admin.payments.dashboard') }}"
                        class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="fas fa-chart-pie w-4 text-gray-400 text-center"></i> Pay Dashboard
                    </a>
                </div>

                <div class="border-t border-gray-100 py-1.5">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                            <i class="fas fa-arrow-right-from-bracket w-4 text-center"></i> Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

{{-- Sidebar toggle script --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar    = document.getElementById('sidebar');
        const toggleBtn  = document.getElementById('sidebarToggleBtn');
        const toggleBtn2 = document.getElementById('sidebarToggle');

        function toggle() {
            sidebar.classList.toggle('collapsed');
            const isCollapsed = sidebar.classList.contains('collapsed');
            sidebar.style.width = isCollapsed ? '64px' : '260px';
        }

        if (toggleBtn)  toggleBtn.addEventListener('click', toggle);
        if (toggleBtn2) toggleBtn2.addEventListener('click', toggle);
    });
</script>
