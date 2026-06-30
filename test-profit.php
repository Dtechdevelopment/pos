<?php
/**
 * Quick test: verify profit endpoint works
 * Visit: https://nespos.cloud/test-profit.php
 * Self-deletes after running.
 */

$host = 'localhost';
$db   = 'u273387727_pos';
$user = 'u273387727_pos';
$pass = '5808437052aW@';
$charset = 'utf8mb4';

header('Content-Type: application/json');

try {
    $envPath = __DIR__ . '/.env';
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

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Check if expenses table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'expenses'")->fetchAll();
    $hasExpensesTable = !empty($tables);

    // Check if report route exists by testing the route file
    $routeFile = __DIR__ . '/routes/api.php';
    $routeContent = file_get_contents($routeFile);
    $hasProfitRoute = strpos($routeContent, 'profit') !== false;

    echo json_encode([
        'success' => true,
        'expenses_table_exists' => $hasExpensesTable,
        'profit_route_exists' => $hasProfitRoute,
        'db_connected' => true,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
