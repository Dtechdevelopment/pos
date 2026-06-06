<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['branch', 'roles']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        $summary = [
            'total'     => User::count(),
            'active'    => User::where('status', 'active')->count(),
            'suspended' => User::where('status', 'suspended')->count(),
            'online'    => User::where('last_login_at', '>=', now()->subMinutes(15))->count(),
        ];

        $roles    = \Spatie\Permission\Models\Role::orderBy('name')->get();
        $branches = Branch::where('status', 'active')->get(['id', 'name']);

        return view('admin.users.index', compact('users', 'summary', 'roles', 'branches'));
    }

    public function create()
    {
        $branches = Branch::where('status', 'active')->get();
        $roles = \Spatie\Permission\Models\Role::all();
        return view('admin.users.create', compact('branches', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'branch_id' => 'nullable|exists:branches,id',
            'role' => 'required|exists:roles,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        $role = Role::findById($request->role);
        $user->assignRole($role->name);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $branches = Branch::where('status', 'active')->get();
        $roles = \Spatie\Permission\Models\Role::all();
        $userRole = $user->roles->first()?->id;
        return view('admin.users.edit', compact('user', 'branches', 'roles', 'userRole'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'branch_id' => 'nullable|exists:branches,id',
            'role' => 'required|exists:roles,id',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        $role = Role::findById($request->role);
        $user->syncRoles([$role->name]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function suspend(User $user)
    {
        $user->update(['status' => $user->status === 'active' ? 'suspended' : 'active']);
        return redirect()->route('admin.users.index')
            ->with('success', 'User status updated successfully.');
    }

    public function resetPassword(User $user)
    {
        $password = Str::random(12);
        $user->update(['password' => Hash::make($password)]);
        return redirect()->route('admin.users.index')
            ->with('success', "Password reset successfully. New password: $password");
    }
}
