<?php
/**
 * นำเข้าข้อมูลเริ่มต้นจากไฟล์ JS ปัจจุบันเข้าฐานข้อมูล (รันครั้งเดียวหลัง install)
 */
$pageTitle = 'นำเข้าข้อมูลเริ่มต้น';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
requireLogin();
require_once __DIR__ . '/includes/header.php';

$message = '';
$error = '';

$categories = require __DIR__ . '/seed-categories.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_once __DIR__ . '/includes/seed-import.php';
        $stats = runFullSeedImport($categories);
        $message = sprintf(
            'นำเข้าข้อมูลเรียบร้อย: แผนประกัน %d รายการ, บทความ %d รายการ, โปรโมชัน %d รายการ',
            $stats['plans'],
            $stats['blogs'],
            $stats['promos']
        );
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>

<?php if ($message): ?>
<div class="bg-emerald-900/30 border border-emerald-800 text-emerald-300 rounded-lg p-4 mb-6 text-sm"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="bg-red-900/30 border border-red-800 text-red-300 rounded-lg p-4 mb-6 text-sm"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="bg-gray-900 border border-gray-800 rounded-xl p-6 max-w-2xl">
  <h3 class="text-white font-semibold mb-2">นำเข้าข้อมูลจากไฟล์ JS ปัจจุบัน</h3>
  <p class="text-gray-400 text-sm mb-6">ใช้ครั้งแรกหลังติดตั้งฐานข้อมูล เพื่อโหลดหมวดหมู่ แผนประกัน บทความ และโปรโมชันจากไฟล์ <code class="text-gray-300">assets/js/*-data.js</code> เข้า MySQL</p>
  <form method="POST" onsubmit="return confirm('นำเข้าข้อมูล? ข้อมูลเดิมในตารางที่ซ้ำจะถูกแทนที่');">
    <button type="submit" class="admin-btn-primary px-6 py-3 text-sm">เริ่มนำเข้าข้อมูล</button>
  </form>
  <p class="text-gray-500 text-xs mt-4">หลังนำเข้าแล้ว แก้ไขใน Admin แล้วกด <strong class="text-emerald-400">เผยแพร่เว็บไซต์</strong> ในเมนูซ้าย</p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
