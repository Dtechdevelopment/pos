@extends('admin.layouts.app')

@section('title', 'User Management')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">User Management</h1>
        <p class="text-sm text-gray-500 mt-0.5">Manage staff accounts, roles and access.</p>
    </div>
    <a href="{{ route('admin.users.create') }}"
        class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-lg text-sm font-semibold shadow-sm transition-all">
        <i class="fas fa-user-plus mr-2"></i> Add User
    </a>
</div>

@if(session('success'))
<div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3">
    <i class="fas fa-circle-check text-green-500"></i>
    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
</div>
@endif

{{-- ── Summary Cards ───────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-gradient-to-br from-blue-500 to-indigo-700 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/10 rounded-full"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-2">
                <p class="text-blue-100 text-sm font-medium">Total Users</p>
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-sm"></i>
                </div>
            </div>
            <p class="text-3xl font-bold">{{ number_format($summary['total']) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3 cursor-pointer hover:shadow-md transition"
        onclick="filterByStatus('active')">
        <div class="w-11 h-11 bg-green-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-circle-check text-green-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Active</p>
            <p class="text-2xl font-bold text-green-700">{{ number_format($summary['active']) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3 cursor-pointer hover:shadow-md transition"
        onclick="filterByStatus('suspended')">
        <div class="w-11 h-11 bg-red-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-circle-xmark text-red-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Suspended</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($summary['suspended']) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-11 h-11 bg-emerald-50 rounded-xl flex items-center justify-center flex-shrink-0 relative">
            <i class="fas fa-circle text-emerald-500"></i>
            <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
        </div>
        <div>
            <p class="text-xs text-gray-500">Online (15 min)</p>
            <p class="text-2xl font-bold text-emerald-600">{{ number_format($summary['online']) }}</p>
        </div>
    </div>
</div>

{{-- ── Filters ─────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-5">
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-end gap-3" id="filterForm">
        <input type="hidden" name="status" id="statusHidden" value="{{ request('status') }}">

        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Search</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-search text-xs"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Name, email, or phone..."
                    class="w-full pl-8 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50">
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Role</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-shield-halved text-xs"></i>
                </span>
                <select name="role"
                    class="pl-8 pr-8 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 appearance-none">
                    <option value="">All Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                            {{ ucwords(str_replace('_', ' ', $role->name)) }}
                        </option>
                    @endforeach
                </select>
                <span class="absolute inset-y-0 right-0 pr-2 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-chevron-down text-xs"></i>
                </span>
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Branch</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-store text-xs"></i>
                </span>
                <select name="branch_id"
                    class="pl-8 pr-8 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 appearance-none">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                <span class="absolute inset-y-0 right-0 pr-2 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-chevron-down text-xs"></i>
                </span>
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                <i class="fas fa-search mr-1.5"></i> Filter
            </button>
            @if(request()->hasAny(['search', 'role', 'status', 'branch_id']))
            <a href="{{ route('admin.users.index') }}"
                class="inline-flex items-center px-4 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                <i class="fas fa-xmark mr-1.5"></i> Clear
            </a>
            @endif
        </div>
    </form>
</div>

{{-- Status quick-filter pills --}}
<div class="flex flex-wrap gap-2 mb-4">
    @php
        $statusPills = [
            ''          => ['All',       'bg-gray-800 text-white',       'bg-gray-100 text-gray-600 hover:bg-gray-200'],
            'active'    => ['Active',    'bg-green-600 text-white',      'bg-green-50 text-green-700 hover:bg-green-100'],
            'suspended' => ['Suspended', 'bg-red-500 text-white',        'bg-red-50 text-red-600 hover:bg-red-100'],
            'inactive'  => ['Inactive',  'bg-gray-500 text-white',       'bg-gray-50 text-gray-600 hover:bg-gray-100'],
        ];
    @endphp
    @foreach($statusPills as $val => [$label, $active, $inactive])
    <a href="{{ route('admin.users.index', array_merge(request()->except('status','page'), $val ? ['status' => $val] : [])) }}"
        class="px-3 py-1.5 rounded-full text-xs font-semibold transition
            {{ request('status', '') === $val ? $active : $inactive }}">
        {{ $label }}
    </a>
    @endforeach
</div>

{{-- ── Table ───────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">User</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Contact</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Role</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Branch</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Status</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Last Login</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                @php
                    $initials = collect(explode(' ', $user->name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
                    $gradients = [
                        'from-blue-400 to-indigo-600',
                        'from-violet-400 to-purple-600',
                        'from-emerald-400 to-teal-600',
                        'from-orange-400 to-red-500',
                        'from-pink-400 to-rose-600',
                        'from-cyan-400 to-blue-500',
                        'from-amber-400 to-orange-500',
                        'from-lime-400 to-green-500',
                    ];
                    $grad = $gradients[$user->id % count($gradients)];

                    $role     = $user->roles->first();
                    $roleName = $role ? ucwords(str_replace('_', ' ', $role->name)) : null;

                    $roleColors = [
                        'admin'          => 'bg-red-100 text-red-700',
                        'manager'        => 'bg-purple-100 text-purple-700',
                        'cashier'        => 'bg-blue-100 text-blue-700',
                        'waiter'         => 'bg-teal-100 text-teal-700',
                        'kitchen staff'  => 'bg-orange-100 text-orange-700',
                        'kitchen_staff'  => 'bg-orange-100 text-orange-700',
                    ];
                    $roleCls = $roleColors[strtolower($role?->name ?? '')] ?? 'bg-gray-100 text-gray-600';

                    $isOnline   = $user->last_login_at && $user->last_login_at->gte(now()->subMinutes(15));
                    $statusCfg  = [
                        'active'    => ['bg-green-100 text-green-700',   'fa-circle-check'],
                        'suspended' => ['bg-red-100 text-red-600',       'fa-circle-xmark'],
                        'inactive'  => ['bg-gray-100 text-gray-500',     'fa-circle-minus'],
                    ];
                    [$sBadge, $sIcon] = $statusCfg[$user->status] ?? ['bg-gray-100 text-gray-500', 'fa-circle'];
                @endphp
                <tr class="border-b border-gray-50 hover:bg-blue-50/20 transition-colors">

                    {{-- User --}}
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-3">
                            <div class="relative flex-shrink-0">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br {{ $grad }} flex items-center justify-center text-white text-xs font-bold shadow-sm">
                                    {{ $initials }}
                                </div>
                                @if($isOnline)
                                <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-green-400 border-2 border-white"></span>
                                @endif
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800 text-sm">{{ $user->name }}</p>
                                <p class="text-xs text-gray-400">ID #{{ $user->id }}</p>
                            </div>
                        </div>
                    </td>

                    {{-- Contact --}}
                    <td class="py-3 px-4">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-1.5 text-xs text-gray-600">
                                <i class="fas fa-envelope text-gray-400 w-3 text-center"></i>
                                <span class="truncate max-w-[160px]">{{ $user->email }}</span>
                            </div>
                            @if($user->phone)
                            <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                <i class="fas fa-phone text-gray-400 w-3 text-center"></i>
                                {{ $user->phone }}
                            </div>
                            @endif
                        </div>
                    </td>

                    {{-- Role --}}
                    <td class="py-3 px-4">
                        @if($roleName)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold {{ $roleCls }}">
                            <i class="fas fa-shield-halved text-[9px]"></i>
                            {{ $roleName }}
                        </span>
                        @else
                        <span class="text-gray-300 text-xs">— No role —</span>
                        @endif
                    </td>

                    {{-- Branch --}}
                    <td class="py-3 px-4 text-xs text-gray-600">
                        @if($user->branch)
                        <div class="flex items-center gap-1.5">
                            <i class="fas fa-store text-gray-400 text-xs"></i>
                            {{ $user->branch->name }}
                        </div>
                        @else
                        <span class="text-gray-300">—</span>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td class="py-3 px-4 text-center">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $sBadge }}">
                            <i class="fas {{ $sIcon }} text-[9px]"></i>
                            {{ ucfirst($user->status) }}
                        </span>
                    </td>

                    {{-- Last Login --}}
                    <td class="py-3 px-4 text-center">
                        @if($user->last_login_at)
                        <div>
                            <p class="text-xs {{ $isOnline ? 'text-green-600 font-medium' : 'text-gray-600' }}">
                                {{ $isOnline ? '🟢 Online' : $user->last_login_at->diffForHumans() }}
                            </p>
                            <p class="text-xs text-gray-400">{{ $user->last_login_at->format('M d, H:i') }}</p>
                        </div>
                        @else
                        <span class="text-xs text-gray-400">Never</span>
                        @endif
                    </td>

                    {{-- Actions --}}
                    <td class="py-3 px-4">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('admin.users.edit', $user) }}"
                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition"
                                title="Edit">
                                <i class="fas fa-pen text-xs"></i>
                            </a>

                            <form action="{{ route('admin.users.suspend', $user) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg transition
                                        {{ $user->status === 'active'
                                            ? 'bg-yellow-50 text-yellow-600 hover:bg-yellow-100'
                                            : 'bg-green-50 text-green-600 hover:bg-green-100' }}"
                                    title="{{ $user->status === 'active' ? 'Suspend' : 'Activate' }}">
                                    <i class="fas {{ $user->status === 'active' ? 'fa-user-slash' : 'fa-user-check' }} text-xs"></i>
                                </button>
                            </form>

                            <form action="{{ route('admin.users.reset-password', $user) }}" method="POST"
                                onsubmit="return confirm('Reset password for {{ addslashes($user->name) }}? A new random password will be generated.')">
                                @csrf
                                <button type="submit"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-purple-50 text-purple-600 hover:bg-purple-100 transition"
                                    title="Reset Password">
                                    <i class="fas fa-key text-xs"></i>
                                </button>
                            </form>

                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                onsubmit="return confirm('Delete user {{ addslashes($user->name) }}? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition"
                                    title="Delete">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-16 text-center">
                        <i class="fas fa-users text-4xl text-gray-200 block mb-3"></i>
                        <p class="text-gray-400 text-sm">No users found.</p>
                        @if(request()->hasAny(['search','role','status','branch_id']))
                            <a href="{{ route('admin.users.index') }}"
                                class="text-blue-500 text-sm mt-1 hover:underline inline-block">Clear filters</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
    <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
        <p class="text-xs text-gray-500">
            Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }} users
        </p>
        {{ $users->links() }}
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
    function filterByStatus(status) {
        document.getElementById('statusHidden').value = status;
        document.getElementById('filterForm').submit();
    }
</script>
@endpush
