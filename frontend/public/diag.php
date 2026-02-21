<?php
header('Content-Type: application/json');

$results = [
    'php_version' => PHP_VERSION,
    'curl_enabled' => function_exists('curl_init'),
    'candidates' => []
];

require_once __DIR__ . '/api/config.php';

foreach (backend_candidates() as $base) {
    $url = rtrim($base, '/') . '/health';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
    $resp = curl_exec($ch);
    $info = curl_getinfo($ch);
    $err = curl_error($ch);
    curl_close($ch);

    $results['candidates'][] = [
        'url' => $url,
        'http_code' => $info['http_code'],
        'success' => ($resp !== false),
        'error' => $err,
        'response' => $resp ? json_decode($resp, true) : null
    ];
}

echo "\n--- Database Check ---\n";
// Try to list users (requires DB connection)
$ch = curl_init(rtrim(backend_candidates()[0], '/') . '/users/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 2);
$resp = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($status == 200) {
    $users = json_decode($resp, true);
    echo "Users Table: OK\n";
    echo "User Count : " . (is_array($users) ? count($users) : 'Invalid response') . "\n";
} else {
    echo "Users Table: FAILED (Status $status)\n";
    echo "Error Detail: " . $resp . "\n";
}

echo "\n--- Raw JSON ---\n";
echo json_encode($results, JSON_PRETTY_PRINT);
