<?php
require_once __DIR__ . '/../lib/api-common.php';
api_headers(); $body = api_body();
$name = $body['name'] ?? null; $message = $body['message'] ?? null; $rating = $body['rating'] ?? null;
if (!is_string($name) || !is_string($message) || !is_int($rating) || $rating < 1 || $rating > 5 || mb_strlen(trim($name)) < 1 || mb_strlen($name)>30 || mb_strlen(trim($message))<1 || mb_strlen($message)>200) api_response(400, ['error'=>'Nama, pesan, atau rating tidak valid.']);
if (!empty($body['website'])) api_response(400, ['error'=>'Permintaan tidak valid.']);
enforce_rate_limit('guestbook', 1, 60);
[$status] = service_request('guestbook', ['name'=>trim($name),'message'=>trim($message),'rating'=>$rating,'is_approved'=>false]);
if ($status < 200 || $status >= 300) api_response(502, ['error'=>'Pesan belum berhasil disimpan. Silakan coba lagi.']);
api_response(201, ['success'=>true]);
