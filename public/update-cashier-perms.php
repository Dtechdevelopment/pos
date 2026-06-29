<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;

$waiter = Role::where('name', 'waiter')->first();
if ($waiter) {
    $waiter->syncPermissions([
        'create-orders', 'edit-orders', 'view-orders',
        'create-bills', 'view-bills',
    ]);
    echo "Waiter permissions confirmed: " . implode(', ', $waiter->permissions->pluck('name')->toArray()) . "\n";
}

$cashier = Role::where('name', 'cashier')->first();
if ($cashier) {
    echo "Cashier permissions: " . implode(', ', $cashier->permissions->pluck('name')->toArray()) . "\n";
}

echo "\nDone. Delete this file now!\n";
unlink(__FILE__);
echo "File deleted.\n";
