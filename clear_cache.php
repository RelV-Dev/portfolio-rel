<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';

if (file_exists(CACHE_FILE)) {
    if (unlink(CACHE_FILE)) {
        echo json_encode(['success' => true, 'message' => 'Cache cleared successfully.']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete cache file.']);
    }
} else {
    echo json_encode(['success' => true, 'message' => 'Cache file does not exist, nothing to clear.']);
}
?>
