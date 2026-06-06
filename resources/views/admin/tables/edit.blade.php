@extends('admin.layouts.app')

@section('title', 'Edit Table')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Edit Table</h1>
    <a href="{{ route('admin.tables.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>Back
    </a>
</div>
<div class="bg-white rounded-lg shadow-sm p-6 max-w-2xl">
    <form action="{{ route('admin.tables.update', $table) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Table Number</label>
                <input type="text" name="table_number" value="{{ old('table_number', $table->table_number) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                <select name="branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($branches ?? [] as $branch)
                    <option value="{{ $branch->id }}" {{ $table->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                <input type="number" name="capacity" value="{{ old('capacity', $table->capacity) }}" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Section</label>
                <input type="text" name="section" value="{{ old('section', $table->section) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="available" {{ $table->status == 'available' ? 'selected' : '' }}>Available</option>
                    <option value="occupied" {{ $table->status == 'occupied' ? 'selected' : '' }}>Occupied</option>
                    <option value="reserved" {{ $table->status == 'reserved' ? 'selected' : '' }}>Reserved</option>
                    <option value="maintenance" {{ $table->status == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                </select>
            </div>
        </div>
        <div class="mt-6">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-save mr-2"></i>Update Table
            </button>
        </div>
    </form>
</div>
@endsection