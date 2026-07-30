<?php
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/feedback-lib.php';

if (!feedbackCanManage()) {
    http_response_code(403);
    echo 'Unauthorized';
    exit;
}

$type = $_GET['type'] ?? 'json';
$filters = [
    'page' => $_GET['page'] ?? '',
    'category' => $_GET['category'] ?? '',
    'priority' => $_GET['priority'] ?? '',
    'status' => $_GET['status'] ?? '',
];
$items = feedbackFilterItems(feedbackLoadItems(), $filters);

if ($type === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="feedback.json"');
    echo json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($type === 'markdown') {
    header('Content-Type: text/markdown; charset=utf-8');
    header('Content-Disposition: attachment; filename="FEEDBACK.md"');
    echo feedbackGenerateMarkdown($items);
    exit;
}

if ($type === 'screenshots') {
    if (!class_exists('ZipArchive')) {
        http_response_code(500);
        echo 'ZipArchive not available';
        exit;
    }
    $zip = new ZipArchive();
    $tmp = tempnam(sys_get_temp_dir(), 'fb');
    if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
        http_response_code(500);
        echo 'Cannot create zip';
        exit;
    }
    foreach ($items as $item) {
        if (empty($item['screenshot'])) {
            continue;
        }
        $path = feedbackBaseDir() . '/' . $item['screenshot'];
        if (is_file($path)) {
            $zip->addFile($path, basename($path));
        }
    }
    $zip->close();
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="feedback-screenshots.zip"');
    readfile($tmp);
    @unlink($tmp);
    exit;
}

if ($type === 'package') {
    if (!class_exists('ZipArchive')) {
        http_response_code(500);
        echo 'ZipArchive not available';
        exit;
    }
    $zip = new ZipArchive();
    $tmp = tempnam(sys_get_temp_dir(), 'fbpkg');
    if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
        http_response_code(500);
        echo 'Cannot create zip';
        exit;
    }
    $zip->addFromString('feedback.json', json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $zip->addFromString('FEEDBACK.md', feedbackGenerateMarkdown($items));
    foreach ($items as $item) {
        if (empty($item['screenshot'])) {
            continue;
        }
        $path = feedbackBaseDir() . '/' . $item['screenshot'];
        if (is_file($path)) {
            $zip->addFile($path, 'images/' . basename($path));
        }
    }
    $zip->close();
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="feedback-package.zip"');
    readfile($tmp);
    @unlink($tmp);
    exit;
}

http_response_code(400);
echo 'Unknown export type';
