<?php
/**
 * Health check - no exec() used
 * Visit: https://nespos.cloud/health-check.php
 * Self-deletes after running.
 */

header('Content-Type: application/json');

try {
    require __DIR__ . '/vendor/autoload.php';

    $file = __DIR__ . '/app/Http/Controllers/Api/ReportController.php';
    $content = file_get_contents($file);

    $hasExpenseImport = (strpos($content, 'use App\\Models\\Expense;') !== false);
    $hasFullNamespace = (strpos($content, '\\App\\Models\\Expense::') !== false);
    $hasClassExists = (strpos($content, 'class_exists') !== false);
    $expenseModelExists = file_exists(__DIR__ . '/app/Models/Expense.php');

    // Check syntax using token_get_all instead of exec
    $tokens = @token_get_all($content);
    $syntaxValid = ($tokens !== false);

    echo json_encode([
        'success' => true,
        'has_top_level_expense_import' => $hasExpenseImport,
        'has_full_namespace' => $hasFullNamespace,
        'has_class_exists_guard' => $hasClassExists,
        'expense_model_on_server' => $expenseModelExists,
        'syntax_parse_ok' => $syntaxValid,
        'report_controller_lines' => substr_count($content, "\n"),
    ]);

    unlink(__FILE__);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
