<?php
declare(strict_types=1);

function backupDir(): string {
    return ADMIN_ROOT . '/data/backups';
}

function backupEnsureDir(): void {
    $dir = backupDir();
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('สร้างโฟลเดอร์แบ็คอัพไม่ได้');
    }
}

function backupContentTables(): array {
    return [
        'plan_categories' => 'หมวดหมู่แผน',
        'plan_category_features' => 'จุดเด่นหมวดหมู่',
        'plan_products' => 'แผนประกัน',
        'plan_benefits' => 'สิทธิประโยชน์แผน',
        'plan_tiers' => 'ระดับความคุ้มครอง',
        'plan_highlights' => 'ไฮไลต์แผน',
        'plan_coverage_summary' => 'สรุปความคุ้มครอง',
        'plan_coverage_rows' => 'ตารางความคุ้มครอง',
        'plan_conditions' => 'เงื่อนไขแผน',
        'plan_faqs' => 'FAQ ของแผน',
        'plan_renewal' => 'การต่ออายุ',
        'plan_why' => 'เหตุผลของแผน',
        'blog_articles' => 'บทความ',
        'blog_tags' => 'แท็กบทความ',
        'blog_content' => 'เนื้อหาบทความ',
        'promotions' => 'โปรโมชัน',
        'promotion_bullets' => 'รายละเอียดโปรโมชัน',
        'promotion_filter_items' => 'ตัวกรองโปรโมชัน',
        'faq_items' => 'คำถามที่พบบ่อย',
        'page_sections' => 'เนื้อหาแต่ละหน้า',
        'cms_blocks' => 'บล็อกเนื้อหา',
        'site_settings' => 'ตั้งค่าเว็บไซต์',
        'contact_submissions' => 'ข้อความติดต่อ',
    ];
}

function backupPublishedFiles(): array {
    return [
        'assets/data/hospital-locator.json',
        'assets/js/site-content.js',
        'assets/js/plan-data.js',
        'assets/js/blog-data.js',
        'assets/js/promotion-data.js',
    ];
}

function backupTableExists(string $table): bool {
    if (!preg_match('/^[a-z0-9_]+$/', $table)) {
        return false;
    }
    $row = fetchOne(
        'SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
        [$table]
    );
    return (int) ($row['c'] ?? 0) > 0;
}

function backupDumpTables(): array {
    $dump = [];
    foreach (backupContentTables() as $table => $label) {
        if (!backupTableExists($table)) {
            continue;
        }
        $dump[$table] = fetchAll('SELECT * FROM `' . $table . '`');
    }
    return $dump;
}

function backupCollectAssetPaths(mixed $value, array &$paths): void {
    if (is_array($value)) {
        foreach ($value as $item) {
            backupCollectAssetPaths($item, $paths);
        }
        return;
    }
    if (!is_string($value) || $value === '') {
        return;
    }
    if (!preg_match_all('#assets/(?:img|data)/[^"\'\s<>\\\\]+#u', $value, $matches)) {
        return;
    }
    foreach ($matches[0] as $path) {
        $path = rawurldecode(strtok($path, '?'));
        $path = rtrim($path, '.,);');
        $paths[$path] = true;
    }
}

function backupResolveInsideRoot(string $relative): ?string {
    $relative = str_replace('\\', '/', $relative);
    if ($relative === '' || str_contains($relative, '..')) {
        return null;
    }
    if (!preg_match('#^assets/(?:img/.+|data/hospital-locator\.json|js/(?:site-content|plan-data|blog-data|promotion-data)\.js)$#u', $relative)) {
        return null;
    }
    $full = SITE_ROOT . '/' . $relative;
    if (!is_file($full)) {
        return null;
    }
    $real = realpath($full);
    $root = realpath(SITE_ROOT);
    if ($real === false || $root === false) {
        return null;
    }
    $realNorm = strtolower(str_replace('\\', '/', $real));
    $rootNorm = rtrim(strtolower(str_replace('\\', '/', $root)), '/') . '/';
    if (!str_starts_with($realNorm, $rootNorm)) {
        return null;
    }
    return $real;
}

function backupValidName(string $name): bool {
    return (bool) preg_match('/^backup-\d{8}-\d{6}-[a-f0-9]{8}\.zip$/', $name);
}

function backupPath(string $name): string {
    if (!backupValidName($name)) {
        throw new RuntimeException('ชื่อไฟล์แบ็คอัพไม่ถูกต้อง');
    }
    return backupDir() . '/' . $name;
}

function backupPrune(int $keep = 20): void {
    $files = glob(backupDir() . '/backup-*.zip') ?: [];
    usort($files, static fn($a, $b) => filemtime($b) <=> filemtime($a));
    foreach (array_slice($files, $keep) as $old) {
        @unlink($old);
    }
}

function backupCreate(): array {
    if (!class_exists('ZipArchive')) {
        throw new RuntimeException('เซิร์ฟเวอร์ไม่มี ZipArchive — เปิดส่วนขยาย zip ใน PHP ก่อน');
    }
    backupEnsureDir();
    $tables = backupDumpTables();
    $paths = [];
    backupCollectAssetPaths($tables, $paths);
    foreach (backupPublishedFiles() as $relative) {
        $paths[$relative] = true;
    }

    $files = [];
    foreach (array_keys($paths) as $relative) {
        $full = backupResolveInsideRoot($relative);
        if ($full !== null) {
            $files[$relative] = $full;
        }
    }

    $counts = [];
    foreach ($tables as $table => $rows) {
        $counts[$table] = count($rows);
    }
    $manifest = [
        'version' => 1,
        'app' => 'agent-thailand',
        'createdAt' => date('c'),
        'counts' => $counts,
        'files' => array_keys($files),
    ];

    $name = 'backup-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.zip';
    $dest = backupDir() . '/' . $name;
    $zip = new ZipArchive();
    if ($zip->open($dest, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new RuntimeException('สร้างไฟล์แบ็คอัพไม่ได้');
    }
    $zip->addFromString(
        'manifest.json',
        json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    );
    $zip->addFromString(
        'data.json',
        json_encode(['tables' => $tables], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    );
    foreach ($files as $relative => $full) {
        $zip->addFile($full, 'files/' . $relative);
    }
    $zip->close();
    backupPrune();

    return [
        'name' => $name,
        'bytes' => filesize($dest) ?: 0,
        'counts' => $counts,
        'fileCount' => count($files),
    ];
}

function backupList(): array {
    backupEnsureDir();
    $files = glob(backupDir() . '/backup-*.zip') ?: [];
    usort($files, static fn($a, $b) => filemtime($b) <=> filemtime($a));
    $items = [];
    foreach ($files as $path) {
        $name = basename($path);
        if (!backupValidName($name)) {
            continue;
        }
        $manifest = backupReadManifest($path);
        $items[] = [
            'name' => $name,
            'bytes' => filesize($path) ?: 0,
            'createdAt' => $manifest['createdAt'] ?? date('c', filemtime($path) ?: time()),
            'counts' => is_array($manifest['counts'] ?? null) ? $manifest['counts'] : [],
            'fileCount' => is_array($manifest['files'] ?? null) ? count($manifest['files']) : 0,
        ];
    }
    return $items;
}

function backupReadManifest(string $path): array {
    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        return [];
    }
    $raw = $zip->getFromName('manifest.json');
    $zip->close();
    $data = json_decode($raw ?: '', true);
    return is_array($data) ? $data : [];
}

function backupDelete(string $name): void {
    $path = backupPath($name);
    if (!is_file($path) || !unlink($path)) {
        throw new RuntimeException('ลบแบ็คอัพไม่ได้');
    }
}

function backupFormatBytes(int $bytes): string {
    if ($bytes < 1024) {
        return $bytes . ' B';
    }
    if ($bytes < 1024 * 1024) {
        return number_format($bytes / 1024, 1) . ' KB';
    }
    return number_format($bytes / (1024 * 1024), 1) . ' MB';
}

function backupRestore(string $name): array {
    $path = backupPath($name);
    if (!is_file($path)) {
        throw new RuntimeException('ไม่พบไฟล์แบ็คอัพ');
    }
    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        throw new RuntimeException('เปิดไฟล์แบ็คอัพไม่ได้');
    }
    $raw = $zip->getFromName('data.json');
    $payload = json_decode($raw ?: '', true);
    $tables = $payload['tables'] ?? null;
    if (!is_array($tables)) {
        $zip->close();
        throw new RuntimeException('ไฟล์แบ็คอัพไม่ครบ');
    }

    $allowed = backupContentTables();
    $restored = [];
    $pdo = getDB();
    $pdo->beginTransaction();
    try {
        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        foreach ($tables as $table => $rows) {
            if (!isset($allowed[$table]) || !is_array($rows) || !backupTableExists($table)) {
                continue;
            }
            $pdo->exec('DELETE FROM `' . $table . '`');
            if ($rows) {
                backupInsertRows($pdo, $table, $rows);
            }
            $restored[$table] = count($rows);
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        try {
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
        } catch (Throwable $ignored) {
        }
        $zip->close();
        throw new RuntimeException('กู้คืนฐานข้อมูลไม่สำเร็จ: ' . $e->getMessage(), 0, $e);
    }

    $fileCount = 0;
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entry = $zip->getNameIndex($i);
        if (!is_string($entry) || !str_starts_with($entry, 'files/')) {
            continue;
        }
        $relative = substr($entry, strlen('files/'));
        $dest = backupRestoreDestination($relative);
        if ($dest === null) {
            continue;
        }
        $dir = dirname($dest);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            continue;
        }
        $contents = $zip->getFromIndex($i);
        if ($contents === false) {
            continue;
        }
        if (file_put_contents($dest, $contents) !== false) {
            $fileCount++;
        }
    }
    $zip->close();

    return ['tables' => $restored, 'fileCount' => $fileCount];
}

function backupInsertRows(PDO $pdo, string $table, array $rows): void {
    $columns = array_keys($rows[0]);
    foreach ($columns as $column) {
        if (!preg_match('/^[a-z0-9_]+$/', $column)) {
            throw new RuntimeException('คอลัมน์ในแบ็คอัพไม่ถูกต้อง');
        }
    }
    $quoted = '`' . implode('`,`', $columns) . '`';
    $placeholders = implode(',', array_fill(0, count($columns), '?'));
    $sql = 'INSERT INTO `' . $table . '` (' . $quoted . ') VALUES (' . $placeholders . ')';
    $stmt = $pdo->prepare($sql);
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $values = [];
        foreach ($columns as $column) {
            $values[] = $row[$column] ?? null;
        }
        $stmt->execute($values);
    }
}

function backupRestoreDestination(string $relative): ?string {
    $relative = str_replace('\\', '/', $relative);
    if ($relative === '' || str_contains($relative, '..')) {
        return null;
    }
    if (!preg_match('#^assets/(?:img/.+|data/hospital-locator\.json|js/(?:site-content|plan-data|blog-data|promotion-data)\.js)$#u', $relative)) {
        return null;
    }
    return SITE_ROOT . '/' . $relative;
}
