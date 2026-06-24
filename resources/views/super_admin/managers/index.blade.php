@extends('super_admin.layouts.app')

@section('title', 'Managers')
@section('header', 'Manager Management')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Managers</h1>
        <p class="text-sm text-gray-500 mt-1">Manage restaurant managers and their PINs.</p>
    </div>
    <a href="{{ route('super_admin.managers.create') }}"
        class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-lg text-sm font-semibold shadow-sm transition-all">
        <i class="fas fa-plus mr-2"></i> Add Manager
    </a>
</div>

@if(session('success'))
<div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4 flex items-center gap-3">
    <i class="fas fa-circle-check text-green-500"></i>
    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
</div>
@endif

<form method="GET" class="mb-6">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search managers..."
        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
</form>

@if($managers->count())
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-5 py-3 font-medium text-gray-500">Name</th>
                    <th class="text-left px-5 py-3 font-medium text-gray-500">Email</th>
                    <th class="text-left px-5 py-3 font-medium text-gray-500">Restaurant</th>
                    <th class="text-left px-5 py-3 font-medium text-gray-500">PIN</th>
                    <th class="text-left px-5 py-3 font-medium text-gray-500">Status</th>
                    <th class="text-right px-5 py-3 font-medium text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($managers as $manager)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $manager->name }}</td>
                    <td class="px-5 py-3 text-gray-500">{{ $manager->email }}</td>
                    <td class="px-5 py-3 text-gray-500">{{ $manager->branch?->name ?? '—' }}</td>
                    <td class="px-5 py-3">
                        @if($manager->pin)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                <i class="fas fa-check-circle"></i> Set
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-600">
                                <i class="fas fa-times-circle"></i> Not Set
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold
                            {{ $manager->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                            {{ ucfirst($manager->status) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('super_admin.managers.edit', $manager) }}"
                                class="px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg text-xs font-medium transition">
                                Edit
                            </a>
                            <form action="{{ route('super_admin.managers.reset-password', $manager) }}" method="POST"
                                onsubmit="return confirm('Reset password for {{ addslashes($manager->name) }}? New password will be: password')">
                                @csrf
                                <button type="submit"
                                    class="px-3 py-1.5 bg-yellow-50 text-yellow-600 hover:bg-yellow-100 rounded-lg text-xs font-medium transition">
                                    Reset Password
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if($managers->hasPages())
<div class="mt-2">{{ $managers->links() }}</div>
@endif

@else
<div class="bg-white rounded-xl shadow-sm border border-gray-100 py-20 text-center">
    <div class="w-20 h-20 rounded-full bg-blue-50 flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-user-tie text-blue-300 text-3xl"></i>
    </div>
    <h3 class="text-gray-600 font-semibold mb-1">No managers yet</h3>
    <p class="text-sm text-gray-400 mb-5">Add a manager to get started.</p>
    <a href="{{ route('super_admin.managers.create') }}"
        class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg text-sm font-semibold shadow-sm">
        <i class="fas fa-plus mr-2"></i> Add First Manager
    </a>
</div>
@endif

@endsection
