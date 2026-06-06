@extends('admin.layouts.app')

@section('title', 'Inventory')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Inventory</h1>
        <p class="text-sm text-gray-500 mt-1">Track stock levels across all branches.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.inventory.alerts') }}"
            class="inline-flex items-center px-4 py-2.5 bg-white border border-red-200 text-red-600 rounded-lg hover:bg-red-50 text-sm font-medium transition shadow-sm">
            <i class="fas fa-triangle-exclamation mr-2"></i>
            Low Stock
            @if($summary['low'] + $summary['out'] > 0)
                <span class="ml-1.5 px-1.5 py-0.5 bg-red-500 text-white text-xs rounded-full leading-none">
                    {{ $summary['low'] + $summary['out'] }}
                </span>
            @endif
        </a>
        <a href="{{ route('admin.inventory.create') }}"
            class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-teal-500 to-emerald-600 hover:from-teal-600 hover:to-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm transition-all">
            <i class="fas fa-plus mr-2"></i>Stock In
        </a>
    </div>
</div>

@if(session('success'))
<div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4 flex items-center gap-3">
    <i class="fas fa-circle-check text-green-500"></i>
    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
</div>
@endif

{{-- Summary Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3 cursor-pointer hover:shadow-md transition stock-filter-card" data-status="" onclick="quickFilter('', this)">
        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-boxes-stacked text-blue-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Total Items</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($summary['total']) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3 cursor-pointer hover:shadow-md transition stock-filter-card" data-status="ok" onclick="quickFilter('ok', this)">
        <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-circle-check text-green-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">In Stock</p>
            <p class="text-2xl font-bold text-green-700">{{ number_format($summary['in_stock']) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3 cursor-pointer hover:shadow-md transition stock-filter-card" data-status="low" onclick="quickFilter('low', this)">
        <div class="w-10 h-10 rounded-lg bg-yellow-50 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-triangle-exclamation text-yellow-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Low Stock</p>
            <p class="text-2xl font-bold text-yellow-600">{{ number_format($summary['low']) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3 cursor-pointer hover:shadow-md transition stock-filter-card" data-status="out" onclick="quickFilter('out', this)">
        <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-circle-xmark text-red-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Out of Stock</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($summary['out']) }}</p>
        </div>
    </div>
    <div class="bg-gradient-to-br from-teal-500 to-emerald-600 rounded-xl shadow-sm p-4 flex items-center gap-3 col-span-2 lg:col-span-1">
        <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-dollar-sign text-white"></i>
        </div>
        <div>
            <p class="text-xs text-teal-100">Stock Value</p>
            <p class="text-xl font-bold text-white">${{ number_format($summary['value'], 2) }}</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-5">
    <form method="GET" action="{{ route('admin.inventory.index') }}" class="flex flex-wrap items-end gap-3" id="filterForm">
        <input type="hidden" name="stock_status" id="stockStatusInput" value="{{ request('stock_status') }}">

        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Search</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-search text-xs"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Item name or SKU..."
                    class="w-full pl-8 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 bg-gray-50">
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Branch</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-store text-xs"></i>
                </span>
                <select name="branch_id"
                    class="pl-8 pr-8 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 bg-gray-50 appearance-none">
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
                class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-sm font-medium transition">
                <i class="fas fa-search mr-1.5"></i> Filter
            </button>
            @if(request()->hasAny(['search','branch_id','stock_status']))
            <a href="{{ route('admin.inventory.index') }}"
                class="inline-flex items-center px-4 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                <i class="fas fa-xmark mr-1.5"></i> Clear
            </a>
            @endif
        </div>
    </form>
</div>

{{-- Stock status quick-filter pills --}}
<div class="flex flex-wrap gap-2 mb-4">
    @php
        $stockPills = [
            ''    => ['All',          'bg-gray-800 text-white',        'bg-gray-100 text-gray-600 hover:bg-gray-200'],
            'ok'  => ['In Stock',     'bg-green-600 text-white',       'bg-green-50 text-green-700 hover:bg-green-100'],
            'low' => ['Low Stock',    'bg-yellow-500 text-white',      'bg-yellow-50 text-yellow-700 hover:bg-yellow-100'],
            'out' => ['Out of Stock', 'bg-red-500 text-white',         'bg-red-50 text-red-600 hover:bg-red-100'],
        ];
    @endphp
    @foreach($stockPills as $val => [$label, $active, $inactive])
    <a href="{{ route('admin.inventory.index', array_merge(request()->except('stock_status','page'), $val ? ['stock_status' => $val] : [])) }}"
        class="px-3 py-1.5 rounded-full text-xs font-semibold transition
            {{ request('stock_status', '') === $val ? $active : $inactive }}">
        {{ $label }}
    </a>
    @endforeach
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Item</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Branch</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Opening</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Received</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Used</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Remaining</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Reorder At</th>
                    <th class="text-right py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Cost</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Expiry</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Status</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Stock Level</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inventoryItems as $item)
                @php
                    $remaining  = $item->remaining_stock;
                    $reorder    = $item->reorder_level;
                    $isOut      = $remaining <= 0;
                    $isLow      = !$isOut && $remaining <= $reorder;
                    $isOk       = !$isOut && !$isLow;

                    $statusBadge = $isOut
                        ? 'bg-red-100 text-red-700'
                        : ($isLow ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700');
                    $statusLabel = $isOut ? 'Out of Stock' : ($isLow ? 'Low Stock' : 'In Stock');
                    $statusIcon  = $isOut ? 'fa-circle-xmark' : ($isLow ? 'fa-triangle-exclamation' : 'fa-circle-check');

                    // Stock bar pct
                    $maxStock = max($item->opening_stock + $item->received_stock, 1);
                    $pct      = min(round(($remaining / $maxStock) * 100), 100);
                    $barColor = $isOut ? 'bg-red-400' : ($isLow ? 'bg-yellow-400' : 'bg-green-500');

                    // Expiry warning
                    $expiring = $item->expiry_date && $item->expiry_date->diffInDays(now()) <= 30 && $item->expiry_date->isFuture();
                    $expired  = $item->expiry_date && $item->expiry_date->isPast();
                @endphp
                <tr class="border-b border-gray-50 hover:bg-teal-50/20 transition-colors">

                    {{-- Item --}}
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-lg bg-teal-50 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-box text-teal-500 text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800 text-sm">{{ $item->name }}</p>
                                <p class="text-xs text-gray-400 font-mono">{{ $item->sku ?? '—' }}
                                    <span class="ml-1 text-gray-300">{{ $item->unit }}</span>
                                </p>
                            </div>
                        </div>
                    </td>

                    {{-- Branch --}}
                    <td class="py-3 px-4 text-xs text-gray-600">{{ $item->branch->name ?? '—' }}</td>

                    {{-- Opening --}}
                    <td class="py-3 px-4 text-center text-gray-500 text-xs">{{ number_format($item->opening_stock, 2) }}</td>

                    {{-- Received --}}
                    <td class="py-3 px-4 text-center">
                        <span class="text-xs text-green-600 font-medium">+{{ number_format($item->received_stock, 2) }}</span>
                    </td>

                    {{-- Used --}}
                    <td class="py-3 px-4 text-center">
                        <span class="text-xs text-red-500 font-medium">-{{ number_format($item->used_stock, 2) }}</span>
                    </td>

                    {{-- Remaining --}}
                    <td class="py-3 px-4 text-center">
                        <span class="font-bold text-sm {{ $isOut ? 'text-red-600' : ($isLow ? 'text-yellow-600' : 'text-gray-800') }}">
                            {{ number_format($remaining, 2) }}
                        </span>
                    </td>

                    {{-- Reorder Level --}}
                    <td class="py-3 px-4 text-center text-xs text-gray-500">{{ number_format($reorder, 2) }}</td>

                    {{-- Cost Price --}}
                    <td class="py-3 px-4 text-right text-xs text-gray-600">${{ number_format($item->cost_price, 2) }}</td>

                    {{-- Expiry --}}
                    <td class="py-3 px-4 text-center">
                        @if($item->expiry_date)
                            <span class="text-xs {{ $expired ? 'text-red-600 font-semibold' : ($expiring ? 'text-yellow-600 font-medium' : 'text-gray-500') }}">
                                @if($expired) <i class="fas fa-skull text-[9px] mr-0.5"></i> @endif
                                @if($expiring && !$expired) <i class="fas fa-clock text-[9px] mr-0.5"></i> @endif
                                {{ $item->expiry_date->format('M d, Y') }}
                            </span>
                        @else
                            <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>

                    {{-- Status Badge --}}
                    <td class="py-3 px-4 text-center">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusBadge }}">
                            <i class="fas {{ $statusIcon }} text-[9px]"></i>
                            {{ $statusLabel }}
                        </span>
                    </td>

                    {{-- Stock Level Bar --}}
                    <td class="py-3 px-4 min-w-[100px]">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                                <div class="h-2 rounded-full {{ $barColor }} transition-all"
                                    style="width: {{ $pct }}%"></div>
                            </div>
                            <span class="text-xs text-gray-400 w-8 text-right">{{ $pct }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="py-16 text-center">
                        <i class="fas fa-boxes-stacked text-4xl text-gray-200 block mb-3"></i>
                        <p class="text-gray-400 text-sm">No inventory items found.</p>
                        @if(request()->hasAny(['search','branch_id','stock_status']))
                            <a href="{{ route('admin.inventory.index') }}"
                                class="text-teal-500 text-sm mt-1 hover:underline inline-block">Clear filters</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($inventoryItems->hasPages())
    <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
        <p class="text-xs text-gray-500">
            Showing {{ $inventoryItems->firstItem() }}–{{ $inventoryItems->lastItem() }}
            of {{ $inventoryItems->total() }} items
        </p>
        {{ $inventoryItems->links() }}
    </div>
    @endif
</div>

@endsection
