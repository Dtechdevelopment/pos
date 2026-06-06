@extends('admin.layouts.app')

@section('title', 'Payment Dashboard')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Payment Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Real-time overview of all payment activity.</p>
    </div>
    <a href="{{ route('admin.payments.index') }}"
        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors shadow-sm text-sm font-medium">
        <i class="fas fa-list mr-2 text-gray-400"></i>All Payments
    </a>
</div>

{{-- Top KPI Row --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Today --}}
    <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl shadow-sm p-5 text-white">
        <div class="flex items-center justify-between mb-3">
            <p class="text-emerald-100 text-sm font-medium">Today</p>
            <div class="w-9 h-9 bg-white/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-sun text-white"></i>
            </div>
        </div>
        <p class="text-3xl font-bold">${{ number_format($totals['today'], 2) }}</p>
        @php $diff = $totals['today'] - $totals['yesterday']; @endphp
        <p class="text-emerald-100 text-xs mt-1.5 flex items-center gap-1">
            <i class="fas fa-{{ $diff >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
            ${{ number_format(abs($diff), 2) }} vs yesterday
        </p>
    </div>

    {{-- This Week --}}
    <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-sm p-5 text-white">
        <div class="flex items-center justify-between mb-3">
            <p class="text-blue-100 text-sm font-medium">This Week</p>
            <div class="w-9 h-9 bg-white/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-calendar-week text-white"></i>
            </div>
        </div>
        <p class="text-3xl font-bold">${{ number_format($totals['thisWeek'], 2) }}</p>
        <p class="text-blue-100 text-xs mt-1.5">{{ now()->startOfWeek()->format('M d') }} – {{ now()->endOfWeek()->format('M d') }}</p>
    </div>

    {{-- This Month --}}
    <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl shadow-sm p-5 text-white">
        <div class="flex items-center justify-between mb-3">
            <p class="text-purple-100 text-sm font-medium">This Month</p>
            <div class="w-9 h-9 bg-white/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-calendar text-white"></i>
            </div>
        </div>
        <p class="text-3xl font-bold">${{ number_format($totals['thisMonth'], 2) }}</p>
        <p class="text-purple-100 text-xs mt-1.5">{{ now()->format('F Y') }}</p>
    </div>

    {{-- Outstanding --}}
    <div class="bg-gradient-to-br from-orange-400 to-red-500 rounded-xl shadow-sm p-5 text-white">
        <div class="flex items-center justify-between mb-3">
            <p class="text-orange-100 text-sm font-medium">Outstanding</p>
            <div class="w-9 h-9 bg-white/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-hourglass-half text-white"></i>
            </div>
        </div>
        <p class="text-3xl font-bold">${{ number_format($totals['outstanding'], 2) }}</p>
        <p class="text-orange-100 text-xs mt-1.5">{{ $totals['pending'] }} pending transactions</p>
    </div>
</div>

{{-- Secondary Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-teal-50 flex items-center justify-center">
            <i class="fas fa-money-bill-wave text-teal-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">All-time Collected</p>
            <p class="text-lg font-bold text-gray-800">${{ number_format($totals['total'], 2) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-yellow-50 flex items-center justify-center">
            <i class="fas fa-clock text-yellow-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Pending Count</p>
            <p class="text-lg font-bold text-gray-800">{{ number_format($totals['pending']) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center">
            <i class="fas fa-rotate-left text-red-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Refunded</p>
            <p class="text-lg font-bold text-gray-800">${{ number_format($totals['refunded'], 2) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center">
            <i class="fas fa-chart-pie text-indigo-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Methods Used</p>
            <p class="text-lg font-bold text-gray-800">{{ $methodTotals->count() }}</p>
        </div>
    </div>
</div>

{{-- Charts Row --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    {{-- Doughnut: Methods --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-1">Payment Methods</h3>
        <p class="text-xs text-gray-400 mb-4">All-time breakdown by method</p>
        <div class="relative" style="height:200px">
            <canvas id="paymentMethodsChart"></canvas>
        </div>
        {{-- Legend --}}
        @php
            $methodColors = ['cash' => 'bg-emerald-500', 'm_pesa' => 'bg-blue-500', 'card' => 'bg-purple-500', 'bank_transfer' => 'bg-orange-400'];
        @endphp
        <div class="mt-4 space-y-2">
            @foreach($methodTotals as $method => $data)
            @php $total = $totals['total'] > 0 ? round(($data->total / $totals['total']) * 100, 1) : 0; @endphp
            <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full {{ $methodColors[$method] ?? 'bg-gray-400' }}"></span>
                    <span class="text-gray-600 font-medium">{{ ucwords(str_replace('_', ' ', $method)) }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-gray-500">{{ $data->count }}x</span>
                    <span class="font-semibold text-gray-700">${{ number_format($data->total, 2) }}</span>
                    <span class="text-gray-400 w-8 text-right">{{ $total }}%</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Bar: Daily --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-1">Daily Collections</h3>
        <p class="text-xs text-gray-400 mb-4">Last 7 days</p>
        <div class="relative" style="height:250px">
            <canvas id="dailyCollectionsChart"></canvas>
        </div>
    </div>

    {{-- Line: Monthly Trend --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-1">Monthly Trend</h3>
        <p class="text-xs text-gray-400 mb-4">Last 6 months</p>
        <div class="relative" style="height:250px">
            <canvas id="monthlyTrendChart"></canvas>
        </div>
    </div>
</div>

{{-- Recent Payments --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fas fa-clock-rotate-left text-gray-400"></i>
            <span class="text-sm font-semibold text-gray-700">Recent Transactions</span>
        </div>
        <a href="{{ route('admin.payments.index') }}"
            class="text-xs text-blue-600 hover:underline font-medium">View all →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Reference</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Invoice</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Method</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Cashier</th>
                    <th class="text-right py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Amount</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Status</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentPayments as $payment)
                @php
                    $methodIcons = ['cash' => 'fa-money-bill-wave', 'm_pesa' => 'fa-mobile-screen', 'card' => 'fa-credit-card', 'bank_transfer' => 'fa-building-columns'];
                    $methodColors = ['cash' => 'text-emerald-500 bg-emerald-50', 'm_pesa' => 'text-blue-500 bg-blue-50', 'card' => 'text-purple-500 bg-purple-50', 'bank_transfer' => 'text-orange-500 bg-orange-50'];
                    $statusCfg = [
                        'completed' => 'bg-green-100 text-green-700',
                        'pending'   => 'bg-yellow-100 text-yellow-700',
                        'reversed'  => 'bg-gray-100 text-gray-600',
                        'refunded'  => 'bg-red-100 text-red-600',
                        'failed'    => 'bg-red-100 text-red-700',
                    ];
                    $mIcon  = $methodIcons[$payment->payment_method] ?? 'fa-circle-dollar-sign';
                    $mColor = $methodColors[$payment->payment_method] ?? 'text-gray-500 bg-gray-50';
                    $sBadge = $statusCfg[$payment->status] ?? 'bg-gray-100 text-gray-600';
                @endphp
                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                    <td class="py-3 px-4">
                        <p class="text-xs font-mono text-gray-600">{{ $payment->reference_number ?? '—' }}</p>
                    </td>
                    <td class="py-3 px-4 text-xs text-gray-600">
                        {{ $payment->invoice->invoice_number ?? '—' }}
                    </td>
                    <td class="py-3 px-4">
                        <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg {{ $mColor }} text-xs font-medium">
                            <i class="fas {{ $mIcon }} text-xs"></i>
                            {{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}
                        </div>
                    </td>
                    <td class="py-3 px-4 text-gray-600 text-xs">{{ $payment->cashier->name ?? '—' }}</td>
                    <td class="py-3 px-4 text-right font-semibold text-gray-800">${{ number_format($payment->amount, 2) }}</td>
                    <td class="py-3 px-4 text-center">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold {{ $sBadge }}">
                            <i class="fas fa-circle text-[6px]"></i>
                            {{ ucfirst($payment->status) }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-center text-xs text-gray-400">
                        {{ $payment->created_at->diffForHumans() }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-gray-400">
                        <i class="fas fa-receipt text-3xl text-gray-200 block mb-2"></i>
                        No payments yet.
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
document.addEventListener('DOMContentLoaded', function () {

    // ── Payment Methods Doughnut ─────────────────────────────────────────
    new Chart(document.getElementById('paymentMethodsChart'), {
        type: 'doughnut',
        data: {
            labels: @json($chartData['paymentMethods']['labels'] ?? []),
            datasets: [{
                data: @json($chartData['paymentMethods']['data'] ?? []),
                backgroundColor: [
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(139, 92, 246, 0.8)',
                    'rgba(251, 146, 60, 0.8)',
                ],
                borderColor: ['#fff','#fff','#fff','#fff'],
                borderWidth: 3,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` $${ctx.parsed.toLocaleString('en-US', {minimumFractionDigits:2})}`
                    }
                }
            },
            cutout: '70%',
        }
    });

    // ── Daily Collections Bar ────────────────────────────────────────────
    new Chart(document.getElementById('dailyCollectionsChart'), {
        type: 'bar',
        data: {
            labels: @json($chartData['dailyCollections']['labels'] ?? []),
            datasets: [{
                label: 'Collected ($)',
                data: @json($chartData['dailyCollections']['data'] ?? []),
                backgroundColor: 'rgba(16, 185, 129, 0.6)',
                borderColor: 'rgb(16, 185, 129)',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { callback: v => '$'+v } },
                x: { grid: { display: false } }
            }
        }
    });

    // ── Monthly Trend Line ───────────────────────────────────────────────
    new Chart(document.getElementById('monthlyTrendChart'), {
        type: 'line',
        data: {
            labels: @json($chartData['monthlyTrend']['labels'] ?? []),
            datasets: [{
                label: 'Revenue ($)',
                data: @json($chartData['monthlyTrend']['data'] ?? []),
                fill: true,
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                borderColor: 'rgb(139, 92, 246)',
                borderWidth: 2.5,
                tension: 0.4,
                pointBackgroundColor: 'rgb(139, 92, 246)',
                pointRadius: 4,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { callback: v => '$'+v } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>
@endpush
