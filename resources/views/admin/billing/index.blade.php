@extends('admin.layouts.app')

@section('title', 'Billing & Invoices')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Billing & Invoices</h1>
        <p class="text-sm text-gray-500 mt-1">Track and manage all customer invoices.</p>
    </div>
</div>

@if(session('success'))
<div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4 flex items-center gap-3">
    <i class="fas fa-circle-check text-green-500"></i>
    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
</div>
@endif

{{-- Summary Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center">
                <i class="fas fa-file-invoice text-blue-600 text-sm"></i>
            </div>
            <p class="text-xs text-gray-500 font-medium">Total</p>
        </div>
        <p class="text-2xl font-bold text-gray-800">{{ number_format($summary['total']) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-9 h-9 rounded-lg bg-green-100 flex items-center justify-center">
                <i class="fas fa-circle-check text-green-600 text-sm"></i>
            </div>
            <p class="text-xs text-gray-500 font-medium">Paid</p>
        </div>
        <p class="text-2xl font-bold text-green-700">{{ number_format($summary['paid']) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-9 h-9 rounded-lg bg-yellow-100 flex items-center justify-center">
                <i class="fas fa-clock text-yellow-600 text-sm"></i>
            </div>
            <p class="text-xs text-gray-500 font-medium">Pending</p>
        </div>
        <p class="text-2xl font-bold text-yellow-700">{{ number_format($summary['pending']) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-9 h-9 rounded-lg bg-red-100 flex items-center justify-center">
                <i class="fas fa-ban text-red-500 text-sm"></i>
            </div>
            <p class="text-xs text-gray-500 font-medium">Cancelled</p>
        </div>
        <p class="text-2xl font-bold text-red-600">{{ number_format($summary['cancelled']) }}</p>
    </div>
    <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-sm p-4 xl:col-span-1">
        <p class="text-xs text-green-100 font-medium mb-1">Revenue Collected</p>
        <p class="text-xl font-bold text-white">${{ number_format($summary['revenue'], 2) }}</p>
    </div>
    <div class="bg-gradient-to-br from-orange-400 to-red-500 rounded-xl shadow-sm p-4 xl:col-span-1">
        <p class="text-xs text-orange-100 font-medium mb-1">Outstanding</p>
        <p class="text-xl font-bold text-white">${{ number_format($summary['outstanding'], 2) }}</p>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-5">
    <form method="GET" action="{{ route('admin.billing.index') }}"
        class="flex flex-wrap items-end gap-3">

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Invoice #</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-search text-xs"></i>
                </span>
                <input type="text" name="invoice_no" value="{{ request('invoice_no') }}"
                    placeholder="Search invoice..."
                    class="pl-8 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 w-44">
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Status</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-filter text-xs"></i>
                </span>
                <select name="status"
                    class="pl-8 pr-8 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 appearance-none">
                    <option value="">All Statuses</option>
                    @foreach(['draft','pending','paid','cancelled','void'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>
                            {{ ucfirst($s) }}
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
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Date To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50">
        </div>

        <div class="flex gap-2">
            <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                <i class="fas fa-search mr-1.5"></i> Filter
            </button>
            @if(request()->hasAny(['invoice_no','status','date_from','date_to']))
            <a href="{{ route('admin.billing.index') }}"
                class="inline-flex items-center px-4 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                <i class="fas fa-xmark mr-1.5"></i> Clear
            </a>
            @endif
        </div>
    </form>
</div>

{{-- Status quick-filter pills --}}
<div class="flex flex-wrap gap-2 mb-4">
    @php
        $pills = [
            ''          => ['All', 'bg-gray-800 text-white', 'bg-gray-100 text-gray-600 hover:bg-gray-200'],
            'paid'      => ['Paid', 'bg-green-600 text-white', 'bg-green-50 text-green-700 hover:bg-green-100'],
            'pending'   => ['Pending', 'bg-yellow-500 text-white', 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100'],
            'draft'     => ['Draft', 'bg-blue-500 text-white', 'bg-blue-50 text-blue-700 hover:bg-blue-100'],
            'cancelled' => ['Cancelled', 'bg-red-500 text-white', 'bg-red-50 text-red-600 hover:bg-red-100'],
            'void'      => ['Void', 'bg-gray-500 text-white', 'bg-gray-50 text-gray-600 hover:bg-gray-100'],
        ];
    @endphp
    @foreach($pills as $val => [$label, $active, $inactive])
    <a href="{{ route('admin.billing.index', array_merge(request()->except('status','page'), $val ? ['status' => $val] : [])) }}"
        class="px-3 py-1.5 rounded-full text-xs font-semibold transition
            {{ request('status', '') === $val ? $active : $inactive }}">
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
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Invoice</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Customer</th>
                    <th class="text-right py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Subtotal</th>
                    <th class="text-right py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Tax</th>
                    <th class="text-right py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Discount</th>
                    <th class="text-right py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Total</th>
                    <th class="text-right py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Paid</th>
                    <th class="text-right py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Balance</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Status</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Date</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                @php
                    $balance = ($invoice->total ?? 0) - ($invoice->paid_amount ?? 0);
                    $statusCfg = [
                        'paid'      => ['bg-green-100 text-green-700',  'fa-circle-check'],
                        'pending'   => ['bg-yellow-100 text-yellow-700','fa-clock'],
                        'draft'     => ['bg-blue-100 text-blue-700',    'fa-pen'],
                        'cancelled' => ['bg-red-100 text-red-600',      'fa-circle-xmark'],
                        'void'      => ['bg-gray-100 text-gray-600',    'fa-ban'],
                    ];
                    [$sBadge, $sIcon] = $statusCfg[$invoice->status] ?? ['bg-gray-100 text-gray-600', 'fa-circle'];
                @endphp
                <tr class="border-b border-gray-50 hover:bg-blue-50/20 transition-colors">
                    {{-- Invoice # --}}
                    <td class="py-3 px-4">
                        <div>
                            <p class="font-semibold text-gray-800 text-xs">{{ $invoice->invoice_number }}</p>
                            @if($invoice->order)
                                <p class="text-xs text-gray-400 mt-0.5">Order #{{ $invoice->order->order_number ?? $invoice->order_id }}</p>
                            @endif
                        </div>
                    </td>

                    {{-- Customer --}}
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-user text-indigo-400 text-xs"></i>
                            </div>
                            <span class="text-gray-700">{{ $invoice->customer->name ?? 'Walk-in' }}</span>
                        </div>
                    </td>

                    {{-- Subtotal --}}
                    <td class="py-3 px-4 text-right text-gray-600">${{ number_format($invoice->subtotal ?? 0, 2) }}</td>

                    {{-- Tax --}}
                    <td class="py-3 px-4 text-right text-gray-500 text-xs">${{ number_format($invoice->tax ?? 0, 2) }}</td>

                    {{-- Discount --}}
                    <td class="py-3 px-4 text-right">
                        @if(($invoice->discount ?? 0) > 0)
                            <span class="text-red-500 text-xs">-${{ number_format($invoice->discount, 2) }}</span>
                        @else
                            <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>

                    {{-- Total --}}
                    <td class="py-3 px-4 text-right font-semibold text-gray-800">${{ number_format($invoice->total ?? 0, 2) }}</td>

                    {{-- Paid --}}
                    <td class="py-3 px-4 text-right text-green-600 font-medium">${{ number_format($invoice->paid_amount ?? 0, 2) }}</td>

                    {{-- Balance --}}
                    <td class="py-3 px-4 text-right">
                        @if($balance > 0)
                            <span class="text-red-500 font-semibold">${{ number_format($balance, 2) }}</span>
                        @else
                            <span class="text-green-500 text-xs font-medium">Settled</span>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td class="py-3 px-4 text-center">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $sBadge }}">
                            <i class="fas {{ $sIcon }} text-[10px]"></i>
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </td>

                    {{-- Date --}}
                    <td class="py-3 px-4 text-center text-gray-500 text-xs whitespace-nowrap">
                        {{ $invoice->created_at->format('M d, Y') }}
                        <p class="text-gray-300">{{ $invoice->created_at->format('h:i A') }}</p>
                    </td>

                    {{-- Actions --}}
                    <td class="py-3 px-4">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('admin.billing.show', $invoice) }}"
                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition"
                                title="View">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <a href="{{ route('admin.billing.reprint', $invoice) }}"
                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-50 text-gray-600 hover:bg-gray-100 transition"
                                title="Print" target="_blank">
                                <i class="fas fa-print text-xs"></i>
                            </a>
                            @if(!in_array($invoice->status, ['void','cancelled']))
                            <form action="{{ route('admin.billing.void', $invoice) }}" method="POST"
                                onsubmit="return confirm('Void invoice {{ $invoice->invoice_number }}?')">
                                @csrf
                                <button type="submit"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition"
                                    title="Void">
                                    <i class="fas fa-ban text-xs"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="py-16 text-center">
                        <i class="fas fa-file-invoice text-4xl text-gray-200 block mb-3"></i>
                        <p class="text-gray-400 text-sm">No invoices found.</p>
                        @if(request()->hasAny(['invoice_no','status','date_from','date_to']))
                            <a href="{{ route('admin.billing.index') }}" class="text-blue-500 text-sm mt-1 hover:underline">Clear filters</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($invoices->hasPages())
    <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
        <p class="text-xs text-gray-500">
            Showing {{ $invoices->firstItem() }}–{{ $invoices->lastItem() }} of {{ $invoices->total() }} invoices
        </p>
        {{ $invoices->links() }}
    </div>
    @endif
</div>

@endsection
