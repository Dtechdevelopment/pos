@extends('super_admin.layouts.app')

@section('title', 'Edit ' . $manager->name)
@section('header', 'Edit Manager')

@section('content')

<div class="max-w-2xl">
    <form method="POST" action="{{ route('super_admin.managers.update', $manager) }}">
        @csrf @method('PUT')

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Manager Details</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                    <input type="text" name="name" value="{{ old('name', $manager->name) }}" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" name="email" value="{{ old('email', $manager->email) }}" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $manager->phone) }}"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Restaurant *</label>
                    <select name="branch_id" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('branch_id') border-red-500 @enderror">
                        <option value="">Select restaurant</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id', $manager->branch_id) == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('branch_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-sm text-yellow-700">
                    <i class="fas fa-info-circle mr-1"></i>
                    To reset the manager's password, use the "Reset Password" button on the managers list.
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 mt-6">
            <a href="{{ route('super_admin.managers.index') }}"
                class="px-5 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit"
                class="px-5 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-lg text-sm font-semibold shadow-sm transition-all">
                Update Manager
            </button>
        </div>
    </form>
</div>

@endsection
