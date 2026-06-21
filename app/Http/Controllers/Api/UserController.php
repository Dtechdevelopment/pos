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

        if (!$request->user()->hasRole('super_admin')) {
            $query->where('branch_id', $request->user()->branch_id);
        }

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
        $allowedRoles = ['manager', 'waiter', 'kitchen', 'cashier'];
        if ($request->user()->hasRole('super_admin')) {
            $allowedRoles[] = 'admin';
            $allowedRoles[] = 'super_admin';
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
            'branch_id' => 'nullable|exists:branches,id',
            'role' => 'required|string|in:' . implode(',', $allowedRoles),
            'status' => 'nullable|string|in:active,inactive',
        ]);

        DB::beginTransaction();

        try {
            $branchId = $validated['branch_id'] ?? $request->user()->branch_id;

            if (!$request->user()->hasRole('super_admin')) {
                $branchId = $request->user()->branch_id;
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'phone' => $validated['phone'] ?? null,
                'branch_id' => $branchId,
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

    public function show(Request $request, User $user): JsonResponse
    {
        if (!$request->user()->hasRole('super_admin') && $user->branch_id !== $request->user()->branch_id) {
            return $this->error('Unauthorized', 403);
        }
        $user->load('branch', 'roles');
        return $this->success($user);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        if (!$request->user()->hasRole('super_admin') && $user->branch_id !== $request->user()->branch_id) {
            return $this->error('Unauthorized', 403);
        }

        $allowedRoles = ['manager', 'waiter', 'kitchen', 'cashier'];
        if ($request->user()->hasRole('super_admin')) {
            $allowedRoles[] = 'admin';
            $allowedRoles[] = 'super_admin';
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'phone' => 'nullable|string|max:20',
            'branch_id' => 'nullable|exists:branches,id',
            'role' => 'required|string|in:' . implode(',', $allowedRoles),
            'status' => 'nullable|string|in:active,inactive',
        ]);

        DB::beginTransaction();

        try {
            $data = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'branch_id' => $validated['branch_id'] ?? $user->branch_id,
                'status' => $validated['status'] ?? $user->status,
            ];

            if (!$request->user()->hasRole('super_admin')) {
                $data['branch_id'] = $request->user()->branch_id;
            }

            if (!empty($validated['password'])) {
                $data['password'] = $validated['password'];
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

        if (!$request->user()->hasRole('super_admin') && $user->branch_id !== $request->user()->branch_id) {
            return $this->error('Unauthorized', 403);
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
