<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AuditController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\KitchenController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\ExpenseCategoryController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PinAuthController;
use App\Http\Controllers\Api\SuperAdminRestaurantController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/pin-login', [PinAuthController::class, 'pinLogin']);
Route::post('/pin-login/ambiguous', [PinAuthController::class, 'pinLoginAmbiguous']);

Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Menu & Categories
    Route::get('/menu', [MenuController::class, 'index']);
    Route::get('/menu/{menuItem}', [MenuController::class, 'show']);
    Route::post('/menu', [MenuController::class, 'store']);
    Route::put('/menu/{menuItem}', [MenuController::class, 'update']);
    Route::delete('/menu/{menuItem}', [MenuController::class, 'destroy']);
    Route::post('/menu/{menuItem}/toggle-availability', [MenuController::class, 'toggleAvailability']);
    Route::post('/menu/{menuItem}/toggle-active', [MenuController::class, 'toggleActive']);

    Route::get('/categories', [MenuController::class, 'categories']);
    Route::post('/categories', [MenuController::class, 'storeCategory']);
    Route::put('/categories/{category}', [MenuController::class, 'updateCategory']);
    Route::delete('/categories/{category}', [MenuController::class, 'destroyCategory']);

    // Tables
    Route::get('/tables', [TableController::class, 'index']);
    Route::post('/tables', [TableController::class, 'store']);
    Route::get('/tables/{table}', [TableController::class, 'show']);
    Route::put('/tables/{table}', [TableController::class, 'update']);
    Route::delete('/tables/{table}', [TableController::class, 'destroy']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/summary', [OrderController::class, 'summary']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders/{order}/send-to-kitchen', [OrderController::class, 'sendToKitchen']);
    Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus']);
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::post('/orders/{order}/add-items', [OrderController::class, 'addItems']);

    // Kitchen
    Route::get('/kitchen', [KitchenController::class, 'index']);
    Route::put('/kitchen/{kitchenOrder}/status', [KitchenController::class, 'updateStatus']);
    Route::get('/kitchen/analytics', [KitchenController::class, 'analytics']);

    // Billing / Invoices
    Route::get('/invoices', [BillingController::class, 'index']);
    Route::post('/invoices', [BillingController::class, 'store']);
    Route::post('/invoices/{invoice}/regenerate', [BillingController::class, 'regenerate']);
    Route::get('/invoices/summary', [BillingController::class, 'summary']);
    Route::get('/invoices/{invoice}', [BillingController::class, 'show']);
    Route::post('/invoices/{invoice}/void', [BillingController::class, 'void']);
    Route::post('/invoices/{invoice}/discount', [BillingController::class, 'applyDiscount']);

    // Payments
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::post('/payments/combined', [PaymentController::class, 'storeCombined']);
    Route::get('/payments/dashboard', [PaymentController::class, 'dashboard']);
    Route::post('/payments/{payment}/verify', [PaymentController::class, 'verify']);
    Route::post('/payments/{payment}/reverse', [PaymentController::class, 'reverse']);
    Route::post('/payments/{payment}/refund', [PaymentController::class, 'refund']);

    // Payment Methods (dynamic per branch)
    Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
    Route::get('/payment-methods/active', [PaymentMethodController::class, 'active']);
    Route::post('/payment-methods', [PaymentMethodController::class, 'store']);
    Route::put('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'update']);
    Route::delete('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'destroy']);

    // Expenses
    Route::get('/expenses', [ExpenseController::class, 'index']);
    Route::get('/expenses/summary', [ExpenseController::class, 'summary']);
    Route::post('/expenses', [ExpenseController::class, 'store']);
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update']);
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy']);

    // Expense Categories
    Route::get('/expense-categories', [ExpenseCategoryController::class, 'index']);
    Route::post('/expense-categories', [ExpenseCategoryController::class, 'store']);
    Route::put('/expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'update']);
    Route::delete('/expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'destroy']);

    // Cashier
    Route::get('/cashier/tables', [TableController::class, 'cashierTables']);

    // Customers
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::get('/customers/{customer}', [CustomerController::class, 'show']);
    Route::put('/customers/{customer}', [CustomerController::class, 'update']);

    // Users
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/roles', [UserController::class, 'roles']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword']);

    // PIN Management
    Route::post('/pin/set', [PinAuthController::class, 'setPin']);
    Route::post('/pin/set/{user}', [PinAuthController::class, 'setPinForUser']);
    Route::post('/pin/clear/{user}', [PinAuthController::class, 'clearPin']);
    Route::post('/pin/verify', [PinAuthController::class, 'verifyPin']);
    Route::post('/pin/verify-self', [PinAuthController::class, 'verifySelf']);

    // Audit Logs
    Route::get('/audit-logs', [AuditController::class, 'index']);

    // Reports
    Route::get('/reports/sales', [ReportController::class, 'sales']);
    Route::get('/reports/kitchen', [ReportController::class, 'kitchen']);
    Route::get('/reports/waiter', [ReportController::class, 'waiter']);
    Route::get('/reports/waiter/{type}', [ReportController::class, 'waiterReport']);
    Route::get('/reports/financial', [ReportController::class, 'financial']);
    Route::get('/reports/reconciliation', [ReportController::class, 'reconciliation']);
    Route::get('/reports/profit', [ReportController::class, 'profit']);

    // Settings
    Route::get('/settings', function (Request $request) {
        $branch = $request->user()->branch;
        if (!$branch) {
            return response()->json(['message' => 'No branch assigned'], 404);
        }
        return response()->json([
            'success' => true,
            'data' => [
                'order_method' => $branch->order_method ?? 'digital',
            ],
        ]);
    });

    Route::put('/settings', function (Request $request) {
        $user = $request->user();
        $roles = $user->getRoleNames();
        if (!$roles->contains('super_admin') && !$roles->contains('manager') && !$roles->contains('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'order_method' => 'required|in:digital,manual',
        ]);

        $branch = $user->branch;
        if (!$branch) {
            return response()->json(['message' => 'No branch assigned'], 404);
        }

        $branch->order_method = $validated['order_method'];
        $branch->save();

        return response()->json([
            'success' => true,
            'message' => 'Settings updated',
            'data' => ['order_method' => $branch->order_method],
        ]);
    });

    // Branch Logo — stores directly in public/branches/ (no symlink needed)
    Route::post('/settings/logo', function (Request $request) {
        $user = $request->user();
        $roles = $user->getRoleNames();
        if (!$roles->contains('super_admin') && !$roles->contains('manager') && !$roles->contains('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $branch = $user->branch;
        if (!$branch) {
            return response()->json(['message' => 'No branch assigned'], 404);
        }

        $request->validate([
            'logo' => 'required|file|mimes:png,jpg,jpeg|max:2048',
        ]);

        $file = $request->file('logo');
        $filename = 'branch_' . $branch->id . '_logo.' . $file->getClientOriginalExtension();

        $dir = public_path('branches');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file->move($dir, $filename);

        $branch->logo_path = $filename;
        $branch->save();

        return response()->json([
            'success' => true,
            'message' => 'Logo uploaded successfully',
            'data' => ['logo_url' => $branch->logo_url],
        ]);
    });

    Route::delete('/settings/logo', function (Request $request) {
        $user = $request->user();
        $roles = $user->getRoleNames();
        if (!$roles->contains('super_admin') && !$roles->contains('manager') && !$roles->contains('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $branch = $user->branch;
        if (!$branch) {
            return response()->json(['message' => 'No branch assigned'], 404);
        }

        if ($branch->logo_path) {
            $file = public_path('branches/' . $branch->logo_path);
            if (file_exists($file)) {
                unlink($file);
            }
        }

        $branch->logo_path = null;
        $branch->save();

        return response()->json([
            'success' => true,
            'message' => 'Logo removed',
        ]);
    });

    Route::get('/settings/logo', function (Request $request) {
        $branch = $request->user()->branch;
        if (!$branch) {
            return response()->json(['message' => 'No branch assigned'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => ['logo_url' => $branch->logo_url],
        ]);
    });

    // One-time fix: alter kitchen_orders status enum to include 'picked_up'
    Route::post('/alter-kitchen-status', function () {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE kitchen_orders MODIFY COLUMN status ENUM('pending','preparing','ready','picked_up','delivered','cancelled') DEFAULT 'pending'");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','sent_to_kitchen','preparing','ready','picked_up','delivered','closed','cancelled') DEFAULT 'pending'");
        return response()->json(['message' => 'Both status enums updated to include picked_up']);
    });

    // One-time fix: sync all order statuses from kitchen items
    Route::post('/fix-order-statuses', function () {
        $orders = \App\Models\Order::whereHas('kitchenOrders')->get();
        $fixed = 0;
        $priority = ['pending' => 1, 'preparing' => 2, 'ready' => 3, 'picked_up' => 4, 'delivered' => 5];
        $orderStatusMap = ['pending' => 'sent_to_kitchen', 'preparing' => 'preparing', 'ready' => 'ready', 'picked_up' => 'picked_up', 'delivered' => 'delivered'];

        foreach ($orders as $order) {
            $statuses = $order->kitchenOrders()->pluck('status')->toArray();
            $active = array_diff($statuses, ['cancelled']);

            if (empty($active)) {
                $newStatus = 'cancelled';
            } else {
                $minStatus = collect($active)->min(fn($s) => $priority[$s] ?? 0);
                $minKey = array_search($minStatus, $priority);
                $newStatus = $orderStatusMap[$minKey] ?? 'sent_to_kitchen';
            }

            if ($order->status !== $newStatus) {
                $order->status = $newStatus;
                $order->save();
                $fixed++;
            }
        }

        return response()->json(['fixed' => $fixed, 'total' => $orders->count()]);
    });

    // One-time fix: add guest_count column to orders table
    Route::post('/add-guest-count', function () {
        $columns = \Illuminate\Support\Facades\DB::getSchemaBuilder()->getColumnListing('orders');
        if (!in_array('guest_count', $columns)) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE orders ADD COLUMN guest_count INT NOT NULL DEFAULT 1 AFTER restaurant_table_id");
            return response()->json(['message' => 'guest_count column added']);
        }
        return response()->json(['message' => 'guest_count column already exists']);
    });

    // One-time fix: add is_addon column to kitchen_orders table
    Route::post('/add-addon-column', function () {
        $columns = \Illuminate\Support\Facades\DB::getSchemaBuilder()->getColumnListing('kitchen_orders');
        if (!in_array('is_addon', $columns)) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE kitchen_orders ADD COLUMN is_addon TINYINT(1) NOT NULL DEFAULT 0 AFTER notes");
            return response()->json(['message' => 'is_addon column added to kitchen_orders']);
        }
        return response()->json(['message' => 'is_addon column already exists']);
    });

    // One-time fix: add order_method column to branches table
    Route::post('/add-order-method', function () {
        $columns = \Illuminate\Support\Facades\DB::getSchemaBuilder()->getColumnListing('branches');
        if (!in_array('order_method', $columns)) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE branches ADD COLUMN order_method VARCHAR(10) NOT NULL DEFAULT 'digital' AFTER status");
            return response()->json(['message' => 'order_method column added to branches']);
        }
        return response()->json(['message' => 'order_method column already exists']);
    });

    // One-time fix: migrate logo files from storage/app/public/branches/ to public/branches/
    Route::post('/migrate-logos', function () {
        $branches = \App\Models\Branch::whereNotNull('logo_path')->get();
        $moved = 0;
        foreach ($branches as $branch) {
            $oldPath = $branch->logo_path;
            $filename = basename($oldPath);
            $newDir = public_path('branches');
            if (!is_dir($newDir)) {
                mkdir($newDir, 0755, true);
            }
            $oldFile = storage_path('app/public/' . $oldPath);
            $newFile = $newDir . '/' . $filename;
            if (file_exists($oldFile) && !file_exists($newFile)) {
                copy($oldFile, $newFile);
            }
            if (file_exists($newFile)) {
                $branch->logo_path = $filename;
                $branch->save();
                $moved++;
            }
        }
        return response()->json(['message' => "Migrated $moved logo(s)", 'total' => $branches->count()]);
    });

    // One-time fix: seed default payment methods for existing branches
    Route::post('/seed-payment-methods', function () {
        $branches = \App\Models\Branch::all();
        $defaults = [
            ['name' => 'Cash', 'slug' => 'cash', 'sort_order' => 1],
            ['name' => 'M-Pesa', 'slug' => 'm_pesa', 'sort_order' => 2],
            ['name' => 'Card', 'slug' => 'card', 'sort_order' => 3],
            ['name' => 'Bank Transfer', 'slug' => 'bank_transfer', 'sort_order' => 4],
        ];
        $created = 0;
        foreach ($branches as $branch) {
            foreach ($defaults as $def) {
                $exists = \App\Models\PaymentMethod::where('branch_id', $branch->id)
                    ->where('slug', $def['slug'])->exists();
                if (!$exists) {
                    \App\Models\PaymentMethod::create(array_merge($def, [
                        'branch_id' => $branch->id,
                        'is_active' => true,
                    ]));
                    $created++;
                }
            }
        }
        return response()->json(['message' => "Seeded $created payment method(s) for {$branches->count()} branch(es)"]);
    });

    // Super Admin: Restaurant Management
    Route::get('/admin/restaurants', [SuperAdminRestaurantController::class, 'index']);
    Route::get('/admin/restaurants/summary', [SuperAdminRestaurantController::class, 'summary']);
    Route::post('/admin/restaurants', [SuperAdminRestaurantController::class, 'store']);
    Route::get('/admin/restaurants/{restaurant}', [SuperAdminRestaurantController::class, 'show']);
    Route::put('/admin/restaurants/{restaurant}', [SuperAdminRestaurantController::class, 'update']);
    Route::post('/admin/restaurants/{restaurant}/toggle-status', [SuperAdminRestaurantController::class, 'toggleStatus']);
    Route::delete('/admin/restaurants/{restaurant}', [SuperAdminRestaurantController::class, 'destroy']);
});
