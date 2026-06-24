@extends('super_admin.layouts.app')

@section('title', 'Restaurants')
@section('header', 'Restaurants')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Restaurants</h1>
        <p class="text-sm text-gray-500 mt-1">Manage all restaurant locations.</p>
    </div>
    <a href="{{ route('super_admin.restaurants.create') }}"
        class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white rounded-lg text-sm font-semibold shadow-sm transition-all">
        <i class="fas fa-plus mr-2"></i> Add Restaurant
    </a>
</div>

@if(session('success'))
<div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4 flex items-center gap-3">
    <i class="fas fa-circle-check text-green-500"></i>
    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
</div>
@endif

<form method="GET" class="flex gap-3 mb-6">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search restaurants..."
        class="flex-1 px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
    <select name="status" class="px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-purple-500">
        <option value="">All Status</option>
        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
    </select>
    <button type="submit" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium transition">Filter</button>
</form>

@if($branches->count())
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 mb-6">
    @foreach($branches as $branch)
    @php
        $colors = ['from-purple-400 to-indigo-600', 'from-emerald-400 to-teal-600', 'from-blue-400 to-indigo-600', 'from-orange-400 to-red-500'];
        $grad = $colors[$branch->id % count($colors)];
        $initials = collect(explode(' ', $branch->name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
    @endphp
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
        <div class="h-2 bg-gradient-to-r {{ $grad }}"></div>
        <div class="p-5">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br {{ $grad }} flex items-center justify-center text-white font-bold text-lg shadow-sm">
                        {{ $initials }}
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">{{ $branch->name }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $branch->address ?: 'No address' }}</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold
                    {{ $branch->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                    <i class="fas fa-circle text-[6px]"></i> {{ ucfirst($branch->status) }}
                </span>
            </div>

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

            <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
                <a href="{{ route('super_admin.restaurants.edit', $branch) }}"
                    class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg text-xs font-medium transition">
                    <i class="fas fa-pen"></i> Edit
                </a>
                <form action="{{ route('super_admin.restaurants.toggle-status', $branch) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 {{ $branch->status === 'active' ? 'bg-yellow-50 text-yellow-600 hover:bg-yellow-100' : 'bg-green-50 text-green-600 hover:bg-green-100' }} rounded-lg text-xs font-medium transition">
                        <i class="fas {{ $branch->status === 'active' ? 'fa-ban' : 'fa-check' }}"></i>
                        {{ $branch->status === 'active' ? 'Deactivate' : 'Activate' }}
                    </button>
                </form>
                <form action="{{ route('super_admin.restaurants.destroy', $branch) }}" method="POST"
                    onsubmit="return confirm('Delete restaurant \'{{ addslashes($branch->name) }}\'? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-red-50 text-red-500 hover:bg-red-100 rounded-lg text-xs font-medium transition">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="bg-white rounded-xl shadow-sm border border-gray-100 py-20 text-center">
    <div class="w-20 h-20 rounded-full bg-purple-50 flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-store text-purple-300 text-3xl"></i>
    </div>
    <h3 class="text-gray-600 font-semibold mb-1">No restaurants yet</h3>
    <p class="text-sm text-gray-400 mb-5">Get started by adding your first restaurant.</p>
    <a href="{{ route('super_admin.restaurants.create') }}"
        class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-purple-500 to-indigo-600 text-white rounded-lg text-sm font-semibold shadow-sm">
        <i class="fas fa-plus mr-2"></i> Add First Restaurant
    </a>
</div>
@endif

@if($branches->hasPages())
<div class="mt-2">{{ $branches->links() }}</div>
@endif

@endsection
