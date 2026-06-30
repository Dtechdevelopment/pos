<?php
/**
 * Debug profit endpoint directly
 * Visit: https://nespos.cloud/test-profit-query.php
 * Self-deletes after running.
 */

header('Content-Type: application/json');

try {
    // Bootstrap Laravel
    require __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($request = Illuminate\Http\Request::capture());

    // Simulate authenticated request
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

    $dsn = "mysql:host={$env['DB_HOST']};dbname={$env['DB_DATABASE']};charset=utf8mb4";
    $pdo = new PDO($dsn, $env['DB_USERNAME'], $env['DB_PASSWORD'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Check expenses table structure
    $cols = $pdo->query("SHOW COLUMNS FROM expenses")->fetchAll(PDO::FETCH_COLUMN);

    // Check if report method exists
    $controllerFile = __DIR__ . '/app/Http/Controllers/Api/ReportController.php';
    $content = file_get_contents($controllerFile);
    $hasProfitMethod = strpos($content, 'public function profit') !== false;

    // Check for any syntax errors in the controller
    $output = [];
    exec('php -l ' . escapeshellarg($controllerFile) . ' 2>&1', $output);
    $syntaxOk = strpos(implode($output), 'No syntax errors') !== false;

    echo json_encode([
        'success' => true,
        'expenses_columns' => $cols,
        'has_profit_method' => $hasProfitMethod,
        'syntax_ok' => $syntaxOk,
        'syntax_output' => $output,
    ]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
}
