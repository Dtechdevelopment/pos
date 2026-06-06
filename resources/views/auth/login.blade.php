<x-guest-layout>
<div class="min-h-screen flex">

    {{-- ── Left Panel: Branding ──────────────────────────────────────── --}}
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden"
        style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 40%, #1e293b 100%);">

        {{-- Decorative circles --}}
        <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full opacity-20"
            style="background: radial-gradient(circle, #6366f1, transparent)"></div>
        <div class="absolute -bottom-32 -right-16 w-[500px] h-[500px] rounded-full opacity-10"
            style="background: radial-gradient(circle, #f97316, transparent)"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full opacity-5"
            style="background: radial-gradient(circle, #8b5cf6, transparent)"></div>

        {{-- Grid overlay --}}
        <div class="absolute inset-0 opacity-5"
            style="background-image: linear-gradient(rgba(255,255,255,.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.1) 1px, transparent 1px); background-size: 40px 40px;"></div>

        {{-- Content --}}
        <div class="relative z-10 flex flex-col justify-between p-12 w-full">
            {{-- Logo --}}
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center shadow-lg">
                    <i class="fas fa-utensils text-white text-lg"></i>
                </div>
                <div>
                    <p class="text-white font-bold text-lg leading-none">{{ config('app.name', 'POS Admin') }}</p>
                    <p class="text-slate-400 text-xs leading-none mt-0.5">Restaurant Management</p>
                </div>
            </div>

            {{-- Center content --}}
            <div>
                <h1 class="text-4xl font-bold text-white leading-tight mb-4">
                    Your restaurant,<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-red-500">
                        fully in control.
                    </span>
                </h1>
                <p class="text-slate-400 text-base leading-relaxed max-w-sm">
                    Manage orders, kitchen, billing, inventory and your entire team from one place.
                </p>

                {{-- Feature pills --}}
                <div class="flex flex-wrap gap-2 mt-8">
                    @foreach(['Orders', 'Kitchen', 'Billing', 'Inventory', 'Reports', 'Staff'] as $feat)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium text-slate-300 border border-white/10 bg-white/5 backdrop-blur-sm">
                        <i class="fas fa-check text-orange-400 text-[9px]"></i>
                        {{ $feat }}
                    </span>
                    @endforeach
                </div>
            </div>

            {{-- Bottom stat row --}}
            <div class="grid grid-cols-3 gap-4">
                @foreach([['fa-receipt','Orders','Tracked'],['fa-users','Staff','Managed'],['fa-chart-line','Revenue','Monitored']] as [$ic,$lab,$sub])
                <div class="text-center">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center mx-auto mb-2">
                        <i class="fas {{ $ic }} text-orange-400"></i>
                    </div>
                    <p class="text-white text-sm font-semibold">{{ $lab }}</p>
                    <p class="text-slate-500 text-xs">{{ $sub }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Right Panel: Login Form ────────────────────────────────────── --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 lg:p-12 bg-gray-950">
        <div class="w-full max-w-md">

            {{-- Mobile logo --}}
            <div class="flex items-center gap-3 mb-8 lg:hidden">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center shadow-lg">
                    <i class="fas fa-utensils text-white"></i>
                </div>
                <p class="text-white font-bold">{{ config('app.name', 'POS Admin') }}</p>
            </div>

            {{-- Heading --}}
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-white mb-1">Welcome back</h2>
                <p class="text-slate-400 text-sm">Sign in to your account to continue.</p>
            </div>

            {{-- Session status --}}
            @if(session('status'))
            <div class="mb-5 bg-blue-900/50 border border-blue-700/50 rounded-xl p-3.5 flex items-center gap-3">
                <i class="fas fa-circle-info text-blue-400"></i>
                <p class="text-sm text-blue-300">{{ session('status') }}</p>
            </div>
            @endif

            {{-- Validation errors --}}
            @if($errors->any())
            <div class="mb-5 bg-red-900/40 border border-red-700/50 rounded-xl p-3.5 flex items-start gap-3">
                <i class="fas fa-circle-exclamation text-red-400 mt-0.5"></i>
                <div>
                    @foreach($errors->all() as $error)
                    <p class="text-sm text-red-300">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Email Address
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500 pointer-events-none">
                            <i class="fas fa-envelope text-sm"></i>
                        </span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                            placeholder="admin@restaurant.com"
                            required autofocus autocomplete="username"
                            class="w-full pl-10 pr-4 py-3 rounded-xl text-sm text-white placeholder-slate-600
                                bg-white/5 border border-white/10
                                focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500/50
                                transition">
                    </div>
                </div>

                {{-- Password --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-sm font-medium text-slate-300">
                            Password
                        </label>
                        @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                            class="text-xs text-orange-400 hover:text-orange-300 transition">
                            Forgot password?
                        </a>
                        @endif
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500 pointer-events-none">
                            <i class="fas fa-lock text-sm"></i>
                        </span>
                        <input id="password" type="password" name="password"
                            placeholder="••••••••"
                            required autocomplete="current-password"
                            class="w-full pl-10 pr-12 py-3 rounded-xl text-sm text-white placeholder-slate-600
                                bg-white/5 border border-white/10
                                focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500/50
                                transition">
                        <button type="button" id="togglePw"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300 transition">
                            <i class="fas fa-eye text-sm" id="pwEyeIcon"></i>
                        </button>
                    </div>
                </div>

                {{-- Remember me --}}
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2.5 cursor-pointer group">
                        <div class="relative">
                            <input id="remember_me" type="checkbox" name="remember"
                                class="sr-only peer">
                            <div class="w-10 h-6 bg-white/10 border border-white/20 peer-focus:ring-2 peer-focus:ring-orange-500/40 rounded-full
                                peer-checked:bg-orange-500 peer-checked:border-orange-500
                                after:content-[''] after:absolute after:top-0.5 after:left-0.5
                                after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all
                                peer-checked:after:translate-x-4 transition"></div>
                        </div>
                        <span class="text-sm text-slate-400 group-hover:text-slate-300 transition">Remember me</span>
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit"
                    class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-sm font-semibold text-white
                        bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700
                        shadow-lg shadow-orange-500/20 focus:outline-none focus:ring-2 focus:ring-orange-500/50
                        transition-all active:scale-[0.98]">
                    <i class="fas fa-arrow-right-to-bracket"></i>
                    Sign In
                </button>
            </form>

            {{-- Footer --}}
            <p class="mt-8 text-center text-xs text-slate-600">
                {{ config('app.name', 'POS Admin') }} &copy; {{ date('Y') }} — Restaurant Management System
            </p>
        </div>
    </div>

</div>

<script>
    // Password visibility toggle
    document.getElementById('togglePw').addEventListener('click', function () {
        const input = document.getElementById('password');
        const icon  = document.getElementById('pwEyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });
</script>
</x-guest-layout>
