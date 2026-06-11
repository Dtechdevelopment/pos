<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Customer::withCount(['orders', 'invoices']);

        $branchId = $request->user()->branch_id;
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $customers = $query->orderBy('name')->get();

        return $this->success($customers);
    }

    public function show(Customer $customer): JsonResponse
    {
        $customer->loadCount(['orders', 'invoices']);
        $customer->loadSum('invoices as total_spent', function ($q) {
            $q->where('status', 'paid');
        });

        return $this->success($customer);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
        ]);

        $customer = new Customer();
        $customer->name = $validated['name'];
        $customer->phone = $validated['phone'] ?? null;
        $customer->email = $validated['email'] ?? null;
        $customer->notes = $validated['notes'] ?? null;
        $customer->branch_id = $request->user()->branch_id;
        $customer->save();

        return $this->success($customer, 'Customer created successfully', 201);
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
        ]);

        if (isset($validated['name'])) $customer->name = $validated['name'];
        if (isset($validated['phone'])) $customer->phone = $validated['phone'];
        if (isset($validated['email'])) $customer->email = $validated['email'];
        if (isset($validated['notes'])) $customer->notes = $validated['notes'];
        $customer->save();

        return $this->success($customer->fresh(), 'Customer updated successfully');
    }
}
