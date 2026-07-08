<?php
$pageTitle = 'บทความ';
require_once __DIR__ . '/includes/auth.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    try {
        query('DELETE FROM blog_articles WHERE id = ?', [$_POST['delete_id']]);
        $message = 'ลบบทความเรียบร้อยแล้ว';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        $messageType = 'error';
    }
}

$articles = [];
$dbError = false;
try {
    $articles = fetchAll('SELECT * FROM blog_articles ORDER BY pub_date DESC, created_at DESC');
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
  <p class="text-sm text-gray-400">จัดการบทความทั้งหมด</p>
  <a href="<?= ADMIN_URL ?>/blog-edit.php" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand hover:bg-brand-light text-white text-sm font-medium rounded-lg transition">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
    เขียนบทความใหม่
  </a>
</div>

<?php if ($dbError): ?>
<div class="bg-gray-900 border border-gray-800 rounded-xl p-8 text-center">
  <p class="text-gray-400 mb-2">กรุณาตั้งค่าฐานข้อมูลก่อน</p>
  <p class="text-sm text-gray-500">รัน <code class="bg-gray-800 px-2 py-0.5 rounded text-xs text-gray-300">setup.sql</code> เพื่อสร้างตารางที่จำเป็น</p>
</div>
<?php elseif (empty($articles)): ?>
<div class="bg-gray-900 border border-gray-800 rounded-xl p-12 text-center">
  <p class="text-gray-500">ยังไม่มีบทความ</p>
</div>
<?php else: ?>
<div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
  <div class="overflow-x-auto">
    <table class="min-w-full">
      <thead class="bg-gray-800/50">
        <tr>
          <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase text-left">หัวข้อ</th>
          <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase text-left">หมวดหมู่</th>
          <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase text-left">วันที่เผยแพร่</th>
          <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase text-center">เข้าชม</th>
          <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase text-center">สถานะ</th>
          <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase text-right">จัดการ</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-800">
        <?php foreach ($articles as $article): ?>
        <tr class="hover:bg-gray-800/30 transition">
          <td class="px-4 py-3 text-sm text-white font-medium max-w-xs truncate"><?= htmlspecialchars($article['title']) ?></td>
          <td class="px-4 py-3 text-sm text-gray-400"><?= htmlspecialchars($article['category_label'] ?? $article['category'] ?? '-') ?></td>
          <td class="px-4 py-3 text-sm text-gray-400"><?= $article['pub_date'] ? date('d/m/Y', strtotime($article['pub_date'])) : '-' ?></td>
          <td class="px-4 py-3 text-sm text-center text-gray-400"><?= number_format($article['views']) ?></td>
          <td class="px-4 py-3 text-sm text-center">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $article['is_active'] ? 'bg-emerald-900/30 text-emerald-400' : 'bg-red-900/30 text-red-400' ?>">
              <?= $article['is_active'] ? 'เผยแพร่' : 'ฉบับร่าง' ?>
            </span>
          </td>
          <td class="px-4 py-3 text-sm text-right">
            <div class="flex items-center justify-end gap-2">
              <a href="<?= ADMIN_URL ?>/blog-edit.php?id=<?= urlencode($article['id']) ?>" class="inline-flex items-center gap-1 px-3 py-1.5 border border-gray-700 text-gray-300 hover:bg-gray-800 text-xs rounded-lg transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                แก้ไข
              </a>
              <form method="POST" onsubmit="return confirm('ต้องการลบบทความนี้?')" class="inline">
                <input type="hidden" name="delete_id" value="<?= htmlspecialchars($article['id']) ?>">
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
