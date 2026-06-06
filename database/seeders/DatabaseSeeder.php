<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'create-orders', 'edit-orders', 'view-orders', 'cancel-orders',
            'create-bills', 'edit-bills', 'view-bills', 'void-bills',
            'confirm-payment', 'reverse-payment', 'refund-payment',
            'view-reports', 'export-reports',
            'manage-inventory', 'view-inventory',
            'manage-users', 'manage-roles', 'manage-permissions',
            'manage-branches', 'manage-tables', 'manage-menu',
            'manage-customers', 'view-audit-logs',
            'manage-settings', 'view-kitchen', 'update-kitchen-status',
        ];

        foreach ($permissions as $perm) {
            Permission::create(['name' => $perm, 'guard_name' => 'web']);
        }

        $superAdmin = Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());

        $manager = Role::create(['name' => 'manager', 'guard_name' => 'web']);
        $manager->givePermissionTo([
            'view-orders', 'edit-orders', 'view-bills',
            'view-reports', 'export-reports',
            'manage-inventory', 'view-inventory',
            'manage-tables', 'manage-menu',
            'manage-customers', 'view-kitchen', 'update-kitchen-status',
            'view-audit-logs',
        ]);

        $waiter = Role::create(['name' => 'waiter', 'guard_name' => 'web']);
        $waiter->givePermissionTo([
            'create-orders', 'edit-orders', 'view-orders',
            'create-bills', 'view-bills',
        ]);

        $cashier = Role::create(['name' => 'cashier', 'guard_name' => 'web']);
        $cashier->givePermissionTo([
            'view-bills', 'edit-bills',
            'confirm-payment', 'reverse-payment', 'refund-payment',
        ]);

        $kitchenStaff = Role::create(['name' => 'kitchen_staff', 'guard_name' => 'web']);
        $kitchenStaff->givePermissionTo([
            'view-orders', 'view-kitchen', 'update-kitchen-status',
        ]);

        $inventoryOfficer = Role::create(['name' => 'inventory_officer', 'guard_name' => 'web']);
        $inventoryOfficer->givePermissionTo([
            'manage-inventory', 'view-inventory',
        ]);

        $branch = Branch::create([
            'name' => 'Main Branch',
            'address' => '123 Main Street, City Center',
            'phone' => '+1-555-0100',
            'email' => 'main@restaurant.com',
            'manager_name' => 'John Manager',
            'status' => 'active',
        ]);

        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@pos.com',
            'phone' => '+1-555-0001',
            'password' => Hash::make('password'),
            'status' => 'active',
            'branch_id' => $branch->id,
        ]);
        $admin->assignRole('super_admin');

        $waiterUser = User::create([
            'name' => 'John Waiter',
            'email' => 'waiter@pos.com',
            'phone' => '+1-555-0002',
            'password' => Hash::make('password'),
            'status' => 'active',
            'branch_id' => $branch->id,
        ]);
        $waiterUser->assignRole('waiter');

        $cashierUser = User::create([
            'name' => 'Jane Cashier',
            'email' => 'cashier@pos.com',
            'phone' => '+1-555-0003',
            'password' => Hash::make('password'),
            'status' => 'active',
            'branch_id' => $branch->id,
        ]);
        $cashierUser->assignRole('cashier');

        $kitchenUser = User::create([
            'name' => 'Bob Kitchen',
            'email' => 'kitchen@pos.com',
            'phone' => '+1-555-0004',
            'password' => Hash::make('password'),
            'status' => 'active',
            'branch_id' => $branch->id,
        ]);
        $kitchenUser->assignRole('kitchen_staff');
    }
}
