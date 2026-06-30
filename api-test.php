<?php
/**
 * Test report endpoints through Laravel bootstrap
 * Visit: https://nespos.cloud/api-test.php
 * Self-deletes after running.
 */

header('Content-Type: application/json');

try {
    require __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

    // Make a request to each endpoint
    $endpoints = [
        'sales' => '/api/reports/sales',
        'financial' => '/api/reports/financial',
        'reconciliation' => '/api/reports/reconciliation',
    ];

    $results = [];

    // First get a token - try using Artisan
    $token = null;

    // Find admin user
    $user = \App\Models\User::where('email', 'admin@serenapos.com')->first();
    if (!$user) {
        echo json_encode(['error' => 'No admin user found']);
        exit;
    }

    // Get latest token
    $token = $user->currentAccessToken();
    if (!$token) {
        // Create a test token
        $token = $user->createToken('api-test')->plainTextToken;
    }

    $results['auth_user'] = [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'role' => $user->role,
        'branch_id' => $user->branch_id,
    ];

    foreach ($endpoints as $name => $url) {
        try {
            $response = $kernel->handle(
                \Illuminate\Http\Request::create($url, 'GET', [
                    'date_from' => date('Y-m-01'),
                    'date_to' => date('Y-m-d'),
                ])->merge([
                    ' Authorization' => 'Bearer ' . $token->plainTextToken ?? $token,
                ])
            );
            $content = $response->getContent();
            $decoded = json_decode($content, true);

            $results[$name] = [
                'status' => $response->getStatusCode(),
                'success' => $decoded['success'] ?? false,
                'message' => $decoded['message'] ?? null,
                'has_data' => isset($decoded['data']),
            ];
        } catch (\Throwable $e) {
            $results[$name] = [
                'error' => $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine(),
            ];
        }
    }

    echo json_encode($results, JSON_PRETTY_PRINT);

    // Check if we created a test token and clean it up
    try {
        $user->tokens()->where('name', 'api-test')->delete();
    } catch (\Throwable $e) {}

    unlink(__FILE__);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine(),
    ]);
}
