<?php
$pageTitle = 'คำถามที่พบบ่อย';
require_once __DIR__ . '/includes/auth.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    try {
        query('DELETE FROM faq_items WHERE id = ?', [(int) $_POST['delete_id']]);
        $message = 'ลบคำถามเรียบร้อยแล้ว';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        $messageType = 'error';
    }
}

$items = [];
$dbError = false;
try {
    $items = fetchAll('SELECT * FROM faq_items ORDER BY sort_order, id');
} catch (Exception $e) {
    $dbError = true;
}

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
<?= adminAlert($message, $messageType) ?>
<?php endif; ?>

<div class="flex items-center justify-between mb-6">
  <p class="text-sm text-slate-500">จัดการคำถามที่แสดงบนหน้า FAQ</p>
  <a href="<?= ADMIN_URL ?>/faq-edit.php" class="admin-btn-primary">
    + เพิ่มคำถาม
  </a>
</div>

<?php if ($dbError): ?>
<div class="admin-card p-8 text-center">
  <p class="text-slate-500 mb-2">กรุณาติดตั้งฐานข้อมูลก่อน</p>
  <a href="<?= ADMIN_URL ?>/install.php" class="text-brand text-sm hover:underline">ไปหน้าติดตั้ง →</a>
</div>
<?php elseif (empty($items)): ?>
<div class="admin-card p-12 text-center text-slate-400">ยังไม่มีคำถาม — <a href="<?= ADMIN_URL ?>/faq-edit.php" class="text-brand hover:underline">เพิ่มคำถามแรก</a></div>
<?php else: ?>
<div class="admin-card overflow-hidden">
  <table class="admin-table w-full">
    <thead>
      <tr>
        <th>คำถาม</th>
        <th class="w-24">ลำดับ</th>
        <th class="w-24">สถานะ</th>
        <th class="w-32 text-right">จัดการ</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $item): ?>
      <tr>
        <td>
          <p class="font-medium text-slate-800 line-clamp-2"><?= htmlspecialchars($item['question']) ?></p>
          <p class="text-xs text-slate-400 mt-1 line-clamp-1"><?= htmlspecialchars(mb_substr(strip_tags($item['answer']), 0, 80)) ?>…</p>
        </td>
        <td class="text-slate-500"><?= (int) $item['sort_order'] ?></td>
        <td>
          <?php if ($item['is_active']): ?>
          <span class="admin-badge-green">เผยแพร่</span>
          <?php else: ?>
          <span class="admin-badge-red">ซ่อน</span>
          <?php endif; ?>
        </td>
        <td class="text-right">
          <a href="<?= ADMIN_URL ?>/faq-edit.php?id=<?= (int) $item['id'] ?>" class="text-brand text-sm hover:underline mr-3">แก้ไข</a>
          <form method="POST" class="inline" onsubmit="return confirm('ลบคำถามนี้?')">
            <input type="hidden" name="delete_id" value="<?= (int) $item['id'] ?>">
            <button type="submit" class="text-red-500 text-sm hover:underline">ลบ</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<p class="text-xs text-slate-400 mt-4">แก้ไข Hero ของหน้า FAQ ได้ที่ <a href="<?= ADMIN_URL ?>/page-edit.php?page=faq" class="text-brand hover:underline">จัดการหน้าเว็บ → คำถามที่พบบ่อย</a></p>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
