<?php
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/upload.php';
requireLogin();

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Method not allowed');
    }
    $subdir = preg_replace('/[^a-z0-9_-]/i', '', $_POST['folder'] ?? 'uploads') ?: 'uploads';
    $path = handleImageUpload($_FILES['image'] ?? null, $subdir);
    if (!$path) {
        throw new RuntimeException('ไม่พบไฟล์รูปภาพ');
    }
    echo json_encode([
        'ok' => true,
        'path' => $path,
        'url' => imagePublicUrl($path),
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
