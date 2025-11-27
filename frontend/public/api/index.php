<?php
require_once __DIR__ . '/config.php';

// Simple PHP proxy to forward requests from /api/* to backend candidates.
// This avoids CORS and host-resolution issues when serving PHP from host (XAMPP)

function forward_request_to_backend(string $backend_base, string $path) {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $query = $_SERVER['QUERY_STRING'] ?? '';
    $url = rtrim($backend_base, '/') . $path . ($query ? ('?' . $query) : '');

    $ch = curl_init();
    $headers = [];
    if (function_exists('getallheaders')) {
        foreach (getallheaders() as $k => $v) {
            // skip Host header
            if (strtolower($k) === 'host') continue;
            $headers[] = "$k: $v";
        }
    }

    $body = file_get_contents('php://input');

    $opts = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => array_merge($headers, ['X-Forwarded-Proto: ' . ($_SERVER['HTTPS'] ? 'https' : 'http')]),
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
    ];

    if ($body !== false && $body !== '') {
        $opts[CURLOPT_POSTFIELDS] = $body;
    }

    curl_setopt_array($ch, $opts);
    $resp = curl_exec($ch);
    if ($resp === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return ['ok' => false, 'error' => $err];
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $resp_headers_raw = substr($resp, 0, $header_size);
    $resp_body = substr($resp, $header_size);
    curl_close($ch);

    // parse headers and forward relevant ones
    $lines = preg_split('/\r?\n/', $resp_headers_raw);
    foreach ($lines as $line) {
        if (stripos($line, 'Content-Type:') === 0) {
            header($line, true);
        }
        // skip set-cookie and other hop-by-hop headers for simplicity
    }

    http_response_code($status);
    echo $resp_body;
    return ['ok' => true, 'status' => $status];
}

$path = api_requested_path();

// Try each backend candidate until one responds (2xx or 4xx/5xx will be returned to client)
foreach (backend_candidates() as $backend) {
    $result = forward_request_to_backend($backend, $path);
    if ($result['ok']) {
        // forwarded successfully
        exit;
    }
}

// If we reach here none of the backends responded
http_response_code(502);
header('Content-Type: application/json');
echo json_encode(['error' => 'Bad gateway - backend unreachable']);
