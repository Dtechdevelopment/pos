<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\KitchenController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::post('/login', [AuthController::class, 'login']);

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
    Route::get('/categories', [MenuController::class, 'categories']);

    // Tables
    Route::get('/tables', [TableController::class, 'index']);
    Route::get('/tables/{table}', [TableController::class, 'show']);

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

    // Cashier
    Route::get('/cashier/tables', [TableController::class, 'cashierTables']);

    // Customers
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::get('/customers/{customer}', [CustomerController::class, 'show']);
    Route::put('/customers/{customer}', [CustomerController::class, 'update']);

    // Reports
    Route::get('/reports/sales', [ReportController::class, 'sales']);
    Route::get('/reports/kitchen', [ReportController::class, 'kitchen']);
    Route::get('/reports/waiter', [ReportController::class, 'waiter']);
    Route::get('/reports/financial', [ReportController::class, 'financial']);
    Route::get('/reports/reconciliation', [ReportController::class, 'reconciliation']);

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
});
