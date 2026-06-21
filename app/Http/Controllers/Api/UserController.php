<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = User::with('branch', 'roles');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('name')->paginate($request->get('per_page', 25));

        return $this->success($users);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
            'branch_id' => 'nullable|exists:branches,id',
            'role' => 'required|string|in:super_admin,admin,manager,waiter,kitchen,cashier',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'] ?? null,
                'branch_id' => $validated['branch_id'] ?? null,
                'status' => $validated['status'] ?? 'active',
            ]);

            $user->assignRole($validated['role']);

            $user->load('branch', 'roles');

            DB::commit();

            return $this->success($user, 'User created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to create user: ' . $e->getMessage(), 500);
        }
    }

    public function show(User $user): JsonResponse
    {
        $user->load('branch', 'roles');
        return $this->success($user);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'phone' => 'nullable|string|max:20',
            'branch_id' => 'nullable|exists:branches,id',
            'role' => 'required|string|in:super_admin,admin,manager,waiter,kitchen,cashier',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        DB::beginTransaction();

        try {
            $data = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'branch_id' => $validated['branch_id'] ?? null,
                'status' => $validated['status'] ?? $user->status,
            ];

            if (!empty($validated['password'])) {
                $data['password'] = Hash::make($validated['password']);
            }

            $user->update($data);
            $user->syncRoles([$validated['role']]);

            $user->load('branch', 'roles');

            DB::commit();

            return $this->success($user, 'User updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to update user: ' . $e->getMessage(), 500);
        }
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()->id) {
            return $this->error('You cannot delete your own account.', 422);
        }

        $user->delete();

        return $this->success(null, 'User deleted successfully');
    }

    public function roles(): JsonResponse
    {
        $roles = ['super_admin', 'admin', 'manager', 'waiter', 'kitchen', 'cashier'];
        return $this->success($roles);
    }
}
