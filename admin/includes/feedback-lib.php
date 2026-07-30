<?php
declare(strict_types=1);

function feedbackBaseDir(): string {
    return ADMIN_ROOT . '/data/feedback';
}

function feedbackJsonPath(): string {
    return feedbackBaseDir() . '/feedback.json';
}

function feedbackImagesDir(): string {
    return feedbackBaseDir() . '/images';
}

function feedbackEnsureDirs(): void {
    $base = feedbackBaseDir();
    if (!is_dir($base)) {
        mkdir($base, 0755, true);
    }
    $images = feedbackImagesDir();
    if (!is_dir($images)) {
        mkdir($images, 0755, true);
    }
    $json = feedbackJsonPath();
    if (!is_file($json)) {
        file_put_contents($json, "[]\n");
    }
}

function feedbackPreviewRequested(): bool {
    return isset($_GET['feedback_preview']) && $_GET['feedback_preview'] === '1';
}

function feedbackPreviewActive(): bool {
    return !empty($_SESSION['feedback_preview']);
}

function feedbackActivatePreview(): void {
    $_SESSION['feedback_preview'] = true;
}

function isFeedbackReview(): bool {
    return !empty($_SESSION['feedback_review']);
}

function feedbackCanUseTool(): bool {
    // Logged-in admins can always report; clients need review session
    return isLoggedIn() || isFeedbackReview();
}

function feedbackCanManage(): bool {
    return isLoggedIn();
}

function feedbackReviewToken(): string {
    $stored = feedbackGetSetting('feedback_review_token', '');
    if ($stored !== '') {
        return $stored;
    }
    $token = bin2hex(random_bytes(16));
    feedbackSetSetting('feedback_review_token', $token);
    return $token;
}

function feedbackReviewPasswordHash(): string {
    $hash = feedbackGetSetting('feedback_review_password', '');
    if ($hash !== '') {
        return $hash;
    }
    $default = password_hash('review123', PASSWORD_DEFAULT);
    feedbackSetSetting('feedback_review_password', $default);
    return $default;
}

function feedbackVerifyReviewPassword(string $password): bool {
    return password_verify($password, feedbackReviewPasswordHash());
}

function feedbackSetReviewPassword(string $password): void {
    feedbackSetSetting('feedback_review_password', password_hash($password, PASSWORD_DEFAULT));
}

function feedbackGetSetting(string $key, string $default = ''): string {
    try {
        $row = fetchOne('SELECT setting_value FROM site_settings WHERE setting_key = ?', [$key]);
        if ($row && isset($row['setting_value'])) {
            return (string) $row['setting_value'];
        }
    } catch (Throwable $e) {
        // fall through
    }
    return $default;
}

function feedbackSetSetting(string $key, string $value): void {
    try {
        $exists = fetchOne('SELECT setting_key FROM site_settings WHERE setting_key = ?', [$key]);
        if ($exists) {
            query('UPDATE site_settings SET setting_value = ? WHERE setting_key = ?', [$value, $key]);
        } else {
            query(
                'INSERT INTO site_settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?)',
                [$key, $value, 'feedback']
            );
        }
    } catch (Throwable $e) {
        // ignore if DB unavailable
    }
}

function feedbackLoadItems(): array {
    feedbackEnsureDirs();
    $raw = file_get_contents(feedbackJsonPath());
    $data = json_decode($raw ?: '[]', true);
    return is_array($data) ? $data : [];
}

function feedbackSaveItems(array $items): void {
    feedbackEnsureDirs();
    file_put_contents(
        feedbackJsonPath(),
        json_encode(array_values($items), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n"
    );
}

function feedbackNextId(array $items): string {
    $max = 0;
    foreach ($items as $item) {
        if (!empty($item['id']) && preg_match('/^FB-(\d+)$/', $item['id'], $m)) {
            $max = max($max, (int) $m[1]);
        }
    }
    return 'FB-' . str_pad((string) ($max + 1), 3, '0', STR_PAD_LEFT);
}

function feedbackCategories(): array {
    return [
        'text' => 'ข้อความ',
        'image' => 'รูปภาพ',
        'layout' => 'Layout',
        'color' => 'สี',
        'function' => 'ฟังก์ชัน',
        'mobile' => 'Mobile',
        'other' => 'อื่น ๆ',
    ];
}

function feedbackPriorities(): array {
    return [
        'low' => 'ต่ำ',
        'medium' => 'ปานกลาง',
        'high' => 'สูง',
    ];
}

function feedbackStatuses(): array {
    return [
        'pending' => 'รอดำเนินการ',
        'in-progress' => 'กำลังดำเนินการ',
        'completed' => 'เสร็จสิ้น',
        'rejected' => 'ปฏิเสธ',
    ];
}

function feedbackAddItem(array $payload): array {
    $items = feedbackLoadItems();
    $preId = trim((string) ($payload['id'] ?? ''));
    if ($preId !== '' && preg_match('/^FB-\d{3}$/', $preId)) {
        $taken = false;
        foreach ($items as $existing) {
            if (($existing['id'] ?? '') === $preId) {
                $taken = true;
                break;
            }
        }
        $id = $taken ? feedbackNextId($items) : $preId;
    } else {
        $id = feedbackNextId($items);
    }

    $screenshotData = $payload['screenshot'] ?? '';
    $screenshotFile = '';
    if (is_string($screenshotData) && strpos($screenshotData, 'data:image/png;base64,') === 0) {
        $bin = base64_decode(substr($screenshotData, 22), true);
        if ($bin !== false) {
            feedbackEnsureDirs();
            $screenshotFile = 'images/' . $id . '.png';
            file_put_contents(feedbackBaseDir() . '/' . $screenshotFile, $bin);
        }
    }

    $item = [
        'id' => $id,
        'page' => trim((string) ($payload['page'] ?? '')),
        'url' => trim((string) ($payload['url'] ?? '')),
        'route' => trim((string) ($payload['route'] ?? '')),
        'section' => trim((string) ($payload['section'] ?? '')),
        'feedbackId' => trim((string) ($payload['feedbackId'] ?? '')),
        'selector' => trim((string) ($payload['selector'] ?? '')),
        'elementText' => trim((string) ($payload['elementText'] ?? '')),
        'componentName' => trim((string) ($payload['componentName'] ?? '')),
        'comment' => trim((string) ($payload['comment'] ?? '')),
        'category' => trim((string) ($payload['category'] ?? 'other')),
        'priority' => trim((string) ($payload['priority'] ?? 'medium')),
        'clientName' => trim((string) ($payload['clientName'] ?? '')),
        'status' => 'pending',
        'screenshot' => $screenshotFile,
        'boundingRect' => $payload['boundingRect'] ?? null,
        'viewport' => $payload['viewport'] ?? ['width' => 0, 'height' => 0],
        'scrollPosition' => $payload['scrollPosition'] ?? ['x' => 0, 'y' => 0],
        'createdAt' => date('c'),
    ];

    $items[] = $item;
    feedbackSaveItems($items);
    return $item;
}

function feedbackUpdateStatus(string $id, string $status): bool {
    if (!isset(feedbackStatuses()[$status])) {
        return false;
    }
    $items = feedbackLoadItems();
    $found = false;
    foreach ($items as &$item) {
        if (($item['id'] ?? '') === $id) {
            $item['status'] = $status;
            $found = true;
            break;
        }
    }
    unset($item);
    if ($found) {
        feedbackSaveItems($items);
    }
    return $found;
}

function feedbackDeleteItem(string $id): bool {
    $items = feedbackLoadItems();
    $new = [];
    $deleted = false;
    foreach ($items as $item) {
        if (($item['id'] ?? '') === $id) {
            $deleted = true;
            if (!empty($item['screenshot'])) {
                $path = feedbackBaseDir() . '/' . $item['screenshot'];
                if (is_file($path)) {
                    @unlink($path);
                }
            }
            continue;
        }
        $new[] = $item;
    }
    if ($deleted) {
        feedbackSaveItems($new);
    }
    return $deleted;
}

function feedbackFilterItems(array $items, array $filters): array {
    return array_values(array_filter($items, static function ($item) use ($filters) {
        foreach (['page', 'category', 'priority', 'status'] as $key) {
            if (!empty($filters[$key]) && ($item[$key] ?? '') !== $filters[$key]) {
                return false;
            }
        }
        return true;
    }));
}

function feedbackGenerateMarkdown(array $items): string {
    $lines = ["# รายการแก้ไขจากลูกค้า", ''];
    $cats = feedbackCategories();
    $prios = feedbackPriorities();
    $stats = feedbackStatuses();

    foreach ($items as $item) {
        $id = $item['id'] ?? '';
        $lines[] = '## ' . $id;
        $lines[] = '- หน้า: ' . ($item['page'] ?? '');
        $lines[] = '- URL: ' . ($item['url'] ?? '');
        $lines[] = '- ส่วน: ' . ($item['section'] ?? $item['feedbackId'] ?? '');
        $lines[] = '- Selector: `' . ($item['selector'] ?? '') . '`';
        $cat = $item['category'] ?? '';
        $lines[] = '- ประเภท: ' . ($cats[$cat] ?? $cat);
        $prio = $item['priority'] ?? '';
        $lines[] = '- ความสำคัญ: ' . ($prios[$prio] ?? $prio);
        $st = $item['status'] ?? '';
        $lines[] = '- สถานะ: ' . ($stats[$st] ?? $st);
        if (!empty($item['clientName'])) {
            $lines[] = '- ผู้แจ้ง: ' . $item['clientName'];
        }
        $lines[] = '';
        $lines[] = '### รายละเอียด';
        $lines[] = $item['comment'] ?? '';
        $lines[] = '';
        if (!empty($item['screenshot'])) {
            $img = basename($item['screenshot']);
            $lines[] = '### รูปภาพ';
            $lines[] = '![' . $id . '](./images/' . $img . ')';
            $lines[] = '';
        }
    }

    return implode("\n", $lines) . "\n";
}

function feedbackReviewUrl(): string {
    return ADMIN_URL . '/feedback-review.php?token=' . urlencode(feedbackReviewToken());
}

function feedbackAppendPreview(string $url): string {
    if (!feedbackPreviewActive()) {
        return $url;
    }
    return $url . (strpos($url, '?') === false ? '?' : '&') . 'feedback_preview=1';
}
