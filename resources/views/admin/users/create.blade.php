@extends('admin.layouts.app')

@section('title', 'Create User')

@section('content')

{{-- Page Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Create New User</h1>
        <p class="text-sm text-gray-500 mt-1">Fill in the details below to add a new system user.</p>
    </div>
    <a href="{{ route('admin.users.index') }}"
        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors shadow-sm text-sm font-medium">
        <i class="fas fa-arrow-left mr-2 text-gray-400"></i>Back to Users
    </a>
</div>

@if($errors->any())
<div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4 flex items-start gap-3">
    <i class="fas fa-circle-exclamation text-red-500 mt-0.5"></i>
    <div>
        <p class="text-sm font-medium text-red-700">Please fix the following errors:</p>
        <ul class="mt-1 list-disc list-inside text-sm text-red-600 space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<form action="{{ route('admin.users.store') }}" method="POST" id="createUserForm">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT: Avatar Card --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col items-center text-center">
                <div class="relative mb-4">
                    <div id="avatarCircle"
                        class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center text-white text-3xl font-bold shadow-md select-none">
                        <span id="avatarInitials"><i class="fas fa-user text-2xl opacity-70"></i></span>
                    </div>
                    <span class="absolute bottom-0 right-0 w-7 h-7 bg-green-400 border-2 border-white rounded-full" id="statusDot"></span>
                </div>
                <p id="previewName" class="text-gray-400 text-sm italic">Name will appear here</p>
                <p id="previewRole" class="text-xs text-gray-400 mt-1">— No role selected —</p>
            </div>

            {{-- Account Settings --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4 flex items-center gap-2">
                    <i class="fas fa-shield-halved text-indigo-500"></i> Account Settings
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">
                            Role <span class="text-red-500">*</span>
                        </label>
                        <select name="role" id="roleSelect"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-gray-50 transition" required>
                            <option value="">— Select Role —</option>
                            @foreach($roles ?? [] as $role)
                                <option value="{{ $role->id }}" {{ old('role') == $role->id ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Branch</label>
                        <select name="branch_id"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-gray-50 transition">
                            <option value="">— No Branch —</option>
                            @foreach($branches ?? [] as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Status</label>
                        <div class="flex gap-3">
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="status" value="active" class="sr-only peer" checked>
                                <div class="peer-checked:bg-green-50 peer-checked:border-green-400 peer-checked:text-green-700 border border-gray-200 rounded-lg px-3 py-2.5 text-center text-sm font-medium text-gray-500 transition hover:border-green-300">
                                    <i class="fas fa-circle-check mr-1"></i> Active
                                </div>
                            </label>
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="status" value="inactive" class="sr-only peer">
                                <div class="peer-checked:bg-red-50 peer-checked:border-red-400 peer-checked:text-red-700 border border-gray-200 rounded-lg px-3 py-2.5 text-center text-sm font-medium text-gray-500 transition hover:border-red-300">
                                    <i class="fas fa-circle-xmark mr-1"></i> Inactive
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT: Main Form --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Personal Information --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-5 flex items-center gap-2">
                    <i class="fas fa-user text-blue-500"></i> Personal Information
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <i class="fas fa-user text-sm"></i>
                            </span>
                            <input type="text" name="name" id="nameInput" value="{{ old('name') }}"
                                placeholder="e.g. John Doe"
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 transition" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <i class="fas fa-envelope text-sm"></i>
                            </span>
                            <input type="email" name="email" value="{{ old('email') }}"
                                placeholder="john@example.com"
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 transition" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Phone Number</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <i class="fas fa-phone text-sm"></i>
                            </span>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                placeholder="+1 (555) 000-0000"
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 transition">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Security --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-5 flex items-center gap-2">
                    <i class="fas fa-lock text-yellow-500"></i> Security
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <i class="fas fa-key text-sm"></i>
                            </span>
                            <input type="password" name="password" id="passwordInput"
                                placeholder="Min. 8 characters"
                                class="w-full pl-9 pr-10 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 bg-gray-50 transition" required>
                            <button type="button" onclick="togglePassword('passwordInput', 'eyeIcon1')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i id="eyeIcon1" class="fas fa-eye text-sm"></i>
                            </button>
                        </div>
                        {{-- Strength Bar --}}
                        <div class="mt-2 space-y-1">
                            <div class="flex gap-1">
                                <div class="h-1 flex-1 rounded-full bg-gray-200" id="s1"></div>
                                <div class="h-1 flex-1 rounded-full bg-gray-200" id="s2"></div>
                                <div class="h-1 flex-1 rounded-full bg-gray-200" id="s3"></div>
                                <div class="h-1 flex-1 rounded-full bg-gray-200" id="s4"></div>
                            </div>
                            <p class="text-xs text-gray-400" id="strengthLabel">Enter a password</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">
                            Confirm Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <i class="fas fa-key text-sm"></i>
                            </span>
                            <input type="password" name="password_confirmation" id="confirmInput"
                                placeholder="Repeat password"
                                class="w-full pl-9 pr-10 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 bg-gray-50 transition" required>
                            <button type="button" onclick="togglePassword('confirmInput', 'eyeIcon2')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i id="eyeIcon2" class="fas fa-eye text-sm"></i>
                            </button>
                        </div>
                        <p class="text-xs mt-1.5 hidden" id="matchMsg"></p>
                    </div>
                </div>

                <div class="mt-4 bg-blue-50 border border-blue-100 rounded-lg p-3 flex items-start gap-2">
                    <i class="fas fa-circle-info text-blue-400 mt-0.5 text-sm"></i>
                    <p class="text-xs text-blue-600">Use at least 8 characters with a mix of letters, numbers, and symbols for a strong password.</p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.users.index') }}"
                    class="px-5 py-2.5 border border-gray-300 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg text-sm font-semibold shadow-sm transition-all">
                    <i class="fas fa-user-plus mr-2"></i> Create User
                </button>
            </div>

        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    // Live avatar preview
    const nameInput = document.getElementById('nameInput');
    const avatarInitials = document.getElementById('avatarInitials');
    const previewName = document.getElementById('previewName');
    const avatarColors = [
        'from-blue-400 to-indigo-600',
        'from-green-400 to-teal-600',
        'from-orange-400 to-red-500',
        'from-purple-400 to-pink-600',
        'from-yellow-400 to-orange-500',
    ];

    nameInput.addEventListener('input', function () {
        const val = this.value.trim();
        previewName.textContent = val || 'Name will appear here';
        previewName.classList.toggle('italic', !val);
        previewName.classList.toggle('text-gray-400', !val);
        previewName.classList.toggle('text-gray-700', !!val);
        previewName.classList.toggle('font-semibold', !!val);

        if (val) {
            const parts = val.split(' ').filter(Boolean);
            const initials = parts.length >= 2
                ? parts[0][0].toUpperCase() + parts[parts.length - 1][0].toUpperCase()
                : parts[0].slice(0, 2).toUpperCase();
            avatarInitials.textContent = initials;
            const colorIdx = val.charCodeAt(0) % avatarColors.length;
            const circle = document.getElementById('avatarCircle');
            circle.className = `w-24 h-24 rounded-full bg-gradient-to-br ${avatarColors[colorIdx]} flex items-center justify-center text-white text-3xl font-bold shadow-md select-none`;
        } else {
            avatarInitials.innerHTML = '<i class="fas fa-user text-2xl opacity-70"></i>';
        }
    });

    // Role preview
    document.getElementById('roleSelect').addEventListener('change', function () {
        const label = this.options[this.selectedIndex].text;
        document.getElementById('previewRole').textContent = this.value ? label : '— No role selected —';
    });

    // Toggle password visibility
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    // Password strength
    document.getElementById('passwordInput').addEventListener('input', function () {
        const val = this.value;
        const bars = ['s1', 's2', 's3', 's4'];
        const label = document.getElementById('strengthLabel');
        let score = 0;
        if (val.length >= 8) score++;
        if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
        if (/\d/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const colors = ['bg-red-400', 'bg-orange-400', 'bg-yellow-400', 'bg-green-500'];
        const labels = ['Weak', 'Fair', 'Good', 'Strong'];
        const textColors = ['text-red-500', 'text-orange-500', 'text-yellow-600', 'text-green-600'];

        bars.forEach((id, i) => {
            const el = document.getElementById(id);
            el.className = `h-1 flex-1 rounded-full ${i < score ? colors[score - 1] : 'bg-gray-200'}`;
        });

        if (val.length === 0) {
            label.textContent = 'Enter a password';
            label.className = 'text-xs text-gray-400';
        } else {
            label.textContent = `Strength: ${labels[score - 1] || 'Very Weak'}`;
            label.className = `text-xs ${score > 0 ? textColors[score - 1] : 'text-red-500'}`;
        }

        checkMatch();
    });

    // Confirm password match
    document.getElementById('confirmInput').addEventListener('input', checkMatch);

    function checkMatch() {
        const pw = document.getElementById('passwordInput').value;
        const cf = document.getElementById('confirmInput').value;
        const msg = document.getElementById('matchMsg');
        if (!cf) { msg.classList.add('hidden'); return; }
        msg.classList.remove('hidden');
        if (pw === cf) {
            msg.textContent = '✓ Passwords match';
            msg.className = 'text-xs mt-1.5 text-green-600';
        } else {
            msg.textContent = '✗ Passwords do not match';
            msg.className = 'text-xs mt-1.5 text-red-500';
        }
    }
</script>
@endpush
