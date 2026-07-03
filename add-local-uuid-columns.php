<?php
/**
 * Migration: Add local_uuid columns to orders, invoices, payments tables
 * This enables the mobile app to sync offline orders without duplicates.
 *
 * Usage: php add-local-uuid-columns.php
 * Self-deletes after running.
 */

$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    echo "ERROR: .env file not found\n";
    exit(1);
}

$env = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') continue;
    if (strpos($line, '=') !== false) {
        [$key, $value] = explode('=', $line, 2);
        $env[trim($key)] = trim($value, ' "\'');
    }
}

$host = $env['DB_HOST'] ?? 'localhost';
$dbname = $env['DB_DATABASE'] ?? '';
$username = $env['DB_USERNAME'] ?? '';
$password = $env['DB_PASSWORD'] ?? '';

if (!$dbname || !$username) {
    echo "ERROR: Missing DB_DATABASE or DB_USERNAME in .env\n";
    exit(1);
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Connected to database: $dbname\n\n";

$tables = ['orders', 'invoices', 'payments'];

foreach ($tables as $table) {
    echo "Processing `$table`...\n";

    // Check if table exists
    $result = $pdo->query("SHOW TABLES LIKE '$table'");
    if ($result->rowCount() === 0) {
        echo "  SKIP: Table `$table` does not exist\n\n";
        continue;
    }

    // Check if local_uuid column exists
    $result = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'local_uuid'");
    if ($result->rowCount() > 0) {
        echo "  SKIP: Column `local_uuid` already exists\n\n";
        continue;
    }

    // Add local_uuid column
    $pdo->exec("ALTER TABLE `$table` ADD COLUMN `local_uuid` VARCHAR(36) DEFAULT NULL AFTER `id`");
    echo "  OK: Added `local_uuid` column\n";

    // Add unique index on local_uuid (nullable, so multiple NULLs allowed)
    $indexName = "idx_{$table}_local_uuid";
    try {
        $pdo->exec("CREATE UNIQUE INDEX `$indexName` ON `$table` (`local_uuid`)");
        echo "  OK: Created unique index `$indexName`\n";
    } catch (PDOException $e) {
        echo "  WARN: Could not create index: " . $e->getMessage() . "\n";
    }

    echo "\n";
}

echo "Migration complete!\n";
echo "Self-deleting...\n";
unlink(__FILE__);
