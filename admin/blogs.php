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
<?= adminAlert($message, $messageType) ?>
<?php endif; ?>

<div class="flex items-center justify-between mb-6">
  <p class="text-sm text-slate-500">จัดการบทความทั้งหมด</p>
  <a href="<?= ADMIN_URL ?>/blog-edit.php" class="admin-btn-primary">+ เขียนบทความใหม่</a>
</div>

<?php if ($dbError): ?>
<div class="admin-card p-8 text-center">
  <p class="text-slate-500 mb-2">กรุณาตั้งค่าฐานข้อมูลก่อน</p>
  <p class="text-sm text-slate-400">รัน setup.sql เพื่อสร้างตารางที่จำเป็น</p>
</div>
<?php elseif (empty($articles)): ?>
<div class="admin-card p-12 text-center text-slate-400">ยังไม่มีบทความ</div>
<?php else: ?>
<div class="admin-card overflow-hidden">
  <table class="admin-table w-full">
    <thead>
      <tr>
        <th>หัวข้อ</th>
        <th>หมวดหมู่</th>
        <th>วันที่เผยแพร่</th>
        <th class="text-center">เข้าชม</th>
        <th class="text-center">สถานะ</th>
        <th class="text-right">จัดการ</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($articles as $article): ?>
      <tr>
        <td class="font-medium text-slate-800 max-w-xs truncate"><?= htmlspecialchars($article['title']) ?></td>
        <td class="text-slate-600"><?= htmlspecialchars($article['category_label'] ?? $article['category'] ?? '-') ?></td>
        <td class="text-slate-500"><?= $article['pub_date'] ? date('d/m/Y', strtotime($article['pub_date'])) : '-' ?></td>
        <td class="text-center text-slate-500"><?= number_format((int) $article['views']) ?></td>
        <td class="text-center">
          <?php if ($article['is_active']): ?>
          <span class="admin-badge-green">เผยแพร่</span>
          <?php else: ?>
          <span class="admin-badge-red">ฉบับร่าง</span>
          <?php endif; ?>
        </td>
        <td class="text-right whitespace-nowrap">
          <a href="<?= ADMIN_URL ?>/blog-edit.php?id=<?= urlencode($article['id']) ?>" class="text-brand text-sm hover:underline mr-3">แก้ไข</a>
          <form method="POST" class="inline" onsubmit="return confirm('ต้องการลบบทความนี้?')">
            <input type="hidden" name="delete_id" value="<?= htmlspecialchars($article['id']) ?>">
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
