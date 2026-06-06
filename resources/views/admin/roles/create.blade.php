@extends('admin.layouts.app')

@section('title', 'Create Role')

@section('content')

{{-- Page Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Create New Role</h1>
        <p class="text-sm text-gray-500 mt-1">Define a role name and assign the permissions it should have.</p>
    </div>
    <a href="{{ route('admin.roles.index') }}"
        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors shadow-sm text-sm font-medium">
        <i class="fas fa-arrow-left mr-2 text-gray-400"></i>Back to Roles
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

<form action="{{ route('admin.roles.store') }}" method="POST" id="roleForm">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT: Role Identity --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- Role Card Preview --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col items-center text-center">
                <div id="roleIconCircle"
                    class="w-20 h-20 rounded-2xl bg-gradient-to-br from-violet-500 to-purple-700 flex items-center justify-center shadow-md mb-4">
                    <i id="roleIconDisplay" class="fas fa-shield-halved text-white text-3xl"></i>
                </div>
                <p id="previewRoleName" class="text-gray-400 text-sm italic">Role name will appear here</p>
                <p id="previewPermCount" class="text-xs text-gray-400 mt-1">0 permissions selected</p>
            </div>

            {{-- Role Details --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fas fa-tag text-violet-500"></i> Role Details
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">
                            Role Name <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-shield-halved text-sm"></i>
                            </span>
                            <input type="text" name="name" id="roleNameInput" value="{{ old('name') }}"
                                placeholder="e.g. cashier, manager"
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 bg-gray-50 transition"
                                required>
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">Use lowercase with underscores. e.g. <code class="bg-gray-100 px-1 rounded">kitchen_staff</code></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Icon</label>
                        <div class="grid grid-cols-5 gap-2" id="iconPicker">
                            @php
                                $icons = [
                                    'fa-shield-halved'  => 'violet',
                                    'fa-user-tie'       => 'blue',
                                    'fa-utensils'       => 'orange',
                                    'fa-cash-register'  => 'green',
                                    'fa-chef-hat'      => 'red',
                                    'fa-user-gear'      => 'indigo',
                                    'fa-star'           => 'yellow',
                                    'fa-lock'           => 'gray',
                                    'fa-crown'          => 'amber',
                                    'fa-users'          => 'teal',
                                ];
                                $bgMap = [
                                    'violet' => 'bg-violet-100 text-violet-600 border-violet-300',
                                    'blue'   => 'bg-blue-100 text-blue-600 border-blue-300',
                                    'orange' => 'bg-orange-100 text-orange-600 border-orange-300',
                                    'green'  => 'bg-green-100 text-green-600 border-green-300',
                                    'red'    => 'bg-red-100 text-red-600 border-red-300',
                                    'indigo' => 'bg-indigo-100 text-indigo-600 border-indigo-300',
                                    'yellow' => 'bg-yellow-100 text-yellow-600 border-yellow-300',
                                    'gray'   => 'bg-gray-100 text-gray-600 border-gray-300',
                                    'amber'  => 'bg-amber-100 text-amber-600 border-amber-300',
                                    'teal'   => 'bg-teal-100 text-teal-600 border-teal-300',
                                ];
                                $gradMap = [
                                    'violet' => 'from-violet-500 to-purple-700',
                                    'blue'   => 'from-blue-500 to-indigo-600',
                                    'orange' => 'from-orange-400 to-red-500',
                                    'green'  => 'from-green-400 to-teal-600',
                                    'red'    => 'from-red-400 to-rose-600',
                                    'indigo' => 'from-indigo-400 to-blue-700',
                                    'yellow' => 'from-yellow-400 to-orange-500',
                                    'gray'   => 'from-gray-400 to-slate-600',
                                    'amber'  => 'from-amber-400 to-orange-600',
                                    'teal'   => 'from-teal-400 to-cyan-600',
                                ];
                            @endphp
                            @foreach($icons as $icon => $color)
                            <button type="button"
                                data-icon="{{ $icon }}"
                                data-color="{{ $color }}"
                                data-gradient="{{ $gradMap[$color] }}"
                                onclick="selectIcon(this)"
                                class="icon-btn w-full aspect-square rounded-lg border-2 flex items-center justify-center transition
                                    {{ $loop->first ? 'border-violet-400 '.$bgMap[$color] : 'border-transparent '.$bgMap[$color] }}">
                                <i class="fas {{ $icon }} text-sm"></i>
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Summary box --}}
            <div class="bg-gradient-to-br from-violet-50 to-purple-50 border border-violet-100 rounded-xl p-4">
                <p class="text-xs font-semibold text-violet-700 uppercase tracking-wide mb-2">Selection Summary</p>
                <div id="summaryList" class="space-y-1 text-xs text-violet-600">
                    <p class="text-gray-400 italic">No permissions selected yet.</p>
                </div>
            </div>
        </div>

        {{-- RIGHT: Permissions --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Toolbar --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-wrap items-center justify-between gap-3">
                <div class="relative flex-1 min-w-[180px]">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                        <i class="fas fa-search text-sm"></i>
                    </span>
                    <input type="text" id="permSearch" placeholder="Search permissions..."
                        class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 bg-gray-50 transition">
                </div>
                <div class="flex items-center gap-2">
                    <span id="selectedCount" class="text-sm text-gray-500 font-medium">0 selected</span>
                    <button type="button" onclick="selectAll()"
                        class="px-3 py-2 text-xs font-medium bg-violet-50 text-violet-700 border border-violet-200 rounded-lg hover:bg-violet-100 transition">
                        Select All
                    </button>
                    <button type="button" onclick="clearAll()"
                        class="px-3 py-2 text-xs font-medium bg-gray-50 text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-100 transition">
                        Clear All
                    </button>
                </div>
            </div>

            {{-- Permission Groups --}}
            <div class="space-y-4" id="permissionsContainer">
                @forelse($permissions ?? [] as $group => $groupPermissions)
                <div class="perm-group bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    {{-- Group Header --}}
                    <div class="flex items-center justify-between px-5 py-3 bg-gray-50 border-b border-gray-100 cursor-pointer group-toggle"
                        data-group="{{ $group }}">
                        <div class="flex items-center gap-3">
                            <label class="flex items-center gap-2 cursor-pointer" onclick="event.stopPropagation()">
                                <input type="checkbox" class="group-check rounded border-gray-300 text-violet-600 focus:ring-violet-400"
                                    data-group="{{ $group }}" onchange="toggleGroup('{{ $group }}', this.checked)">
                            </label>
                            <span class="text-sm font-semibold text-gray-700 capitalize">
                                {{ ucwords(str_replace(['_', '.'], ' ', $group)) }}
                            </span>
                            <span class="text-xs px-2 py-0.5 bg-violet-100 text-violet-600 rounded-full font-medium group-badge" data-group="{{ $group }}">
                                0 / {{ count($groupPermissions) }}
                            </span>
                        </div>
                        <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform group-chevron"></i>
                    </div>
                    {{-- Permissions Grid --}}
                    <div class="group-body px-5 py-4" style="display:none">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                            @foreach($groupPermissions as $permission)
                            <label class="perm-item flex items-center gap-2.5 p-2.5 rounded-lg border border-transparent hover:border-violet-200 hover:bg-violet-50 cursor-pointer transition group"
                                data-name="{{ strtolower($permission->name) }}">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                    class="perm-check rounded border-gray-300 text-violet-600 focus:ring-violet-400"
                                    data-group="{{ $group }}"
                                    onchange="onPermChange()"
                                    {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                <span class="text-sm text-gray-600 group-hover:text-violet-700 transition leading-tight">
                                    {{ $permission->name }}
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-xl border border-gray-100 p-10 text-center">
                    <i class="fas fa-lock-open text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-400 text-sm">No permissions found. Run <code class="bg-gray-100 px-1 rounded">php artisan db:seed</code> to populate permissions.</p>
                </div>
                @endforelse
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.roles.index') }}"
                    class="px-5 py-2.5 border border-gray-300 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-violet-600 to-purple-700 hover:from-violet-700 hover:to-purple-800 text-white rounded-lg text-sm font-semibold shadow-sm transition-all">
                    <i class="fas fa-plus mr-2"></i> Create Role
                </button>
            </div>

        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    // ── Icon Picker ──────────────────────────────────────────────────────
    function selectIcon(btn) {
        document.querySelectorAll('.icon-btn').forEach(b => {
            b.classList.remove('border-violet-400', 'border-blue-400', 'border-orange-400',
                'border-green-400', 'border-red-400', 'border-indigo-400',
                'border-yellow-400', 'border-gray-400', 'border-amber-400', 'border-teal-400');
            b.classList.add('border-transparent');
        });
        const color = btn.dataset.color;
        btn.classList.remove('border-transparent');
        btn.classList.add(`border-${color}-400`);

        const circle = document.getElementById('roleIconCircle');
        const grad = btn.dataset.gradient;
        circle.className = `w-20 h-20 rounded-2xl bg-gradient-to-br ${grad} flex items-center justify-center shadow-md mb-4`;
        document.getElementById('roleIconDisplay').className = `fas ${btn.dataset.icon} text-white text-3xl`;
    }

    // ── Role name live preview ────────────────────────────────────────────
    document.getElementById('roleNameInput').addEventListener('input', function () {
        const val = this.value.trim();
        const preview = document.getElementById('previewRoleName');
        preview.textContent = val
            ? val.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
            : 'Role name will appear here';
        preview.classList.toggle('italic', !val);
        preview.classList.toggle('text-gray-400', !val);
        preview.classList.toggle('text-gray-800', !!val);
        preview.classList.toggle('font-semibold', !!val);
    });

    // ── Collapse / Expand groups ─────────────────────────────────────────
    document.querySelectorAll('.group-toggle').forEach(header => {
        header.addEventListener('click', function () {
            const body = this.nextElementSibling;
            const chevron = this.querySelector('.group-chevron');
            const isHidden = body.style.display === 'none';
            body.style.display = isHidden ? '' : 'none';
            chevron.style.transform = isHidden ? '' : 'rotate(-90deg)';
        });
    });

    // ── Toggle all in a group ─────────────────────────────────────────────
    function toggleGroup(group, checked) {
        document.querySelectorAll(`.perm-check[data-group="${group}"]`).forEach(cb => {
            cb.checked = checked;
        });
        onPermChange();
    }

    // ── Select / Clear all ───────────────────────────────────────────────
    function selectAll() {
        document.querySelectorAll('.perm-check:not([style*="display:none"])').forEach(cb => cb.checked = true);
        document.querySelectorAll('.group-check').forEach(cb => cb.checked = true);
        onPermChange();
    }
    function clearAll() {
        document.querySelectorAll('.perm-check').forEach(cb => cb.checked = false);
        document.querySelectorAll('.group-check').forEach(cb => cb.checked = false);
        onPermChange();
    }

    // ── Update counters & summary ─────────────────────────────────────────
    function onPermChange() {
        let total = 0;

        document.querySelectorAll('.perm-group').forEach(group => {
            const groupName = group.querySelector('.group-toggle').dataset.group;
            const all = group.querySelectorAll('.perm-check');
            const checked = group.querySelectorAll('.perm-check:checked');
            const badge = group.querySelector('.group-badge');
            const groupCb = group.querySelector('.group-check');

            badge.textContent = `${checked.length} / ${all.length}`;
            badge.className = `text-xs px-2 py-0.5 rounded-full font-medium group-badge ${
                checked.length === 0 ? 'bg-gray-100 text-gray-500' :
                checked.length === all.length ? 'bg-violet-600 text-white' :
                'bg-violet-100 text-violet-600'
            }`;
            groupCb.checked = checked.length === all.length;
            groupCb.indeterminate = checked.length > 0 && checked.length < all.length;
            total += checked.length;
        });

        document.getElementById('selectedCount').textContent = `${total} selected`;
        document.getElementById('previewPermCount').textContent =
            total === 0 ? '0 permissions selected' : `${total} permission${total !== 1 ? 's' : ''} selected`;

        // Summary list
        const summaryEl = document.getElementById('summaryList');
        const groups = {};
        document.querySelectorAll('.perm-check:checked').forEach(cb => {
            const g = cb.dataset.group;
            groups[g] = (groups[g] || 0) + 1;
        });
        if (Object.keys(groups).length === 0) {
            summaryEl.innerHTML = '<p class="text-gray-400 italic">No permissions selected yet.</p>';
        } else {
            summaryEl.innerHTML = Object.entries(groups).map(([g, n]) =>
                `<div class="flex items-center justify-between">
                    <span class="capitalize">${g.replace(/_/g, ' ')}</span>
                    <span class="font-semibold text-violet-700">${n}</span>
                </div>`
            ).join('');
        }
    }

    // ── Search / Filter ──────────────────────────────────────────────────
    document.getElementById('permSearch').addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('.perm-group').forEach(group => {
            let anyVisible = false;
            group.querySelectorAll('.perm-item').forEach(item => {
                const match = !q || item.dataset.name.includes(q);
                item.style.display = match ? '' : 'none';
                if (match) anyVisible = true;
            });
            group.style.display = anyVisible ? '' : 'none';
            // Auto-expand groups when searching
            if (q) {
                group.querySelector('.group-body').style.display = '';
                group.querySelector('.group-chevron').style.transform = '';
            }
        });
    });

    // ── Init ─────────────────────────────────────────────────────────────
    // Collapse all groups on load
    document.querySelectorAll('.group-chevron').forEach(c => c.style.transform = 'rotate(-90deg)');
    onPermChange();
</script>
@endpush
