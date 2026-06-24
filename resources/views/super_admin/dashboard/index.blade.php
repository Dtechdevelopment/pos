@extends('super_admin.layouts.app')

@section('title', 'Super Admin Dashboard')
@section('header', 'Dashboard')

@section('content')

{{-- Stats Grid --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5 mb-6 sm:mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-5 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3 sm:gap-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-store text-purple-600 text-lg sm:text-xl"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-gray-500 truncate">Restaurants</p>
                <p class="text-xl sm:text-2xl font-bold text-gray-800">{{ $totalRestaurants }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-5 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3 sm:gap-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-green-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-circle-check text-green-600 text-lg sm:text-xl"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-gray-500 truncate">Active</p>
                <p class="text-xl sm:text-2xl font-bold text-gray-800">{{ $activeRestaurants }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-5 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3 sm:gap-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user-tie text-blue-600 text-lg sm:text-xl"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-gray-500 truncate">Managers</p>
                <p class="text-xl sm:text-2xl font-bold text-gray-800">{{ $totalManagers }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-5 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3 sm:gap-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-orange-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-users text-orange-600 text-lg sm:text-xl"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-gray-500 truncate">Staff</p>
                <p class="text-xl sm:text-2xl font-bold text-gray-800">{{ $totalStaff }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Quick Actions --}}
<div class="grid grid-cols-2 gap-3 sm:gap-4 mb-6 sm:mb-8">
    <a href="{{ route('super_admin.restaurants.index') }}"
        class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-5 hover:shadow-md hover:border-purple-200 transition-all group">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center flex-shrink-0 shadow-sm group-hover:scale-105 transition-transform">
                <i class="fas fa-store text-white"></i>
            </div>
            <div class="min-w-0">
                <p class="font-semibold text-gray-800 text-sm">Restaurants</p>
                <p class="text-xs text-gray-400">Manage locations</p>
            </div>
        </div>
    </a>
    <a href="{{ route('super_admin.managers.index') }}"
        class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-5 hover:shadow-md hover:border-blue-200 transition-all group">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center flex-shrink-0 shadow-sm group-hover:scale-105 transition-transform">
                <i class="fas fa-user-tie text-white"></i>
            </div>
            <div class="min-w-0">
                <p class="font-semibold text-gray-800 text-sm">Managers</p>
                <p class="text-xs text-gray-400">Manage accounts</p>
            </div>
        </div>
    </a>
</div>

{{-- Recent Restaurants --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="flex items-center justify-between px-4 sm:px-6 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Recent Restaurants</h3>
        <a href="{{ route('super_admin.restaurants.index') }}" class="text-xs sm:text-sm text-purple-600 hover:text-purple-700 font-medium">View All</a>
    </div>

    @if($recentBranches->count())
    {{-- Desktop table --}}
    <div class="hidden sm:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b bg-gray-50">
                    <th class="px-6 py-3 font-medium">Name</th>
                    <th class="px-6 py-3 font-medium">Address</th>
                    <th class="px-6 py-3 font-medium">Manager</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium text-right">Stats</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentBranches as $branch)
                <tr class="border-b last:border-0 hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-3 font-medium text-gray-800">{{ $branch->name }}</td>
                    <td class="px-6 py-3 text-gray-500">{{ $branch->address ?: '—' }}</td>
                    <td class="px-6 py-3 text-gray-500">{{ $branch->manager_name ?: '—' }}</td>
                    <td class="px-6 py-3">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold
                            {{ $branch->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                            {{ ucfirst($branch->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-right text-gray-500">
                        {{ $branch->orders_count }} orders / {{ $branch->users_count }} staff
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="sm:hidden divide-y">
        @foreach($recentBranches as $branch)
        @php
            $colors = ['from-purple-400 to-indigo-600', 'from-emerald-400 to-teal-600', 'from-blue-400 to-indigo-600', 'from-orange-400 to-red-500'];
            $grad = $colors[$branch->id % count($colors)];
            $initials = collect(explode(' ', $branch->name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
        @endphp
        <div class="px-4 py-3 hover:bg-gray-50 transition-colors">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br {{ $grad }} flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    {{ $initials }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 text-sm truncate">{{ $branch->name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ $branch->address ?: 'No address' }}</p>
                </div>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold flex-shrink-0
                    {{ $branch->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                    {{ ucfirst($branch->status) }}
                </span>
            </div>
            <div class="flex items-center gap-4 text-xs text-gray-500 ml-12">
                <span><i class="fas fa-receipt mr-1"></i>{{ $branch->orders_count }}</span>
                <span><i class="fas fa-users mr-1"></i>{{ $branch->users_count }} staff</span>
                @if($branch->manager_name)
                <span class="truncate"><i class="fas fa-user-tie mr-1"></i>{{ $branch->manager_name }}</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    @else
    <div class="py-12 sm:py-16 text-center px-4">
        <div class="w-16 h-16 rounded-full bg-purple-50 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-store text-purple-300 text-2xl"></i>
        </div>
        <h3 class="text-gray-600 font-semibold mb-1">No restaurants yet</h3>
        <p class="text-sm text-gray-400 mb-5">Get started by adding your first restaurant.</p>
        <a href="{{ route('super_admin.restaurants.create') }}"
            class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-purple-500 to-indigo-600 text-white rounded-lg text-sm font-semibold shadow-sm">
            <i class="fas fa-plus mr-2"></i> Add Restaurant
        </a>
    </div>
    @endif
</div>

@endsection
