<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        foreach ($request->except('_token') as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => 'general']
            );
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }

    public function security()
    {
        $settings = Setting::whereIn('key', [
            'password_min_length', 'session_lifetime', 'max_login_attempts',
            'lockout_duration', 'two_factor_enabled',
        ])->get()->pluck('value', 'key');

        return view('admin.settings.security', compact('settings'));
    }

    public function updateSecurity(Request $request)
    {
        $validated = $request->validate([
            'password_min_length' => 'nullable|integer|min:6|max:128',
            'session_lifetime' => 'nullable|integer|min:5|max:1440',
            'max_login_attempts' => 'nullable|integer|min:1|max:10',
            'lockout_duration' => 'nullable|integer|min:1|max:60',
            'two_factor_enabled' => 'nullable|boolean',
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => 'security']
            );
        }

        return redirect()->route('admin.settings.security')
            ->with('success', 'Security settings updated successfully.');
    }
}
