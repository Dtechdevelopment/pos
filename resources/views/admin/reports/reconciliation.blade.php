@extends('admin.layouts.app')

@section('title', 'Reconciliation Report')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Reconciliation Report</h1>
        <p class="text-sm text-gray-500 mt-0.5">
            End-of-day cash & sales reconciliation for
            <span class="font-semibold text-gray-700">{{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}</span>
        </p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.reports.sales') }}"
            class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 text-sm font-medium shadow-sm transition">
            <i class="fas fa-chart-line mr-2 text-gray-400"></i>Sales Report
        </a>
        <a href="{{ route('admin.reports.export', 'reconciliation') }}?date={{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}"
            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
            <i class="fas fa-file-arrow-down mr-2"></i>Export
        </a>
    </div>
</div>

{{-- Filter bar --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
    <form method="GET" action="{{ route('admin.reports.reconciliation') }}" class="flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Date</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-calendar text-xs"></i>
                </span>
                <input type="date" name="date" value="{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}"
                    class="pl-8 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-gray-50">
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Branch</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-store text-xs"></i>
                </span>
                <select name="branch_id"
                    class="pl-8 pr-8 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-gray-50 appearance-none">
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

        {{-- Quick day shortcuts --}}
        <div class="flex items-end gap-1.5">
            @foreach([
                ['Today',      now()->format('Y-m-d')],
                ['Yesterday',  now()->subDay()->format('Y-m-d')],
            ] as [$label, $d])
            <a href="{{ route('admin.reports.reconciliation', array_merge(request()->except('date'), ['date' => $d])) }}"
                class="px-3 py-2 rounded-lg border text-xs font-medium transition
                    {{ \Carbon\Carbon::parse($date)->format('Y-m-d') === $d
                        ? 'bg-indigo-600 text-white border-indigo-600'
                        : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        <button type="submit"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">
            <i class="fas fa-search mr-1.5"></i> Run Report
        </button>
    </form>
</div>

{{-- ── Variance Hero Banner ──────────────────────────────────────────── --}}
@php
    $balanced = abs($variance) < 0.01;
    $surplus  = $variance > 0;
@endphp
<div class="rounded-2xl p-5 mb-6 flex items-center justify-between shadow-sm
    {{ $balanced ? 'bg-gradient-to-r from-emerald-500 to-teal-600' : ($surplus ? 'bg-gradient-to-r from-blue-500 to-indigo-600' : 'bg-gradient-to-r from-red-500 to-rose-600') }}">
    <div class="text-white">
        <p class="text-sm font-medium opacity-80 mb-1">
            {{ $balanced ? '✓ Perfectly Balanced' : ($surplus ? '↑ Surplus Detected' : '↓ Shortfall Detected') }}
        </p>
        <p class="text-4xl font-bold">
            {{ $variance >= 0 ? '+' : '' }}${{ number_format($variance, 2) }}
        </p>
        <p class="text-sm opacity-70 mt-1">
            Collected ${{ number_format($totalCollected, 2) }} vs Net Sales ${{ number_format($netSales, 2) }}
        </p>
    </div>
    <div class="text-white opacity-20">
        <i class="fas {{ $balanced ? 'fa-scale-balanced' : ($surplus ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down') }} text-8xl"></i>
    </div>
</div>

{{-- ── KPI Row ───────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-11 h-11 bg-green-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-file-invoice-dollar text-green-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Gross Sales</p>
            <p class="text-xl font-bold text-gray-800">${{ number_format($grossSales, 2) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-circle-check text-blue-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Net Sales (Paid)</p>
            <p class="text-xl font-bold text-gray-800">${{ number_format($netSales, 2) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-11 h-11 bg-teal-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-money-bill-wave text-teal-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Total Collected</p>
            <p class="text-xl font-bold text-gray-800">${{ number_format($totalCollected, 2) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-11 h-11 bg-purple-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-receipt text-purple-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Total Invoices</p>
            <div class="flex items-center gap-2 mt-0.5">
                <p class="text-xl font-bold text-gray-800">{{ $totalInvoices }}</p>
                <span class="text-xs text-green-600 font-medium">{{ $paidInvoices }} paid</span>
            </div>
        </div>
    </div>
</div>

{{-- ── Main Content Grid ─────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT: Reconciliation Ledger --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Sales Breakdown --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <div class="w-7 h-7 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-bar text-blue-500 text-xs"></i>
                </div>
                <span class="text-sm font-semibold text-gray-700">Sales Breakdown</span>
            </div>
            <div class="divide-y divide-gray-50">
                @php
                    $salesRows = [
                        ['Gross Sales (Subtotal)',  $grossSales,          'text-gray-800', null,        'fa-file-invoice'],
                        ['Tax Collected',           $tax,                 'text-orange-600','+ Tax',    'fa-percent'],
                        ['Discounts Applied',       -$discounts,          'text-red-500',  '- Discount','fa-tag'],
                        ['Void / Cancelled Orders', -$voids,              'text-red-500',  '- Voids',  'fa-ban'],
                    ];
                @endphp
                @foreach($salesRows as [$label, $amount, $color, $badge, $icon])
                <div class="flex items-center justify-between px-5 py-3.5 hover:bg-gray-50/50 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas {{ $icon }} text-gray-500 text-xs"></i>
                        </div>
                        <span class="text-sm text-gray-700">{{ $label }}</span>
                        @if($badge)
                            <span class="text-xs {{ $amount < 0 ? 'bg-red-100 text-red-600' : 'bg-orange-100 text-orange-600' }} px-1.5 py-0.5 rounded font-medium">{{ $badge }}</span>
                        @endif
                    </div>
                    <span class="font-semibold {{ $color }}">
                        {{ $amount < 0 ? '-' : '' }}${{ number_format(abs($amount), 2) }}
                    </span>
                </div>
                @endforeach
                {{-- Net Sales Total --}}
                <div class="flex items-center justify-between px-5 py-4 bg-blue-50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-equals text-blue-600 text-xs"></i>
                        </div>
                        <span class="text-sm font-bold text-blue-800">Net Sales</span>
                    </div>
                    <span class="font-bold text-blue-800 text-lg">${{ number_format($netSales, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Collections Breakdown --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <div class="w-7 h-7 bg-teal-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-teal-500 text-xs"></i>
                </div>
                <span class="text-sm font-semibold text-gray-700">Collections by Method</span>
            </div>
            @php
                $methods = [
                    ['Cash',          $cashCollected,  'fa-money-bill-wave', 'bg-green-100 text-green-600'],
                    ['Card',          $cardPayments,   'fa-credit-card',     'bg-purple-100 text-purple-600'],
                    ['M-Pesa',        $mobilePayments, 'fa-mobile-screen',   'bg-blue-100 text-blue-600'],
                    ['Bank Transfer', $bankPayments,   'fa-building-columns','bg-orange-100 text-orange-600'],
                ];
                $totalForPct = max($totalCollected, 0.01);
            @endphp
            <div class="divide-y divide-gray-50">
                @foreach($methods as [$label, $amount, $icon, $iconCls])
                @php $pct = round(($amount / $totalForPct) * 100, 1); @endphp
                <div class="px-5 py-3.5 hover:bg-gray-50/50 transition">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg {{ explode(' ', $iconCls)[0] }} flex items-center justify-center flex-shrink-0">
                                <i class="fas {{ $icon }} {{ explode(' ', $iconCls)[1] }} text-xs"></i>
                            </div>
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-400">{{ $pct }}%</span>
                            <span class="font-semibold text-gray-800">${{ number_format($amount, 2) }}</span>
                        </div>
                    </div>
                    <div class="ml-11">
                        <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                            <div class="h-1.5 rounded-full {{ str_replace('text', 'bg', explode(' ', $iconCls)[1]) }} transition-all"
                                style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                </div>
                @endforeach
                {{-- Total --}}
                <div class="flex items-center justify-between px-5 py-4 bg-teal-50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-teal-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-equals text-teal-600 text-xs"></i>
                        </div>
                        <span class="text-sm font-bold text-teal-800">Total Collected</span>
                    </div>
                    <span class="font-bold text-teal-800 text-lg">${{ number_format($totalCollected, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Hourly Sales Chart --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-700">Hourly Sales</h3>
                    <p class="text-xs text-gray-400">Revenue distribution throughout the day</p>
                </div>
            </div>
            <div class="relative" style="height: 200px">
                <canvas id="hourlyChart"></canvas>
            </div>
        </div>
    </div>

    {{-- RIGHT: Summary Panel --}}
    <div class="space-y-5">

        {{-- Invoice Status --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="fas fa-file-invoice text-purple-400"></i> Invoice Status
            </h3>
            @php
                $invoiceStats = [
                    ['Paid',      $paidInvoices,    'bg-green-100 text-green-700',  'fa-circle-check'],
                    ['Pending',   $pendingInvoices, 'bg-yellow-100 text-yellow-700','fa-clock'],
                    ['Void',      $voidInvoices,    'bg-red-100 text-red-600',      'fa-ban'],
                    ['Total',     $totalInvoices,   'bg-gray-100 text-gray-700',    'fa-receipt'],
                ];
            @endphp
            <div class="grid grid-cols-2 gap-3">
                @foreach($invoiceStats as [$label, $count, $cls, $icon])
                <div class="{{ explode(' ', $cls)[0] }} rounded-xl p-3 text-center">
                    <i class="fas {{ $icon }} {{ explode(' ', $cls)[1] }} mb-1 block"></i>
                    <p class="text-xl font-bold {{ explode(' ', $cls)[1] }}">{{ $count }}</p>
                    <p class="text-xs {{ explode(' ', $cls)[1] }} opacity-80">{{ $label }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Payment Method Doughnut --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="fas fa-chart-pie text-indigo-400"></i> Payment Mix
            </h3>
            <div class="relative" style="height: 190px">
                <canvas id="paymentMixChart"></canvas>
            </div>
        </div>

        {{-- Variance Detail --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="fas fa-scale-balanced text-indigo-400"></i> Variance Detail
            </h3>
            <div class="space-y-3">
                @php
                    $varRows = [
                        ['Net Sales',       $netSales,      'text-gray-800'],
                        ['Total Collected', $totalCollected,'text-gray-800'],
                        ['Variance',        $variance,      $variance >= 0 ? 'text-emerald-600 font-bold' : 'text-red-600 font-bold'],
                        ['Tax Collected',   $tax,           'text-orange-600'],
                        ['Discounts',       $discounts,     'text-red-500'],
                    ];
                @endphp
                @foreach($varRows as [$label, $val, $cls])
                <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                    <span class="text-sm text-gray-600">{{ $label }}</span>
                    <span class="text-sm {{ $cls }}">
                        {{ $val < 0 ? '-' : ($label === 'Variance' && $val >= 0 ? '+' : '') }}${{ number_format(abs($val), 2) }}
                    </span>
                </div>
                @endforeach
            </div>

            {{-- Visual variance bar --}}
            <div class="mt-4 bg-gray-50 rounded-xl p-3">
                <div class="flex justify-between text-xs text-gray-400 mb-1.5">
                    <span>Collected</span>
                    <span>Net Sales</span>
                </div>
                @php
                    $max = max($totalCollected, $netSales, 0.01);
                    $collectedPct = round(($totalCollected / $max) * 100);
                    $salesPct     = round(($netSales / $max) * 100);
                @endphp
                <div class="space-y-1.5">
                    <div>
                        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                            <div class="h-3 rounded-full bg-teal-500 transition-all" style="width: {{ $collectedPct }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                            <div class="h-3 rounded-full bg-blue-500 transition-all" style="width: {{ $salesPct }}%"></div>
                        </div>
                    </div>
                </div>
                <div class="flex gap-4 mt-2">
                    <div class="flex items-center gap-1 text-xs text-gray-500">
                        <span class="w-2.5 h-2.5 rounded-full bg-teal-500 inline-block"></span> Collected
                    </div>
                    <div class="flex items-center gap-1 text-xs text-gray-500">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span> Net Sales
                    </div>
                </div>
            </div>
        </div>

        {{-- Status verdict --}}
        <div class="rounded-2xl p-4 border-2
            {{ $balanced ? 'bg-green-50 border-green-200' : ($surplus ? 'bg-blue-50 border-blue-200' : 'bg-red-50 border-red-200') }}">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl {{ $balanced ? 'bg-green-100' : ($surplus ? 'bg-blue-100' : 'bg-red-100') }} flex items-center justify-center flex-shrink-0">
                    <i class="fas {{ $balanced ? 'fa-circle-check text-green-600' : ($surplus ? 'fa-arrow-up text-blue-600' : 'fa-triangle-exclamation text-red-600') }}"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold {{ $balanced ? 'text-green-800' : ($surplus ? 'text-blue-800' : 'text-red-800') }}">
                        {{ $balanced ? 'Reconciliation Balanced' : ($surplus ? 'Surplus of $'.number_format(abs($variance),2) : 'Shortfall of $'.number_format(abs($variance),2)) }}
                    </p>
                    <p class="text-xs {{ $balanced ? 'text-green-600' : ($surplus ? 'text-blue-600' : 'text-red-600') }} mt-0.5">
                        {{ $balanced
                            ? 'All collections match recorded sales perfectly.'
                            : ($surplus
                                ? 'More cash collected than invoiced. Check for unrecorded returns.'
                                : 'Collections fall short of net sales. Investigate missing payments.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Hourly Sales Bar
    new Chart(document.getElementById('hourlyChart'), {
        type: 'bar',
        data: {
            labels: @json($hourlyData->pluck('hour')),
            datasets: [{
                data: @json($hourlyData->pluck('sales')),
                backgroundColor: 'rgba(99,102,241,0.6)',
                borderRadius: 4,
                borderSkipped: false,
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
                    ticks: { callback: v => '$' + v }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        maxRotation: 0,
                        callback: (val, i) => i % 3 === 0 ? this.getLabelForValue(val) : ''
                    }
                }
            }
        }
    });

    // Payment Mix Doughnut
    const pmLabels = @json(array_keys($paymentMethods));
    const pmData   = @json(array_values($paymentMethods));
    const pmColors = [
        'rgba(16,185,129,0.8)',
        'rgba(139,92,246,0.8)',
        'rgba(59,130,246,0.8)',
        'rgba(245,158,11,0.8)',
    ];

    new Chart(document.getElementById('paymentMixChart'), {
        type: 'doughnut',
        data: {
            labels: pmLabels.map(l => l.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase())),
            datasets: [{
                data: pmData,
                backgroundColor: pmColors,
                borderColor: '#fff',
                borderWidth: 3,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, padding: 8, font: { size: 10 } }
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
