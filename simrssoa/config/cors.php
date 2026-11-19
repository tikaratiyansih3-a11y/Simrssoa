<?php
// ========================================================
// SERVER B: CORS & JSON RESPONSE HELPER
// ========================================================

// Mengizinkan akses dari semua origin / Server A
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-API-KEY");

// Tangani Preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

function sendResponse($status, $statusCode, $message, $data = null) {
    http_response_code($statusCode);
    $response = [
        "status"    => $status,
        "code"      => $statusCode,
        "message"   => $message,
        "timestamp" => date('Y-m-d H:i:s')
    ];
    if ($data !== null) {
        $response["data"] = $data;
    }
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

function getJsonInput() {
    $content = file_get_contents("php://input");
    $data = json_decode($content, true);
    if (!is_array($data)) {
        $data = $_POST;
    }
    return $data;
}
