<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::withCount(['orders', 'invoices'])
            ->withSum(['invoices as total_spent' => fn($q) => $q->where('status', 'paid')], 'total');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $customers = $query->latest()->paginate(15)->withQueryString();

        $summary = [
            'total'    => Customer::count(),
            'withEmail'=> Customer::whereNotNull('email')->count(),
            'withPhone'=> Customer::whereNotNull('phone')->count(),
            'revenue'  => \App\Models\Invoice::where('status', 'paid')->whereNotNull('customer_id')->sum('total'),
        ];

        $branches = \App\Models\Branch::where('status', 'active')->get(['id', 'name']);

        return view('admin.customers.index', compact('customers', 'summary', 'branches'));
    }

    public function show(Customer $customer)
    {
        $customer->load(['orders.orderItems', 'invoices', 'branch']);
        $totalSpent = $customer->invoices()->where('status', 'paid')->sum('total');
        return view('admin.customers.show', compact('customer', 'totalSpent'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        Customer::create($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $customer->update($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer updated successfully.');
    }
}
