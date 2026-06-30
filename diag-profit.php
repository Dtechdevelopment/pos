<?php
/**
 * Diagnose profit endpoint
 * Visit: https://nespos.cloud/diag-profit.php
 * Self-deletes after running.
 */

header('Content-Type: application/json');

try {
    require __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';

    // Check if Expense model exists
    $modelExists = class_exists('App\Models\Expense');

    // Check if ReportController has profit method
    $controllerFile = __DIR__ . '/app/Http/Controllers/Api/ReportController.php';
    $controllerContent = file_get_contents($controllerFile);
    $hasProfitMethod = strpos($controllerContent, 'public function profit') !== false;

    // Check for daily_breakdown in controller
    $hasDailyBreakdown = strpos($controllerContent, 'daily_breakdown') !== false;

    // Check syntax
    $output = [];
    exec('php -l ' . escapeshellarg($controllerFile) . ' 2>&1', $output, $returnCode);

    echo json_encode([
        'success' => true,
        'expense_model_exists' => $modelExists,
        'has_profit_method' => $hasProfitMethod,
        'has_daily_breakdown' => $hasDailyBreakdown,
        'syntax_check' => $output,
        'syntax_ok' => $returnCode === 0,
    ]);

    unlink(__FILE__);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
}
