<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

require_once dirname(__DIR__) . '/includes/db.php';

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
    exit;
}

$name = trim($payload['name'] ?? '');
$phone = trim($payload['phone'] ?? '');
$email = trim($payload['email'] ?? '');

if ($name === '' && $phone === '' && $email === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'กรุณากรอกชื่อหรือเบอร์โทร']);
    exit;
}

try {
    query(
        'INSERT INTO contact_submissions (name, phone, email, insurance_type, province, age, callback_time, message, status) VALUES (?,?,?,?,?,?,?,?,\'new\')',
        [
            $name,
            $phone,
            $email,
            trim($payload['insurance_type'] ?? ''),
            trim($payload['province'] ?? ''),
            (int) ($payload['age'] ?? 0) ?: null,
            trim($payload['callback_time'] ?? ''),
            trim($payload['message'] ?? ''),
        ]
    );
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'บันทึกไม่สำเร็จ']);
}
