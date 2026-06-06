@extends('admin.layouts.app')

@section('title', 'Low Stock Alerts')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Low Stock Alerts</h1>
    <a href="{{ route('admin.inventory.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>Back to Inventory
    </a>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-red-50 rounded-lg shadow-sm p-5 border-l-4 border-red-500">
        <p class="text-sm text-red-600 font-medium">Out of Stock</p>
        <p class="text-2xl font-bold text-red-700">{{ $outOfStock ?? 0 }}</p>
    </div>
    <div class="bg-yellow-50 rounded-lg shadow-sm p-5 border-l-4 border-yellow-500">
        <p class="text-sm text-yellow-600 font-medium">Low Stock</p>
        <p class="text-2xl font-bold text-yellow-700">{{ $lowStock ?? 0 }}</p>
    </div>
    <div class="bg-green-50 rounded-lg shadow-sm p-5 border-l-4 border-green-500">
        <p class="text-sm text-green-600 font-medium">In Stock</p>
        <p class="text-2xl font-bold text-green-700">{{ $inStock ?? 0 }}</p>
    </div>
</div>
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Item</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">SKU</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Current Stock</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Min Stock</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Status</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alerts ?? [] as $item)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="py-3 px-4">{{ $item->name }}</td>
                    <td class="py-3 px-4">{{ $item->sku }}</td>
                    <td class="py-3 px-4">{{ $item->quantity }}</td>
                    <td class="py-3 px-4">{{ $item->min_stock }}</td>
                    <td class="py-3 px-4">
                        @if($item->quantity <= 0)
                        <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-700">Out of Stock</span>
                        @else
                        <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-700">Low Stock</span>
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        <a href="{{ route('admin.inventory.create') }}" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-plus"></i> Reorder
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-gray-400">No stock alerts</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection