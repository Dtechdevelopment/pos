<?php
/**
 * Run: php create-expense-categories-table.php
 * Creates expense_categories table and alters expenses.category from ENUM to VARCHAR.
 * Reads DB credentials from .env. Self-deletes after running.
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

    // 1. Create expense_categories table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `expense_categories` (
        `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `branch_id` BIGINT UNSIGNED NOT NULL,
        `name` VARCHAR(100) NOT NULL,
        `slug` VARCHAR(100) NOT NULL,
        `icon` VARCHAR(50) DEFAULT 'more_horiz',
        `color` VARCHAR(20) DEFAULT '#757575',
        `is_active` TINYINT(1) DEFAULT 1,
        `sort_order` INT DEFAULT 0,
        `created_at` TIMESTAMP NULL,
        `updated_at` TIMESTAMP NULL,
        UNIQUE KEY `unique_branch_slug` (`branch_id`, `slug`),
        KEY `idx_branch_active` (`branch_id`, `is_active`),
        CONSTRAINT `expense_categories_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "OK: expense_categories table created\n";

    // 2. Alter expenses.category from ENUM to VARCHAR
    $stmt = $pdo->query("SHOW COLUMNS FROM `expenses` LIKE 'category'");
    $col = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($col && strpos($col['Type'], 'enum') !== false) {
        $pdo->exec("ALTER TABLE `expenses` MODIFY COLUMN `category` VARCHAR(100) NOT NULL");
        echo "OK: expenses.category changed from ENUM to VARCHAR(100)\n";
    } else {
        echo "SKIP: expenses.category is already VARCHAR or doesn't exist\n";
    }

    // 3. Seed default categories for all branches
    $branches = $pdo->query("SELECT id FROM `branches`")->fetchAll(PDO::FETCH_COLUMN);
    $defaults = [
        ['name' => 'Utilities',     'slug' => 'utilities',     'icon' => 'bolt',            'color' => '#FF9800', 'sort' => 1],
        ['name' => 'Supplies',      'slug' => 'supplies',      'icon' => 'inventory_2',     'color' => '#4CAF50', 'sort' => 2],
        ['name' => 'Maintenance',   'slug' => 'maintenance',   'icon' => 'build',           'color' => '#2196F3', 'sort' => 3],
        ['name' => 'Rent',          'slug' => 'rent',          'icon' => 'home',            'color' => '#9C27B0', 'sort' => 4],
        ['name' => 'Salaries',      'slug' => 'salaries',      'icon' => 'people',          'color' => '#F44336', 'sort' => 5],
        ['name' => 'Other',         'slug' => 'other',         'icon' => 'more_horiz',      'color' => '#757575', 'sort' => 6],
    ];

    $insert = $pdo->prepare("INSERT IGNORE INTO `expense_categories` (`branch_id`, `name`, `slug`, `icon`, `color`, `is_active`, `sort_order`, `created_at`, `updated_at`)
        VALUES (?, ?, ?, ?, ?, 1, ?, NOW(), NOW())");

    $seeded = 0;
    foreach ($branches as $branchId) {
        foreach ($defaults as $cat) {
            $insert->execute([$branchId, $cat['name'], $cat['slug'], $cat['icon'], $cat['color'], $cat['sort']]);
            if ($insert->rowCount() > 0) $seeded++;
        }
    }
    echo "OK: Seeded default categories for " . count($branches) . " branch(es) ($seeded new rows)\n";

    echo "\nDone! This script will now self-delete.\n";
    unlink(__FILE__);

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
