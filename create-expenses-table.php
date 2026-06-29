<?php
/**
 * One-time migration: create expenses table
 * Visit: https://nespos.cloud/create-expenses-table.php
 * Self-deletes after running.
 */

header('Content-Type: application/json');

try {
    // Read credentials from Laravel .env
    $envPath = __DIR__ . '/.env';
    if (!file_exists($envPath)) {
        throw new Exception('.env file not found at: ' . $envPath);
    }

    $envContent = file_get_contents($envPath);
    $env = [];
    foreach (explode("\n", $envContent) as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $env[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
    }

    $host = $env['DB_HOST'] ?? 'localhost';
    $db   = $env['DB_DATABASE'] ?? '';
    $user = $env['DB_USERNAME'] ?? '';
    $pass = $env['DB_PASSWORD'] ?? '';
    $charset = 'utf8mb4';

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

    // Create table without foreign keys (safer for shared hosting)
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

    echo json_encode(['success' => true, 'message' => 'expenses table created successfully']);
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
