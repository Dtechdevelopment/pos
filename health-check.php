<?php
/**
 * Quick health check for reports
 * Visit: https://nespos.cloud/health-check.php
 * Self-deletes after running.
 */

header('Content-Type: application/json');

try {
    require __DIR__ . '/vendor/autoload.php';

    // Check ReportController content
    $file = __DIR__ . '/app/Http/Controllers/Api/ReportController.php';
    $content = file_get_contents($file);

    $hasExpenseImport = (strpos($content, 'use App\Models\Expense;') !== false);
    $hasFullNamespace = (strpos($content, '\App\Models\Expense::') !== false);
    $hasClassExists = (strpos($content, 'class_exists') !== false);

    // Check if Expense model exists on server
    $expenseModelExists = file_exists(__DIR__ . '/app/Models/Expense.php');

    // Check syntax
    $output = [];
    exec('php -l ' . escapeshellarg($file) . ' 2>&1', $output, $exitCode);

    echo json_encode([
        'success' => true,
        'has_top_level_expense_import' => $hasExpenseImport,
        'has_full_namespace' => $hasFullNamespace,
        'has_class_exists_guard' => $hasClassExists,
        'expense_model_on_server' => $expenseModelExists,
        'syntax_ok' => $exitCode === 0,
        'syntax_output' => implode(' | ', $output),
    ]);

    unlink(__FILE__);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
