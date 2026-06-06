@extends('admin.layouts.app')

@section('title', 'Security Settings')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Security Settings</h1>
</div>
<div class="bg-white rounded-lg shadow-sm p-6 max-w-3xl">
    <form action="{{ route('admin.settings.security.update') }}" method="POST">
        @csrf
        <div class="space-y-6">
            <div>
                <h3 class="font-semibold text-gray-700 mb-3">Password Policy</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Password Length</label>
                        <input type="number" name="min_password_length" value="{{ old('min_password_length', $settings['min_password_length'] ?? 8) }}" min="6" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Require Special Characters</label>
                        <select name="require_special_chars" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="1" {{ ($settings['require_special_chars'] ?? 1) ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ !($settings['require_special_chars'] ?? 1) ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password Expiry (Days)</label>
                        <input type="number" name="password_expiry_days" value="{{ old('password_expiry_days', $settings['password_expiry_days'] ?? 0) }}" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-400 mt-1">0 = never expires</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Login Attempts</label>
                        <input type="number" name="max_login_attempts" value="{{ old('max_login_attempts', $settings['max_login_attempts'] ?? 5) }}" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
            <hr>
            <div>
                <h3 class="font-semibold text-gray-700 mb-3">Session & Authentication</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Session Timeout (Minutes)</label>
                        <input type="number" name="session_timeout" value="{{ old('session_timeout', $settings['session_timeout'] ?? 120) }}" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Two-Factor Authentication</label>
                        <select name="two_factor_enabled" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="1" {{ ($settings['two_factor_enabled'] ?? 0) ? 'selected' : '' }}>Enabled</option>
                            <option value="0" {{ !($settings['two_factor_enabled'] ?? 0) ? 'selected' : '' }}>Disabled</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lock Screen After (Minutes)</label>
                        <input type="number" name="lock_screen_timeout" value="{{ old('lock_screen_timeout', $settings['lock_screen_timeout'] ?? 5) }}" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
            <hr>
            <div>
                <h3 class="font-semibold text-gray-700 mb-3">Audit & Logging</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Retain Audit Logs (Days)</label>
                        <input type="number" name="audit_log_retention" value="{{ old('audit_log_retention', $settings['audit_log_retention'] ?? 90) }}" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Log All Activities</label>
                        <select name="log_all_activities" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="1" {{ ($settings['log_all_activities'] ?? 1) ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ !($settings['log_all_activities'] ?? 1) ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-6">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-save mr-2"></i>Save Security Settings
            </button>
        </div>
    </form>
</div>
@endsection