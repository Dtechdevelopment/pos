@extends('admin.layouts.app')

@section('title', 'Orders')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Orders</h1>
        <p class="text-sm text-gray-500 mt-0.5">Track and manage all customer orders.</p>
    </div>
    <a href="{{ route('admin.kitchen.index') }}"
        class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 text-sm font-medium shadow-sm transition">
        <i class="fas fa-fire-burner mr-2 text-orange-400"></i>Kitchen View
    </a>
</div>

@if(session('success'))
<div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3">
    <i class="fas fa-circle-check text-green-500"></i>
    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
</div>
@endif

{{-- ── Summary Cards ───────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/10 rounded-full"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-2">
                <p class="text-orange-100 text-sm font-medium">Today's Orders</p>
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-receipt text-sm"></i>
                </div>
            </div>
            <p class="text-3xl font-bold">{{ number_format($summary['today']) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3 cursor-pointer hover:shadow-md transition"
        onclick="quickFilter('pending')">
        <div class="w-11 h-11 bg-yellow-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-clock text-yellow-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-yellow-600">{{ number_format($summary['pending']) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3 cursor-pointer hover:shadow-md transition"
        onclick="quickFilter('preparing')">
        <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-fire-burner text-blue-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">In Kitchen</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($summary['preparing']) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3 cursor-pointer hover:shadow-md transition"
        onclick="quickFilter('ready')">
        <div class="w-11 h-11 bg-purple-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-bell text-purple-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Ready</p>
            <p class="text-2xl font-bold text-purple-600">{{ number_format($summary['ready']) }}</p>
        </div>
    </div>

    <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden col-span-2 lg:col-span-1">
        <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/10 rounded-full"></div>
        <div class="relative">
            <p class="text-emerald-100 text-sm font-medium mb-2">Revenue</p>
            <p class="text-2xl font-bold">${{ number_format($summary['revenue'], 2) }}</p>
            <p class="text-emerald-200 text-xs mt-1">Delivered & closed</p>
        </div>
    </div>
</div>

{{-- ── Filters ─────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-5">
    <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-wrap items-end gap-3" id="filterForm">
        <input type="hidden" name="status" id="statusHidden" value="{{ request('status') }}">

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Order #</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-hashtag text-xs"></i>
                </span>
                <input type="text" name="order_no" value="{{ request('order_no') }}"
                    placeholder="Search order..."
                    class="pl-8 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 bg-gray-50 w-40">
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Branch</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-store text-xs"></i>
                </span>
                <select name="branch_id"
                    class="pl-8 pr-8 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 bg-gray-50 appearance-none">
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

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Date From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 bg-gray-50">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Date To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 bg-gray-50">
        </div>

        <div class="flex gap-2">
            <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm font-medium transition">
                <i class="fas fa-search mr-1.5"></i> Filter
            </button>
            @if(request()->hasAny(['order_no','status','branch_id','date_from','date_to']))
            <a href="{{ route('admin.orders.index') }}"
                class="inline-flex items-center px-4 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                <i class="fas fa-xmark mr-1.5"></i> Clear
            </a>
            @endif
        </div>
    </form>
</div>

{{-- Status pills --}}
@php
    $statusConfig = [
        ''               => ['All',             'bg-gray-800 text-white',         'bg-gray-100 text-gray-600 hover:bg-gray-200'],
        'pending'        => ['Pending',          'bg-yellow-500 text-white',       'bg-yellow-50 text-yellow-700 hover:bg-yellow-100'],
        'sent_to_kitchen'=> ['Sent to Kitchen',  'bg-blue-500 text-white',         'bg-blue-50 text-blue-700 hover:bg-blue-100'],
        'preparing'      => ['Preparing',        'bg-indigo-500 text-white',       'bg-indigo-50 text-indigo-700 hover:bg-indigo-100'],
        'ready'          => ['Ready',            'bg-purple-500 text-white',       'bg-purple-50 text-purple-700 hover:bg-purple-100'],
        'delivered'      => ['Delivered',        'bg-teal-500 text-white',         'bg-teal-50 text-teal-700 hover:bg-teal-100'],
        'closed'         => ['Closed',           'bg-gray-500 text-white',         'bg-gray-50 text-gray-600 hover:bg-gray-100'],
    ];
@endphp
<div class="flex flex-wrap gap-2 mb-4">
    @foreach($statusConfig as $val => [$label, $active, $inactive])
    <a href="{{ route('admin.orders.index', array_merge(request()->except('status','page'), $val ? ['status' => $val] : [])) }}"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition
            {{ request('status', '') === $val ? $active : $inactive }}">
        {{ $label }}
        @if($val && ($statusCounts[$val] ?? 0) > 0)
        <span class="text-[10px] {{ request('status','') === $val ? 'opacity-80' : 'opacity-60' }}">
            ({{ $statusCounts[$val] }})
        </span>
        @endif
    </a>
    @endforeach
</div>

{{-- ── Table ───────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Order</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Table</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Waiter</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Customer</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Items</th>
                    <th class="text-right py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Total</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Status</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Time</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                @php
                    $statusCfg = [
                        'pending'         => ['bg-yellow-100 text-yellow-700',  'fa-clock',          'border-yellow-200'],
                        'sent_to_kitchen' => ['bg-blue-100 text-blue-700',      'fa-paper-plane',    'border-blue-200'],
                        'preparing'       => ['bg-indigo-100 text-indigo-700',  'fa-fire-burner',    'border-indigo-200'],
                        'ready'           => ['bg-purple-100 text-purple-700',  'fa-bell',           'border-purple-200'],
                        'delivered'       => ['bg-teal-100 text-teal-700',      'fa-circle-check',   'border-teal-200'],
                        'closed'          => ['bg-gray-100 text-gray-600',      'fa-lock',           'border-gray-200'],
                    ];
                    [$sBadge, $sIcon, $sBorder] = $statusCfg[$order->status] ?? ['bg-gray-100 text-gray-600', 'fa-circle', 'border-gray-200'];

                    $isActive = in_array($order->status, ['pending','sent_to_kitchen','preparing','ready']);
                    $age      = $order->created_at->diffInMinutes(now());
                    $isUrgent = $isActive && $age > 30;

                    $waiterName    = $order->waiter->name ?? null;
                    $waiterInitials= $waiterName ? collect(explode(' ', $waiterName))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('') : null;
                @endphp
                <tr class="border-b border-gray-50 hover:bg-orange-50/20 transition-colors {{ $isUrgent ? 'bg-red-50/30' : '' }}">

                    {{-- Order # --}}
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                            @if($isUrgent)
                            <span class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0 animate-pulse" title="Order is taking too long"></span>
                            @endif
                            <div>
                                <p class="font-bold text-gray-800 text-xs">{{ $order->order_number }}</p>
                                <p class="text-xs text-gray-400">{{ $order->branch->name ?? '—' }}</p>
                            </div>
                        </div>
                    </td>

                    {{-- Table --}}
                    <td class="py-3 px-4">
                        @if($order->restaurantTable)
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 rounded-lg text-xs font-semibold text-gray-700">
                            <i class="fas fa-chair text-gray-400 text-xs"></i>
                            {{ $order->restaurantTable->table_number }}
                        </div>
                        @else
                        <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>

                    {{-- Waiter --}}
                    <td class="py-3 px-4">
                        @if($waiterName)
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-cyan-100 flex items-center justify-center text-cyan-700 text-xs font-bold flex-shrink-0">
                                {{ $waiterInitials }}
                            </div>
                            <span class="text-xs text-gray-600">{{ $waiterName }}</span>
                        </div>
                        @else
                        <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>

                    {{-- Customer --}}
                    <td class="py-3 px-4 text-xs text-gray-600">
                        {{ $order->customer->name ?? 'Walk-in' }}
                    </td>

                    {{-- Items --}}
                    <td class="py-3 px-4 text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-semibold
                            {{ $order->order_items_count > 0 ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-400' }}">
                            {{ $order->order_items_count ?? 0 }}
                        </span>
                    </td>

                    {{-- Total --}}
                    <td class="py-3 px-4 text-right">
                        <span class="font-bold text-gray-800">${{ number_format($order->total ?? 0, 2) }}</span>
                    </td>

                    {{-- Status --}}
                    <td class="py-3 px-4 text-center">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold {{ $sBadge }}">
                            <i class="fas {{ $sIcon }} text-[9px]"></i>
                            {{ ucwords(str_replace('_', ' ', $order->status)) }}
                        </span>
                    </td>

                    {{-- Time --}}
                    <td class="py-3 px-4 text-center">
                        <p class="text-xs {{ $isUrgent ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                            {{ $order->created_at->format('H:i') }}
                        </p>
                        <p class="text-xs {{ $isUrgent ? 'text-red-500' : 'text-gray-400' }}">
                            {{ $order->created_at->diffForHumans(null, true) }}
                            @if($isUrgent) <i class="fas fa-triangle-exclamation"></i> @endif
                        </p>
                    </td>

                    {{-- Actions --}}
                    <td class="py-3 px-4">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('admin.orders.show', $order) }}"
                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition"
                                title="View order">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <a href="{{ route('admin.orders.edit', $order) }}"
                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-50 text-gray-600 hover:bg-gray-100 transition"
                                title="Edit order">
                                <i class="fas fa-pen text-xs"></i>
                            </a>
                            @if(!in_array($order->status, ['closed','delivered']))
                            <form action="{{ route('admin.orders.cancel', $order) }}" method="POST"
                                onsubmit="return confirm('Cancel order {{ $order->order_number }}?')">
                                @csrf
                                <button type="submit"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition"
                                    title="Cancel order">
                                    <i class="fas fa-ban text-xs"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="py-16 text-center">
                        <i class="fas fa-clipboard-list text-4xl text-gray-200 block mb-3"></i>
                        <p class="text-gray-400 text-sm">No orders found.</p>
                        @if(request()->hasAny(['order_no','status','branch_id','date_from','date_to']))
                            <a href="{{ route('admin.orders.index') }}"
                                class="text-orange-500 text-sm mt-1 hover:underline inline-block">Clear filters</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($orders->hasPages())
    <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
        <p class="text-xs text-gray-500">
            Showing {{ $orders->firstItem() }}–{{ $orders->lastItem() }} of {{ $orders->total() }} orders
        </p>
        {{ $orders->links() }}
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
    function quickFilter(status) {
        document.getElementById('statusHidden').value = status;
        document.getElementById('filterForm').submit();
    }
</script>
@endpush
