<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends ApiController
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status !== 'active') {
            return $this->error('Your account has been suspended.', 403);
        }

        if ($user->hasRole('super_admin')) {
            return $this->error('Super Admin accounts cannot log in to the mobile app. Please use the web dashboard at nespos.cloud/admin.', 403);
        }

        $hasPin = !empty($user->pin);

        $token = $user->createToken('mobile-app')->plainTextToken;

        $user->last_login_at = now();
        $user->last_login_ip = $request->ip();
        $user->save();

        $user->load('branch');
        $roles = $user->getRoleNames();

        return $this->success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'status' => $user->status,
                'branch_id' => $user->branch_id,
                'branch' => $user->branch ? [
                    'id' => $user->branch->id,
                    'name' => $user->branch->name,
                    'order_method' => $user->branch->order_method ?? 'digital',
                    'phone' => $user->branch->phone,
                    'address' => $user->branch->address,
                ] : null,
                'roles' => $roles,
                'has_pin' => $hasPin,
            ],
            'token' => $token,
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully');
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('branch');
        $roles = $user->getRoleNames();
        $permissions = $user->getAllPermissions()->pluck('name');

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->status,
            'branch_id' => $user->branch_id,
            'branch' => $user->branch ? [
                'id' => $user->branch->id,
                'name' => $user->branch->name,
                'order_method' => $user->branch->order_method ?? 'digital',
                'phone' => $user->branch->phone,
                'address' => $user->branch->address,
            ] : null,
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $request->user()->id,
        ]);

        $user = $request->user();
        if (isset($validated['name'])) $user->name = $validated['name'];
        if (isset($validated['phone'])) $user->phone = $validated['phone'];
        if (isset($validated['email'])) $user->email = $validated['email'];
        $user->save();

        return $this->success(null, 'Profile updated successfully');
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $request->user()->password)) {
            return $this->error('Current password is incorrect.', 422);
        }

        $user = $request->user();
        $user->password = $validated['password'];
        $user->save();

        return $this->success(null, 'Password changed successfully');
    }
}
