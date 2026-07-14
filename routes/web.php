<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\RestaurantController as SuperAdminRestaurantController;
use App\Http\Controllers\SuperAdmin\ManagerController as SuperAdminManagerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('super_admin.dashboard');
});

Route::middleware(['auth', 'verified'])->prefix('super-admin')->name('super_admin.')->group(function () {
    Route::get('/', fn() => redirect()->route('super_admin.dashboard'));
    Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('restaurants/{restaurant}/toggle-status', [SuperAdminRestaurantController::class, 'toggleStatus'])->name('restaurants.toggle-status');
    Route::resource('restaurants', SuperAdminRestaurantController::class)->parameters(['restaurants' => 'restaurant']);

    Route::post('managers/{manager}/reset-password', [SuperAdminManagerController::class, 'resetPassword'])->name('managers.reset-password');
    Route::resource('managers', SuperAdminManagerController::class)->parameters(['managers' => 'manager']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
})->middleware('role:super_admin');

require __DIR__.'/auth.php';
