@extends('admin.layouts.app')

@section('title', 'Payments')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Payments</h1>
        <p class="text-sm text-gray-500 mt-1">All payment transactions across branches.</p>
    </div>
    <a href="{{ route('admin.payments.dashboard') }}"
        class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-lg text-sm font-semibold shadow-sm transition-all">
        <i class="fas fa-chart-pie mr-2"></i>Dashboard
    </a>
</div>

@if(session('success'))
<div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4 flex items-center gap-3">
    <i class="fas fa-circle-check text-green-500"></i>
    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
</div>
@endif

{{-- Summary Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-receipt text-blue-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Total</p>
            <p class="text-xl font-bold text-gray-800">{{ number_format($summary['total']) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-circle-check text-green-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Completed</p>
            <p class="text-xl font-bold text-green-700">{{ number_format($summary['completed']) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-yellow-50 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-clock text-yellow-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Pending</p>
            <p class="text-xl font-bold text-yellow-600">{{ number_format($summary['pending']) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-rotate-left text-red-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Refunded</p>
            <p class="text-xl font-bold text-red-600">{{ number_format($summary['refunded']) }}</p>
        </div>
    </div>
    <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl shadow-sm p-4 flex items-center gap-3 lg:col-span-1 col-span-2">
        <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-money-bill-wave text-white"></i>
        </div>
        <div>
            <p class="text-xs text-emerald-100">Collected</p>
            <p class="text-xl font-bold text-white">${{ number_format($summary['collected'], 2) }}</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-5">
    <form method="GET" action="{{ route('admin.payments.index') }}" class="flex flex-wrap items-end gap-3">

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Reference #</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-search text-xs"></i>
                </span>
                <input type="text" name="reference" value="{{ request('reference') }}"
                    placeholder="Search reference..."
                    class="pl-8 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 w-44">
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Method</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-wallet text-xs"></i>
                </span>
                <select name="payment_method"
                    class="pl-8 pr-8 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50 appearance-none">
                    <option value="">All Methods</option>
                    @foreach(['cash' => 'Cash', 'm_pesa' => 'M-Pesa', 'card' => 'Card', 'bank_transfer' => 'Bank Transfer'] as $val => $label)
                        <option value="{{ $val }}" {{ request('payment_method') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <span class="absolute inset-y-0 right-0 pr-2 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-chevron-down text-xs"></i>
                </span>
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
                    @foreach(['pending','completed','reversed','refunded','failed'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
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
            @if(request()->hasAny(['reference','payment_method','status','date_from','date_to']))
            <a href="{{ route('admin.payments.index') }}"
                class="inline-flex items-center px-4 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                <i class="fas fa-xmark mr-1.5"></i> Clear
            </a>
            @endif
        </div>
    </form>
</div>

{{-- Quick-filter pills --}}
<div class="flex flex-wrap gap-2 mb-4">
    @php
        $pills = [
            ''          => 'All',
            'completed' => 'Completed',
            'pending'   => 'Pending',
            'reversed'  => 'Reversed',
            'refunded'  => 'Refunded',
            'failed'    => 'Failed',
        ];
        $pillActive   = 'bg-gray-800 text-white';
        $pillColors   = [
            ''          => ['bg-gray-800 text-white',       'bg-gray-100 text-gray-600 hover:bg-gray-200'],
            'completed' => ['bg-green-600 text-white',      'bg-green-50 text-green-700 hover:bg-green-100'],
            'pending'   => ['bg-yellow-500 text-white',     'bg-yellow-50 text-yellow-700 hover:bg-yellow-100'],
            'reversed'  => ['bg-gray-500 text-white',       'bg-gray-50 text-gray-600 hover:bg-gray-100'],
            'refunded'  => ['bg-red-500 text-white',        'bg-red-50 text-red-600 hover:bg-red-100'],
            'failed'    => ['bg-rose-600 text-white',       'bg-rose-50 text-rose-600 hover:bg-rose-100'],
        ];
    @endphp
    @foreach($pills as $val => $label)
    <a href="{{ route('admin.payments.index', array_merge(request()->except('status','page'), $val ? ['status' => $val] : [])) }}"
        class="px-3 py-1.5 rounded-full text-xs font-semibold transition
            {{ request('status', '') === $val ? $pillColors[$val][0] : $pillColors[$val][1] }}">
        {{ $label }}
    </a>
    @endforeach

    {{-- Method pills --}}
    @php
        $methodPills = ['cash' => 'Cash', 'm_pesa' => 'M-Pesa', 'card' => 'Card', 'bank_transfer' => 'Bank Transfer'];
        $methodPillColors = [
            'cash'          => ['bg-emerald-600 text-white', 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'],
            'm_pesa'        => ['bg-blue-600 text-white',    'bg-blue-50 text-blue-700 hover:bg-blue-100'],
            'card'          => ['bg-purple-600 text-white',  'bg-purple-50 text-purple-700 hover:bg-purple-100'],
            'bank_transfer' => ['bg-orange-500 text-white',  'bg-orange-50 text-orange-700 hover:bg-orange-100'],
        ];
    @endphp
    <span class="text-gray-300 text-xs flex items-center">|</span>
    @foreach($methodPills as $val => $label)
    <a href="{{ route('admin.payments.index', array_merge(request()->except('payment_method','page'), ['payment_method' => request('payment_method') === $val ? '' : $val])) }}"
        class="px-3 py-1.5 rounded-full text-xs font-semibold transition
            {{ request('payment_method') === $val ? $methodPillColors[$val][0] : $methodPillColors[$val][1] }}">
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
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Reference</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Invoice</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Method</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Branch</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Cashier</th>
                    <th class="text-right py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Amount</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Status</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Date</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                @php
                    $methodConfig = [
                        'cash'          => ['fa-money-bill-wave',    'bg-emerald-50 text-emerald-600'],
                        'm_pesa'        => ['fa-mobile-screen',      'bg-blue-50 text-blue-600'],
                        'card'          => ['fa-credit-card',        'bg-purple-50 text-purple-600'],
                        'bank_transfer' => ['fa-building-columns',   'bg-orange-50 text-orange-600'],
                    ];
                    $statusConfig = [
                        'completed' => ['bg-green-100 text-green-700',  'fa-circle-check'],
                        'pending'   => ['bg-yellow-100 text-yellow-700','fa-clock'],
                        'reversed'  => ['bg-gray-100 text-gray-600',    'fa-rotate-left'],
                        'refunded'  => ['bg-red-100 text-red-600',      'fa-rotate-left'],
                        'failed'    => ['bg-rose-100 text-rose-700',    'fa-circle-xmark'],
                    ];
                    [$mIcon, $mClass] = $methodConfig[$payment->payment_method] ?? ['fa-circle-dollar-sign', 'bg-gray-50 text-gray-500'];
                    [$sBadge, $sIcon] = $statusConfig[$payment->status] ?? ['bg-gray-100 text-gray-600', 'fa-circle'];
                @endphp
                <tr class="border-b border-gray-50 hover:bg-blue-50/20 transition-colors">

                    {{-- Reference --}}
                    <td class="py-3 px-4">
                        <span class="text-xs font-mono text-gray-600 bg-gray-50 px-2 py-1 rounded">
                            {{ $payment->reference_number ?? '—' }}
                        </span>
                    </td>

                    {{-- Invoice --}}
                    <td class="py-3 px-4">
                        @if($payment->invoice)
                            <a href="{{ route('admin.billing.show', $payment->invoice) }}"
                                class="text-blue-600 hover:underline text-xs font-medium">
                                {{ $payment->invoice->invoice_number }}
                            </a>
                        @else
                            <span class="text-gray-400 text-xs">—</span>
                        @endif
                    </td>

                    {{-- Method --}}
                    <td class="py-3 px-4">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg {{ $mClass }} text-xs font-medium">
                            <i class="fas {{ $mIcon }} text-xs"></i>
                            {{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}
                        </div>
                    </td>

                    {{-- Branch --}}
                    <td class="py-3 px-4 text-gray-600 text-xs">{{ $payment->branch->name ?? '—' }}</td>

                    {{-- Cashier --}}
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-user text-indigo-400 text-[9px]"></i>
                            </div>
                            <span class="text-xs text-gray-600">{{ $payment->cashier->name ?? '—' }}</span>
                        </div>
                    </td>

                    {{-- Amount --}}
                    <td class="py-3 px-4 text-right">
                        <span class="font-bold text-gray-800">${{ number_format($payment->amount, 2) }}</span>
                    </td>

                    {{-- Status --}}
                    <td class="py-3 px-4 text-center">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $sBadge }}">
                            <i class="fas {{ $sIcon }} text-[9px]"></i>
                            {{ ucfirst($payment->status) }}
                        </span>
                    </td>

                    {{-- Date --}}
                    <td class="py-3 px-4 text-center">
                        <p class="text-xs text-gray-600">{{ $payment->created_at->format('M d, Y') }}</p>
                        <p class="text-xs text-gray-400">{{ $payment->created_at->format('h:i A') }}</p>
                    </td>

                    {{-- Actions --}}
                    <td class="py-3 px-4">
                        <div class="flex items-center justify-center gap-1">
                            @if($payment->status === 'pending')
                            <form action="{{ route('admin.payments.verify', $payment) }}" method="POST"
                                onsubmit="return confirm('Verify this payment?')">
                                @csrf
                                <button type="submit"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-green-50 text-green-600 hover:bg-green-100 transition"
                                    title="Verify">
                                    <i class="fas fa-check text-xs"></i>
                                </button>
                            </form>
                            @endif

                            @if($payment->status === 'completed')
                            <form action="{{ route('admin.payments.reverse', $payment) }}" method="POST"
                                onsubmit="return confirm('Reverse this payment?')">
                                @csrf
                                <button type="submit"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-yellow-50 text-yellow-600 hover:bg-yellow-100 transition"
                                    title="Reverse">
                                    <i class="fas fa-rotate-left text-xs"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.payments.refund', $payment) }}" method="POST"
                                onsubmit="return confirm('Refund this payment?')">
                                @csrf
                                <button type="submit"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition"
                                    title="Refund">
                                    <i class="fas fa-money-bill-transfer text-xs"></i>
                                </button>
                            </form>
                            @endif

                            @if($payment->invoice)
                            <a href="{{ route('admin.billing.show', $payment->invoice) }}"
                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition"
                                title="View Invoice">
                                <i class="fas fa-file-invoice text-xs"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="py-16 text-center">
                        <i class="fas fa-receipt text-4xl text-gray-200 block mb-3"></i>
                        <p class="text-gray-400 text-sm">No payments found.</p>
                        @if(request()->hasAny(['reference','payment_method','status','date_from','date_to']))
                            <a href="{{ route('admin.payments.index') }}"
                                class="text-blue-500 text-sm mt-1 hover:underline inline-block">Clear filters</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($payments->hasPages())
    <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
        <p class="text-xs text-gray-500">
            Showing {{ $payments->firstItem() }}–{{ $payments->lastItem() }} of {{ $payments->total() }} payments
        </p>
        {{ $payments->links() }}
    </div>
    @endif
</div>

@endsection
