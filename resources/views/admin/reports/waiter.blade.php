@extends('admin.layouts.app')

@section('title', 'Waiter Reports')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Waiter Reports</h1>
</div>
<div class="bg-white rounded-lg shadow-sm p-4 mb-6">
    <form method="GET" action="{{ route('admin.reports.waiter') }}" class="flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Date From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Date To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Waiter</label>
            <select name="waiter_id" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Waiters</option>
                @foreach($waiters ?? [] as $waiter)
                <option value="{{ $waiter->id }}" {{ request('waiter_id') == $waiter->id ? 'selected' : '' }}>{{ $waiter->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
            <i class="fas fa-search mr-1"></i>Generate Report
        </button>
    </form>
</div>
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Waiter</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Orders Taken</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Tables Served</th>
                    <th class="text-right py-3 px-4 font-medium text-gray-500">Revenue Generated</th>
                    <th class="text-right py-3 px-4 font-medium text-gray-500">Avg Order Value</th>
                    <th class="text-right py-3 px-4 font-medium text-gray-500">Tips</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Rating</th>
                </tr>
            </thead>
            <tbody>
                @forelse($waiterData ?? [] as $row)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="py-3 px-4">{{ $row->name ?? $row['name'] }}</td>
                    <td class="py-3 px-4">{{ $row->orders_count ?? $row['orders_count'] }}</td>
                    <td class="py-3 px-4">{{ $row->tables_served ?? $row['tables_served'] }}</td>
                    <td class="py-3 px-4 text-right">${{ number_format($row->revenue ?? $row['revenue'], 2) }}</td>
                    <td class="py-3 px-4 text-right">${{ number_format($row->avg_order ?? $row['avg_order'], 2) }}</td>
                    <td class="py-3 px-4 text-right">${{ number_format($row->tips ?? $row['tips'], 2) }}</td>
                    <td class="py-3 px-4">
                        <div class="flex items-center">
                            @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star text-xs {{ $i <= ($row->rating ?? $row['rating']) ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                            @endfor
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-8 text-center text-gray-400">No waiter data found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection