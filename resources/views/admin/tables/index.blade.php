@extends('admin.layouts.app')

@section('title', 'Tables')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Restaurant Tables</h1>
        <p class="text-sm text-gray-500 mt-1">Monitor and manage your dining floor.</p>
    </div>
    <a href="{{ route('admin.tables.create') }}"
        class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-lg text-sm font-semibold shadow-sm transition-all">
        <i class="fas fa-plus mr-2"></i> Add Table
    </a>
</div>

@if(session('success'))
<div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4 flex items-center gap-3">
    <i class="fas fa-circle-check text-green-500"></i>
    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
</div>
@endif

{{-- Status Summary --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $statuses = [
            'available'  => ['label' => 'Available',  'icon' => 'fa-circle-check',  'bg' => 'bg-green-50',  'icon_color' => 'text-green-500',  'text' => 'text-green-700',  'count_color' => 'text-green-800'],
            'occupied'   => ['label' => 'Occupied',   'icon' => 'fa-users',          'bg' => 'bg-red-50',    'icon_color' => 'text-red-500',    'text' => 'text-red-700',    'count_color' => 'text-red-800'],
            'reserved'   => ['label' => 'Reserved',   'icon' => 'fa-clock',          'bg' => 'bg-yellow-50', 'icon_color' => 'text-yellow-500', 'text' => 'text-yellow-700', 'count_color' => 'text-yellow-800'],
            'cleaning'   => ['label' => 'Cleaning',   'icon' => 'fa-broom',          'bg' => 'bg-blue-50',   'icon_color' => 'text-blue-400',   'text' => 'text-blue-700',   'count_color' => 'text-blue-800'],
        ];
    @endphp
    @foreach($statuses as $key => $s)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3 cursor-pointer hover:shadow-md transition-shadow filter-btn"
        data-status="{{ $key }}" onclick="filterByStatus('{{ $key }}', this)">
        <div class="w-11 h-11 rounded-xl {{ $s['bg'] }} flex items-center justify-center flex-shrink-0">
            <i class="fas {{ $s['icon'] }} {{ $s['icon_color'] }} text-lg"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">{{ $s['label'] }}</p>
            <p class="text-2xl font-bold {{ $s['count_color'] }}">{{ $statusCounts[$key] ?? 0 }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- Toolbar --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-5 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-2 flex-wrap">
        {{-- Branch filter --}}
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                <i class="fas fa-store text-xs"></i>
            </span>
            <select id="branchFilter" onchange="applyFilters()"
                class="pl-8 pr-8 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 appearance-none">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
            <span class="absolute inset-y-0 right-0 pr-2 flex items-center text-gray-400 pointer-events-none">
                <i class="fas fa-chevron-down text-xs"></i>
            </span>
        </div>

        {{-- Capacity filter --}}
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                <i class="fas fa-users text-xs"></i>
            </span>
            <select id="capacityFilter" onchange="applyFilters()"
                class="pl-8 pr-8 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 appearance-none">
                <option value="">Any Capacity</option>
                <option value="2">2 seats</option>
                <option value="4">4 seats</option>
                <option value="6">6 seats</option>
                <option value="8">8+ seats</option>
            </select>
            <span class="absolute inset-y-0 right-0 pr-2 flex items-center text-gray-400 pointer-events-none">
                <i class="fas fa-chevron-down text-xs"></i>
            </span>
        </div>

        {{-- Clear filters --}}
        <button onclick="clearFilters()"
            class="px-3 py-2 text-xs font-medium text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
            <i class="fas fa-xmark mr-1"></i> Clear
        </button>
    </div>

    <div class="flex items-center gap-2">
        {{-- View toggle --}}
        <div class="flex items-center bg-gray-100 rounded-lg p-1">
            <button onclick="setView('grid')" id="gridBtn"
                class="view-btn px-3 py-1.5 rounded-md text-xs font-medium transition bg-white shadow-sm text-gray-700">
                <i class="fas fa-border-all mr-1"></i> Grid
            </button>
            <button onclick="setView('list')" id="listBtn"
                class="view-btn px-3 py-1.5 rounded-md text-xs font-medium transition text-gray-500">
                <i class="fas fa-list mr-1"></i> List
            </button>
        </div>

        <span id="visibleCount" class="text-sm text-gray-500 font-medium">
            {{ $tables->count() }} tables
        </span>
    </div>
</div>

{{-- GRID VIEW --}}
<div id="gridView">
    @if($tables->count())
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4" id="tableGrid">
        @foreach($tables as $table)
        @php
            $st = $table->status;
            $styles = [
                'available' => ['border' => 'border-green-200',  'bg' => 'bg-green-50',   'badge' => 'bg-green-100 text-green-700',  'icon_bg' => 'bg-green-100',  'icon' => 'text-green-500',  'dot' => 'bg-green-400'],
                'occupied'  => ['border' => 'border-red-200',    'bg' => 'bg-red-50',     'badge' => 'bg-red-100 text-red-700',      'icon_bg' => 'bg-red-100',    'icon' => 'text-red-500',    'dot' => 'bg-red-500'],
                'reserved'  => ['border' => 'border-yellow-200', 'bg' => 'bg-yellow-50',  'badge' => 'bg-yellow-100 text-yellow-700','icon_bg' => 'bg-yellow-100', 'icon' => 'text-yellow-600', 'dot' => 'bg-yellow-400'],
                'cleaning'  => ['border' => 'border-blue-200',   'bg' => 'bg-blue-50',    'badge' => 'bg-blue-100 text-blue-700',    'icon_bg' => 'bg-blue-100',   'icon' => 'text-blue-400',   'dot' => 'bg-blue-400'],
            ];
            $style = $styles[$st] ?? $styles['available'];
        @endphp
        <div class="table-card bg-white rounded-xl border-2 {{ $style['border'] }} p-4 hover:shadow-md transition-all cursor-pointer group relative"
            data-status="{{ $st }}"
            data-branch="{{ $table->branch_id }}"
            data-capacity="{{ $table->capacity }}"
            onclick="window.location='{{ route('admin.tables.edit', $table) }}'">

            {{-- Status dot --}}
            <span class="absolute top-3 right-3 w-2.5 h-2.5 rounded-full {{ $style['dot'] }} ring-2 ring-white"></span>

            {{-- Icon --}}
            <div class="w-12 h-12 rounded-xl {{ $style['icon_bg'] }} flex items-center justify-center mx-auto mb-3 group-hover:scale-105 transition-transform">
                <i class="fas fa-chair {{ $style['icon'] }} text-xl"></i>
            </div>

            {{-- Table number --}}
            <p class="text-center font-bold text-gray-800 text-lg leading-tight">{{ $table->table_number }}</p>

            {{-- Branch --}}
            <p class="text-center text-xs text-gray-400 mt-0.5 truncate">{{ $table->branch->name ?? '—' }}</p>

            {{-- Capacity --}}
            <div class="flex items-center justify-center gap-1 mt-2">
                @for($i = 0; $i < min($table->capacity, 8); $i++)
                    <i class="fas fa-user text-[8px] {{ $style['icon'] }} opacity-70"></i>
                @endfor
                @if($table->capacity > 8)
                    <span class="text-xs text-gray-400">+{{ $table->capacity - 8 }}</span>
                @endif
            </div>

            {{-- Status badge --}}
            <div class="mt-3 text-center">
                <span class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $style['badge'] }}">
                    {{ ucfirst($st) }}
                </span>
            </div>

            {{-- Hover actions --}}
            <div class="absolute inset-0 bg-white/90 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2"
                onclick="event.stopPropagation()">
                <a href="{{ route('admin.tables.edit', $table) }}"
                    class="w-9 h-9 flex items-center justify-center rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition"
                    title="Edit">
                    <i class="fas fa-pen text-sm"></i>
                </a>
                <form action="{{ route('admin.tables.destroy', $table) }}" method="POST"
                    onsubmit="return confirm('Delete table {{ addslashes($table->table_number) }}?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="w-9 h-9 flex items-center justify-center rounded-lg bg-red-100 text-red-500 hover:bg-red-200 transition"
                        title="Delete">
                        <i class="fas fa-trash text-sm"></i>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 py-20 text-center">
        <div class="w-20 h-20 rounded-full bg-blue-50 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-chair text-blue-300 text-3xl"></i>
        </div>
        <h3 class="text-gray-600 font-semibold mb-1">No tables yet</h3>
        <p class="text-sm text-gray-400 mb-5">Add your first table to get started.</p>
        <a href="{{ route('admin.tables.create') }}"
            class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg text-sm font-semibold shadow-sm hover:from-blue-600 hover:to-indigo-700 transition">
            <i class="fas fa-plus mr-2"></i> Add First Table
        </a>
    </div>
    @endif
</div>

{{-- LIST VIEW --}}
<div id="listView" class="hidden">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Table</th>
                        <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Branch</th>
                        <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Capacity</th>
                        <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Status</th>
                        <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableList">
                    @forelse($tables as $table)
                    @php
                        $stStyles = [
                            'available' => ['badge' => 'bg-green-100 text-green-700',  'icon_bg' => 'bg-green-100',  'icon' => 'text-green-500'],
                            'occupied'  => ['badge' => 'bg-red-100 text-red-700',      'icon_bg' => 'bg-red-100',    'icon' => 'text-red-500'],
                            'reserved'  => ['badge' => 'bg-yellow-100 text-yellow-700','icon_bg' => 'bg-yellow-100', 'icon' => 'text-yellow-600'],
                            'cleaning'  => ['badge' => 'bg-blue-100 text-blue-700',    'icon_bg' => 'bg-blue-100',   'icon' => 'text-blue-400'],
                        ];
                        $ls = $stStyles[$table->status] ?? $stStyles['available'];
                    @endphp
                    <tr class="list-row border-b border-gray-50 hover:bg-gray-50/70 transition-colors"
                        data-status="{{ $table->status }}"
                        data-branch="{{ $table->branch_id }}"
                        data-capacity="{{ $table->capacity }}">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg {{ $ls['icon_bg'] }} flex items-center justify-center">
                                    <i class="fas fa-chair {{ $ls['icon'] }} text-sm"></i>
                                </div>
                                <span class="font-semibold text-gray-800">{{ $table->table_number }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-gray-600">{{ $table->branch->name ?? '—' }}</td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-0.5">
                                @for($i = 0; $i < min($table->capacity, 6); $i++)
                                    <i class="fas fa-user text-[9px] text-gray-400"></i>
                                @endfor
                                <span class="text-xs text-gray-500 ml-1">{{ $table->capacity }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $ls['badge'] }}">
                                <i class="fas fa-circle text-[6px]"></i>
                                {{ ucfirst($table->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('admin.tables.edit', $table) }}"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition">
                                    <i class="fas fa-pen text-xs"></i>
                                </a>
                                <form action="{{ route('admin.tables.destroy', $table) }}" method="POST"
                                    onsubmit="return confirm('Delete table {{ addslashes($table->table_number) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-gray-400">No tables found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Pagination --}}
@if($tables->hasPages())
<div class="mt-4">{{ $tables->links() }}</div>
@endif

@endsection

@push('scripts')
<script>
    // Shared style map for list view (PHP can't share vars across blade sections cleanly)
    const statusStyles = {
        available: { badge: 'bg-green-100 text-green-700',  icon_bg: 'bg-green-100',  icon: 'text-green-500'  },
        occupied:  { badge: 'bg-red-100 text-red-700',      icon_bg: 'bg-red-100',    icon: 'text-red-500'    },
        reserved:  { badge: 'bg-yellow-100 text-yellow-700',icon_bg: 'bg-yellow-100', icon: 'text-yellow-600' },
        cleaning:  { badge: 'bg-blue-100 text-blue-700',    icon_bg: 'bg-blue-100',   icon: 'text-blue-400'   },
    };

    // ── View toggle ──────────────────────────────────────────────────────
    function setView(view) {
        const isGrid = view === 'grid';
        document.getElementById('gridView').classList.toggle('hidden', !isGrid);
        document.getElementById('listView').classList.toggle('hidden', isGrid);
        document.getElementById('gridBtn').className = isGrid
            ? 'view-btn px-3 py-1.5 rounded-md text-xs font-medium transition bg-white shadow-sm text-gray-700'
            : 'view-btn px-3 py-1.5 rounded-md text-xs font-medium transition text-gray-500';
        document.getElementById('listBtn').className = !isGrid
            ? 'view-btn px-3 py-1.5 rounded-md text-xs font-medium transition bg-white shadow-sm text-gray-700'
            : 'view-btn px-3 py-1.5 rounded-md text-xs font-medium transition text-gray-500';
        localStorage.setItem('tableView', view);
    }

    // ── Status filter (from summary cards) ───────────────────────────────
    let activeStatus = '';
    function filterByStatus(status, btn) {
        if (activeStatus === status) {
            activeStatus = '';
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('ring-2', 'ring-offset-1', 'ring-blue-400'));
        } else {
            activeStatus = status;
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('ring-2', 'ring-offset-1', 'ring-blue-400'));
            btn.classList.add('ring-2', 'ring-offset-1', 'ring-blue-400');
        }
        applyFilters();
    }

    // ── Combined filters ─────────────────────────────────────────────────
    function applyFilters() {
        const branch   = document.getElementById('branchFilter').value;
        const cap      = document.getElementById('capacityFilter').value;
        let visible    = 0;

        ['table-card', 'list-row'].forEach(cls => {
            document.querySelectorAll('.' + cls).forEach(el => {
                const stMatch  = !activeStatus || el.dataset.status === activeStatus;
                const brMatch  = !branch || el.dataset.branch === branch;
                const capMatch = !cap || matchCapacity(parseInt(el.dataset.capacity), cap);
                const show = stMatch && brMatch && capMatch;
                el.style.display = show ? '' : 'none';
                if (show && cls === 'table-card') visible++;
            });
        });

        // count from grid
        visible = document.querySelectorAll('.table-card:not([style*="display: none"])').length;
        document.getElementById('visibleCount').textContent = visible + ' table' + (visible !== 1 ? 's' : '');
    }

    function matchCapacity(cap, filter) {
        if (filter === '2') return cap <= 2;
        if (filter === '4') return cap <= 4 && cap > 2;
        if (filter === '6') return cap <= 6 && cap > 4;
        if (filter === '8') return cap > 6;
        return true;
    }

    function clearFilters() {
        activeStatus = '';
        document.getElementById('branchFilter').value = '';
        document.getElementById('capacityFilter').value = '';
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('ring-2', 'ring-offset-1', 'ring-blue-400'));
        applyFilters();
    }

    // ── Init ─────────────────────────────────────────────────────────────
    const savedView = localStorage.getItem('tableView') || 'grid';
    setView(savedView);
</script>
@endpush
