<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Fix 1: Convert payment_method from ENUM to VARCHAR(50)
try {
    DB::statement("ALTER TABLE payments MODIFY payment_method VARCHAR(50) NOT NULL DEFAULT 'cash'");
    echo "payments.payment_method converted to VARCHAR(50)\n";
} catch (Exception $e) {
    echo "payments ALTER failed (may already be VARCHAR): " . $e->getMessage() . "\n";
}

echo "\nDone. Delete this file now!\n";
unlink(__FILE__);
echo "File deleted.\n";
