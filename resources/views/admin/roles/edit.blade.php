@extends('admin.layouts.app')

@section('title', 'Edit Role')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Edit Role</h1>
    <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>Back
    </a>
</div>
<div class="bg-white rounded-lg shadow-sm p-6 max-w-2xl">
    <form action="{{ route('admin.roles.update', $role) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Role Name</label>
            <input type="text" name="name" value="{{ old('name', $role->name) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Permissions</label>
            <div class="max-h-96 overflow-y-auto p-3 border border-gray-200 rounded-lg space-y-3">
                @foreach($permissions ?? [] as $group => $groupPermissions)
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 capitalize mb-1 border-b pb-1">{{ $group }}</h4>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach($groupPermissions as $permission)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" {{ in_array($permission->id, $rolePermissions ?? []) ? 'checked' : '' }} class="rounded border-gray-300">
                            {{ $permission->name }}
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-save mr-2"></i>Update Role
        </button>
    </form>
</div>
@endsection