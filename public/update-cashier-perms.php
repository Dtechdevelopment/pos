<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

$cashier = Role::where('name', 'cashier')->first();
if (!$cashier) {
    echo "Cashier role not found!\n";
    exit(1);
}

$cashier->syncPermissions([
    'create-orders', 'edit-orders', 'view-orders',
    'create-bills', 'view-bills', 'edit-bills',
    'confirm-payment', 'reverse-payment', 'refund-payment',
]);

echo "Cashier permissions updated successfully!\n";
echo "Permissions: " . implode(', ', $cashier->permissions->pluck('name')->toArray()) . "\n";
