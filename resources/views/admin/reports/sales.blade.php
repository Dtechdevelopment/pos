@extends('admin.layouts.app')

@section('title', 'Sales Report')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Sales Report</h1>
        <p class="text-sm text-gray-500 mt-0.5">
            {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }}
            — {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
        </p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.reports.financial') }}"
            class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 text-sm font-medium shadow-sm transition">
            <i class="fas fa-coins mr-2 text-gray-400"></i>Financial
        </a>
        <a href="{{ route('admin.reports.export', 'sales') }}?{{ http_build_query(request()->all()) }}"
            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
            <i class="fas fa-file-arrow-down mr-2"></i>Export
        </a>
    </div>
</div>

{{-- ── Filters ─────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
    <form method="GET" action="{{ route('admin.reports.sales') }}" class="flex flex-wrap items-end gap-3">

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Date From</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-calendar text-xs"></i>
                </span>
                <input type="date" name="date_from" value="{{ request('date_from', $dateFrom->format('Y-m-d')) }}"
                    class="pl-8 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50">
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Date To</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-calendar text-xs"></i>
                </span>
                <input type="date" name="date_to" value="{{ request('date_to', $dateTo->format('Y-m-d')) }}"
                    class="pl-8 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50">
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
                    @foreach($branches ?? [] as $branch)
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

        {{-- Quick ranges --}}
        <div class="flex items-end gap-1.5">
            @foreach([
                ['Today',     now()->format('Y-m-d'),            now()->format('Y-m-d')],
                ['7 Days',    now()->subDays(6)->format('Y-m-d'), now()->format('Y-m-d')],
                ['30 Days',   now()->subDays(29)->format('Y-m-d'),now()->format('Y-m-d')],
                ['This Month',now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
            ] as [$label, $from, $to])
            <a href="{{ route('admin.reports.sales', array_merge(request()->except('date_from','date_to'), ['date_from' => $from, 'date_to' => $to])) }}"
                class="px-3 py-2 rounded-lg border text-xs font-medium transition
                    {{ request('date_from') === $from && request('date_to') === $to
                        ? 'bg-blue-600 text-white border-blue-600'
                        : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        <button type="submit"
            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
            <i class="fas fa-search mr-1.5"></i> Generate
        </button>
    </form>
</div>

{{-- ── KPI Cards ────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
    <div class="lg:col-span-2 bg-gradient-to-br from-blue-500 to-indigo-700 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/10 rounded-full"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-2">
                <p class="text-blue-100 text-sm font-medium">Total Sales</p>
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-sm"></i>
                </div>
            </div>
            <p class="text-3xl font-bold">${{ number_format($totalSales ?? 0, 2) }}</p>
            <p class="text-blue-200 text-xs mt-1">Net of discounts</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-receipt text-green-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Orders</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($totalOrders ?? 0) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-calculator text-purple-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Avg Order</p>
            <p class="text-2xl font-bold text-gray-800">${{ number_format($avgOrderValue ?? 0, 2) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-percent text-orange-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Tax Collected</p>
            <p class="text-2xl font-bold text-gray-800">${{ number_format($totalTax ?? 0, 2) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-tag text-red-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Discounts</p>
            <p class="text-2xl font-bold text-gray-800">${{ number_format($totalDiscount ?? 0, 2) }}</p>
        </div>
    </div>

    {{-- Cancelled overlay on last card --}}
    @if(($cancelledOrders ?? 0) > 0)
    <div class="hidden lg:flex bg-rose-50 border border-rose-100 rounded-2xl shadow-sm p-4 items-center gap-3">
        <div class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-ban text-rose-500"></i>
        </div>
        <div>
            <p class="text-xs text-rose-500">Cancelled</p>
            <p class="text-2xl font-bold text-rose-700">{{ number_format($cancelledOrders) }}</p>
        </div>
    </div>
    @endif
</div>

{{-- ── Charts ───────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">

    {{-- Sales Trend (wide) --}}
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-700">Sales Trend</h3>
                <p class="text-xs text-gray-400">Revenue per day in selected range</p>
            </div>
            <span class="text-xs bg-blue-50 text-blue-600 font-medium px-2.5 py-1 rounded-full">
                {{ $salesData->count() }} days
            </span>
        </div>
        <div class="relative" style="height: 260px">
            <canvas id="salesByDateChart"></canvas>
        </div>
    </div>

    {{-- By Branch --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="mb-4">
            <h3 class="text-sm font-semibold text-gray-700">By Branch</h3>
            <p class="text-xs text-gray-400">Revenue distribution</p>
        </div>
        <div class="relative" style="height: 260px">
            <canvas id="salesByBranchChart"></canvas>
        </div>
    </div>
</div>

{{-- ── Detail Table ─────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-table text-blue-500 text-xs"></i>
            </div>
            <span class="text-sm font-semibold text-gray-700">Daily Breakdown</span>
            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full font-medium">
                {{ $salesData->count() }} rows
            </span>
        </div>
        @if($salesData->count())
        <div class="text-xs text-gray-400">
            Total net: <span class="font-semibold text-gray-700">
                ${{ number_format($salesData->sum(fn($r) => ($r['revenue'] ?? 0) - ($r['discount'] ?? 0)), 2) }}
            </span>
        </div>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Date</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Orders</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Items</th>
                    <th class="text-right py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Revenue</th>
                    <th class="text-right py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Tax</th>
                    <th class="text-right py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Discount</th>
                    <th class="text-right py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Net Sales</th>
                    <th class="py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide w-32">Share</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salesData as $row)
                @php
                    $revenue  = $row['revenue'] ?? $row->revenue ?? 0;
                    $tax      = $row['tax'] ?? $row->tax ?? 0;
                    $discount = $row['discount'] ?? $row->discount ?? 0;
                    $net      = $revenue - $discount;
                    $share    = $totalSales > 0 ? round(($revenue / $totalSales) * 100, 1) : 0;
                    $date     = $row['date'] ?? $row->date;
                @endphp
                <tr class="border-b border-gray-50 hover:bg-blue-50/20 transition-colors">
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-calendar-day text-blue-400 text-xs"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800 text-xs">
                                    {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}
                                </p>
                                <p class="text-gray-400 text-xs">{{ \Carbon\Carbon::parse($date)->format('l') }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <span class="text-xs font-semibold bg-green-100 text-green-700 px-2 py-0.5 rounded-full">
                            {{ number_format($row['orders_count'] ?? $row->orders_count ?? 0) }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-center text-gray-600 text-xs">
                        {{ number_format($row['items_sold'] ?? 0) }}
                    </td>
                    <td class="py-3 px-4 text-right font-semibold text-gray-800">
                        ${{ number_format($revenue, 2) }}
                    </td>
                    <td class="py-3 px-4 text-right text-orange-500 text-xs">
                        ${{ number_format($tax, 2) }}
                    </td>
                    <td class="py-3 px-4 text-right text-red-500 text-xs">
                        @if($discount > 0)-${{ number_format($discount, 2) }}
                        @else <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-right">
                        <span class="font-bold text-blue-700">${{ number_format($net, 2) }}</span>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                <div class="h-1.5 rounded-full bg-blue-500 transition-all" style="width: {{ $share }}%"></div>
                            </div>
                            <span class="text-xs text-gray-400 w-7 text-right">{{ $share }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-16 text-center">
                        <i class="fas fa-chart-line text-4xl text-gray-200 block mb-3"></i>
                        <p class="text-gray-400 text-sm">No sales data for this period.</p>
                        <p class="text-gray-400 text-xs mt-1">Try adjusting the date range or branch filter.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($salesData->count())
            <tfoot>
                <tr class="bg-gray-50 border-t border-gray-200">
                    <td class="py-3 px-4 font-semibold text-gray-700 text-xs uppercase">Totals</td>
                    <td class="py-3 px-4 text-center">
                        <span class="text-xs font-bold bg-green-200 text-green-800 px-2 py-0.5 rounded-full">
                            {{ number_format($salesData->sum(fn($r) => $r['orders_count'] ?? $r->orders_count ?? 0)) }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-center text-gray-700 text-xs font-semibold">
                        {{ number_format($salesData->sum(fn($r) => $r['items_sold'] ?? 0)) }}
                    </td>
                    <td class="py-3 px-4 text-right font-bold text-gray-800">
                        ${{ number_format($salesData->sum(fn($r) => $r['revenue'] ?? $r->revenue ?? 0), 2) }}
                    </td>
                    <td class="py-3 px-4 text-right font-semibold text-orange-600">
                        ${{ number_format($salesData->sum(fn($r) => $r['tax'] ?? $r->tax ?? 0), 2) }}
                    </td>
                    <td class="py-3 px-4 text-right font-semibold text-red-500">
                        ${{ number_format($salesData->sum(fn($r) => $r['discount'] ?? $r->discount ?? 0), 2) }}
                    </td>
                    <td class="py-3 px-4 text-right font-bold text-blue-700">
                        ${{ number_format($salesData->sum(fn($r) => ($r['revenue'] ?? $r->revenue ?? 0) - ($r['discount'] ?? $r->discount ?? 0)), 2) }}
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-blue-200 rounded-full h-1.5"></div>
                            <span class="text-xs font-bold text-blue-700">100%</span>
                        </div>
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Sales Trend — area line
    new Chart(document.getElementById('salesByDateChart'), {
        type: 'line',
        data: {
            labels: @json($chartData['salesByDate']['labels'] ?? []),
            datasets: [{
                label: 'Revenue ($)',
                data: @json($chartData['salesByDate']['data'] ?? []),
                fill: true,
                backgroundColor: 'rgba(59,130,246,0.08)',
                borderColor: 'rgb(59,130,246)',
                borderWidth: 2.5,
                tension: 0.4,
                pointBackgroundColor: 'rgb(59,130,246)',
                pointRadius: 3,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: { callback: v => '$' + v.toLocaleString() }
                },
                x: { grid: { display: false } }
            }
        }
    });

    // By Branch — doughnut
    const branchLabels = @json($chartData['salesByBranch']['labels'] ?? []);
    const branchData   = @json($chartData['salesByBranch']['data'] ?? []);
    const branchColors = [
        'rgba(59,130,246,0.8)',
        'rgba(16,185,129,0.8)',
        'rgba(245,158,11,0.8)',
        'rgba(139,92,246,0.8)',
        'rgba(239,68,68,0.8)',
        'rgba(6,182,212,0.8)',
    ];

    new Chart(document.getElementById('salesByBranchChart'), {
        type: 'doughnut',
        data: {
            labels: branchLabels,
            datasets: [{
                data: branchData,
                backgroundColor: branchColors.slice(0, branchData.length),
                borderColor: '#fff',
                borderWidth: 3,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, padding: 10, font: { size: 11 } }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => ` $${Number(ctx.parsed).toLocaleString('en-US', { minimumFractionDigits: 2 })}`
                    }
                }
            }
        }
    });
});
</script>
@endpush
