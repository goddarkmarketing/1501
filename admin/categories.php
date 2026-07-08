<?php
$pageTitle = 'หมวดหมู่แผน';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/upload.php';

$categories = [];
$dbError = false;
try {
    $categories = fetchAll('SELECT * FROM plan_categories ORDER BY sort_order, id');
} catch (Exception $e) {
    $dbError = true;
}

require_once __DIR__ . '/includes/header.php';
?>

<p class="text-sm text-slate-500 mb-6">จัดการรูปและข้อความของแต่ละหมวดหมู่แผนประกัน (แสดงบนหน้า plan-category)</p>

<?php if ($dbError): ?>
<div class="admin-card p-8 text-center text-slate-500">กรุณาติดตั้งฐานข้อมูลก่อน</div>
<?php else: ?>
<div class="admin-card overflow-hidden">
  <table class="admin-table w-full">
    <thead>
      <tr>
        <th class="w-16">รูป</th>
        <th>หมวดหมู่</th>
        <th>หัวข้อ</th>
        <th class="text-right w-28">จัดการ</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($categories as $cat): ?>
      <tr>
        <td>
          <?php if (!empty($cat['hero_image'])): ?>
          <img src="<?= htmlspecialchars(imagePublicUrl($cat['hero_image'])) ?>" alt="" class="admin-plan-thumb">
          <?php else: ?>
          <span class="text-xs text-slate-300">—</span>
          <?php endif; ?>
        </td>
        <td class="font-medium text-slate-800"><?= htmlspecialchars($cat['label']) ?></td>
        <td class="text-slate-500 text-sm line-clamp-1"><?= htmlspecialchars($cat['headline'] ?? '') ?></td>
        <td class="text-right">
          <a href="<?= ADMIN_URL ?>/category-edit.php?id=<?= urlencode($cat['id']) ?>" class="text-brand text-sm hover:underline">แก้ไข</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
