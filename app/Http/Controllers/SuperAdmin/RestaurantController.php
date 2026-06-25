<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        $query = Branch::withCount(['orders', 'invoices', 'users']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('address', 'like', "%{$request->search}%")
                  ->orWhere('manager_name', 'like', "%{$request->search}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $branches = $query->latest()->paginate(12);

        return view('super_admin.restaurants.index', compact('branches'));
    }

    public function create()
    {
        return view('super_admin.restaurants.create');
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
            'order_method' => 'required|in:digital,manual',
        ]);

        Branch::create($validated);

        return redirect()->route('super_admin.restaurants.index')
            ->with('success', 'Restaurant created successfully.');
    }

    public function edit(Branch $restaurant)
    {
        return view('super_admin.restaurants.edit', ['branch' => $restaurant]);
    }

    public function update(Request $request, Branch $restaurant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'order_method' => 'required|in:digital,manual',
        ]);

        $restaurant->update($validated);

        return redirect()->route('super_admin.restaurants.index')
            ->with('success', 'Restaurant updated successfully.');
    }

    public function destroy(Branch $restaurant)
    {
        $restaurant->delete();
        return redirect()->route('super_admin.restaurants.index')
            ->with('success', 'Restaurant deleted successfully.');
    }

    public function toggleStatus(Branch $restaurant)
    {
        $restaurant->status = $restaurant->status === 'active' ? 'inactive' : 'active';
        $restaurant->save();

        return back()->with('success', "Restaurant {$restaurant->status}.");
    }
}
