@extends('admin.layouts.app')

@section('title', 'Dashboard Overview')

@section('content')

{{-- ── Greeting Bar ──────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">
            Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }},
            {{ auth()->user()->name ?? 'Admin' }} 👋
        </h1>
        <p class="text-sm text-gray-500 mt-0.5">
            {{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Here's what's happening today.
        </p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.reports.sales') }}"
            class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 text-sm font-medium shadow-sm transition">
            <i class="fas fa-chart-line mr-2 text-gray-400"></i> Reports
        </a>
        <a href="{{ route('admin.billing.index') }}"
            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white rounded-lg text-sm font-semibold shadow-sm transition">
            <i class="fas fa-file-invoice mr-2"></i> Billing
        </a>
    </div>
</div>

{{-- ── Hero Revenue Strip ────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-gradient-to-br from-emerald-500 to-teal-700 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full"></div>
        <div class="absolute -right-2 bottom-2 w-14 h-14 bg-white/10 rounded-full"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <p class="text-emerald-100 text-sm font-medium">Today's Revenue</p>
                <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-white"></i>
                </div>
            </div>
            <p class="text-3xl font-bold">${{ number_format($analytics['salesToday'] ?? 0, 2) }}</p>
            <p class="text-emerald-200 text-xs mt-1.5 flex items-center gap-1">
                <i class="fas fa-arrow-trend-up text-xs"></i> Paid invoices today
            </p>
        </div>
    </div>

    <div class="bg-gradient-to-br from-blue-500 to-indigo-700 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full"></div>
        <div class="absolute -right-2 bottom-2 w-14 h-14 bg-white/10 rounded-full"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <p class="text-blue-100 text-sm font-medium">This Week</p>
                <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar-week text-white"></i>
                </div>
            </div>
            <p class="text-3xl font-bold">${{ number_format($analytics['salesThisWeek'] ?? 0, 2) }}</p>
            <p class="text-blue-200 text-xs mt-1.5">{{ now()->startOfWeek()->format('M d') }} – {{ now()->endOfWeek()->format('M d') }}</p>
        </div>
    </div>

    <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full"></div>
        <div class="absolute -right-2 bottom-2 w-14 h-14 bg-white/10 rounded-full"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <p class="text-purple-100 text-sm font-medium">This Month</p>
                <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar text-white"></i>
                </div>
            </div>
            <p class="text-3xl font-bold">${{ number_format($analytics['salesThisMonth'] ?? 0, 2) }}</p>
            <p class="text-purple-200 text-xs mt-1.5">{{ now()->format('F Y') }}</p>
        </div>
    </div>

    <div class="bg-gradient-to-br from-orange-400 to-red-500 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full"></div>
        <div class="absolute -right-2 bottom-2 w-14 h-14 bg-white/10 rounded-full"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <p class="text-orange-100 text-sm font-medium">Outstanding</p>
                <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-hourglass-half text-white"></i>
                </div>
            </div>
            <p class="text-3xl font-bold">${{ number_format($analytics['outstandingPayments'] ?? 0, 2) }}</p>
            <p class="text-orange-200 text-xs mt-1.5">Pending invoices</p>
        </div>
    </div>
</div>

{{-- ── Operations Stats Row ──────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 mb-6">
    @php
        $opStats = [
            ['label' => 'Orders',        'value' => $stats['totalOrders'] ?? 0,         'icon' => 'fa-receipt',            'bg' => 'bg-orange-50',  'color' => 'text-orange-500',  'link' => route('admin.orders.index')],
            ['label' => 'Kitchen',       'value' => $stats['totalKitchenOrders'] ?? 0,   'icon' => 'fa-utensils',           'bg' => 'bg-red-50',     'color' => 'text-red-500',     'link' => route('admin.kitchen.index')],
            ['label' => 'Bills',         'value' => $stats['totalBillsGenerated'] ?? 0,  'icon' => 'fa-file-invoice',       'bg' => 'bg-blue-50',    'color' => 'text-blue-500',    'link' => route('admin.billing.index')],
            ['label' => 'Active Tables', 'value' => $stats['activeTables'] ?? 0,         'icon' => 'fa-chair',              'bg' => 'bg-purple-50',  'color' => 'text-purple-500',  'link' => route('admin.tables.index')],
            ['label' => 'Customers',     'value' => $stats['totalCustomersServed'] ?? 0, 'icon' => 'fa-users',              'bg' => 'bg-amber-50',   'color' => 'text-amber-500',   'link' => route('admin.customers.index')],
            ['label' => 'Items Sold',    'value' => $stats['totalItemsSold'] ?? 0,       'icon' => 'fa-cube',               'bg' => 'bg-lime-50',    'color' => 'text-lime-600',    'link' => route('admin.orders.index')],
            ['label' => 'Void Orders',   'value' => $stats['voidOrders'] ?? 0,           'icon' => 'fa-ban',                'bg' => 'bg-rose-50',    'color' => 'text-rose-500',    'link' => route('admin.orders.index')],
            ['label' => 'Avg Order',     'value' => '$'.number_format($stats['avgOrderValue'] ?? 0, 2), 'icon' => 'fa-calculator', 'bg' => 'bg-gray-50', 'color' => 'text-gray-500', 'link' => route('admin.orders.index')],
        ];
    @endphp
    @foreach($opStats as $s)
    <a href="{{ $s['link'] }}"
        class="bg-white rounded-xl border border-gray-100 shadow-sm p-3 flex flex-col items-center text-center hover:shadow-md transition-shadow group">
        <div class="w-10 h-10 {{ $s['bg'] }} rounded-xl flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
            <i class="fas {{ $s['icon'] }} {{ $s['color'] }}"></i>
        </div>
        <p class="text-lg font-bold text-gray-800 leading-none">{{ $s['value'] }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $s['label'] }}</p>
    </a>
    @endforeach
</div>

{{-- ── Staff & Financial Highlights ─────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-11 h-11 bg-teal-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-credit-card text-teal-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Payments Collected</p>
            <p class="text-xl font-bold text-gray-800">${{ number_format($stats['totalPaymentsCollected'] ?? 0, 2) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-11 h-11 bg-yellow-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-clock text-yellow-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Pending Payments</p>
            <p class="text-xl font-bold text-gray-800">${{ number_format($stats['pendingPayments'] ?? 0, 2) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-11 h-11 bg-sky-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-percent text-sky-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Discounts Given</p>
            <p class="text-xl font-bold text-gray-800">${{ number_format($stats['discountGiven'] ?? 0, 2) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <p class="text-xs text-gray-500 mb-2">Active Staff</p>
        <div class="flex items-center justify-between">
            <div class="text-center">
                <p class="text-lg font-bold text-cyan-600">{{ $stats['activeWaiters'] ?? 0 }}</p>
                <p class="text-xs text-gray-400">Waiters</p>
            </div>
            <div class="w-px h-8 bg-gray-100"></div>
            <div class="text-center">
                <p class="text-lg font-bold text-pink-600">{{ $stats['activeKitchenStaff'] ?? 0 }}</p>
                <p class="text-xs text-gray-400">Kitchen</p>
            </div>
            <div class="w-px h-8 bg-gray-100"></div>
            <div class="text-center">
                <p class="text-lg font-bold text-indigo-600">{{ $stats['activeCashiers'] ?? 0 }}</p>
                <p class="text-xs text-gray-400">Cashiers</p>
            </div>
        </div>
    </div>
</div>

{{-- ── Charts Row 1 ──────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-700">Revenue Trend</h3>
                <p class="text-xs text-gray-400">Last 30 days</p>
            </div>
            <span class="text-xs bg-emerald-50 text-emerald-600 font-medium px-2.5 py-1 rounded-full">
                <i class="fas fa-arrow-trend-up mr-1"></i>30 days
            </span>
        </div>
        <div class="relative" style="height:230px">
            <canvas id="revenueTrendChart"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-700">Invoice Status</h3>
                <p class="text-xs text-gray-400">Paid vs Unpaid</p>
            </div>
        </div>
        <div class="relative" style="height:175px">
            <canvas id="paidUnpaidChart"></canvas>
        </div>
        <div class="flex justify-center gap-5 mt-3">
            <div class="flex items-center gap-1.5 text-xs text-gray-500">
                <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span> Paid
            </div>
            <div class="flex items-center gap-1.5 text-xs text-gray-500">
                <span class="w-3 h-3 rounded-full bg-red-400 inline-block"></span> Unpaid
            </div>
        </div>
    </div>
</div>

{{-- ── Charts Row 2 ──────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-700">Daily Sales</h3>
                <p class="text-xs text-gray-400">Last 7 days</p>
            </div>
        </div>
        <div class="relative" style="height:200px">
            <canvas id="dailySalesChart"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-700">Orders vs Sales</h3>
                <p class="text-xs text-gray-400">Last 7 days</p>
            </div>
        </div>
        <div class="relative" style="height:200px">
            <canvas id="ordersVsSalesChart"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-700">Top Items</h3>
                <p class="text-xs text-gray-400">By qty sold today</p>
            </div>
        </div>
        <div class="relative" style="height:200px">
            <canvas id="topItemsChart"></canvas>
        </div>
    </div>
</div>

{{-- ── Waiter Performance Chart ──────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-5">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-sm font-semibold text-gray-700">Waiter Performance</h3>
            <p class="text-xs text-gray-400">Orders & revenue today</p>
        </div>
        <a href="{{ route('admin.reports.waiter') }}"
            class="text-xs text-blue-600 hover:underline font-medium">Full report →</a>
    </div>
    <div class="relative" style="height:200px">
        <canvas id="waiterPerformanceChart"></canvas>
    </div>
</div>

{{-- ── Tables Row: Top Items + Top Waiters ──────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- Top Selling Items --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-fire text-purple-500 text-xs"></i>
                </div>
                <span class="text-sm font-semibold text-gray-700">Top Selling Items</span>
                <span class="text-xs bg-purple-50 text-purple-600 px-2 py-0.5 rounded-full font-medium">Today</span>
            </div>
            <a href="{{ route('admin.menu.index') }}" class="text-xs text-blue-500 hover:underline">View menu →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left py-2.5 px-4 font-medium text-gray-400 text-xs w-8">#</th>
                        <th class="text-left py-2.5 px-4 font-medium text-gray-400 text-xs">Item</th>
                        <th class="text-right py-2.5 px-4 font-medium text-gray-400 text-xs">Qty</th>
                        <th class="text-right py-2.5 px-4 font-medium text-gray-400 text-xs">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topItems ?? [] as $item)
                    @php $rank = $loop->iteration; @endphp
                    <tr class="border-b border-gray-50 hover:bg-purple-50/30 transition-colors">
                        <td class="py-3 px-4">
                            @if($rank <= 3)
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold
                                {{ $rank == 1 ? 'bg-yellow-100 text-yellow-700' : ($rank == 2 ? 'bg-gray-100 text-gray-600' : 'bg-orange-100 text-orange-600') }}">
                                {{ $rank }}
                            </span>
                            @else
                            <span class="text-gray-400 text-xs pl-1">{{ $rank }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-medium text-gray-700">{{ $item['name'] ?? 'N/A' }}</td>
                        <td class="py-3 px-4 text-right">
                            <span class="text-xs font-semibold bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">
                                {{ $item['qty'] ?? 0 }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right font-semibold text-gray-800">
                            ${{ number_format($item['revenue'] ?? 0, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-10 text-center">
                            <i class="fas fa-utensils text-2xl text-gray-200 block mb-2"></i>
                            <p class="text-gray-400 text-xs">No sales data for today</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Most Active Waiters --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 bg-cyan-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-medal text-cyan-500 text-xs"></i>
                </div>
                <span class="text-sm font-semibold text-gray-700">Top Waiters</span>
                <span class="text-xs bg-cyan-50 text-cyan-600 px-2 py-0.5 rounded-full font-medium">Today</span>
            </div>
            <a href="{{ route('admin.reports.waiter') }}" class="text-xs text-blue-500 hover:underline">Full report →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left py-2.5 px-4 font-medium text-gray-400 text-xs">Waiter</th>
                        <th class="text-right py-2.5 px-4 font-medium text-gray-400 text-xs">Orders</th>
                        <th class="text-right py-2.5 px-4 font-medium text-gray-400 text-xs">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topWaiters ?? [] as $waiter)
                    @php
                        $name = $waiter['name'] ?? 'N/A';
                        $initials = collect(explode(' ', $name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
                        $colors = ['from-cyan-400 to-blue-500','from-purple-400 to-pink-500','from-orange-400 to-red-400','from-green-400 to-teal-500','from-indigo-400 to-purple-500'];
                        $grad = $colors[$loop->index % count($colors)];
                    @endphp
                    <tr class="border-b border-gray-50 hover:bg-cyan-50/30 transition-colors">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br {{ $grad }} flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                    {{ $initials }}
                                </div>
                                <span class="font-medium text-gray-700">{{ $name }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <span class="text-xs font-semibold bg-cyan-100 text-cyan-700 px-2 py-0.5 rounded-full">
                                {{ $waiter['orders_count'] ?? 0 }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right font-semibold text-gray-800">
                            ${{ number_format($waiter['revenue'] ?? 0, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="py-10 text-center">
                            <i class="fas fa-user text-2xl text-gray-200 block mb-2"></i>
                            <p class="text-gray-400 text-xs">No waiter activity today</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const gridDefaults = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { callback: v => '$'+v } },
            x: { grid: { display: false } }
        }
    };

    // Revenue Trend — line
    new Chart(document.getElementById('revenueTrendChart'), {
        type: 'line',
        data: {
            labels: @json($chartData['revenueTrend']['labels'] ?? []),
            datasets: [{
                data: @json($chartData['revenueTrend']['data'] ?? []),
                fill: true,
                backgroundColor: 'rgba(16,185,129,0.08)',
                borderColor: 'rgb(16,185,129)',
                borderWidth: 2.5,
                tension: 0.4,
                pointBackgroundColor: 'rgb(16,185,129)',
                pointRadius: 3,
                pointHoverRadius: 5,
            }]
        },
        options: { ...gridDefaults, plugins: { legend: { display: false } } }
    });

    // Daily Sales — bar
    new Chart(document.getElementById('dailySalesChart'), {
        type: 'bar',
        data: {
            labels: @json($chartData['dailySales']['labels'] ?? []),
            datasets: [{
                data: @json($chartData['dailySales']['data'] ?? []),
                backgroundColor: 'rgba(59,130,246,0.6)',
                borderColor: 'rgb(59,130,246)',
                borderWidth: 0,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: gridDefaults
    });

    // Orders vs Sales — grouped bar
    new Chart(document.getElementById('ordersVsSalesChart'), {
        type: 'bar',
        data: {
            labels: @json($chartData['ordersVsSales']['labels'] ?? []),
            datasets: [
                {
                    label: 'Orders',
                    data: @json($chartData['ordersVsSales']['orders'] ?? []),
                    backgroundColor: 'rgba(249,115,22,0.6)',
                    borderRadius: 4,
                    borderSkipped: false,
                },
                {
                    label: 'Sales ($)',
                    data: @json($chartData['ordersVsSales']['sales'] ?? []),
                    backgroundColor: 'rgba(99,102,241,0.6)',
                    borderRadius: 4,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            ...gridDefaults,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: { boxWidth: 10, padding: 10, font: { size: 10 } }
                }
            }
        }
    });

    // Paid vs Unpaid — doughnut
    new Chart(document.getElementById('paidUnpaidChart'), {
        type: 'doughnut',
        data: {
            labels: ['Paid', 'Unpaid'],
            datasets: [{
                data: @json($chartData['paidUnpaid']['data'] ?? [0,0]),
                backgroundColor: ['rgba(16,185,129,0.85)', 'rgba(239,68,68,0.75)'],
                borderColor: ['#fff','#fff'],
                borderWidth: 3,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: { legend: { display: false } }
        }
    });

    // Top Items — horizontal bar
    new Chart(document.getElementById('topItemsChart'), {
        type: 'bar',
        data: {
            labels: @json($chartData['topItems']['labels'] ?? []),
            datasets: [{
                data: @json($chartData['topItems']['data'] ?? []),
                backgroundColor: 'rgba(139,92,246,0.6)',
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            ...gridDefaults,
            indexAxis: 'y',
            scales: {
                x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' } },
                y: { grid: { display: false } }
            }
        }
    });

    // Waiter Performance — grouped bar
    new Chart(document.getElementById('waiterPerformanceChart'), {
        type: 'bar',
        data: {
            labels: @json($chartData['waiterPerformance']['labels'] ?? []),
            datasets: [
                {
                    label: 'Orders',
                    data: @json($chartData['waiterPerformance']['orders'] ?? []),
                    backgroundColor: 'rgba(6,182,212,0.6)',
                    borderRadius: 4,
                    borderSkipped: false,
                },
                {
                    label: 'Revenue ($)',
                    data: @json($chartData['waiterPerformance']['revenue'] ?? []),
                    backgroundColor: 'rgba(251,191,36,0.6)',
                    borderRadius: 4,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: { boxWidth: 10, padding: 10, font: { size: 10 } }
                }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { callback: v => '$'+v } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>
@endpush
