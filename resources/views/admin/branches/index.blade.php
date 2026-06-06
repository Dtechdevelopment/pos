@extends('admin.layouts.app')

@section('title', 'Branches')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Branches</h1>
        <p class="text-sm text-gray-500 mt-1">Manage your restaurant locations.</p>
    </div>
    <a href="{{ route('admin.branches.create') }}"
        class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-lg text-sm font-semibold shadow-sm transition-all">
        <i class="fas fa-plus mr-2"></i> Add Branch
    </a>
</div>

@if(session('success'))
<div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4 flex items-center gap-3">
    <i class="fas fa-circle-check text-green-500"></i>
    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
</div>
@endif

{{-- Summary Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
            <i class="fas fa-store text-emerald-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Total</p>
            <p class="text-xl font-bold text-gray-800">{{ $branches->total() }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
            <i class="fas fa-circle-check text-green-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Active</p>
            <p class="text-xl font-bold text-gray-800">{{ $branches->getCollection()->where('status', 'active')->count() }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
            <i class="fas fa-circle-xmark text-red-500"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Inactive</p>
            <p class="text-xl font-bold text-gray-800">{{ $branches->getCollection()->where('status', 'inactive')->count() }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
            <i class="fas fa-receipt text-blue-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Total Orders</p>
            <p class="text-xl font-bold text-gray-800">{{ $branches->getCollection()->sum('orders_count') }}</p>
        </div>
    </div>
</div>

{{-- Branch Cards --}}
@if($branches->count())
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 mb-6">
    @foreach($branches as $branch)
    @php
        $colors = ['from-emerald-400 to-teal-600', 'from-blue-400 to-indigo-600', 'from-orange-400 to-red-500', 'from-purple-400 to-pink-600', 'from-yellow-400 to-orange-500', 'from-cyan-400 to-blue-500'];
        $grad = $colors[$branch->id % count($colors)];
        $initials = collect(explode(' ', $branch->name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
    @endphp
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow group">
        {{-- Card Top Banner --}}
        <div class="h-2 bg-gradient-to-r {{ $grad }}"></div>

        <div class="p-5">
            {{-- Header Row --}}
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br {{ $grad }} flex items-center justify-center text-white font-bold text-lg shadow-sm flex-shrink-0">
                        {{ $initials }}
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800 leading-tight">{{ $branch->name }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-1">
                            <i class="fas fa-location-dot text-gray-300"></i>
                            {{ $branch->address ?? 'No address' }}
                        </p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold flex-shrink-0
                    {{ $branch->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                    <i class="fas fa-circle text-[6px]"></i>
                    {{ ucfirst($branch->status) }}
                </span>
            </div>

            {{-- Info Grid --}}
            <div class="grid grid-cols-2 gap-2 mb-4">
                <div class="bg-gray-50 rounded-lg p-2.5">
                    <p class="text-xs text-gray-400 mb-0.5">Manager</p>
                    <p class="text-sm font-medium text-gray-700 truncate">
                        {{ $branch->manager_name ?: '—' }}
                    </p>
                </div>
                <div class="bg-gray-50 rounded-lg p-2.5">
                    <p class="text-xs text-gray-400 mb-0.5">Phone</p>
                    <p class="text-sm font-medium text-gray-700 truncate">
                        {{ $branch->phone ?: '—' }}
                    </p>
                </div>
            </div>

            {{-- Stats Row --}}
            <div class="grid grid-cols-3 gap-2 mb-4">
                <div class="text-center py-2 bg-blue-50 rounded-lg">
                    <p class="text-lg font-bold text-blue-700">{{ $branch->orders_count }}</p>
                    <p class="text-xs text-blue-500">Orders</p>
                </div>
                <div class="text-center py-2 bg-purple-50 rounded-lg">
                    <p class="text-lg font-bold text-purple-700">{{ $branch->invoices_count }}</p>
                    <p class="text-xs text-purple-500">Invoices</p>
                </div>
                <div class="text-center py-2 bg-teal-50 rounded-lg">
                    <p class="text-lg font-bold text-teal-700">{{ $branch->users_count }}</p>
                    <p class="text-xs text-teal-500">Staff</p>
                </div>
            </div>

            {{-- Email --}}
            @if($branch->email)
            <p class="text-xs text-gray-400 flex items-center gap-1.5 mb-4 truncate">
                <i class="fas fa-envelope text-gray-300"></i>
                {{ $branch->email }}
            </p>
            @endif

            {{-- Actions --}}
            <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
                <a href="{{ route('admin.branches.edit', $branch) }}"
                    class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg text-xs font-medium transition">
                    <i class="fas fa-pen"></i> Edit
                </a>
                <form action="{{ route('admin.branches.destroy', $branch) }}" method="POST"
                    onsubmit="return confirm('Delete branch \'{{ addslashes($branch->name) }}\'? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-red-50 text-red-500 hover:bg-red-100 rounded-lg text-xs font-medium transition">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
{{-- Empty state --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 py-20 text-center">
    <div class="w-20 h-20 rounded-full bg-emerald-50 flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-store text-emerald-300 text-3xl"></i>
    </div>
    <h3 class="text-gray-600 font-semibold mb-1">No branches yet</h3>
    <p class="text-sm text-gray-400 mb-5">Get started by adding your first restaurant location.</p>
    <a href="{{ route('admin.branches.create') }}"
        class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-lg text-sm font-semibold shadow-sm hover:from-emerald-600 hover:to-teal-700 transition">
        <i class="fas fa-plus mr-2"></i> Add First Branch
    </a>
</div>
@endif

{{-- Pagination --}}
@if($branches->hasPages())
<div class="mt-2">
    {{ $branches->links() }}
</div>
@endif

@endsection
