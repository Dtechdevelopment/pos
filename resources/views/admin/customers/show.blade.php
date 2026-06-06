@extends('admin.layouts.app')

@section('title', 'Customer Detail')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">{{ $customer->name }}</h1>
    <a href="{{ route('admin.customers.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>Back
    </a>
</div>
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <div class="bg-white rounded-lg shadow-sm p-5">
        <div class="text-center">
            <div class="w-20 h-20 bg-gray-200 rounded-full mx-auto flex items-center justify-center">
                <i class="fas fa-user text-gray-500 text-3xl"></i>
            </div>
            <h3 class="mt-3 font-semibold text-lg">{{ $customer->name }}</h3>
            <p class="text-sm text-gray-500">{{ $customer->email }}</p>
            <p class="text-sm text-gray-500">{{ $customer->phone }}</p>
        </div>
        <hr class="my-4">
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between">
                <dt class="text-gray-500">Total Orders</dt>
                <dd class="font-medium">{{ $customer->orders_count ?? 0 }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500">Total Spent</dt>
                <dd class="font-medium text-green-600">${{ number_format($customer->total_spent ?? 0, 2) }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500">Last Visit</dt>
                <dd>{{ $customer->last_visit_at ? $customer->last_visit_at->format('Y-m-d') : 'N/A' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500">Member Since</dt>
                <dd>{{ $customer->created_at->format('Y-m-d') }}</dd>
            </div>
        </dl>
    </div>
    <div class="lg:col-span-3">
        <div class="bg-white rounded-lg shadow-sm p-5">
            <h3 class="font-semibold text-gray-700 mb-4">Order History</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 font-medium text-gray-500">Order #</th>
                            <th class="text-left py-3 font-medium text-gray-500">Date</th>
                            <th class="text-left py-3 font-medium text-gray-500">Items</th>
                            <th class="text-right py-3 font-medium text-gray-500">Total</th>
                            <th class="text-left py-3 font-medium text-gray-500">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customer->orders ?? [] as $order)
                        <tr class="border-b border-gray-100">
                            <td class="py-3"><a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:underline">{{ $order->order_number }}</a></td>
                            <td class="py-3">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                            <td class="py-3">{{ $order->items_count ?? count($order->items ?? []) }}</td>
                            <td class="py-3 text-right">${{ number_format($order->total, 2) }}</td>
                            <td class="py-3">
                                <span class="px-2 py-1 rounded-full text-xs 
                                    @if($order->status == 'pending') bg-yellow-100 text-yellow-700
                                    @elseif($order->status == 'preparing') bg-blue-100 text-blue-700
                                    @elseif($order->status == 'ready') bg-purple-100 text-purple-700
                                    @elseif($order->status == 'served') bg-green-100 text-green-700
                                    @else bg-gray-100 text-gray-700 @endif">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-400">No orders yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection