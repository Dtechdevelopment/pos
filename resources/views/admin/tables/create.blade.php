@extends('admin.layouts.app')

@section('title', 'Create Table')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Create Table</h1>
        <p class="text-sm text-gray-500 mt-1">Add a new table to your restaurant floor.</p>
    </div>
    <a href="{{ route('admin.tables.index') }}"
        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors shadow-sm text-sm font-medium">
        <i class="fas fa-arrow-left mr-2 text-gray-400"></i>Back to Tables
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

<form action="{{ route('admin.tables.store') }}" method="POST" id="tableForm">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT: Preview --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- Live Table Preview Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col items-center text-center">
                <div id="previewCard"
                    class="w-28 h-28 rounded-2xl bg-gradient-to-br from-green-100 to-green-200 border-2 border-green-300 flex flex-col items-center justify-center shadow-md mb-4 transition-all duration-300 relative">
                    {{-- Status dot --}}
                    <span id="previewDot" class="absolute top-2.5 right-2.5 w-3 h-3 rounded-full bg-green-400 ring-2 ring-white"></span>
                    <i id="previewIcon" class="fas fa-chair text-green-500 text-3xl mb-1"></i>
                    <p id="previewNumber" class="text-green-800 font-bold text-lg leading-none">T1</p>
                </div>

                {{-- Capacity dots --}}
                <div id="capacityDots" class="flex flex-wrap justify-center gap-1 mb-3 min-h-[20px]">
                    <i class="fas fa-user text-xs text-gray-300"></i>
                    <i class="fas fa-user text-xs text-gray-300"></i>
                    <i class="fas fa-user text-xs text-gray-300"></i>
                    <i class="fas fa-user text-xs text-gray-300"></i>
                </div>

                <span id="previewStatusBadge"
                    class="text-xs px-3 py-1 rounded-full font-semibold bg-green-100 text-green-700 mb-2">
                    Available
                </span>
                <p id="previewBranch" class="text-xs text-gray-400">— No branch —</p>
            </div>

            {{-- Status Picker --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fas fa-circle-half-stroke text-blue-400"></i> Table Status
                </h3>
                <div class="grid grid-cols-2 gap-2">
                    @php
                        $statuses = [
                            'available' => ['label' => 'Available', 'icon' => 'fa-circle-check', 'checked_cls' => 'peer-checked:border-green-400 peer-checked:bg-green-50 peer-checked:text-green-700', 'base_cls' => 'text-gray-500'],
                            'occupied'  => ['label' => 'Occupied',  'icon' => 'fa-users',         'checked_cls' => 'peer-checked:border-red-400 peer-checked:bg-red-50 peer-checked:text-red-700',   'base_cls' => 'text-gray-500'],
                            'reserved'  => ['label' => 'Reserved',  'icon' => 'fa-clock',         'checked_cls' => 'peer-checked:border-yellow-400 peer-checked:bg-yellow-50 peer-checked:text-yellow-700', 'base_cls' => 'text-gray-500'],
                            'cleaning'  => ['label' => 'Cleaning',  'icon' => 'fa-broom',         'checked_cls' => 'peer-checked:border-blue-400 peer-checked:bg-blue-50 peer-checked:text-blue-700', 'base_cls' => 'text-gray-500'],
                        ];
                    @endphp
                    @foreach($statuses as $val => $s)
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="{{ $val }}" class="sr-only peer status-radio"
                            {{ old('status', 'available') === $val ? 'checked' : '' }}
                            onchange="updateStatusPreview('{{ $val }}')">
                        <div class="border-2 border-gray-200 rounded-xl p-3 text-center transition
                            {{ $s['base_cls'] }} {{ $s['checked_cls'] }}">
                            <i class="fas {{ $s['icon'] }} text-lg block mb-1"></i>
                            <span class="text-xs font-medium">{{ $s['label'] }}</span>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Capacity Quick Pick --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fas fa-users text-purple-400"></i> Quick Capacity
                </h3>
                <div class="grid grid-cols-4 gap-2">
                    @foreach([2, 4, 6, 8] as $cap)
                    <button type="button" onclick="setCapacity({{ $cap }})"
                        class="cap-btn py-2.5 rounded-lg border-2 border-gray-200 text-sm font-semibold text-gray-500 hover:border-purple-400 hover:bg-purple-50 hover:text-purple-700 transition">
                        {{ $cap }}
                    </button>
                    @endforeach
                </div>
                <p class="text-xs text-gray-400 mt-3 text-center">Or enter a custom value in the form →</p>
            </div>
        </div>

        {{-- RIGHT: Form --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Table Details --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-5 flex items-center gap-2">
                    <i class="fas fa-chair text-blue-500"></i> Table Details
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">
                            Table Number <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-hashtag text-sm"></i>
                            </span>
                            <input type="text" name="table_number" id="tableNumberInput"
                                value="{{ old('table_number') }}"
                                placeholder="e.g. T1, A3, VIP-1"
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 transition"
                                required>
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">Use a short, unique identifier.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">
                            Branch <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-store text-sm"></i>
                            </span>
                            <select name="branch_id" id="branchSelect"
                                class="w-full pl-9 pr-10 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 transition appearance-none"
                                onchange="updateBranchPreview(this)" required>
                                <option value="">— Select Branch —</option>
                                @foreach($branches ?? [] as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">
                            Seating Capacity <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-users text-sm"></i>
                            </span>
                            <input type="number" name="capacity" id="capacityInput"
                                value="{{ old('capacity', 4) }}"
                                min="1" max="50" placeholder="4"
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 transition"
                                oninput="updateCapacityPreview(this.value)"
                                required>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Info Box --}}
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 rounded-xl p-4 flex items-start gap-3">
                <i class="fas fa-circle-info text-blue-400 mt-0.5"></i>
                <div class="space-y-1 text-xs text-blue-700">
                    <p><strong>Available</strong> — Table is empty and ready to seat guests.</p>
                    <p><strong>Occupied</strong> — Table currently has guests.</p>
                    <p><strong>Reserved</strong> — Table is booked in advance.</p>
                    <p><strong>Cleaning</strong> — Table is being prepared between guests.</p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.tables.index') }}"
                    class="px-5 py-2.5 border border-gray-300 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-lg text-sm font-semibold shadow-sm transition-all">
                    <i class="fas fa-plus mr-2"></i> Create Table
                </button>
            </div>

        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    const statusConfig = {
        available: {
            card:   'bg-gradient-to-br from-green-100 to-green-200 border-green-300',
            icon:   'text-green-500',
            text:   'text-green-800',
            dot:    'bg-green-400',
            badge:  'bg-green-100 text-green-700',
            seats:  'text-green-400',
        },
        occupied: {
            card:   'bg-gradient-to-br from-red-100 to-red-200 border-red-300',
            icon:   'text-red-500',
            text:   'text-red-800',
            dot:    'bg-red-500',
            badge:  'bg-red-100 text-red-700',
            seats:  'text-red-400',
        },
        reserved: {
            card:   'bg-gradient-to-br from-yellow-100 to-yellow-200 border-yellow-300',
            icon:   'text-yellow-600',
            text:   'text-yellow-800',
            dot:    'bg-yellow-400',
            badge:  'bg-yellow-100 text-yellow-700',
            seats:  'text-yellow-400',
        },
        cleaning: {
            card:   'bg-gradient-to-br from-blue-100 to-blue-200 border-blue-300',
            icon:   'text-blue-400',
            text:   'text-blue-800',
            dot:    'bg-blue-400',
            badge:  'bg-blue-100 text-blue-700',
            seats:  'text-blue-300',
        },
    };

    let currentStatus = '{{ old('status', 'available') }}';

    function updateStatusPreview(status) {
        currentStatus = status;
        const cfg = statusConfig[status];

        const card = document.getElementById('previewCard');
        card.className = `w-28 h-28 rounded-2xl border-2 ${cfg.card} flex flex-col items-center justify-center shadow-md mb-4 transition-all duration-300 relative`;

        document.getElementById('previewIcon').className = `fas fa-chair ${cfg.icon} text-3xl mb-1`;
        document.getElementById('previewNumber').className = `${cfg.text} font-bold text-lg leading-none`;
        document.getElementById('previewDot').className = `absolute top-2.5 right-2.5 w-3 h-3 rounded-full ${cfg.dot} ring-2 ring-white`;

        const badge = document.getElementById('previewStatusBadge');
        badge.className = `text-xs px-3 py-1 rounded-full font-semibold ${cfg.badge} mb-2`;
        badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);

        updateCapacityPreview(document.getElementById('capacityInput').value);
    }

    // ── Table number live update ─────────────────────────────────────────
    document.getElementById('tableNumberInput').addEventListener('input', function () {
        const val = this.value.trim();
        document.getElementById('previewNumber').textContent = val || 'T?';
    });

    // ── Branch preview ───────────────────────────────────────────────────
    function updateBranchPreview(select) {
        const label = select.options[select.selectedIndex]?.text ?? '— No branch —';
        document.getElementById('previewBranch').textContent = select.value ? label : '— No branch —';
    }

    // ── Capacity preview (seat icons) ────────────────────────────────────
    function updateCapacityPreview(val) {
        const cap = Math.max(1, Math.min(parseInt(val) || 4, 50));
        const cfg = statusConfig[currentStatus];
        const container = document.getElementById('capacityDots');
        const show = Math.min(cap, 12);
        let html = '';
        for (let i = 0; i < show; i++) {
            html += `<i class="fas fa-user text-xs ${cfg.seats}"></i>`;
        }
        if (cap > 12) html += `<span class="text-xs text-gray-400 ml-0.5">+${cap - 12}</span>`;
        container.innerHTML = html;

        // Sync quick-pick buttons
        document.querySelectorAll('.cap-btn').forEach(btn => {
            const btnCap = parseInt(btn.textContent.trim());
            const active = btnCap === cap;
            btn.className = active
                ? 'cap-btn py-2.5 rounded-lg border-2 border-purple-400 bg-purple-50 text-purple-700 text-sm font-semibold transition'
                : 'cap-btn py-2.5 rounded-lg border-2 border-gray-200 text-sm font-semibold text-gray-500 hover:border-purple-400 hover:bg-purple-50 hover:text-purple-700 transition';
        });
    }

    // ── Quick capacity setter ────────────────────────────────────────────
    function setCapacity(cap) {
        document.getElementById('capacityInput').value = cap;
        updateCapacityPreview(cap);
    }

    // ── Init ─────────────────────────────────────────────────────────────
    updateStatusPreview(currentStatus);
    updateBranchPreview(document.getElementById('branchSelect'));
    updateCapacityPreview(document.getElementById('capacityInput').value);
</script>
@endpush
