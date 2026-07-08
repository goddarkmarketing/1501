<?php
$pageTitle = 'แผนประกัน';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/upload.php';

$message = '';
$messageType = '';

if (!empty($_GET['success'])) {
    $message = $_GET['success'];
    $messageType = 'success';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    try {
        query('DELETE FROM plan_products WHERE id = ?', [$_POST['delete_id']]);
        $message = 'ลบแผนประกันเรียบร้อยแล้ว';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        $messageType = 'error';
    }
}

$plans = [];
$dbError = false;
try {
    $plans = fetchAll('SELECT p.*, c.label as category_label FROM plan_products p LEFT JOIN plan_categories c ON p.category_id = c.id ORDER BY p.sort_order, p.name');
} catch (Exception $e) {
    $dbError = true;
}

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
<?= adminAlert($message, $messageType) ?>
<?php endif; ?>

<div class="flex items-center justify-between mb-6">
  <p class="text-sm text-slate-500">แก้ไขแผนประกันและรูปภาพ — กดเผยแพร่หลังบันทึก</p>
  <a href="<?= ADMIN_URL ?>/plan-edit.php" class="admin-btn-primary">+ เพิ่มแผนประกัน</a>
</div>

<?php if ($dbError): ?>
<div class="admin-card p-8 text-center">
  <p class="text-slate-500 mb-2">กรุณาติดตั้งฐานข้อมูลก่อน</p>
  <a href="<?= ADMIN_URL ?>/install.php" class="text-brand text-sm hover:underline">ไปหน้าติดตั้ง →</a>
</div>
<?php elseif (empty($plans)): ?>
<div class="admin-card p-12 text-center text-slate-400">
  ยังไม่มีแผนประกัน — <a href="<?= ADMIN_URL ?>/seed.php" class="text-brand hover:underline">นำเข้าข้อมูลเริ่มต้น</a>
</div>
<?php else: ?>
<div class="admin-card overflow-hidden">
  <table class="admin-table w-full">
    <thead>
      <tr>
        <th class="w-16">รูป</th>
        <th>ชื่อแผน</th>
        <th>หมวดหมู่</th>
        <th>ราคาเริ่มต้น</th>
        <th class="text-center">สถานะ</th>
        <th class="text-right">จัดการ</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($plans as $plan): ?>
      <tr>
        <td>
          <?php if (!empty($plan['hero_image'])): ?>
          <img src="<?= htmlspecialchars(imagePublicUrl($plan['hero_image'])) ?>" alt="" class="admin-plan-thumb">
          <?php else: ?>
          <span class="text-xs text-slate-300">—</span>
          <?php endif; ?>
        </td>
        <td>
          <p class="font-medium text-slate-800"><?= htmlspecialchars($plan['name']) ?></p>
          <p class="text-xs text-slate-400"><?= htmlspecialchars($plan['id']) ?></p>
        </td>
        <td class="text-slate-600"><?= htmlspecialchars($plan['category_label'] ?? $plan['category_id']) ?></td>
        <td class="text-slate-700"><?= number_format($plan['price_from'], 0) ?> ฿</td>
        <td class="text-center">
          <?php if ($plan['is_active']): ?>
          <span class="admin-badge-green">เปิด</span>
          <?php else: ?>
          <span class="admin-badge-red">ปิด</span>
          <?php endif; ?>
        </td>
        <td class="text-right whitespace-nowrap">
          <a href="<?= ADMIN_URL ?>/plan-edit.php?id=<?= urlencode($plan['id']) ?>" class="text-brand text-sm hover:underline mr-3">แก้ไข</a>
          <form method="POST" class="inline" onsubmit="return confirm('ลบแผนนี้?')">
            <input type="hidden" name="delete_id" value="<?= htmlspecialchars($plan['id']) ?>">
            <button type="submit" class="text-red-500 text-sm hover:underline">ลบ</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
