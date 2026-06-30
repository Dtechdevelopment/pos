<?php
/**
 * Run: php add-menu-icon-fields.php
 * Adds icon_type, icon_shape, icon_color, icon_image columns to menu_items table.
 * Self-deletes after running.
 */

$envFile = __DIR__ . '/.env';
$env = [];
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (trim($line) === '' || $line[0] === '#') continue;
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $env[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
        }
    }
}

$host = $env['DB_HOST'] ?? 'localhost';
$dbname = $env['DB_DATABASE'] ?? '';
$username = $env['DB_USERNAME'] ?? '';
$password = $env['DB_PASSWORD'] ?? '';

if (!$dbname || !$username) {
    echo "ERROR: Could not read DB credentials from .env\n";
    exit(1);
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Check if columns already exist
    $stmt = $pdo->query("SHOW COLUMNS FROM `menu_items` LIKE 'icon_type'");
    if ($stmt->fetch()) {
        echo "SKIP: icon_type column already exists\n";
    } else {
        $pdo->exec("ALTER TABLE `menu_items` ADD COLUMN `icon_type` VARCHAR(20) NOT NULL DEFAULT 'none' AFTER `image`");
        echo "OK: Added icon_type column\n";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM `menu_items` LIKE 'icon_shape'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE `menu_items` ADD COLUMN `icon_shape` VARCHAR(50) NULL AFTER `icon_type`");
        echo "OK: Added icon_shape column\n";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM `menu_items` LIKE 'icon_color'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE `menu_items` ADD COLUMN `icon_color` VARCHAR(20) NULL AFTER `icon_shape`");
        echo "OK: Added icon_color column\n";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM `menu_items` LIKE 'icon_image'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE `menu_items` ADD COLUMN `icon_image` VARCHAR(500) NULL AFTER `icon_color`");
        echo "OK: Added icon_image column\n";
    }

    // Create storage directory for menu icon images
    $iconDir = __DIR__ . '/storage/menu-icons';
    if (!is_dir($iconDir)) {
        mkdir($iconDir, 0755, true);
        echo "OK: Created storage/menu-icons/ directory\n";
    }

    // Create .htaccess to serve images
    $htaccess = $iconDir . '/.htaccess';
    if (!file_exists($htaccess)) {
        file_put_contents($htaccess, "Allow from all\n");
        echo "OK: Created .htaccess for serving images\n";
    }

    echo "\nDone! This script will now self-delete.\n";
    unlink(__FILE__);

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
