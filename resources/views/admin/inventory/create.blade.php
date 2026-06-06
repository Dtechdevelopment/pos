@extends('admin.layouts.app')

@section('title', 'Stock In — New Item')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Stock In</h1>
        <p class="text-sm text-gray-500 mt-1">Add a new item to the inventory.</p>
    </div>
    <a href="{{ route('admin.inventory.index') }}"
        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors shadow-sm text-sm font-medium">
        <i class="fas fa-arrow-left mr-2 text-gray-400"></i>Back to Inventory
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

<form action="{{ route('admin.inventory.store') }}" method="POST">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT: Preview & Status --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- Item Preview Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col items-center text-center">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-teal-400 to-emerald-600 flex items-center justify-center shadow-md mb-4">
                    <i id="previewUnitIcon" class="fas fa-box text-white text-3xl"></i>
                </div>
                <p id="previewName" class="text-gray-400 text-sm italic font-medium">Item name...</p>
                <p id="previewSku" class="text-xs font-mono text-gray-400 mt-1">—</p>

                <div class="mt-4 w-full grid grid-cols-2 gap-2 border-t border-gray-100 pt-4">
                    <div class="bg-teal-50 rounded-lg p-2.5 text-center">
                        <p class="text-xs text-teal-500 mb-0.5">Opening Stock</p>
                        <p id="previewQty" class="text-lg font-bold text-teal-700">0</p>
                        <p id="previewUnit" class="text-xs text-teal-400">pcs</p>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-2.5 text-center">
                        <p class="text-xs text-yellow-600 mb-0.5">Reorder At</p>
                        <p id="previewReorder" class="text-lg font-bold text-yellow-700">0</p>
                        <p class="text-xs text-yellow-400">threshold</p>
                    </div>
                </div>

                <div class="mt-3 w-full">
                    <div class="flex justify-between text-xs text-gray-400 mb-1">
                        <span>Stock level</span>
                        <span id="previewPct">—</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div id="previewBar" class="h-2 rounded-full bg-teal-400 transition-all duration-300" style="width: 100%"></div>
                    </div>
                </div>

                <div class="mt-3">
                    <span id="previewStatusBadge"
                        class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                        <i class="fas fa-circle-check text-[9px]"></i> In Stock
                    </span>
                </div>
            </div>

            {{-- Total Value --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fas fa-dollar-sign text-emerald-500"></i> Estimated Value
                </h3>
                <div class="text-center py-2">
                    <p class="text-3xl font-bold text-emerald-600" id="totalValue">$0.00</p>
                    <p class="text-xs text-gray-400 mt-1">Opening stock × cost price</p>
                </div>
            </div>

            {{-- Unit Quick Pick --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <i class="fas fa-ruler text-blue-400"></i> Quick Unit
                </h3>
                @php
                    $units = [
                        'pcs'   => ['fa-cube',        'Pieces'],
                        'kg'    => ['fa-weight-scale', 'Kg'],
                        'g'     => ['fa-weight-scale', 'Grams'],
                        'l'     => ['fa-droplet',      'Liters'],
                        'ml'    => ['fa-droplet',      'mL'],
                        'box'   => ['fa-box',          'Box'],
                        'bag'   => ['fa-bag-shopping', 'Bag'],
                        'dozen' => ['fa-border-all',   'Dozen'],
                    ];
                @endphp
                <div class="grid grid-cols-4 gap-1.5">
                    @foreach($units as $val => [$icon, $label])
                    <button type="button" onclick="setUnit('{{ $val }}')"
                        data-unit="{{ $val }}"
                        class="unit-btn flex flex-col items-center gap-1 py-2 rounded-lg border-2 border-gray-200 text-gray-500 hover:border-teal-400 hover:bg-teal-50 hover:text-teal-700 transition text-xs font-medium
                            {{ old('unit', 'pcs') === $val ? 'border-teal-400 bg-teal-50 text-teal-700' : '' }}">
                        <i class="fas {{ $icon }} text-sm"></i>
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- RIGHT: Form --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Item Details --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-5 flex items-center gap-2">
                    <i class="fas fa-box text-teal-500"></i> Item Details
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">
                            Item Name <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-box text-sm"></i>
                            </span>
                            <input type="text" name="name" id="nameInput" value="{{ old('name') }}"
                                placeholder="e.g. Cooking Oil, Tomatoes, Paper Cups"
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 bg-gray-50 transition"
                                required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">SKU</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-barcode text-sm"></i>
                            </span>
                            <input type="text" name="sku" id="skuInput" value="{{ old('sku') }}"
                                placeholder="e.g. INV-001"
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 bg-gray-50 transition">
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">Leave blank to auto-generate.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">
                            Branch <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-store text-sm"></i>
                            </span>
                            <select name="branch_id"
                                class="w-full pl-9 pr-10 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 bg-gray-50 transition appearance-none"
                                required>
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
                            Unit of Measure <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-ruler text-sm"></i>
                            </span>
                            <select name="unit" id="unitSelect"
                                class="w-full pl-9 pr-10 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 bg-gray-50 transition appearance-none"
                                onchange="onUnitChange(this.value)" required>
                                @foreach($units as $val => [$icon, $label])
                                    <option value="{{ $val }}" {{ old('unit', 'pcs') === $val ? 'selected' : '' }}>
                                        {{ $label }} ({{ $val }})
                                    </option>
                                @endforeach
                            </select>
                            <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Expiry Date</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-calendar text-sm"></i>
                            </span>
                            <input type="date" name="expiry_date" value="{{ old('expiry_date') }}"
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 bg-gray-50 transition">
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">Leave blank if item doesn't expire.</p>
                    </div>
                </div>
            </div>

            {{-- Stock & Pricing --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-5 flex items-center gap-2">
                    <i class="fas fa-layer-group text-emerald-500"></i> Stock & Pricing
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">
                            Opening Stock <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-layer-group text-sm"></i>
                            </span>
                            <input type="number" name="opening_stock" id="openingStockInput"
                                value="{{ old('opening_stock', 0) }}"
                                min="0" step="0.01" placeholder="0"
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 bg-gray-50 transition"
                                oninput="updatePreview()" required>
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">Initial quantity being added.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">
                            Reorder Level <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-triangle-exclamation text-sm"></i>
                            </span>
                            <input type="number" name="reorder_level" id="reorderInput"
                                value="{{ old('reorder_level', 0) }}"
                                min="0" step="0.01" placeholder="0"
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 bg-gray-50 transition"
                                oninput="updatePreview()" required>
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">Alert when stock falls to this.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">
                            Cost Price <span class="text-gray-400 font-normal text-xs">(per unit)</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 pointer-events-none font-medium text-sm">$</span>
                            <input type="number" name="cost_price" id="costPriceInput"
                                value="{{ old('cost_price', 0) }}"
                                min="0" step="0.01" placeholder="0.00"
                                class="w-full pl-7 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-gray-50 transition"
                                oninput="updatePreview()">
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">Purchase price per unit.</p>
                    </div>
                </div>

                {{-- Stock health indicator --}}
                <div class="mt-5 bg-gray-50 rounded-xl border border-gray-100 p-4 grid grid-cols-3 gap-4 text-center">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Stock vs Reorder</p>
                        <p id="stockVsReorder" class="text-sm font-bold text-green-600">—</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Total Value</p>
                        <p id="stockValue2" class="text-sm font-bold text-teal-700">$0.00</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Health</p>
                        <p id="stockHealth" class="text-sm font-bold text-green-600">Good</p>
                    </div>
                </div>
            </div>

            {{-- Info tip --}}
            <div class="bg-gradient-to-br from-teal-50 to-emerald-50 border border-teal-100 rounded-xl p-4 flex items-start gap-3">
                <i class="fas fa-circle-info text-teal-400 mt-0.5"></i>
                <div class="text-xs text-teal-700 space-y-1">
                    <p><strong>Opening Stock</strong> is recorded as the first stock movement (type: in).</p>
                    <p><strong>Reorder Level</strong> triggers a low-stock alert when remaining stock drops to or below this value.</p>
                    <p><strong>SKU</strong> must be unique across all inventory items if provided.</p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.inventory.index') }}"
                    class="px-5 py-2.5 border border-gray-300 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-teal-500 to-emerald-600 hover:from-teal-600 hover:to-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm transition-all">
                    <i class="fas fa-plus mr-2"></i> Add Stock Item
                </button>
            </div>

        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    const unitIcons = {
        pcs:   'fa-cube',
        kg:    'fa-weight-scale',
        g:     'fa-weight-scale',
        l:     'fa-droplet',
        ml:    'fa-droplet',
        box:   'fa-box',
        bag:   'fa-bag-shopping',
        dozen: 'fa-border-all',
    };

    // ── Name & SKU preview ───────────────────────────────────────────────
    document.getElementById('nameInput').addEventListener('input', function () {
        const val = this.value.trim();
        const el = document.getElementById('previewName');
        el.textContent = val || 'Item name...';
        el.classList.toggle('italic', !val);
        el.classList.toggle('text-gray-400', !val);
        el.classList.toggle('text-gray-800', !!val);
        el.classList.toggle('font-semibold', !!val);
    });

    document.getElementById('skuInput').addEventListener('input', function () {
        document.getElementById('previewSku').textContent = this.value.trim() || '—';
    });

    // ── Unit picker ──────────────────────────────────────────────────────
    function setUnit(val) {
        document.getElementById('unitSelect').value = val;
        onUnitChange(val);
    }

    function onUnitChange(val) {
        // Sync quick-pick buttons
        document.querySelectorAll('.unit-btn').forEach(btn => {
            const active = btn.dataset.unit === val;
            btn.className = btn.className
                .replace(/border-teal-400|bg-teal-50|text-teal-700/g, '')
                .trim();
            if (active) {
                btn.classList.add('border-teal-400', 'bg-teal-50', 'text-teal-700');
                btn.classList.remove('border-gray-200', 'text-gray-500');
            } else {
                btn.classList.add('border-gray-200', 'text-gray-500');
                btn.classList.remove('border-teal-400', 'bg-teal-50', 'text-teal-700');
            }
        });
        // Update preview unit label
        document.getElementById('previewUnit').textContent = val;
        // Update unit icon
        document.getElementById('previewUnitIcon').className = `fas ${unitIcons[val] ?? 'fa-box'} text-white text-3xl`;
        updatePreview();
    }

    // ── Stock values ─────────────────────────────────────────────────────
    function updatePreview() {
        const qty     = parseFloat(document.getElementById('openingStockInput').value) || 0;
        const reorder = parseFloat(document.getElementById('reorderInput').value) || 0;
        const cost    = parseFloat(document.getElementById('costPriceInput').value) || 0;
        const value   = qty * cost;

        // Preview card
        document.getElementById('previewQty').textContent    = qty;
        document.getElementById('previewReorder').textContent = reorder;
        document.getElementById('totalValue').textContent     = '$' + value.toFixed(2);
        document.getElementById('stockValue2').textContent    = '$' + value.toFixed(2);

        // Bar & status
        const isOut = qty <= 0;
        const isLow = !isOut && qty <= reorder;
        const pct   = reorder > 0 ? Math.min(Math.round((qty / (reorder * 2)) * 100), 100) : (qty > 0 ? 100 : 0);

        const bar = document.getElementById('previewBar');
        bar.style.width = pct + '%';
        bar.className   = `h-2 rounded-full transition-all duration-300 ${isOut ? 'bg-red-400' : isLow ? 'bg-yellow-400' : 'bg-teal-500'}`;

        document.getElementById('previewPct').textContent = qty + ' / ' + (reorder * 2 || qty || 1);

        const badge = document.getElementById('previewStatusBadge');
        if (isOut) {
            badge.className = 'inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700';
            badge.innerHTML = '<i class="fas fa-circle-xmark text-[9px]"></i> Out of Stock';
        } else if (isLow) {
            badge.className = 'inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700';
            badge.innerHTML = '<i class="fas fa-triangle-exclamation text-[9px]"></i> Low Stock';
        } else {
            badge.className = 'inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700';
            badge.innerHTML = '<i class="fas fa-circle-check text-[9px]"></i> In Stock';
        }

        // Stock vs reorder
        const diff = qty - reorder;
        const stockVsEl = document.getElementById('stockVsReorder');
        stockVsEl.textContent = (diff >= 0 ? '+' : '') + diff.toFixed(2);
        stockVsEl.className   = `text-sm font-bold ${diff < 0 ? 'text-red-500' : diff === 0 ? 'text-yellow-500' : 'text-green-600'}`;

        // Health
        const healthEl = document.getElementById('stockHealth');
        if (isOut)       { healthEl.textContent = 'Critical'; healthEl.className = 'text-sm font-bold text-red-600'; }
        else if (isLow)  { healthEl.textContent = 'Low';      healthEl.className = 'text-sm font-bold text-yellow-600'; }
        else             { healthEl.textContent = 'Good';     healthEl.className = 'text-sm font-bold text-green-600'; }
    }

    // ── Init ─────────────────────────────────────────────────────────────
    onUnitChange(document.getElementById('unitSelect').value);
    updatePreview();
</script>
@endpush
