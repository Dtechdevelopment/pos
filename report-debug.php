<?php
/**
 * Debug: test each report endpoint and capture actual error
 * Visit: https://nespos.cloud/report-debug.php
 * Self-deletes after running.
 */

header('Content-Type: application/json');

try {
    require __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

    // Get admin user
    $user = \App\Models\User::where('email', 'admin@serenapos.com')->first();
    if (!$user) {
        echo json_encode(['error' => 'No admin user']);
        exit;
    }
    $token = $user->createToken('debug');

    $results = [];
    $endpoints = [
        'sales' => '/api/reports/sales',
        'financial' => '/api/reports/financial',
        'reconciliation' => '/api/reports/reconciliation',
    ];

    foreach ($endpoints as $name => $url) {
        try {
            $request = \Illuminate\Http\Request::create($url, 'GET', [
                'date_from' => date('Y-m-01'),
                'date_to' => date('Y-m-d'),
            ]);
            $request->headers->set('Authorization', 'Bearer ' . $token->plainTextToken);
            $request->headers->set('Accept', 'application/json');

            $response = $kernel->handle($request);
            $content = $response->getContent();
            $decoded = json_decode($content, true);

            $results[$name] = [
                'status' => $response->getStatusCode(),
                'success' => $decoded['success'] ?? false,
                'message' => $decoded['message'] ?? null,
            ];
        } catch (\Throwable $e) {
            $results[$name] = [
                'error' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine(),
                'trace' => array_slice(array_map(fn($line) => trim($line), explode("\n", $e->getTraceAsString())), 0, 5),
            ];
        }
    }

    // Also test profit endpoint
    try {
        $request = \Illuminate\Http\Request::create('/api/reports/profit', 'GET', [
            'date_from' => date('Y-m-01'),
            'date_to' => date('Y-m-d'),
        ]);
        $request->headers->set('Authorization', 'Bearer ' . $token->plainTextToken);
        $request->headers->set('Accept', 'application/json');
        $response = $kernel->handle($request);
        $decoded = json_decode($response->getContent(), true);
        $results['profit'] = [
            'status' => $response->getStatusCode(),
            'success' => $decoded['success'] ?? false,
            'message' => $decoded['message'] ?? null,
        ];
    } catch (\Throwable $e) {
        $results['profit'] = [
            'error' => $e->getMessage(),
            'file' => $e->getFile() . ':' . $e->getLine(),
        ];
    }

    echo json_encode($results, JSON_PRETTY_PRINT);

    // Cleanup
    $token->delete();
    unlink(__FILE__);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => basename($e->getFile()) . ':' . $e->getLine(),
    ]);
}
