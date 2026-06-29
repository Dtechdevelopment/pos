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

header('Content-Type: application/json');

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Check if table already exists
    $tables = $pdo->query("SHOW TABLES LIKE 'expenses'")->fetchAll();
    if (!empty($tables)) {
        echo json_encode(['success' => true, 'message' => 'expenses table already exists']);
        unlink(__FILE__);
        exit;
    }

    // Check branches table structure
    $cols = $pdo->query("SHOW COLUMNS FROM branches")->fetchAll(PDO::FETCH_COLUMN);
    $branchIdType = in_array('id', $cols) ? 'exists' : 'missing';

    // Check users table structure
    $userCols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);

    // Create table without foreign keys first (safer for shared hosting)
    $pdo->exec("
        CREATE TABLE expenses (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            branch_id BIGINT UNSIGNED NOT NULL,
            category VARCHAR(20) NOT NULL,
            description VARCHAR(255) NULL,
            amount DECIMAL(12,2) NOT NULL,
            frequency VARCHAR(20) NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NULL,
            is_recurring TINYINT(1) NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_by BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            INDEX idx_branch (branch_id),
            INDEX idx_category (category),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    echo json_encode([
        'success' => true,
        'message' => 'expenses table created successfully',
        'branches_cols' => count($cols),
        'users_cols' => count($userCols),
    ]);
    unlink(__FILE__);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'line' => $e->getLine(),
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ]);
}
