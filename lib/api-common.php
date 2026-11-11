<?php
require_once __DIR__ . '/../config.php';
function api_response(int $status, array $data): void {
    http_response_code($status); echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE); exit;
}
function api_headers(): void {
    header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store'); header('X-Content-Type-Options: nosniff');
}
function api_body(): array {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { header('Allow: POST'); api_response(405, ['error'=>'Metode tidak didukung.']); }
    if (($_SERVER['HTTP_SEC_FETCH_SITE'] ?? '') === 'cross-site') api_response(403, ['error'=>'Permintaan tidak diizinkan.']);
    if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== 0) api_response(415, ['error'=>'Gunakan JSON.']);
    $raw = file_get_contents('php://input', false, null, 0, 8193);
    if (strlen($raw) > 8192) api_response(413, ['error'=>'Permintaan terlalu panjang.']);
    $body = json_decode($raw, true);
    if (!is_array($body)) api_response(400, ['error'=>'JSON tidak valid.']);
    return $body;
}
function service_request(string $path, array $body): array {
    $key = getenv('SUPABASE_SERVICE_ROLE_KEY');
    if (!$key) return [503, null];
    $ch = curl_init(SUPABASE_URL . '/rest/v1/' . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_POST=>true, CURLOPT_CONNECTTIMEOUT=>3, CURLOPT_TIMEOUT=>8,
        CURLOPT_HTTPHEADER=>['apikey: '.$key, 'Authorization: Bearer '.$key, 'Content-Type: application/json', str_starts_with($path, 'rpc/') ? 'Prefer: return=representation' : 'Prefer: return=minimal'], CURLOPT_POSTFIELDS=>json_encode($body)]);
    $raw = curl_exec($ch); $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    return [$status, json_decode($raw ?: 'null', true)];
}
function enforce_rate_limit(string $scope, int $limit, int $seconds): void {
    $secret = getenv('RATE_LIMIT_SECRET');
    if (!$secret || !getenv('SUPABASE_SERVICE_ROLE_KEY')) {
        $missing = array_values(array_filter(['RATE_LIMIT_SECRET','SUPABASE_SERVICE_ROLE_KEY'], fn($name)=>!getenv($name)));
        error_log('Portfolio configuration missing: '.implode(', ', $missing));
        api_response(503, ['error'=>'Layanan belum dikonfigurasi. Silakan gunakan kontak di website.', 'code'=>'configuration_missing']);
    }
    // On Vercel this header is platform-controlled. Else use the socket address.
    $ip = isset($_SERVER['VERCEL']) ? ($_SERVER['HTTP_X_VERCEL_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '') : ($_SERVER['REMOTE_ADDR'] ?? '');
    $ip = trim(explode(',', $ip)[0]);
    if (!filter_var($ip, FILTER_VALIDATE_IP)) api_response(503, ['error'=>'Identitas koneksi tidak tersedia.']);
    [$status, $allowed] = service_request('rpc/consume_public_rate_limit', ['bucket_key'=>hash_hmac('sha256',$scope.':'.$ip,$secret), 'max_requests'=>$limit, 'window_seconds'=>$seconds]);
    if ($status !== 200 || !is_bool($allowed)) {
        $dbCode = is_array($allowed) && is_string($allowed['code'] ?? null) ? preg_replace('/[^A-Z0-9_]/i','',$allowed['code']) : 'invalid_response';
        error_log('Portfolio rate limit RPC failed: HTTP '.$status.' code='.$dbCode);
        api_response(503, ['error'=>'Layanan sementara tidak tersedia.', 'code'=>'rate_limit_unavailable']);
    }
    if (!$allowed) { header('Retry-After: '.$seconds); api_response(429, ['error'=>'Terlalu banyak permintaan. Coba lagi nanti.']); }
}
