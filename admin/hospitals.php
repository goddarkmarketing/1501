<?php
$pageTitle = 'โรงพยาบาล';
require_once __DIR__ . '/includes/auth.php';

$jsonPath = SITE_ROOT . '/assets/data/hospital-locator.json';
$message = '';
$hospitalCount = 0;
$fileSize = 0;

if (file_exists($jsonPath)) {
    $fileSize = filesize($jsonPath);
    $raw = file_get_contents($jsonPath);
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $hospitalCount = count($decoded['hospitals'] ?? $decoded);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action']) && $_POST['action'] === 'upload') {
            if (empty($_FILES['json_file']['tmp_name'])) {
                throw new Exception('กรุณาเลือกไฟล์ JSON');
            }
            $uploaded = file_get_contents($_FILES['json_file']['tmp_name']);
            $test = json_decode($uploaded, true);
            if (!is_array($test)) {
                throw new Exception('ไฟล์ JSON ไม่ถูกต้อง');
            }
            $backup = $jsonPath . '.bak.' . date('YmdHis');
            if (file_exists($jsonPath)) {
                copy($jsonPath, $backup);
            }
            file_put_contents($jsonPath, $uploaded);
            $message = 'อัปโหลดไฟล์โรงพยาบาลเรียบร้อยแล้ว';
            header('Location: ' . ADMIN_URL . '/hospitals.php');
            $_SESSION['flash'] = $message;
            exit;
        }
    } catch (Throwable $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
<?= adminAlert($message, $messageType ?? 'error') ?>
<?php endif; ?>

<div class="grid lg:grid-cols-2 gap-6">
  <div class="admin-card p-6">
    <h3 class="font-semibold text-slate-800 mb-4">ข้อมูลปัจจุบัน</h3>
    <dl class="space-y-3 text-sm">
      <div class="flex justify-between"><dt class="text-slate-500">จำนวนโรงพยาบาล</dt><dd class="font-medium"><?= number_format($hospitalCount) ?></dd></div>
      <div class="flex justify-between"><dt class="text-slate-500">ขนาดไฟล์</dt><dd class="font-medium"><?= number_format($fileSize / 1024, 1) ?> KB</dd></div>
      <div class="flex justify-between"><dt class="text-slate-500">ไฟล์</dt><dd class="font-mono text-xs">assets/data/hospital-locator.json</dd></div>
    </dl>
    <a href="<?= SITE_URL ?>/hospitals.html" target="_blank" class="admin-btn-outline text-sm mt-6 inline-flex">ดูหน้าเว็บ ↗</a>
  </div>

  <div class="admin-card p-6">
    <h3 class="font-semibold text-slate-800 mb-4">อัปโหลดไฟล์ใหม่</h3>
    <p class="text-sm text-slate-500 mb-4">อัปโหลดไฟล์ JSON ใหม่เพื่อแทนที่ข้อมูลโรงพยาบาลทั้งหมด ระบบจะสำรองไฟล์เดิมไว้</p>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="upload">
      <input type="file" name="json_file" accept=".json,application/json" class="block w-full text-sm text-slate-600 mb-4" required>
      <button type="submit" class="admin-btn-primary" onclick="return confirm('แทนที่ข้อมูลโรงพยาบาลทั้งหมด?')">อัปโหลด JSON</button>
    </form>
  </div>
</div>

<p class="text-xs text-slate-400 mt-6">แก้ Hero หน้าโรงพยาบาลได้ที่ <a href="<?= ADMIN_URL ?>/page-edit.php?page=hospitals" class="text-brand hover:underline">จัดการหน้าเว็บ → โรงพยาบาล</a></p>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
