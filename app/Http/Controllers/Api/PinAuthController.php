<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class PinAuthController extends ApiController
{
    public function pinLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pin' => 'required|string|size:4',
            'branch_id' => 'nullable|integer|exists:branches,id',
        ]);

        $pin = $validated['pin'];
        $branchId = $validated['branch_id'] ?? null;
        $ip = $request->ip();
        $lockoutKey = 'pin_lockout_' . md5($ip);

        if (Cache::has($lockoutKey)) {
            $remaining = Cache::get($lockoutKey);
            return $this->error("Too many failed attempts. Try again in {$remaining} minutes.", 429);
        }

        $query = User::where('pin', $pin)
            ->where('status', 'active')
            ->whereNotNull('pin')
            ->with('branch');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->incrementPinAttempts($ip);
            return $this->error('Invalid PIN.', 401);
        }

        $attemptKey = 'pin_attempts_' . md5($ip);
        Cache::forget($attemptKey);

        if ($users->count() === 1) {
            $user = $users->first();
        } else {
            return $this->success([
                'ambiguous' => true,
                'candidates' => $users->map(fn($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'role' => $u->getRoleNames()->first() ?? 'staff',
                    'branch_id' => $u->branch_id,
                    'branch_name' => $u->branch?->name ?? 'Unknown',
                ])->values(),
            ], 'Multiple accounts found. Select your account.');
        }

        $token = $user->createToken('pos-pin-login')->plainTextToken;
        $roles = $user->getRoleNames();

        $user->last_login_at = now();
        $user->last_login_ip = $ip;
        $user->save();

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
            ],
            'token' => $token,
        ], 'Login successful');
    }

    public function pinLoginAmbiguous(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pin' => 'required|string|size:4',
            'user_id' => 'required|exists:users,id',
            'branch_id' => 'nullable|integer|exists:branches,id',
        ]);

        $ip = $request->ip();
        $lockoutKey = 'pin_lockout_' . md5($ip);

        if (Cache::has($lockoutKey)) {
            $remaining = Cache::get($lockoutKey);
            return $this->error("Too many failed attempts. Try again in {$remaining} minutes.", 429);
        }

        $query = User::where('id', $validated['user_id'])
            ->where('pin', $validated['pin'])
            ->where('status', 'active')
            ->with('branch');

        if ($validated['branch_id'] ?? null) {
            $query->where('branch_id', $validated['branch_id']);
        }

        $user = $query->first();

        if (!$user) {
            $this->incrementPinAttempts($ip);
            return $this->error('Invalid selection.', 401);
        }

        $attemptKey = 'pin_attempts_' . md5($ip);
        Cache::forget($attemptKey);

        $token = $user->createToken('pos-pin-login')->plainTextToken;
        $roles = $user->getRoleNames();

        $user->last_login_at = now();
        $user->last_login_ip = $ip;
        $user->save();

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
            ],
            'token' => $token,
        ], 'Login successful');
    }

    public function setPin(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'pin' => 'required|string|size:4|digits:4',
        ]);

        $existing = User::where('pin', $validated['pin'])
            ->where('branch_id', $user->branch_id)
            ->where('id', '!=', $user->id)
            ->first();

        if ($existing) {
            return $this->error('This PIN is already used by another staff member in your restaurant. Choose a different PIN.', 422);
        }

        $user->pin = $validated['pin'];
        $user->pin_set_at = now();
        $user->save();

        return $this->success(null, 'PIN set successfully');
    }

    public function setPinForUser(Request $request, User $user): JsonResponse
    {
        $auth = $request->user();

        if (!$auth->hasRole('super_admin') && $user->branch_id !== $auth->branch_id) {
            return $this->error('Unauthorized', 403);
        }

        if (!$auth->hasAnyRole(['super_admin', 'admin', 'manager'])) {
            return $this->error('Only managers can set staff PINs.', 403);
        }

        $validated = $request->validate([
            'pin' => 'required|string|size:4|digits:4',
        ]);

        $existing = User::where('pin', $validated['pin'])
            ->where('branch_id', $user->branch_id)
            ->where('id', '!=', $user->id)
            ->first();

        if ($existing) {
            return $this->error('This PIN is already used by another staff member in this restaurant. Choose a different PIN.', 422);
        }

        $user->pin = $validated['pin'];
        $user->pin_set_at = now();
        $user->save();

        return $this->success(null, "PIN set for {$user->name}");
    }

    public function clearPin(Request $request, User $user): JsonResponse
    {
        $auth = $request->user();

        if (!$auth->hasRole('super_admin') && $user->branch_id !== $auth->branch_id) {
            return $this->error('Unauthorized', 403);
        }

        $user->pin = null;
        $user->pin_set_at = null;
        $user->save();

        return $this->success(null, "PIN cleared for {$user->name}");
    }

    public function verifyPin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pin' => 'required|string|size:4',
            'branch_id' => 'required|integer|exists:branches,id',
        ]);

        $user = User::where('pin', $validated['pin'])
            ->where('branch_id', $validated['branch_id'])
            ->where('status', 'active')
            ->whereNotNull('pin')
            ->whereHas('roles', fn($q) => $q->where('name', 'waiter'))
            ->first();

        if (!$user) {
            return $this->error('Invalid waiter PIN.', 401);
        }

        return $this->success([
            'user_id' => $user->id,
            'name' => $user->name,
        ], 'PIN verified');
    }

    private function incrementPinAttempts(string $ip): void
    {
        $attemptKey = 'pin_attempts_' . md5($ip);
        $attempts = Cache::get($attemptKey, 0);
        $attempts++;

        if ($attempts >= 5) {
            Cache::put('pin_lockout_' . md5($ip), 5, now()->addMinutes(5));
            Cache::forget($attemptKey);
        } else {
            Cache::put($attemptKey, $attempts, now()->addMinutes(15));
        }
    }
}