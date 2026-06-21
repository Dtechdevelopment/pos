<?php

namespace App\Http\Controllers\Api;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class SuperAdminRestaurantController extends ApiController
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!$request->user()->hasRole('super_admin')) {
                return response()->json(['message' => 'Unauthorized. Super admin only.'], 403);
            }
            return $next($request);
        });
    }

    public function index(Request $request): JsonResponse
    {
        $query = Branch::withCount(['users', 'orders', 'invoices']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('address', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $restaurants = $query->orderBy('name')->paginate($request->get('per_page', 25));

        return $this->success($restaurants);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_name' => 'required|string|max:255',
            'restaurant_address' => 'nullable|string|max:500',
            'restaurant_phone' => 'nullable|string|max:20',
            'restaurant_email' => 'nullable|max:255',
            'order_method' => 'nullable|string|in:digital,manual',
            'manager_name' => 'required|string|max:255',
            'manager_email' => 'required|email|unique:users,email',
            'manager_password' => 'required|string|min:6',
        ]);

        if (!empty($validated['restaurant_email']) && !filter_var($validated['restaurant_email'], FILTER_VALIDATE_EMAIL)) {
            return $this->error('Invalid restaurant email format', 422);
        }

        if (empty($validated['restaurant_email'])) {
            $validated['restaurant_email'] = null;
        }

        DB::beginTransaction();

        try {
            $branchData = [
                'name' => $validated['restaurant_name'],
                'address' => $validated['restaurant_address'] ?: null,
                'phone' => $validated['restaurant_phone'] ?: null,
                'email' => $validated['restaurant_email'],
                'manager_name' => $validated['manager_name'],
                'status' => 'active',
            ];

            $columns = DB::getSchemaBuilder()->getColumnListing('branches');
            if (in_array('order_method', $columns)) {
                $branchData['order_method'] = $validated['order_method'] ?? 'manual';
            }

            $branch = Branch::create($branchData);

            $manager = User::create([
                'name' => $validated['manager_name'],
                'email' => $validated['manager_email'],
                'password' => Hash::make($validated['manager_password']),
                'branch_id' => $branch->id,
                'status' => 'active',
            ]);

            if (class_exists(Role::class)) {
                $role = Role::findOrCreate('manager');
                $manager->assignRole($role);
            }

            $branch->loadCount(['users', 'orders', 'invoices']);

            DB::commit();

            return $this->success($branch, 'Restaurant created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to create restaurant: ' . $e->getMessage(), 500);
        }
    }

    public function show(Branch $restaurant): JsonResponse
    {
        $restaurant->loadCount(['users', 'orders', 'invoices', 'menuItems', 'restaurantTables']);
        $restaurant->load('users.roles');

        return $this->success($restaurant);
    }

    public function update(Request $request, Branch $restaurant): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:active,inactive',
            'order_method' => 'nullable|string|in:digital,manual',
        ]);

        $restaurant->update($validated);

        return $this->success($restaurant, 'Restaurant updated successfully');
    }

    public function toggleStatus(Branch $restaurant): JsonResponse
    {
        $restaurant->status = $restaurant->status === 'active' ? 'inactive' : 'active';
        $restaurant->save();

        return $this->success($restaurant, 'Restaurant status updated');
    }

    public function destroy(Branch $restaurant): JsonResponse
    {
        if ($restaurant->orders()->count() > 0) {
            return $this->error('Cannot delete a restaurant with existing orders. Deactivate it instead.', 422);
        }

        DB::beginTransaction();

        try {
            User::where('branch_id', $restaurant->id)->delete();
            $restaurant->delete();

            DB::commit();

            return $this->success(null, 'Restaurant deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to delete restaurant: ' . $e->getMessage(), 500);
        }
    }

    public function summary(): JsonResponse
    {
        $total = Branch::count();
        $active = Branch::where('status', 'active')->count();
        $inactive = Branch::where('status', 'inactive')->count();
        $totalUsers = User::count();
        $totalOrders = \App\Models\Order::count();

        return $this->success([
            'total_restaurants' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'total_users' => $totalUsers,
            'total_orders' => $totalOrders,
        ]);
    }
}
