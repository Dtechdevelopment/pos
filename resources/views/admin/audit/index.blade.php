@extends('admin.layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Audit Logs</h1>
</div>
<div class="bg-white rounded-lg shadow-sm p-4 mb-6">
    <form method="GET" action="{{ route('admin.audit.index') }}" class="flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Event Type</label>
            <select name="event" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All</option>
                <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>Created</option>
                <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>Updated</option>
                <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                <option value="login" {{ request('event') == 'login' ? 'selected' : '' }}>Login</option>
                <option value="logout" {{ request('event') == 'logout' ? 'selected' : '' }}>Logout</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">User</label>
            <select name="user_id" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Users</option>
                @foreach($users ?? [] as $user)
                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Date From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Date To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
            <i class="fas fa-search mr-1"></i>Search
        </button>
        <a href="{{ route('admin.audit.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 text-sm">
            <i class="fas fa-undo mr-1"></i>Reset
        </a>
    </form>
</div>
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Time</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">User</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Event</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Auditable Type</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Auditable ID</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">IP Address</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs ?? [] as $log)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="py-3 px-4">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                    <td class="py-3 px-4">{{ $log->user->name ?? 'System' }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 rounded-full text-xs 
                            @if($log->event == 'created') bg-green-100 text-green-700
                            @elseif($log->event == 'updated') bg-blue-100 text-blue-700
                            @elseif($log->event == 'deleted') bg-red-100 text-red-700
                            @else bg-gray-100 text-gray-700 @endif">
                            {{ ucfirst($log->event) }}
                        </span>
                    </td>
                    <td class="py-3 px-4">{{ class_basename($log->auditable_type) }}</td>
                    <td class="py-3 px-4">{{ $log->auditable_id }}</td>
                    <td class="py-3 px-4">{{ $log->ip_address }}</td>
                    <td class="py-3 px-4">
                        <button onclick="alert('{{ addslashes(json_encode($log->old_values)) }}\n\n{{ addslashes(json_encode($log->new_values)) }}')" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-8 text-center text-gray-400">No audit logs found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection