@extends('admin.layouts.app')

@section('title', 'Roles & Permissions')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Roles & Permissions</h1>
        <p class="text-sm text-gray-500 mt-0.5">Define roles and control what each one can access.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.permissions.index') }}"
            class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 text-sm font-medium shadow-sm transition">
            <i class="fas fa-key mr-2 text-gray-400"></i>Permissions
        </a>
        <a href="{{ route('admin.roles.create') }}"
            class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-violet-500 to-purple-700 hover:from-violet-600 hover:to-purple-800 text-white rounded-lg text-sm font-semibold shadow-sm transition-all">
            <i class="fas fa-plus mr-2"></i>New Role
        </a>
    </div>
</div>

@if(session('success'))
<div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3">
    <i class="fas fa-circle-check text-green-500"></i>
    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
</div>
@endif

{{-- ── Summary Bar ─────────────────────────────────────────────────── --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-gradient-to-br from-violet-500 to-purple-700 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/10 rounded-full"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-2">
                <p class="text-violet-100 text-sm font-medium">Total Roles</p>
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shield-halved text-sm"></i>
                </div>
            </div>
            <p class="text-3xl font-bold">{{ $roles->count() }}</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center gap-3">
        <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-key text-blue-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Total Permissions</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($totalPermissions) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center gap-3">
        <div class="w-11 h-11 bg-indigo-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-users text-indigo-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Users with Roles</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($totalUsers) }}</p>
        </div>
    </div>
</div>

{{-- ── Role Cards Grid ─────────────────────────────────────────────── --}}
@php
    $roleStyles = [
        'admin'         => ['from-red-500 to-rose-700',       'fa-crown',          'bg-red-50 border-red-200'],
        'manager'       => ['from-purple-500 to-violet-700',  'fa-user-tie',       'bg-purple-50 border-purple-200'],
        'cashier'       => ['from-blue-500 to-indigo-700',    'fa-cash-register',  'bg-blue-50 border-blue-200'],
        'waiter'        => ['from-teal-500 to-cyan-700',      'fa-utensils',       'bg-teal-50 border-teal-200'],
        'kitchen_staff' => ['from-orange-500 to-red-600',     'fa-fire-burner',    'bg-orange-50 border-orange-200'],
        'kitchen staff' => ['from-orange-500 to-red-600',     'fa-fire-burner',    'bg-orange-50 border-orange-200'],
        'default'       => ['from-slate-500 to-gray-700',     'fa-shield-halved',  'bg-gray-50 border-gray-200'],
    ];

    $groupLabels = [
        'admin'         => 'Full system access',
        'manager'       => 'Branch management',
        'cashier'       => 'Billing & payments',
        'waiter'        => 'Orders & service',
        'kitchen_staff' => 'Kitchen operations',
        'kitchen staff' => 'Kitchen operations',
    ];
@endphp

@if($roles->count())
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 mb-6">
    @foreach($roles as $role)
    @php
        $key     = strtolower($role->name);
        $style   = $roleStyles[$key] ?? $roleStyles['default'];
        $desc    = $groupLabels[$key] ?? 'Custom role';
        $permCount = $role->permissions->count();
        $userCount = $role->users_count ?? 0;

        // Group permissions by module
        $permGroups = $role->permissions->groupBy(fn($p) => explode('.', $p->name)[0] ?? 'general');
    @endphp
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
        {{-- Color top strip --}}
        <div class="h-1.5 bg-gradient-to-r {{ $style[0] }}"></div>

        <div class="p-5">
            {{-- Role header --}}
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br {{ $style[0] }} flex items-center justify-center shadow-sm flex-shrink-0">
                        <i class="fas {{ $style[1] }} text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">
                            {{ ucwords(str_replace('_', ' ', $role->name)) }}
                        </h3>
                        <p class="text-xs text-gray-400">{{ $desc }}</p>
                    </div>
                </div>
                <code class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded font-mono self-start">
                    {{ $role->name }}
                </code>
            </div>

            {{-- Stats row --}}
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="bg-gray-50 rounded-xl p-3 text-center">
                    <p class="text-2xl font-bold text-gray-800">{{ $userCount }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        <i class="fas fa-user text-gray-300 mr-1"></i>Users
                    </p>
                </div>
                <div class="bg-violet-50 rounded-xl p-3 text-center">
                    <p class="text-2xl font-bold text-violet-700">{{ $permCount }}</p>
                    <p class="text-xs text-violet-400 mt-0.5">
                        <i class="fas fa-key text-violet-300 mr-1"></i>Permissions
                    </p>
                </div>
            </div>

            {{-- Permission group pills --}}
            @if($permGroups->count())
            <div class="flex flex-wrap gap-1.5 mb-4">
                @foreach($permGroups->take(6) as $group => $perms)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 {{ $style[2] }} rounded-full text-xs font-medium border">
                    {{ ucwords(str_replace('_', ' ', $group)) }}
                    <span class="text-[10px] opacity-70">({{ $perms->count() }})</span>
                </span>
                @endforeach
                @if($permGroups->count() > 6)
                <span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full text-xs font-medium border border-gray-200">
                    +{{ $permGroups->count() - 6 }} more
                </span>
                @endif
            </div>
            @else
            <p class="text-xs text-gray-400 italic mb-4">No permissions assigned yet.</p>
            @endif

            {{-- Actions --}}
            <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
                <a href="{{ route('admin.roles.edit', $role) }}"
                    class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-violet-50 text-violet-700 hover:bg-violet-100 rounded-lg text-xs font-semibold transition">
                    <i class="fas fa-pen"></i> Edit Role
                </a>
                <a href="{{ route('admin.users.index', ['role' => $role->name]) }}"
                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg text-xs font-medium transition"
                    title="View users with this role">
                    <i class="fas fa-users"></i>
                    <span>{{ $userCount }}</span>
                </a>
                <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                    onsubmit="return confirm('Delete role \'{{ addslashes(ucwords(str_replace('_',' ',$role->name))) }}\'? Users with this role will lose it.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition"
                        title="Delete role">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    {{-- Add Role Card --}}
    <a href="{{ route('admin.roles.create') }}"
        class="bg-white rounded-2xl border-2 border-dashed border-gray-200 hover:border-violet-400 hover:bg-violet-50/30 flex flex-col items-center justify-center p-8 text-center transition-all group min-h-[240px]">
        <div class="w-14 h-14 rounded-2xl bg-gray-100 group-hover:bg-violet-100 flex items-center justify-center mb-3 transition">
            <i class="fas fa-plus text-gray-400 group-hover:text-violet-600 text-xl transition"></i>
        </div>
        <p class="text-sm font-semibold text-gray-500 group-hover:text-violet-700 transition">Create New Role</p>
        <p class="text-xs text-gray-400 mt-1">Define custom permissions</p>
    </a>
</div>

{{-- ── Permission Overview Table ───────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 bg-violet-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-table text-violet-500 text-xs"></i>
            </div>
            <span class="text-sm font-semibold text-gray-700">Permissions Matrix</span>
            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full font-medium">
                {{ $roles->count() }} roles × {{ $totalPermissions }} permissions
            </span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left py-3 px-4 font-medium text-gray-500 sticky left-0 bg-gray-50 min-w-[140px]">Role</th>
                    <th class="text-center py-2 px-3 font-medium text-gray-500">Users</th>
                    <th class="text-center py-2 px-3 font-medium text-gray-500">Permissions</th>
                    <th class="text-left py-2 px-4 font-medium text-gray-500">Modules Covered</th>
                    <th class="text-center py-2 px-3 font-medium text-gray-500">Created</th>
                    <th class="text-center py-2 px-3 font-medium text-gray-500">Coverage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $role)
                @php
                    $key2      = strtolower($role->name);
                    $style2    = $roleStyles[$key2] ?? $roleStyles['default'];
                    $pCount    = $role->permissions->count();
                    $coverage  = $totalPermissions > 0 ? round(($pCount / $totalPermissions) * 100) : 0;
                    $modules   = $role->permissions->groupBy(fn($p) => explode('.', $p->name)[0] ?? 'general')->keys();
                @endphp
                <tr class="border-b border-gray-50 hover:bg-violet-50/20 transition-colors">
                    <td class="py-3 px-4 sticky left-0 bg-white">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br {{ $style2[0] }} flex items-center justify-center flex-shrink-0">
                                <i class="fas {{ $style2[1] }} text-white text-xs"></i>
                            </div>
                            <span class="font-semibold text-gray-700">
                                {{ ucwords(str_replace('_', ' ', $role->name)) }}
                            </span>
                        </div>
                    </td>
                    <td class="py-3 px-3 text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-semibold
                            {{ ($role->users_count ?? 0) > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-400' }}">
                            {{ $role->users_count ?? 0 }}
                        </span>
                    </td>
                    <td class="py-3 px-3 text-center">
                        <span class="font-bold text-violet-700">{{ $pCount }}</span>
                        <span class="text-gray-400"> / {{ $totalPermissions }}</span>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex flex-wrap gap-1">
                            @foreach($modules->take(5) as $mod)
                            <span class="px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px]">{{ $mod }}</span>
                            @endforeach
                            @if($modules->count() > 5)
                            <span class="px-1.5 py-0.5 bg-gray-100 text-gray-400 rounded text-[10px]">+{{ $modules->count() - 5 }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-3 text-center text-gray-400">
                        {{ $role->created_at->format('M d, Y') }}
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                                <div class="h-2 rounded-full bg-gradient-to-r {{ $style2[0] }} transition-all"
                                    style="width: {{ $coverage }}%"></div>
                            </div>
                            <span class="text-gray-500 w-7 text-right font-medium">{{ $coverage }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@else
{{-- Empty state --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 py-20 text-center">
    <div class="w-20 h-20 rounded-full bg-violet-50 flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-shield-halved text-violet-300 text-3xl"></i>
    </div>
    <h3 class="text-gray-600 font-semibold mb-1">No roles yet</h3>
    <p class="text-sm text-gray-400 mb-5">Create your first role to control user access.</p>
    <a href="{{ route('admin.roles.create') }}"
        class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-violet-500 to-purple-700 text-white rounded-lg text-sm font-semibold shadow-sm hover:from-violet-600 hover:to-purple-800 transition">
        <i class="fas fa-plus mr-2"></i> Create First Role
    </a>
</div>
@endif

@endsection
