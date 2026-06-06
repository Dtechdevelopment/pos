@extends('admin.layouts.app')

@section('title', 'Order Detail')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Order #{{ $order->order_number }}</h1>
    <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>Back to Orders
    </a>
</div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-lg shadow-sm p-5">
            <h3 class="font-semibold text-gray-700 mb-4">Order Items</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 font-medium text-gray-500">Item</th>
                            <th class="text-center py-3 font-medium text-gray-500">Qty</th>
                            <th class="text-right py-3 font-medium text-gray-500">Price</th>
                            <th class="text-right py-3 font-medium text-gray-500">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($order->items ?? [] as $item)
                        <tr class="border-b border-gray-100">
                            <td class="py-3">{{ $item->menu_item->name ?? $item->name }}</td>
                            <td class="py-3 text-center">{{ $item->quantity }}</td>
                            <td class="py-3 text-right">${{ number_format($item->price ?? 0, 2) }}</td>
                            <td class="py-3 text-right">${{ number_format(($item->price ?? 0) * $item->quantity, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-gray-400">No items</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="font-semibold">
                            <td colspan="3" class="py-3 text-right">Total</td>
                            <td class="py-3 text-right">${{ number_format($order->total ?? 0, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow-sm p-5">
            <h3 class="font-semibold text-gray-700 mb-4">Order Details</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Order #</dt>
                    <dd class="font-medium">{{ $order->order_number }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Table</dt>
                    <dd>{{ $order->table->table_number ?? 'N/A' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Waiter</dt>
                    <dd>{{ $order->waiter->name ?? 'N/A' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Status</dt>
                    <dd>
                        <span class="px-2 py-1 rounded-full text-xs 
                            @if($order->status == 'pending') bg-yellow-100 text-yellow-700
                            @elseif($order->status == 'preparing') bg-blue-100 text-blue-700
                            @elseif($order->status == 'ready') bg-purple-100 text-purple-700
                            @elseif($order->status == 'served') bg-green-100 text-green-700
                            @elseif($order->status == 'paid') bg-gray-100 text-gray-700
                            @else bg-red-100 text-red-700 @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Created</dt>
                    <dd>{{ $order->created_at->format('Y-m-d H:i') }}</dd>
                </div>
            </dl>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-5">
            <h3 class="font-semibold text-gray-700 mb-4">Actions</h3>
            <div class="space-y-2">
                <button class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                    <i class="fas fa-print mr-2"></i>Print Receipt
                </button>
                <button class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                    <i class="fas fa-check mr-2"></i>Mark as Served
                </button>
            </div>
        </div>
    </div>
</div>
@endsection