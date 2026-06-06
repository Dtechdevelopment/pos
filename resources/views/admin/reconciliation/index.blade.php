@extends('admin.layouts.app')

@section('title', 'Reconciliation Center')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Reconciliation Center</h1>
        <p class="text-sm text-gray-500 mt-0.5">Compare POS records against actual cash & payments.</p>
    </div>
    <a href="{{ route('admin.reports.reconciliation') }}"
        class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 text-sm font-medium shadow-sm transition">
        <i class="fas fa-chart-pie mr-2 text-gray-400"></i>Reconciliation Report
    </a>
</div>

@if(session('success'))
<div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3">
    <i class="fas fa-circle-check text-green-500"></i>
    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
</div>
@endif

@if($errors->any())
<div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3">
    <i class="fas fa-circle-exclamation text-red-500 mt-0.5"></i>
    <div>
        <p class="text-sm font-medium text-red-700">Please fix the following:</p>
        <ul class="mt-1 list-disc list-inside text-sm text-red-600">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
</div>
@endif

{{-- ── All-time Summary Cards ──────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="bg-gradient-to-br from-blue-500 to-indigo-700 rounded-2xl p-4 text-white shadow-sm">
        <p class="text-blue-100 text-xs font-medium mb-1">Total Orders</p>
        <p class="text-2xl font-bold">{{ number_format($totalSalesQty) }}</p>
        <p class="text-blue-200 text-xs mt-1">${{ number_format($totalSalesAmount, 2) }}</p>
    </div>
    <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-4 text-white shadow-sm">
        <p class="text-emerald-100 text-xs font-medium mb-1">Paid Invoices</p>
        <p class="text-2xl font-bold">{{ number_format($totalPaidQty) }}</p>
        <p class="text-emerald-200 text-xs mt-1">${{ number_format($totalPaidAmount, 2) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-yellow-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-clock text-yellow-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Pending</p>
            <p class="text-xl font-bold text-gray-800">${{ number_format($pendingAmount, 2) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-triangle-exclamation text-red-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Missing Items</p>
            <p class="text-xl font-bold {{ $missingItems > 0 ? 'text-red-600' : 'text-gray-800' }}">
                {{ number_format($missingItems) }}
            </p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-rose-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-dollar-sign text-rose-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Missing Sales</p>
            <p class="text-xl font-bold {{ $missingSales > 0 ? 'text-rose-600' : 'text-gray-800' }}">
                ${{ number_format($missingSales, 2) }}
            </p>
        </div>
    </div>
</div>

{{-- ── Filter bar ──────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
    <form method="GET" action="{{ route('admin.reconciliation.index') }}" class="flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Date</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-calendar text-xs"></i>
                </span>
                <input type="date" name="date" value="{{ $date }}"
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
        <button type="submit"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">
            <i class="fas fa-rotate mr-1.5"></i> Load Day
        </button>
    </form>
</div>

{{-- ── Variance Banner ─────────────────────────────────────────────── --}}
@php
    $v         = $posRecord['variance'];
    $balanced  = abs($v) < 0.01;
    $surplus   = $v > 0;
    $bannerGrad = $balanced ? 'from-emerald-500 to-teal-600' : ($surplus ? 'from-blue-500 to-indigo-600' : 'from-red-500 to-rose-600');
    $bannerIcon = $balanced ? 'fa-scale-balanced' : ($surplus ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down');
    $bannerMsg  = $balanced ? 'Balanced — No variance detected' : ($surplus ? 'Surplus: more cash collected than expected' : 'Shortfall: collections fall short of net sales');
@endphp
<div class="rounded-2xl p-5 mb-6 bg-gradient-to-r {{ $bannerGrad }} text-white shadow-sm flex items-center justify-between">
    <div>
        <p class="text-sm font-medium opacity-80 mb-0.5">{{ now()->parse($date)->format('l, F j, Y') }}</p>
        <p class="text-3xl font-bold">{{ $v >= 0 ? '+' : '' }}${{ number_format($v, 2) }}</p>
        <p class="text-sm opacity-70 mt-1">{{ $bannerMsg }}</p>
    </div>
    <i class="fas {{ $bannerIcon }} text-7xl opacity-20"></i>
</div>

{{-- ── Two-Panel: POS Record + Submit Form ────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    {{-- POS Record --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <div class="w-7 h-7 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-desktop text-blue-500 text-xs"></i>
            </div>
            <span class="text-sm font-semibold text-gray-700">POS Record</span>
            <span class="text-xs text-gray-400 ml-1">{{ now()->parse($date)->format('M d, Y') }}</span>
        </div>
        <div class="divide-y divide-gray-50">
            @php
                $posRows = [
                    ['Gross Sales',        $posRecord['grossSales'],     'text-gray-800', 'fa-file-invoice',      'bg-gray-100'],
                    ['Discounts',         -$posRecord['discounts'],      'text-red-500',  'fa-tag',               'bg-red-50'],
                    ['Tax',                $posRecord['tax'],            'text-orange-500','fa-percent',          'bg-orange-50'],
                    ['Net Sales (Paid)',   $posRecord['netSales'],       'text-blue-700 font-bold','fa-circle-check','bg-blue-50'],
                    ['Cash Collected',     $posRecord['cashSales'],      'text-emerald-600','fa-money-bill-wave', 'bg-emerald-50'],
                    ['Card Collected',     $posRecord['cardSales'],      'text-purple-600','fa-credit-card',      'bg-purple-50'],
                    ['M-Pesa Collected',   $posRecord['mobileSales'],    'text-blue-600', 'fa-mobile-screen',     'bg-blue-50'],
                    ['Bank Transfer',      $posRecord['bankSales'],      'text-orange-600','fa-building-columns', 'bg-orange-50'],
                    ['Total Collected',    $posRecord['totalCollected'], 'text-teal-700 font-bold','fa-vault',    'bg-teal-50'],
                ];
            @endphp
            @foreach($posRows as [$label, $amount, $textCls, $icon, $iconBg])
            <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50/40 transition
                {{ in_array($label, ['Net Sales (Paid)', 'Total Collected']) ? 'bg-gray-50/60' : '' }}">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 {{ $iconBg }} rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas {{ $icon }} text-xs {{ $textCls }}"></i>
                    </div>
                    <span class="text-sm text-gray-600">{{ $label }}</span>
                </div>
                <span class="text-sm {{ $textCls }}">
                    {{ $amount < 0 ? '-' : '' }}${{ number_format(abs($amount), 2) }}
                </span>
            </div>
            @endforeach

            {{-- Variance row --}}
            <div class="flex items-center justify-between px-5 py-3.5
                {{ $balanced ? 'bg-emerald-50' : ($surplus ? 'bg-blue-50' : 'bg-red-50') }}">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 {{ $balanced ? 'bg-emerald-100' : ($surplus ? 'bg-blue-100' : 'bg-red-100') }} rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-scale-balanced text-xs {{ $balanced ? 'text-emerald-600' : ($surplus ? 'text-blue-600' : 'text-red-600') }}"></i>
                    </div>
                    <span class="text-sm font-bold {{ $balanced ? 'text-emerald-800' : ($surplus ? 'text-blue-800' : 'text-red-800') }}">
                        Variance
                    </span>
                </div>
                <span class="text-sm font-bold {{ $balanced ? 'text-emerald-700' : ($surplus ? 'text-blue-700' : 'text-red-700') }}">
                    {{ $v >= 0 ? '+' : '' }}${{ number_format($v, 2) }}
                </span>
            </div>

            {{-- Invoice counts --}}
            <div class="px-5 py-3 grid grid-cols-3 gap-3">
                <div class="text-center bg-green-50 rounded-xl py-2">
                    <p class="text-lg font-bold text-green-700">{{ $posRecord['paidCount'] }}</p>
                    <p class="text-xs text-green-600">Paid</p>
                </div>
                <div class="text-center bg-yellow-50 rounded-xl py-2">
                    <p class="text-lg font-bold text-yellow-700">{{ $posRecord['pendingCount'] }}</p>
                    <p class="text-xs text-yellow-600">Pending</p>
                </div>
                <div class="text-center bg-red-50 rounded-xl py-2">
                    <p class="text-lg font-bold text-red-700">{{ $posRecord['voidCount'] }}</p>
                    <p class="text-xs text-red-600">Void</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Submit Reconciliation --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <div class="w-7 h-7 bg-indigo-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-clipboard-check text-indigo-500 text-xs"></i>
            </div>
            <span class="text-sm font-semibold text-gray-700">Submit Reconciliation</span>
        </div>

        <form action="{{ route('admin.reconciliation.store') }}" method="POST" class="p-5">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">
                        Branch <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                            <i class="fas fa-store text-sm"></i>
                        </span>
                        <select name="branch_id"
                            class="w-full pl-9 pr-10 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-gray-50 appearance-none" required>
                            <option value="">— Select Branch —</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id', request('branch_id')) == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 pointer-events-none">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </span>
                    </div>
                </div>

                {{-- Auto-filled from POS --}}
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-3">
                    <p class="text-xs font-semibold text-blue-700 mb-2 flex items-center gap-1">
                        <i class="fas fa-circle-info"></i> Auto-filled from POS for {{ now()->parse($date)->format('M d') }}
                    </p>
                    <div class="grid grid-cols-2 gap-2 text-xs text-blue-600">
                        <div>Net Sales: <span class="font-bold">${{ number_format($posRecord['netSales'], 2) }}</span></div>
                        <div>Collected: <span class="font-bold">${{ number_format($posRecord['totalCollected'], 2) }}</span></div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Notes / Remarks</label>
                    <textarea name="notes" rows="3"
                        placeholder="Describe any discrepancies, cash drawer issues, or other observations..."
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-gray-50 transition resize-none">{{ old('notes') }}</textarea>
                </div>

                {{-- Variance status --}}
                <div class="flex items-center gap-3 p-3 rounded-xl border
                    {{ $balanced ? 'bg-green-50 border-green-200' : ($surplus ? 'bg-blue-50 border-blue-200' : 'bg-red-50 border-red-200') }}">
                    <i class="fas {{ $balanced ? 'fa-circle-check text-green-500' : ($surplus ? 'fa-arrow-up text-blue-500' : 'fa-triangle-exclamation text-red-500') }}"></i>
                    <div>
                        <p class="text-xs font-semibold {{ $balanced ? 'text-green-700' : ($surplus ? 'text-blue-700' : 'text-red-700') }}">
                            {{ $balanced ? 'Balanced' : ($surplus ? 'Surplus' : 'Shortfall') }}:
                            {{ $v >= 0 ? '+' : '' }}${{ number_format($v, 2) }}
                        </p>
                        <p class="text-xs {{ $balanced ? 'text-green-600' : ($surplus ? 'text-blue-600' : 'text-red-600') }} opacity-80">
                            This will be recorded in the reconciliation log.
                        </p>
                    </div>
                </div>

                <button type="submit"
                    class="w-full inline-flex items-center justify-center px-5 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                    <i class="fas fa-clipboard-check mr-2"></i> Submit Reconciliation
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── History Table ───────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-clock-rotate-left text-purple-500 text-xs"></i>
            </div>
            <span class="text-sm font-semibold text-gray-700">Reconciliation History</span>
            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full font-medium">
                {{ $reconciliations->total() }} records
            </span>
        </div>
        <a href="{{ route('admin.reports.reconciliation') }}"
            class="text-xs text-blue-600 hover:underline font-medium">Full report →</a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Date</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Branch</th>
                    <th class="text-right py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Orders</th>
                    <th class="text-right py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Sales Amt</th>
                    <th class="text-right py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Paid Amt</th>
                    <th class="text-right py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Missing</th>
                    <th class="text-right py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Pending</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Status</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Submitted By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reconciliations as $rec)
                @php
                    $rMissing   = $rec->missing_sales ?? 0;
                    $rBalanced  = abs($rMissing) < 0.01;
                @endphp
                <tr class="border-b border-gray-50 hover:bg-indigo-50/20 transition-colors">
                    <td class="py-3 px-4">
                        <p class="font-medium text-gray-800 text-xs">
                            {{ $rec->reconciliation_date->format('M d, Y') }}
                        </p>
                        <p class="text-gray-400 text-xs">{{ $rec->reconciliation_date->format('l') }}</p>
                    </td>
                    <td class="py-3 px-4 text-gray-600 text-xs">{{ $rec->branch->name ?? '—' }}</td>
                    <td class="py-3 px-4 text-right text-gray-600 text-xs">{{ number_format($rec->sales_quantity) }}</td>
                    <td class="py-3 px-4 text-right font-medium text-gray-800">${{ number_format($rec->sales_amount, 2) }}</td>
                    <td class="py-3 px-4 text-right text-emerald-600 font-medium">${{ number_format($rec->paid_amount, 2) }}</td>
                    <td class="py-3 px-4 text-right">
                        @if($rMissing > 0.01)
                            <span class="text-red-600 font-semibold text-xs">-${{ number_format($rMissing, 2) }}</span>
                        @else
                            <span class="text-emerald-500 text-xs font-medium">None</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-right text-orange-500 text-xs">${{ number_format($rec->pending_payments, 2) }}</td>
                    <td class="py-3 px-4 text-center">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold
                            {{ $rBalanced ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                            <i class="fas {{ $rBalanced ? 'fa-circle-check' : 'fa-triangle-exclamation' }} text-[9px]"></i>
                            {{ $rBalanced ? 'Balanced' : 'Variance' }}
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                            @php
                                $cName = $rec->creator->name ?? 'System';
                                $cInit = collect(explode(' ', $cName))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
                            @endphp
                            <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-indigo-600 text-xs font-bold">{{ $cInit }}</span>
                            </div>
                            <span class="text-xs text-gray-600">{{ $cName }}</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="py-14 text-center">
                        <i class="fas fa-scale-balanced text-4xl text-gray-200 block mb-3"></i>
                        <p class="text-gray-400 text-sm">No reconciliation records yet.</p>
                        <p class="text-gray-400 text-xs mt-1">Submit your first reconciliation using the form above.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($reconciliations->hasPages())
    <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
        <p class="text-xs text-gray-500">
            Showing {{ $reconciliations->firstItem() }}–{{ $reconciliations->lastItem() }}
            of {{ $reconciliations->total() }} records
        </p>
        {{ $reconciliations->links() }}
    </div>
    @endif
</div>

@endsection
