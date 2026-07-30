<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/feedback-lib.php';

if (feedbackPreviewRequested()) {
    feedbackActivatePreview();
}

if (!feedbackCanUseTool()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'ไม่มีสิทธิ์ใช้งาน Feedback Mode']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
    exit;
}

$comment = trim((string) ($payload['comment'] ?? ''));
if ($comment === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'กรุณากรอกรายละเอียด']);
    exit;
}

try {
    $item = feedbackAddItem($payload);
    echo json_encode(['ok' => true, 'item' => $item]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'บันทึกไม่สำเร็จ']);
}
