<?php
$pageTitle = 'หมวดหมู่แผน';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/upload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $deleteId = trim($_POST['id'] ?? '');
    if ($deleteId !== '') {
        try {
            $used = fetchOne('SELECT COUNT(*) AS c FROM plan_products WHERE category_id = ?', [$deleteId]);
            if ((int) ($used['c'] ?? 0) > 0) {
                $_SESSION['flash_error'] = 'ลบไม่ได้ — ยังมีแผนประกันผูกกับหมวดนี้อยู่';
            } else {
                query('DELETE FROM plan_categories WHERE id = ?', [$deleteId]);
                $_SESSION['flash'] = 'ลบหมวดหมู่แล้ว';
            }
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }
    }
    header('Location: ' . ADMIN_URL . '/categories.php');
    exit;
}

$categories = [];
$dbError = false;
try {
    $categories = fetchAll('SELECT * FROM plan_categories ORDER BY sort_order, id');
} catch (Exception $e) {
    $dbError = true;
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
  <p class="text-sm text-slate-500">จัดการรูปและข้อความของแต่ละหมวดหมู่แผนประกัน (แสดงบนหน้า plan-category)</p>
  <a href="<?= ADMIN_URL ?>/category-edit.php?new=1" class="admin-btn-primary">+ เพิ่มหมวดหมู่</a>
</div>

<?php if ($dbError): ?>
<div class="admin-card p-8 text-center text-slate-500">กรุณาติดตั้งฐานข้อมูลก่อน</div>
<?php else: ?>
<div class="admin-card overflow-hidden">
  <table class="admin-table w-full">
    <thead>
      <tr>
        <th class="w-16">รูป</th>
        <th>หมวดหมู่</th>
        <th>ID</th>
        <th>หัวข้อ</th>
        <th class="text-right w-40">จัดการ</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$categories): ?>
      <tr>
        <td colspan="5" class="text-center text-slate-400 py-10">ยังไม่มีหมวดหมู่ — กดเพิ่มหมวดหมู่ด้านบน</td>
      </tr>
      <?php endif; ?>
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
        <td class="text-xs text-slate-400 font-mono"><?= htmlspecialchars($cat['id']) ?></td>
        <td class="text-slate-500 text-sm line-clamp-1"><?= htmlspecialchars($cat['headline'] ?? '') ?></td>
        <td class="text-right whitespace-nowrap">
          <a href="<?= ADMIN_URL ?>/category-edit.php?id=<?= urlencode($cat['id']) ?>" class="text-brand text-sm hover:underline">แก้ไข</a>
          <form method="POST" class="inline ms-2" onsubmit="return confirm('ลบหมวด <?= htmlspecialchars($cat['label'], ENT_QUOTES) ?>?');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= htmlspecialchars($cat['id']) ?>">
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
