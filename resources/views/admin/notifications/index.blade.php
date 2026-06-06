@extends('admin.layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Notifications</h1>
    <div class="flex gap-2">
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
            <i class="fas fa-check-double mr-2"></i>Mark All as Read
        </button>
    </div>
</div>
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="divide-y divide-gray-100">
        @forelse($notifications ?? [] as $notification)
        <div class="flex items-start gap-4 p-4 hover:bg-gray-50 {{ $notification->read_at ? '' : 'bg-blue-50' }}">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 rounded-full flex items-center justify-center
                    @if($notification->type == 'order') bg-yellow-100
                    @elseif($notification->type == 'payment') bg-green-100
                    @elseif($notification->type == 'alert') bg-red-100
                    @else bg-gray-100 @endif">
                    <i class="fas 
                        @if($notification->type == 'order') fa-receipt text-yellow-600
                        @elseif($notification->type == 'payment') fa-credit-card text-green-600
                        @elseif($notification->type == 'alert') fa-exclamation-triangle text-red-600
                        @else fa-bell text-gray-600 @endif">
                    </i>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-900">{{ $notification->title }}</p>
                    <span class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-sm text-gray-600 mt-1">{{ $notification->message }}</p>
                @if(!$notification->read_at)
                <div class="mt-2">
                    <a href="#" class="text-xs text-blue-600 hover:text-blue-800">Mark as read</a>
                </div>
                @endif
            </div>
            <div class="flex-shrink-0">
                @if(!$notification->read_at)
                <span class="w-2 h-2 bg-blue-600 rounded-full inline-block"></span>
                @endif
            </div>
        </div>
        @empty
        <div class="py-12 text-center text-gray-400">
            <i class="fas fa-bell text-4xl mb-3"></i>
            <p>No notifications</p>
        </div>
        @endforelse
    </div>
</div>
@endsection