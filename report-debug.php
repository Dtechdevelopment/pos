<?php
/**
 * Read Laravel error log for recent 500 errors
 * Visit: https://nespos.cloud/report-debug.php
 * Self-deletes after running.
 */

header('Content-Type: application/json');

try {
    $logFile = __DIR__ . '/storage/logs/laravel.log';
    if (!file_exists($logFile)) {
        echo json_encode(['error' => 'No log file found']);
        exit;
    }

    $content = file_get_contents($logFile);
    $lines = explode("\n", $content);

    // Find the last block of error entries
    $errors = [];
    $currentBlock = [];
    $inBlock = false;

    // Walk backwards from end
    $tailLines = array_reverse(array_slice($lines, -200));
    $blocks = [];
    $block = [];

    foreach ($tailLines as $line) {
        if (preg_match('/\[\d{4}-\d{2}-\d{2}.*?local\.(ERROR|WARNING|CRITICAL)/', $line)) {
            if (!empty($block)) {
                $blocks[] = implode("\n", array_reverse($block));
                if (count($blocks) >= 5) break;
            }
            $block = [$line];
        } elseif (!empty($block) && trim($line)) {
            $block[] = $line;
        }
    }
    if (!empty($block)) {
        $blocks[] = implode("\n", array_reverse($block));
    }

    echo json_encode([
        'log_size_bytes' => strlen($content),
        'log_lines' => count($lines),
        'recent_errors' => array_reverse($blocks),
    ], JSON_PRETTY_PRINT);

    unlink(__FILE__);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
