<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ManagerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::whereHas('roles', fn($q) => $q->where('name', 'manager'))->with('branch');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $managers = $query->latest()->paginate(12);

        return view('super_admin.managers.index', compact('managers'));
    }

    public function create()
    {
        $branches = Branch::where('status', 'active')->orderBy('name')->get();
        return view('super_admin.managers.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'branch_id' => 'required|exists:branches,id',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'branch_id' => $validated['branch_id'],
            'password' => $validated['password'],
            'status' => 'active',
        ]);

        $user->assignRole('manager');

        return redirect()->route('super_admin.managers.index')
            ->with('success', 'Manager created successfully.');
    }

    public function edit(User $manager)
    {
        $branches = Branch::where('status', 'active')->orderBy('name')->get();
        return view('super_admin.managers.edit', compact('manager', 'branches'));
    }

    public function update(Request $request, User $manager)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $manager->id,
            'phone' => 'nullable|string|max:20',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $manager->update($validated);

        return redirect()->route('super_admin.managers.index')
            ->with('success', 'Manager updated successfully.');
    }

    public function resetPassword(User $manager)
    {
        $manager->update(['password' => 'password']);

        return back()->with('success', "Password reset for {$manager->name}. New password: password");
    }

    public function destroy(User $manager)
    {
        $manager->delete();
        return redirect()->route('super_admin.managers.index')
            ->with('success', 'Manager deleted successfully.');
    }
}
