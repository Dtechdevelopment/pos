<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalRestaurants = Branch::count();
        $activeRestaurants = Branch::where('status', 'active')->count();
        $totalManagers = User::whereHas('roles', fn($q) => $q->where('name', 'manager'))->count();
        $totalStaff = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['waiter', 'kitchen', 'cashier']);
        })->count();

        $recentBranches = Branch::withCount(['orders', 'invoices', 'users'])
            ->latest()
            ->take(5)
            ->get();

        return view('super_admin.dashboard.index', compact(
            'totalRestaurants',
            'activeRestaurants',
            'totalManagers',
            'totalStaff',
            'recentBranches'
        ));
    }
}
