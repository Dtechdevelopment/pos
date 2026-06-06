@extends('admin.layouts.app')

@section('title', 'Customers')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Customers</h1>
        <p class="text-sm text-gray-500 mt-0.5">Manage your customer base and loyalty.</p>
    </div>
</div>

@if(session('success'))
<div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3">
    <i class="fas fa-circle-check text-green-500"></i>
    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
</div>
@endif

{{-- ── Summary Cards ───────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-gradient-to-br from-violet-500 to-purple-700 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/10 rounded-full"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-2">
                <p class="text-violet-100 text-sm font-medium">Total Customers</p>
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-sm"></i>
                </div>
            </div>
            <p class="text-3xl font-bold">{{ number_format($summary['total']) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-envelope text-blue-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">With Email</p>
            <p class="text-xl font-bold text-gray-800">{{ number_format($summary['withEmail']) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-11 h-11 bg-green-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-phone text-green-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">With Phone</p>
            <p class="text-xl font-bold text-gray-800">{{ number_format($summary['withPhone']) }}</p>
        </div>
    </div>

    <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/10 rounded-full"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-2">
                <p class="text-emerald-100 text-sm font-medium">Customer Revenue</p>
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-sm"></i>
                </div>
            </div>
            <p class="text-2xl font-bold">${{ number_format($summary['revenue'], 2) }}</p>
        </div>
    </div>
</div>

{{-- ── Filters ─────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-5">
    <form method="GET" action="{{ route('admin.customers.index') }}" class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Search</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-search text-xs"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Name, phone, or email..."
                    class="w-full pl-8 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 bg-gray-50">
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Branch</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-store text-xs"></i>
                </span>
                <select name="branch_id"
                    class="pl-8 pr-8 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 bg-gray-50 appearance-none">
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
                class="inline-flex items-center px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-lg text-sm font-medium transition">
                <i class="fas fa-search mr-1.5"></i> Search
            </button>
            @if(request()->hasAny(['search', 'branch_id']))
            <a href="{{ route('admin.customers.index') }}"
                class="inline-flex items-center px-4 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                <i class="fas fa-xmark mr-1.5"></i> Clear
            </a>
            @endif
        </div>
    </form>
</div>

{{-- ── Table ───────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Customer</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Contact</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Branch</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Orders</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Invoices</th>
                    <th class="text-right py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Total Spent</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Loyalty Pts</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Since</th>
                    <th class="text-center py-3 px-4 font-medium text-gray-500 text-xs uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                @php
                    $name     = $customer->name;
                    $initials = collect(explode(' ', $name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
                    $colors   = [
                        'from-violet-400 to-purple-600',
                        'from-blue-400 to-indigo-600',
                        'from-emerald-400 to-teal-600',
                        'from-orange-400 to-red-500',
                        'from-pink-400 to-rose-600',
                        'from-cyan-400 to-blue-500',
                    ];
                    $grad     = $colors[$customer->id % count($colors)];
                    $spent    = $customer->total_spent ?? 0;
                    $loyalty  = $customer->loyalty_points ?? 0;
                @endphp
                <tr class="border-b border-gray-50 hover:bg-violet-50/20 transition-colors">

                    {{-- Customer --}}
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br {{ $grad }} flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow-sm">
                                {{ $initials }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800 text-sm">{{ $name }}</p>
                                <p class="text-xs text-gray-400">#{{ $customer->id }}</p>
                            </div>
                        </div>
                    </td>

                    {{-- Contact --}}
                    <td class="py-3 px-4">
                        <div class="space-y-0.5">
                            @if($customer->phone)
                            <div class="flex items-center gap-1.5 text-xs text-gray-600">
                                <i class="fas fa-phone text-gray-400 w-3"></i>
                                {{ $customer->phone }}
                            </div>
                            @endif
                            @if($customer->email)
                            <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                <i class="fas fa-envelope text-gray-400 w-3"></i>
                                <span class="truncate max-w-[140px]">{{ $customer->email }}</span>
                            </div>
                            @endif
                            @if(!$customer->phone && !$customer->email)
                                <span class="text-xs text-gray-300">No contact info</span>
                            @endif
                        </div>
                    </td>

                    {{-- Branch --}}
                    <td class="py-3 px-4 text-xs text-gray-600">
                        {{ $customer->branch->name ?? '—' }}
                    </td>

                    {{-- Orders --}}
                    <td class="py-3 px-4 text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-semibold
                            {{ $customer->orders_count > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-400' }}">
                            {{ $customer->orders_count ?? 0 }}
                        </span>
                    </td>

                    {{-- Invoices --}}
                    <td class="py-3 px-4 text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-semibold
                            {{ $customer->invoices_count > 0 ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-400' }}">
                            {{ $customer->invoices_count ?? 0 }}
                        </span>
                    </td>

                    {{-- Total Spent --}}
                    <td class="py-3 px-4 text-right">
                        @if($spent > 0)
                            <span class="font-bold text-emerald-700">${{ number_format($spent, 2) }}</span>
                        @else
                            <span class="text-gray-300 text-xs">$0.00</span>
                        @endif
                    </td>

                    {{-- Loyalty Points --}}
                    <td class="py-3 px-4 text-center">
                        @if($loyalty > 0)
                        <div class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full text-xs font-semibold">
                            <i class="fas fa-star text-[9px]"></i>
                            {{ number_format($loyalty) }}
                        </div>
                        @else
                        <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>

                    {{-- Since --}}
                    <td class="py-3 px-4 text-center text-xs text-gray-400">
                        {{ $customer->created_at->format('M d, Y') }}
                    </td>

                    {{-- Actions --}}
                    <td class="py-3 px-4">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('admin.customers.show', $customer) }}"
                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition"
                                title="View profile">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <button type="button"
                                onclick="openEditModal({{ $customer->id }}, '{{ addslashes($customer->name) }}', '{{ addslashes($customer->phone ?? '') }}', '{{ addslashes($customer->email ?? '') }}', '{{ addslashes($customer->notes ?? '') }}')"
                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-violet-50 text-violet-600 hover:bg-violet-100 transition"
                                title="Quick edit">
                                <i class="fas fa-pen text-xs"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="py-16 text-center">
                        <i class="fas fa-users text-4xl text-gray-200 block mb-3"></i>
                        <p class="text-gray-400 text-sm">No customers found.</p>
                        @if(request()->hasAny(['search', 'branch_id']))
                            <a href="{{ route('admin.customers.index') }}"
                                class="text-violet-500 text-sm mt-1 hover:underline inline-block">Clear filters</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($customers->hasPages())
    <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
        <p class="text-xs text-gray-500">
            Showing {{ $customers->firstItem() }}–{{ $customers->lastItem() }} of {{ $customers->total() }} customers
        </p>
        {{ $customers->links() }}
    </div>
    @endif
</div>

{{-- ── Quick Edit Modal ────────────────────────────────────────────── --}}
<div id="editModal" class="fixed inset-0 z-50 hidden items-center justify-center">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeEditModal()"></div>

    {{-- Modal --}}
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 bg-violet-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-pen text-violet-600 text-xs"></i>
                </div>
                <span class="text-sm font-semibold text-gray-700">Edit Customer</span>
            </div>
            <button onclick="closeEditModal()" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-400 transition">
                <i class="fas fa-xmark text-sm"></i>
            </button>
        </div>

        <form id="editForm" method="POST" class="p-6">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                            <i class="fas fa-user text-sm"></i>
                        </span>
                        <input type="text" name="name" id="editName"
                            class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 bg-gray-50"
                            required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Phone</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-phone text-sm"></i>
                            </span>
                            <input type="text" name="phone" id="editPhone"
                                class="w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 bg-gray-50">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Email</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-envelope text-sm"></i>
                            </span>
                            <input type="email" name="email" id="editEmail"
                                class="w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 bg-gray-50">
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Notes</label>
                    <textarea name="notes" id="editNotes" rows="2"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 bg-gray-50 resize-none"></textarea>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" onclick="closeEditModal()"
                        class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-violet-600 to-purple-700 hover:from-violet-700 hover:to-purple-800 text-white rounded-lg text-sm font-semibold transition">
                        <i class="fas fa-save mr-2"></i> Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openEditModal(id, name, phone, email, notes) {
        document.getElementById('editName').value  = name;
        document.getElementById('editPhone').value = phone;
        document.getElementById('editEmail').value = email;
        document.getElementById('editNotes').value = notes;
        document.getElementById('editForm').action = `/admin/customers/${id}`;

        const modal = document.getElementById('editModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeEditModal() {
        const modal = document.getElementById('editModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeEditModal();
    });
</script>
@endpush
