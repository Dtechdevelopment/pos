<?php

use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\KitchenController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ReconciliationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\RestaurantController as SuperAdminRestaurantController;
use App\Http\Controllers\SuperAdmin\ManagerController as SuperAdminManagerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

Route::middleware(['auth', 'verified'])->prefix('super-admin')->name('super_admin.')->group(function () {
    Route::get('/', fn() => redirect()->route('super_admin.dashboard'));
    Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('restaurants/{restaurant}/toggle-status', [SuperAdminRestaurantController::class, 'toggleStatus'])->name('restaurants.toggle-status');
    Route::resource('restaurants', SuperAdminRestaurantController::class);

    Route::post('managers/{manager}/reset-password', [SuperAdminManagerController::class, 'resetPassword'])->name('managers.reset-password');
    Route::resource('managers', SuperAdminManagerController::class);
})->middleware('role:super_admin');

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', UserController::class)->except(['show']);
    Route::post('users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

    Route::resource('roles', RoleController::class)->except(['show']);
    Route::resource('permissions', PermissionController::class)->only(['index']);

    Route::resource('branches', BranchController::class)->except(['show']);
    Route::resource('tables', TableController::class)->except(['show']);
    Route::post('tables/merge', [TableController::class, 'mergeTables'])->name('tables.merge');
    Route::post('tables/transfer', [TableController::class, 'transferTable'])->name('tables.transfer');

    Route::get('categories', [MenuController::class, 'categories'])->name('categories.index');
    Route::post('categories', [MenuController::class, 'storeCategory'])->name('categories.store');
    Route::get('categories/{category}/edit', [MenuController::class, 'editCategory'])->name('categories.edit');
    Route::put('categories/{category}', [MenuController::class, 'updateCategory'])->name('categories.update');
    Route::delete('categories/{category}', [MenuController::class, 'destroyCategory'])->name('categories.destroy');

    Route::resource('menu', MenuController::class)->except(['show']);
    Route::post('menu/bulk-import', [MenuController::class, 'bulkImport'])->name('menu.bulk-import');

    Route::resource('orders', OrderController::class)->only(['index', 'show', 'edit', 'update']);
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('orders/{order}/reassign-waiter', [OrderController::class, 'reassignWaiter'])->name('orders.reassign-waiter');

    Route::get('kitchen', [KitchenController::class, 'index'])->name('kitchen.index');
    Route::post('kitchen/{kitchenOrder}/status', [KitchenController::class, 'updateStatus'])->name('kitchen.update-status');
    Route::get('kitchen/analytics', [KitchenController::class, 'analytics'])->name('kitchen.analytics');

    Route::resource('billing', BillingController::class)->only(['index', 'show']);
    Route::post('billing/{invoice}/reprint', [BillingController::class, 'reprint'])->name('billing.reprint');
    Route::post('billing/{invoice}/void', [BillingController::class, 'void'])->name('billing.void');
    Route::post('billing/{invoice}/discount', [BillingController::class, 'applyDiscount'])->name('billing.discount');

    Route::resource('payments', PaymentController::class)->only(['index']);
    Route::post('payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
    Route::post('payments/{payment}/reverse', [PaymentController::class, 'reverse'])->name('payments.reverse');
    Route::post('payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');
    Route::get('payments/dashboard', [PaymentController::class, 'dashboard'])->name('payments.dashboard');

    Route::resource('inventory', InventoryController::class)->except(['show', 'edit', 'update', 'destroy']);
    Route::post('inventory/stock-out', [InventoryController::class, 'stockOut'])->name('inventory.stock-out');
    Route::get('inventory/alerts', [InventoryController::class, 'alerts'])->name('inventory.alerts');
    Route::get('inventory/waste-report', [InventoryController::class, 'wasteReport'])->name('inventory.waste-report');

    Route::resource('customers', CustomerController::class)->only(['index', 'show', 'store', 'update']);

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('kitchen', [ReportController::class, 'kitchen'])->name('kitchen');
        Route::get('waiter', [ReportController::class, 'waiter'])->name('waiter');
        Route::get('financial', [ReportController::class, 'financial'])->name('financial');
        Route::get('reconciliation', [ReportController::class, 'reconciliation'])->name('reconciliation');
        Route::get('export/{type}', [ReportController::class, 'export'])->name('export');
    });

    Route::resource('reconciliation', ReconciliationController::class)->only(['index', 'store']);

    Route::resource('audit', AuditController::class)->only(['index']);

    Route::resource('notifications', NotificationController::class)->only(['index']);
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::get('settings/security', [SettingController::class, 'security'])->name('settings.security');
    Route::post('settings/security', [SettingController::class, 'updateSecurity'])->name('settings.security.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
