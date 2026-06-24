@extends('super_admin.layouts.app')

@section('title', 'Super Admin Dashboard')
@section('header', 'Dashboard')

@section('content')

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
                <i class="fas fa-store text-purple-600 text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Restaurants</p>
                <p class="text-2xl font-bold text-gray-800">{{ $totalRestaurants }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                <i class="fas fa-circle-check text-green-600 text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Active</p>
                <p class="text-2xl font-bold text-gray-800">{{ $activeRestaurants }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                <i class="fas fa-user-tie text-blue-600 text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Managers</p>
                <p class="text-2xl font-bold text-gray-800">{{ $totalManagers }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center">
                <i class="fas fa-users text-orange-600 text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Staff</p>
                <p class="text-2xl font-bold text-gray-800">{{ $totalStaff }}</p>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Restaurants</h3>
    @if($recentBranches->count())
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="pb-3 font-medium">Name</th>
                    <th class="pb-3 font-medium">Address</th>
                    <th class="pb-3 font-medium">Manager</th>
                    <th class="pb-3 font-medium">Status</th>
                    <th class="pb-3 font-medium text-right">Stats</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentBranches as $branch)
                <tr class="border-b last:border-0">
                    <td class="py-3 font-medium text-gray-800">{{ $branch->name }}</td>
                    <td class="py-3 text-gray-500">{{ $branch->address ?: '—' }}</td>
                    <td class="py-3 text-gray-500">{{ $branch->manager_name ?: '—' }}</td>
                    <td class="py-3">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold
                            {{ $branch->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                            {{ ucfirst($branch->status) }}
                        </span>
                    </td>
                    <td class="py-3 text-right text-gray-500">
                        {{ $branch->orders_count }} orders / {{ $branch->users_count }} staff
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <p class="text-gray-400 text-center py-8">No restaurants yet.</p>
    @endif
</div>

@endsection
