<?php
require_once __DIR__ . '/public/api/config.php';

echo "Testing backend discovery...\n";
$start = microtime(true);

$result = backend_request('GET', '/health');

$end = microtime(true);
$duration = $end - $start;

echo "Duration: " . round($duration, 4) . "s\n";
echo "Status: " . ($result['status'] ?: 'FAILED') . "\n";
echo "Candidate: " . ($result['candidate'] ?? 'NONE') . "\n";
if ($result['error']) {
    echo "Error: " . $result['error'] . "\n";
}

if ($duration > 3) {
    echo "WARNING: Discovery took too long (> 3s). Check timeouts.\n";
} else {
    echo "SUCCESS: Discovery responsive.\n";
}

echo "\nTesting POST forwarding (mock login)...\n";
$login_result = backend_request('POST', '/auth/login/', ['username' => 'testuser', 'password' => 'testpass']);
echo "Status: " . $login_result['status'] . "\n";
if ($login_result['status'] == 400 || $login_result['status'] == 422) {
    echo "SUCCESS: Backend responded to POST (likely Invalid Credentials/Unprocessable Entity, which is expected for dummy data).\n";
} elseif ($login_result['status'] == 200) {
    echo "SUCCESS: Logged in (if testuser exists).\n";
} else {
    echo "FAILED: Unexpected status " . $login_result['status'] . "\n";
}
