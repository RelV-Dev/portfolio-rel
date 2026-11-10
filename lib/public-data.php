<?php
require_once __DIR__ . '/../config.php';
function portfolio_gallery(): ?array {
    $ch = curl_init(SUPABASE_URL . '/rest/v1/portfolio_gallery?select=*&is_visible=eq.true&order=sort_order.asc,created_at.desc');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_CONNECTTIMEOUT=>2, CURLOPT_TIMEOUT=>5,
        CURLOPT_HTTPHEADER=>['apikey: '.SUPABASE_KEY, 'Authorization: Bearer '.SUPABASE_KEY]]);
    $raw = curl_exec($ch); $status = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    $rows = $status === 200 ? json_decode($raw ?: '', true) : null;
    return is_array($rows) ? $rows : null;
}
function public_url($url): string {
    if (!is_string($url) || preg_match('/[\x00-\x20]/', $url)) return '';
    return filter_var($url, FILTER_VALIDATE_URL) && in_array(strtolower(parse_url($url, PHP_URL_SCHEME) ?? ''), ['https','http'], true) ? $url : '';
}
