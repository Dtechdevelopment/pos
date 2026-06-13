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
    Route::get('/payments/dashboard', [PaymentController::class, 'dashboard']);
    Route::post('/payments/{payment}/verify', [PaymentController::class, 'verify']);
    Route::post('/payments/{payment}/reverse', [PaymentController::class, 'reverse']);
    Route::post('/payments/{payment}/refund', [PaymentController::class, 'refund']);

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
});
