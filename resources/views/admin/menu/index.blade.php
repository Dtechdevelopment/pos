@extends('admin.layouts.app')

@section('title', 'Menu Items')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Menu Items</h1>
    <a href="{{ route('admin.menu.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
        <i class="fas fa-plus mr-2"></i>Add Menu Item
    </a>
</div>
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left py-3 px-4 font-medium text-gray-500">ID</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Image</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Name</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Category</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Price</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Branch</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Status</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($menuItems ?? [] as $item)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="py-3 px-4">{{ $item->id }}</td>
                    <td class="py-3 px-4">
                        @if($item->image)
                        <img src="{{ asset('storage/' . $item->image) }}" alt="" class="w-10 h-10 rounded object-cover">
                        @else
                        <div class="w-10 h-10 bg-gray-200 rounded flex items-center justify-center">
                            <i class="fas fa-utensils text-gray-400"></i>
                        </div>
                        @endif
                    </td>
                    <td class="py-3 px-4">{{ $item->name }}</td>
                    <td class="py-3 px-4">{{ $item->category->name ?? 'N/A' }}</td>
                    <td class="py-3 px-4">${{ number_format($item->price, 2) }}</td>
                    <td class="py-3 px-4">{{ $item->branch->name ?? 'All' }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 rounded-full text-xs {{ $item->is_available ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $item->is_available ? 'Available' : 'Unavailable' }}
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.menu.edit', $item) }}" class="text-blue-600 hover:text-blue-800"><i class="fas fa-edit"></i></a>
                            <a href="#" class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-8 text-center text-gray-400">No menu items found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection