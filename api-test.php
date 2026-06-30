<?php
/**
 * Test all report endpoints
 * Visit: https://nespos.cloud/api-test.php
 * Self-deletes after running.
 */

header('Content-Type: application/json');

try {
    require __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

    // Get a valid token for admin user
    $pdo = new PDO(
        'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_DATABASE'],
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD']
    );

    $stmt = $pdo->query("SELECT id, branch_id FROM users WHERE email = 'admin@serenapos.com' LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        echo json_encode(['error' => 'Admin user not found']);
        exit;
    }

    // Get latest token
    $stmt = $pdo->query("SELECT token FROM personal_access_tokens WHERE tokenable_id = {$user['id']} ORDER BY id DESC LIMIT 1");
    $tokenRow = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tokenRow) {
        echo json_encode(['error' => 'No token found', 'hint' => 'Login via app first']);
        exit;
    }

    $token = $tokenRow['token'];
    $results = [];

    // Test each endpoint
    $endpoints = [
        'sales' => '/api/reports/sales',
        'financial' => '/api/reports/financial',
        'reconciliation' => '/api/reports/reconciliation',
        'profit' => '/api/reports/profit',
    ];

    foreach ($endpoints as $name => $url) {
        $request = Illuminate\Http\Request::create($url, 'GET', [
            'date_from' => date('Y-m-01'),
            'date_to' => date('Y-m-d'),
        ]);
        $request->headers->set('Authorization', 'Bearer ' . $token);
        $request->headers->set('Accept', 'application/json');

        $response = $kernel->handle($request);
        $content = $response->getContent();
        $decoded = json_decode($content, true);

        $results[$name] = [
            'status' => $response->getStatusCode(),
            'success' => $decoded['success'] ?? false,
            'has_data' => isset($decoded['data']),
            'message' => $decoded['message'] ?? null,
        ];

        // For sales, also return the data
        if ($name === 'sales') {
            $results[$name]['data_keys'] = array_keys($decoded['data'] ?? []);
            $results[$name]['items_count'] = count($decoded['data']['items'] ?? []);
            $results[$name]['total_sales'] = $decoded['data']['total_sales'] ?? null;
        }
    }

    echo json_encode($results, JSON_PRETTY_PRINT);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
}
