<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount(['orders', 'invoices', 'users'])->paginate(15);
        return view('admin.branches.index', compact('branches'));
    }

    public function create()
    {
        $managers = User::whereHas('roles', fn($q) => $q->where('name', 'manager'))->where('status', 'active')->orderBy('name')->get(['id', 'name', 'email']);
        return view('admin.branches.create', compact('managers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        Branch::create($validated);

        return redirect()->route('admin.branches.index')
            ->with('success', 'Branch created successfully.');
    }

    public function edit(Branch $branch)
    {
        return view('admin.branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $branch->update($validated);

        return redirect()->route('admin.branches.index')
            ->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();
        return redirect()->route('admin.branches.index')
            ->with('success', 'Branch deleted successfully.');
    }
}
