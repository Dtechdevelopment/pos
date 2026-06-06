@extends('admin.layouts.app')

@section('title', 'Kitchen Reports')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Kitchen Reports</h1>
</div>
<div class="bg-white rounded-lg shadow-sm p-4 mb-6">
    <form method="GET" action="{{ route('admin.reports.kitchen') }}" class="flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Date From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Date To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
            <i class="fas fa-search mr-1"></i>Generate Report
        </button>
    </form>
</div>
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-blue-500">
        <p class="text-sm text-gray-500">Total Items Prepared</p>
        <p class="text-2xl font-bold text-gray-800">{{ $totalPrepared ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-green-500">
        <p class="text-sm text-gray-500">Avg Prep Time</p>
        <p class="text-2xl font-bold text-gray-800">{{ $avgPrepTime ?? 0 }} min</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-yellow-500">
        <p class="text-sm text-gray-500">Pending Items</p>
        <p class="text-2xl font-bold text-gray-800">{{ $pendingItems ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-red-500">
        <p class="text-sm text-gray-500">Cancelled Items</p>
        <p class="text-2xl font-bold text-gray-800">{{ $cancelledItems ?? 0 }}</p>
    </div>
</div>
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="p-4 border-b border-gray-200">
        <h3 class="font-semibold text-gray-700">Kitchen Performance</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Date</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Items Prepared</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Avg Prep Time</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Items Returned</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Efficiency</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kitchenData ?? [] as $row)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="py-3 px-4">{{ $row->date ?? $row['date'] }}</td>
                    <td class="py-3 px-4">{{ $row->prepared ?? $row['prepared'] }}</td>
                    <td class="py-3 px-4">{{ $row->avg_time ?? $row['avg_time'] }} min</td>
                    <td class="py-3 px-4">{{ $row->returned ?? $row['returned'] }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 rounded-full text-xs 
                            @if(($row->efficiency ?? $row['efficiency']) >= 90) bg-green-100 text-green-700
                            @elseif(($row->efficiency ?? $row['efficiency']) >= 70) bg-yellow-100 text-yellow-700
                            @else bg-red-100 text-red-700 @endif">
                            {{ $row->efficiency ?? $row['efficiency'] }}%
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-8 text-center text-gray-400">No kitchen data found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection