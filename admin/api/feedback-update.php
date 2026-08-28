<?php
header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/feedback-lib.php';

if (!feedbackCanManage()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
    exit;
}

$action = $payload['action'] ?? '';
$id = trim((string) ($payload['id'] ?? ''));

if ($action === 'tool_enabled') {
    $enabled = !empty($payload['enabled']);
    feedbackSetToolEnabled($enabled);
    echo json_encode(['ok' => true, 'enabled' => $enabled]);
    exit;
}

if ($id === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing id']);
    exit;
}

if ($action === 'status') {
    $status = trim((string) ($payload['status'] ?? ''));
    if (!feedbackUpdateStatus($id, $status)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'อัปเดตไม่สำเร็จ']);
        exit;
    }
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'delete') {
    if (!feedbackDeleteItem($id)) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'ไม่พบรายการ']);
        exit;
    }
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['ok' => false, 'error' => 'Unknown action']);
