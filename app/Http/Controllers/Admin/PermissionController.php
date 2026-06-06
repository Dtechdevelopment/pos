<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('permissions')->get();
        $permissions = Permission::all()->groupBy(function ($perm) {
            return explode('.', $perm->name)[0] ?? 'general';
        });

        $totalPermissions = Permission::count();
        $totalGroups      = $permissions->count();

        // Pre-load all role permissions as a map for efficient lookup
        $rolePerms = $roles->mapWithKeys(fn($r) => [
            $r->id => $r->permissions()->pluck('name')->flip()
        ]);

        return view('admin.permissions.index', compact(
            'roles', 'permissions', 'totalPermissions', 'totalGroups', 'rolePerms'
        ));
    }
}
