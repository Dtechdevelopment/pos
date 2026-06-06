@extends('admin.layouts.app')

@section('title', 'Permissions Matrix')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Permissions Matrix</h1>
        <p class="text-sm text-gray-500 mt-0.5">Visual overview of all role permissions by module.</p>
    </div>
    <a href="{{ route('admin.roles.index') }}"
        class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 text-sm font-medium shadow-sm transition">
        <i class="fas fa-shield-halved mr-2 text-gray-400"></i>Manage Roles
    </a>
</div>

{{-- ── Summary Bar ─────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-gradient-to-br from-indigo-500 to-purple-700 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/10 rounded-full"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-2">
                <p class="text-indigo-100 text-sm font-medium">Total Permissions</p>
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-key text-sm"></i>
                </div>
            </div>
            <p class="text-3xl font-bold">{{ number_format($totalPermissions) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center gap-3">
        <div class="w-11 h-11 bg-violet-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-layer-group text-violet-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Module Groups</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalGroups }}</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center gap-3">
        <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-shield-halved text-blue-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Total Roles</p>
            <p class="text-2xl font-bold text-gray-800">{{ $roles->count() }}</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center gap-3">
        <div class="w-11 h-11 bg-emerald-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-circle-check text-emerald-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Avg per Role</p>
            <p class="text-2xl font-bold text-gray-800">
                {{ $roles->count() > 0 ? round($roles->sum('permissions_count') / $roles->count()) : 0 }}
            </p>
        </div>
    </div>
</div>

{{-- ── Role Coverage Cards ──────────────────────────────────────────── --}}
@php
    $roleStyles = [
        'admin'         => ['from-red-500 to-rose-700',       'fa-crown',         'bg-red-50 text-red-700',    'bg-red-500'],
        'manager'       => ['from-purple-500 to-violet-700',  'fa-user-tie',      'bg-purple-50 text-purple-700','bg-purple-500'],
        'cashier'       => ['from-blue-500 to-indigo-700',    'fa-cash-register', 'bg-blue-50 text-blue-700',  'bg-blue-500'],
        'waiter'        => ['from-teal-500 to-cyan-700',      'fa-utensils',      'bg-teal-50 text-teal-700',  'bg-teal-500'],
        'kitchen_staff' => ['from-orange-500 to-red-600',     'fa-fire-burner',   'bg-orange-50 text-orange-700','bg-orange-500'],
        'kitchen staff' => ['from-orange-500 to-red-600',     'fa-fire-burner',   'bg-orange-50 text-orange-700','bg-orange-500'],
        'default'       => ['from-slate-500 to-gray-700',     'fa-shield-halved', 'bg-gray-50 text-gray-700',  'bg-gray-400'],
    ];
@endphp
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
    @foreach($roles as $role)
    @php
        $rs       = $roleStyles[strtolower($role->name)] ?? $roleStyles['default'];
        $pct      = $totalPermissions > 0 ? round(($role->permissions_count / $totalPermissions) * 100) : 0;
    @endphp
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center hover:shadow-md transition-shadow">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $rs[0] }} flex items-center justify-center mx-auto mb-2 shadow-sm">
            <i class="fas {{ $rs[1] }} text-white text-sm"></i>
        </div>
        <p class="text-xs font-semibold text-gray-700 mb-1">
            {{ ucwords(str_replace('_', ' ', $role->name)) }}
        </p>
        <p class="text-lg font-bold text-gray-800">{{ $role->permissions_count }}</p>
        <p class="text-xs text-gray-400 mb-2">of {{ $totalPermissions }}</p>
        <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
            <div class="h-1.5 rounded-full {{ $rs[3] }} transition-all" style="width: {{ $pct }}%"></div>
        </div>
        <p class="text-xs text-gray-400 mt-1">{{ $pct }}%</p>
    </div>
    @endforeach
</div>

{{-- ── Search / Filter Toolbar ─────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-5 flex flex-wrap items-center justify-between gap-3">
    <div class="relative flex-1 min-w-[200px]">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
            <i class="fas fa-search text-xs"></i>
        </span>
        <input type="text" id="permSearch" placeholder="Search permissions..."
            class="w-full pl-8 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-gray-50">
    </div>
    <div class="flex items-center gap-2 text-xs text-gray-500">
        <i class="fas fa-circle-info text-gray-400"></i>
        <span id="visibleCount">{{ $totalPermissions }} permissions shown</span>
    </div>
</div>

{{-- ── Permissions Matrix ───────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" id="matrixContainer">

    {{-- Sticky header --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm" id="matrixTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide sticky left-0 bg-gray-50 min-w-[200px] z-10">
                        Permission
                    </th>
                    @foreach($roles as $role)
                    @php $rs = $roleStyles[strtolower($role->name)] ?? $roleStyles['default']; @endphp
                    <th class="text-center py-3 px-3 min-w-[100px]">
                        <div class="flex flex-col items-center gap-1.5">
                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br {{ $rs[0] }} flex items-center justify-center shadow-sm">
                                <i class="fas {{ $rs[1] }} text-white text-xs"></i>
                            </div>
                            <span class="text-xs font-semibold text-gray-600 whitespace-nowrap">
                                {{ ucwords(str_replace('_', ' ', $role->name)) }}
                            </span>
                        </div>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($permissions as $group => $groupPermissions)
                @php
                    // Count how many roles have ANY permission in this group
                    $groupCoverageByRole = $roles->map(fn($r) =>
                        $groupPermissions->filter(fn($p) => isset($rolePerms[$r->id][$p->name]))->count()
                    );
                @endphp

                {{-- Group header row --}}
                <tr class="perm-group-row border-b border-gray-100" data-group="{{ strtolower($group) }}">
                    <td colspan="{{ 1 + $roles->count() }}"
                        class="py-0">
                        <button type="button"
                            class="w-full flex items-center gap-3 px-4 py-3 bg-gradient-to-r from-gray-50 to-white hover:from-indigo-50 hover:to-white transition text-left group"
                            onclick="toggleGroup('{{ $group }}', this)">
                            <div class="w-7 h-7 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-200 transition">
                                <i class="fas fa-layer-group text-indigo-500 text-xs"></i>
                            </div>
                            <span class="text-sm font-semibold text-gray-700 capitalize flex-1">
                                {{ ucwords(str_replace(['_', '.'], ' ', $group)) }}
                            </span>
                            <span class="text-xs bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded-full font-medium">
                                {{ $groupPermissions->count() }}
                            </span>
                            <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform group-chevron"></i>
                        </button>
                    </td>
                </tr>

                {{-- Permission rows --}}
                @foreach($groupPermissions as $permission)
                @php
                    $action = explode('.', $permission->name)[1] ?? $permission->name;
                @endphp
                <tr class="perm-row border-b border-gray-50 hover:bg-indigo-50/20 transition-colors"
                    data-group="{{ $group }}"
                    data-name="{{ strtolower($permission->name) }}">
                    <td class="py-2.5 px-4 sticky left-0 bg-white hover:bg-indigo-50/20">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-200 flex-shrink-0"></span>
                            <div>
                                <p class="text-xs font-medium text-gray-700">{{ $permission->name }}</p>
                                <p class="text-xs text-gray-400 capitalize">{{ ucwords(str_replace('_', ' ', $action)) }}</p>
                            </div>
                        </div>
                    </td>
                    @foreach($roles as $role)
                    @php $has = isset($rolePerms[$role->id][$permission->name]); @endphp
                    <td class="text-center py-2.5 px-3">
                        @if($has)
                        <div class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-green-100">
                            <i class="fas fa-check text-green-600 text-xs"></i>
                        </div>
                        @else
                        <div class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-100">
                            <i class="fas fa-minus text-gray-300 text-xs"></i>
                        </div>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach
                @empty
                <tr>
                    <td colspan="{{ 1 + $roles->count() }}" class="py-16 text-center">
                        <i class="fas fa-key text-4xl text-gray-200 block mb-3"></i>
                        <p class="text-gray-400 text-sm">No permissions found.</p>
                        <p class="text-gray-400 text-xs mt-1">Run seeders to populate permissions.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // ── Toggle group rows ─────────────────────────────────────────────
    function toggleGroup(group, btn) {
        const rows    = document.querySelectorAll(`.perm-row[data-group="${group}"]`);
        const chevron = btn.querySelector('.group-chevron');
        const isHidden = rows[0]?.style.display === 'none';

        rows.forEach(r => r.style.display = isHidden ? '' : 'none');
        chevron.style.transform = isHidden ? '' : 'rotate(-90deg)';
    }

    // ── Search filter ─────────────────────────────────────────────────
    document.getElementById('permSearch').addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        let visible = 0;

        document.querySelectorAll('.perm-row').forEach(row => {
            const match = !q || row.dataset.name.includes(q);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        // Show/hide group headers based on whether they have visible children
        document.querySelectorAll('.perm-group-row').forEach(groupRow => {
            const group = groupRow.dataset.group;
            const hasVisible = [...document.querySelectorAll(`.perm-row[data-group="${group}"]`)]
                .some(r => r.style.display !== 'none');

            groupRow.style.display = hasVisible ? '' : 'none';

            // Auto-expand groups when searching
            if (q && hasVisible) {
                document.querySelectorAll(`.perm-row[data-group="${group}"]`)
                    .forEach(r => r.style.display = '');
                const chevron = groupRow.querySelector('.group-chevron');
                if (chevron) chevron.style.transform = '';
            }
        });

        document.getElementById('visibleCount').textContent =
            `${visible} permission${visible !== 1 ? 's' : ''} shown`;
    });

    // ── Init: all groups expanded by default ──────────────────────────
    // (no collapse on load for permissions matrix — all visible)
</script>
@endpush
