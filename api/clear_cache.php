<?php
header('Content-Type: application/json');
header('Cache-Control: no-store');
http_response_code(410);
echo json_encode(['success'=>false, 'message'=>'Endpoint ini tidak digunakan lagi. Halaman mengambil galeri terbaru pada setiap permintaan.']);
