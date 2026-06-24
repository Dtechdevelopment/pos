<header class="flex items-center justify-between h-16 px-6 bg-white border-b border-gray-200 flex-shrink-0">
    <div>
        <h2 class="text-lg font-semibold text-gray-800">@yield('header', 'Dashboard')</h2>
    </div>
    <div class="flex items-center gap-4">
        <span class="text-xs text-gray-500">{{ now()->format('D, M d, Y') }}</span>
    </div>
</header>
