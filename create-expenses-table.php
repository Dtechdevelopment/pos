<?php
/**
 * One-time migration: create expenses table
 * Visit: https://nespos.cloud/create-expenses-table.php
 * Self-deletes after running.
 */

$host = 'localhost';
$db   = 'u273387727_pos';
$user = 'u273387727_pos';
$pass = '5808437052aW@';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Check if table already exists
$tables = $pdo->query("SHOW TABLES LIKE 'expenses'")->fetchAll();
if (!empty($tables)) {
    echo json_encode(['success' => true, 'message' => 'expenses table already exists']);
    // Self-delete
    unlink(__FILE__);
    exit;
}

$pdo->exec("
    CREATE TABLE expenses (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        branch_id BIGINT UNSIGNED NOT NULL,
        category ENUM('utilities','supplies','maintenance','rent','salaries','other') NOT NULL,
        description VARCHAR(255) NULL,
        amount DECIMAL(12,2) NOT NULL,
        frequency ENUM('daily','weekly','monthly','one_time') NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NULL,
        is_recurring TINYINT(1) NOT NULL DEFAULT 0,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_by BIGINT UNSIGNED NOT NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
        FOREIGN KEY (created_by) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

echo json_encode(['success' => true, 'message' => 'expenses table created successfully']);
unlink(__FILE__);
