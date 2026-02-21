<?php
// Backend candidates tried by the proxy (order matters)
function backend_candidates(): array {
    // If we are in a Docker environment, 'backend' is the primary hostname.
    // If not (e.g. running via XAMPP host), 'localhost' or '127.0.0.1' is used.
    return [
        'http://backend:8000',
        'http://localhost:8000',
        'http://127.0.0.1:8000'
    ];
}

// Helper to get the requested path (strip the /api prefix)
function api_requested_path(): string {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    // remove query string
    $uri = explode('?', $uri, 2)[0];
    // strip leading /api
    $path = preg_replace('#^/api#', '', $uri);
    return $path ?: '/';
}

// Server-side helper to request backend candidates directly (bypasses proxy)
function backend_request(string $method, string $path, $data = null, bool $isMultipart = false) : array {
    $candidates = backend_candidates();
    foreach ($candidates as $base) {
        $url = rtrim($base, '/') . $path;
        $ch = curl_init();
        
        $headers = [];
        if (!$isMultipart) {
            $headers[] = 'Content-Type: application/json';
        }
        
        $opts = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_CONNECTTIMEOUT => 1,
        ];
        if ($data !== null && in_array(strtoupper($method), ['POST','PUT','PATCH'])) {
            if ($isMultipart) {
                $opts[CURLOPT_POSTFIELDS] = $data;
            } else {
                $opts[CURLOPT_POSTFIELDS] = json_encode($data);
            }
        }
        curl_setopt_array($ch, $opts);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($resp === false) {
            // try next candidate
            continue;
        }

        return [
            'data' => json_decode($resp, true),
            'status' => $status ?: 200,
            'error' => $err,
            'raw_response' => $resp,
            'candidate' => $base
        ];
    }

    return ['data' => null, 'status' => 0, 'error' => 'no-backend-reachable', 'raw_response' => null];
}
