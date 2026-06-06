@extends('admin.layouts.app')

@section('title', 'Kitchen Display')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Kitchen Display</h1>
        <p class="text-sm text-gray-500 mt-0.5">Live queue — auto-refreshes every 30 seconds.</p>
    </div>
    <div class="flex items-center gap-2">
        {{-- Branch filter --}}
        <form method="GET" action="{{ route('admin.kitchen.index') }}" id="branchForm">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-store text-xs"></i>
                </span>
                <select name="branch_id" onchange="document.getElementById('branchForm').submit()"
                    class="pl-8 pr-8 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 bg-white appearance-none shadow-sm">
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
        </form>
        <div class="flex items-center gap-1.5 px-3 py-2 bg-green-50 border border-green-200 rounded-lg text-xs text-green-700 font-medium">
            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
            Live
        </div>
        <button onclick="location.reload()"
            class="w-9 h-9 flex items-center justify-center bg-white border border-gray-200 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition shadow-sm"
            title="Refresh">
            <i class="fas fa-rotate-right text-sm"></i>
        </button>
    </div>
</div>

@if(session('success'))
<div class="mb-5 bg-green-50 border border-green-200 rounded-xl p-3 flex items-center gap-3">
    <i class="fas fa-circle-check text-green-500"></i>
    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
</div>
@endif

{{-- ── Stats Bar ────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-6">
    @php
        $stats = [
            ['Incoming',   $summary['pending'],    'bg-yellow-50 border-yellow-200',  'text-yellow-700', 'fa-inbox',        'text-yellow-500'],
            ['Preparing',  $summary['preparing'],  'bg-blue-50 border-blue-200',      'text-blue-700',   'fa-fire-burner',  'text-blue-500'],
            ['Ready',      $summary['ready'],      'bg-purple-50 border-purple-200',  'text-purple-700', 'fa-bell',         'text-purple-500'],
            ['Delayed',    $summary['delayed'],    'bg-red-50 border-red-200',        'text-red-700',    'fa-triangle-exclamation', 'text-red-500'],
            ['Done Today', $summary['done_today'], 'bg-green-50 border-green-200',    'text-green-700',  'fa-circle-check', 'text-green-500'],
        ];
    @endphp
    @foreach($stats as [$label, $count, $bg, $text, $icon, $iconColor])
    <div class="bg-white rounded-2xl border {{ $bg }} shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 {{ $bg }} rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas {{ $icon }} {{ $iconColor }}"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">{{ $label }}</p>
            <p class="text-2xl font-bold {{ $text }}">{{ $count }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- ── Kanban Columns ───────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ═══ INCOMING / PENDING ═══ --}}
    @php
        $cols = [
            [
                'title'  => 'Incoming',
                'icon'   => 'fa-inbox',
                'strip'  => 'from-yellow-400 to-orange-500',
                'badge'  => 'bg-yellow-100 text-yellow-800',
                'header' => 'bg-yellow-50',
                'orders' => $incoming,
                'empty'  => 'No incoming orders',
                'action_status' => 'preparing',
                'action_label'  => 'Start Preparing',
                'action_class'  => 'from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700',
                'action_icon'   => 'fa-fire-burner',
            ],
            [
                'title'  => 'Preparing',
                'icon'   => 'fa-fire-burner',
                'strip'  => 'from-blue-400 to-indigo-600',
                'badge'  => 'bg-blue-100 text-blue-800',
                'header' => 'bg-blue-50',
                'orders' => $preparing,
                'empty'  => 'Nothing being prepared',
                'action_status' => 'ready',
                'action_label'  => 'Mark as Ready',
                'action_class'  => 'from-purple-500 to-violet-600 hover:from-purple-600 hover:to-violet-700',
                'action_icon'   => 'fa-bell',
            ],
            [
                'title'  => 'Ready to Serve',
                'icon'   => 'fa-bell',
                'strip'  => 'from-purple-400 to-violet-600',
                'badge'  => 'bg-purple-100 text-purple-800',
                'header' => 'bg-purple-50',
                'orders' => $ready,
                'empty'  => 'No orders ready yet',
                'action_status' => 'delivered',
                'action_label'  => 'Mark Delivered',
                'action_class'  => 'from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700',
                'action_icon'   => 'fa-circle-check',
            ],
        ];
    @endphp

    @foreach($cols as $col)
    <div class="flex flex-col bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Column header --}}
        <div class="flex items-center justify-between px-5 py-3.5 {{ $col['header'] }} border-b border-gray-100">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br {{ $col['strip'] }} flex items-center justify-center shadow-sm">
                    <i class="fas {{ $col['icon'] }} text-white text-sm"></i>
                </div>
                <span class="font-semibold text-gray-700">{{ $col['title'] }}</span>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $col['badge'] }}">
                {{ $col['orders']->count() }}
            </span>
        </div>

        {{-- Cards scroll area --}}
        <div class="flex-1 overflow-y-auto p-3 space-y-3" style="max-height: calc(100vh - 340px); min-height: 200px;">
            @forelse($col['orders'] as $ko)
            @php
                $ageMin    = $ko->created_at->diffInMinutes(now());
                $isDelayed = $ageMin > 15 && $ko->status === 'pending';
                $isLong    = $ko->started_at && $ko->started_at->diffInMinutes(now()) > 20;
                $chefName  = $ko->chef->name ?? null;
                $chefInit  = $chefName ? collect(explode(' ', $chefName))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('') : null;
            @endphp
            <div class="rounded-xl border-2 p-3.5 transition-all
                {{ $isDelayed || $isLong
                    ? 'border-red-300 bg-red-50'
                    : ($col['header'] === 'bg-purple-50' ? 'border-purple-200 bg-purple-50/40' : 'border-gray-100 bg-white hover:border-gray-200 hover:shadow-sm') }}">

                {{-- Card header --}}
                <div class="flex items-start justify-between mb-2.5">
                    <div class="flex items-center gap-2">
                        @if($isDelayed || $isLong)
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse flex-shrink-0"></span>
                        @endif
                        <div>
                            <p class="font-bold text-gray-800 text-sm">
                                Order #{{ $ko->order->order_number ?? $ko->order_id }}
                            </p>
                            @if($ko->order?->restaurantTable)
                            <p class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                                <i class="fas fa-chair text-gray-300"></i>
                                Table {{ $ko->order->restaurantTable->table_number }}
                            </p>
                            @endif
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-xs font-semibold {{ $isDelayed || $isLong ? 'text-red-600' : 'text-gray-500' }}">
                            {{ $ko->created_at->format('H:i') }}
                        </p>
                        <p class="text-xs {{ $isDelayed || $isLong ? 'text-red-500 font-bold' : 'text-gray-400' }}">
                            {{ $ageMin }}m ago
                            @if($isDelayed || $isLong) ⚠ @endif
                        </p>
                    </div>
                </div>

                {{-- Item --}}
                <div class="bg-white/80 rounded-lg px-3 py-2.5 border border-gray-100 mb-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-orange-100 text-orange-700 text-xs font-bold flex-shrink-0">
                                {{ $ko->quantity }}×
                            </span>
                            <p class="text-sm font-semibold text-gray-800">{{ $ko->item_name }}</p>
                        </div>
                    </div>
                    @if($ko->notes)
                    <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded px-2 py-1 mt-2 flex items-start gap-1">
                        <i class="fas fa-note-sticky text-amber-500 mt-0.5 flex-shrink-0"></i>
                        {{ $ko->notes }}
                    </p>
                    @endif
                </div>

                {{-- Chef badge if assigned --}}
                @if($chefName)
                <div class="flex items-center gap-1.5 mb-3 text-xs text-gray-500">
                    <div class="w-5 h-5 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 text-[10px] font-bold">
                        {{ $chefInit }}
                    </div>
                    <span>{{ $chefName }}</span>
                    @if($ko->started_at)
                    <span class="text-gray-400">· started {{ $ko->started_at->diffForHumans(null, true) }} ago</span>
                    @endif
                </div>
                @endif

                {{-- Action button --}}
                <form action="{{ route('admin.kitchen.update-status', $ko) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="{{ $col['action_status'] }}">
                    <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-gradient-to-r {{ $col['action_class'] }} text-white rounded-lg text-xs font-semibold shadow-sm transition">
                        <i class="fas {{ $col['action_icon'] }} text-xs"></i>
                        {{ $col['action_label'] }}
                    </button>
                </form>

                {{-- Cancel for pending --}}
                @if($ko->status === 'pending')
                <form action="{{ route('admin.kitchen.update-status', $ko) }}" method="POST" class="mt-1.5">
                    @csrf
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-1.5 border border-red-200 text-red-500 hover:bg-red-50 rounded-lg text-xs font-medium transition"
                        onclick="return confirm('Cancel this kitchen item?')">
                        <i class="fas fa-xmark text-xs"></i> Cancel
                    </button>
                </form>
                @endif
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-3">
                    <i class="fas {{ $col['icon'] }} text-gray-300 text-xl"></i>
                </div>
                <p class="text-sm text-gray-400">{{ $col['empty'] }}</p>
            </div>
            @endforelse
        </div>
    </div>
    @endforeach

</div>

{{-- ── Recently Delivered (Today) ───────────────────────────────────── --}}
@if($delivered->count())
<div class="mt-6 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center gap-2">
        <div class="w-7 h-7 bg-emerald-100 rounded-lg flex items-center justify-center">
            <i class="fas fa-circle-check text-emerald-500 text-xs"></i>
        </div>
        <span class="text-sm font-semibold text-gray-700">Delivered Today</span>
        <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium">
            {{ $delivered->count() }}
        </span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left py-2.5 px-4 font-medium text-gray-500 text-xs uppercase">Order</th>
                    <th class="text-left py-2.5 px-4 font-medium text-gray-500 text-xs uppercase">Item</th>
                    <th class="text-center py-2.5 px-4 font-medium text-gray-500 text-xs uppercase">Qty</th>
                    <th class="text-left py-2.5 px-4 font-medium text-gray-500 text-xs uppercase">Chef</th>
                    <th class="text-center py-2.5 px-4 font-medium text-gray-500 text-xs uppercase">Prep Time</th>
                    <th class="text-center py-2.5 px-4 font-medium text-gray-500 text-xs uppercase">Delivered</th>
                </tr>
            </thead>
            <tbody>
                @foreach($delivered as $ko)
                @php
                    $prepMins = $ko->started_at && $ko->completed_at
                        ? $ko->started_at->diffInMinutes($ko->completed_at)
                        : null;
                @endphp
                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition">
                    <td class="py-2.5 px-4 text-xs font-semibold text-gray-700">
                        #{{ $ko->order->order_number ?? $ko->order_id }}
                    </td>
                    <td class="py-2.5 px-4 text-xs text-gray-600">{{ $ko->item_name }}</td>
                    <td class="py-2.5 px-4 text-center">
                        <span class="text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full font-semibold">
                            {{ $ko->quantity }}
                        </span>
                    </td>
                    <td class="py-2.5 px-4 text-xs text-gray-500">{{ $ko->chef->name ?? '—' }}</td>
                    <td class="py-2.5 px-4 text-center">
                        @if($prepMins !== null)
                        <span class="text-xs {{ $prepMins > 20 ? 'text-red-600 font-semibold' : 'text-emerald-600' }}">
                            {{ $prepMins }}m
                        </span>
                        @else
                        <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                    <td class="py-2.5 px-4 text-center text-xs text-gray-400">
                        {{ $ko->completed_at?->format('H:i') ?? $ko->updated_at->format('H:i') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    // Auto-refresh every 30 seconds
    setTimeout(() => location.reload(), 30000);

    // Countdown timer
    let seconds = 30;
    const indicator = document.querySelector('.animate-pulse');
    setInterval(() => {
        seconds--;
        if (seconds <= 0) seconds = 30;
    }, 1000);
</script>
@endpush
