<?php
$pageTitle = 'โปรโมชัน';
require_once __DIR__ . '/includes/auth.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    try {
        query('DELETE FROM promotions WHERE id = ?', [$_POST['delete_id']]);
        $message = 'ลบโปรโมชันเรียบร้อยแล้ว';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        $messageType = 'error';
    }
}

$promos = [];
$dbError = false;
try {
    $promos = fetchAll('SELECT * FROM promotions ORDER BY sort_order, created_at DESC');
} catch (Exception $e) {
    $dbError = true;
}

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
<div class="mb-6 px-4 py-3 rounded-lg text-sm <?= $messageType === 'success' ? 'bg-emerald-900/30 border border-emerald-800 text-emerald-300' : 'bg-red-900/30 border border-red-800 text-red-300' ?>">
  <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<div class="flex items-center justify-between mb-6">
  <p class="text-sm text-gray-400">จัดการโปรโมชันทั้งหมด</p>
  <a href="<?= ADMIN_URL ?>/promo-edit.php" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand hover:bg-brand-light text-white text-sm font-medium rounded-lg transition">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
    เพิ่มโปรโมชัน
  </a>
</div>

<?php if ($dbError): ?>
<div class="bg-gray-900 border border-gray-800 rounded-xl p-8 text-center">
  <p class="text-gray-400 mb-2">กรุณาตั้งค่าฐานข้อมูลก่อน</p>
  <p class="text-sm text-gray-500">รัน <code class="bg-gray-800 px-2 py-0.5 rounded text-xs text-gray-300">setup.sql</code> เพื่อสร้างตารางที่จำเป็น</p>
</div>
<?php elseif (empty($promos)): ?>
<div class="bg-gray-900 border border-gray-800 rounded-xl p-12 text-center">
  <p class="text-gray-500">ยังไม่มีโปรโมชัน</p>
</div>
<?php else: ?>
<div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
  <div class="overflow-x-auto">
    <table class="min-w-full">
      <thead class="bg-gray-800/50">
        <tr>
          <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase text-left">ชื่อโปรโมชัน</th>
          <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase text-left">หมวดหมู่</th>
          <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase text-left">ใช้ได้ถึง</th>
          <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase text-center">ยอดนิยม</th>
          <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase text-center">ใหม่</th>
          <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase text-center">สถานะ</th>
          <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase text-right">จัดการ</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-800">
        <?php foreach ($promos as $promo): ?>
        <tr class="hover:bg-gray-800/30 transition">
          <td class="px-4 py-3 text-sm text-white font-medium max-w-xs truncate"><?= htmlspecialchars($promo['title']) ?></td>
          <td class="px-4 py-3 text-sm text-gray-400"><?= htmlspecialchars($promo['category'] ?? '-') ?></td>
          <td class="px-4 py-3 text-sm text-gray-400"><?= htmlspecialchars($promo['valid_until'] ?? '-') ?></td>
          <td class="px-4 py-3 text-sm text-center">
            <?php if ($promo['is_popular']): ?>
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-900/30 text-amber-400">ยอดนิยม</span>
            <?php else: ?>
              <span class="text-gray-600">—</span>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3 text-sm text-center">
            <?php if ($promo['is_new']): ?>
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-900/30 text-blue-400">ใหม่</span>
            <?php else: ?>
              <span class="text-gray-600">—</span>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3 text-sm text-center">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $promo['is_active'] ? 'bg-emerald-900/30 text-emerald-400' : 'bg-red-900/30 text-red-400' ?>">
              <?= $promo['is_active'] ? 'เปิดใช้งาน' : 'ปิดใช้งาน' ?>
            </span>
          </td>
          <td class="px-4 py-3 text-sm text-right">
            <div class="flex items-center justify-end gap-2">
              <a href="<?= ADMIN_URL ?>/promo-edit.php?id=<?= urlencode($promo['id']) ?>" class="inline-flex items-center gap-1 px-3 py-1.5 border border-gray-700 text-gray-300 hover:bg-gray-800 text-xs rounded-lg transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                แก้ไข
              </a>
              <form method="POST" onsubmit="return confirm('ต้องการลบโปรโมชันนี้?')" class="inline">
                <input type="hidden" name="delete_id" value="<?= htmlspecialchars($promo['id']) ?>">
                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs rounded-lg transition">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                  ลบ
                </button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
