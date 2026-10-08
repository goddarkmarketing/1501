<?php
require_once dirname(__DIR__) . '/includes/auth.php';
requireLogin();
require_once dirname(__DIR__) . '/includes/backup-lib.php';

$name = (string) ($_GET['file'] ?? '');
try {
    $path = backupPath($name);
} catch (Throwable $e) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'ชื่อไฟล์ไม่ถูกต้อง';
    exit;
}

if (!is_file($path)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'ไม่พบไฟล์';
    exit;
}

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $name . '"');
header('Content-Length: ' . (string) filesize($path));
header('X-Content-Type-Options: nosniff');
readfile($path);
