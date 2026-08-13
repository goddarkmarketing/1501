<?php
$pageTitle = 'โปรโมชัน';
require_once __DIR__ . '/includes/auth.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';

        if ($action === 'delete' && isset($_POST['delete_id'])) {
            query('DELETE FROM promotions WHERE id = ?', [$_POST['delete_id']]);
            $message = 'ลบโปรโมชันเรียบร้อยแล้ว';
            $messageType = 'success';
        }

        if ($action === 'save_filters') {
            $ids = $_POST['filter_id'] ?? [];
            $labels = $_POST['filter_label'] ?? [];
            $orders = $_POST['filter_order'] ?? [];
            query('DELETE FROM promotion_filter_items');
            foreach ($ids as $i => $id) {
                $id = trim((string) $id);
                $label = trim((string) ($labels[$i] ?? ''));
                if ($id === '' || $label === '') {
                    continue;
                }
                query(
                    'INSERT INTO promotion_filter_items (id, label, sort_order) VALUES (?,?,?)',
                    [$id, $label, (int) ($orders[$i] ?? $i)]
                );
            }
            $message = 'บันทึกตัวกรองโปรโมชันเรียบร้อยแล้ว';
            $messageType = 'success';
        }
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

$filters = [];
try {
    $filters = fetchAll('SELECT * FROM promotion_filter_items ORDER BY sort_order, id');
} catch (Throwable $e) {
    $filters = [['id' => 'all', 'label' => 'ทั้งหมด', 'sort_order' => 0]];
}
if (empty($filters)) {
    $filters = [['id' => 'all', 'label' => 'ทั้งหมด', 'sort_order' => 0]];
}

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
<?= adminAlert($message, $messageType) ?>
<?php endif; ?>

<div class="flex items-center justify-between mb-6">
  <p class="text-sm text-slate-500">จัดการโปรโมชันทั้งหมด</p>
  <a href="<?= ADMIN_URL ?>/promo-edit.php" class="admin-btn-primary">+ เพิ่มโปรโมชัน</a>
</div>

<?php if ($dbError): ?>
<div class="admin-card p-8 text-center">
  <p class="text-slate-500 mb-2">กรุณาตั้งค่าฐานข้อมูลก่อน</p>
</div>
<?php elseif (empty($promos)): ?>
<div class="admin-card p-12 text-center text-slate-400">ยังไม่มีโปรโมชัน</div>
<?php else: ?>
<div class="admin-card overflow-hidden mb-8">
  <table class="admin-table w-full">
    <thead>
      <tr>
        <th>ชื่อโปรโมชัน</th>
        <th>หมวดหมู่</th>
        <th>ใช้ได้ถึง</th>
        <th class="text-center">ยอดนิยม</th>
        <th class="text-center">ใหม่</th>
        <th class="text-center">สถานะ</th>
        <th class="text-right">จัดการ</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($promos as $promo): ?>
      <tr>
        <td class="font-medium text-slate-800 max-w-xs truncate"><?= htmlspecialchars($promo['title']) ?></td>
        <td class="text-slate-600"><?= htmlspecialchars($promo['category'] ?? '-') ?></td>
        <td class="text-slate-500"><?= htmlspecialchars($promo['valid_until'] ?? '-') ?></td>
        <td class="text-center">
          <?php if (!empty($promo['is_popular'])): ?>
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">ยอดนิยม</span>
          <?php else: ?>
          <span class="text-slate-300">—</span>
          <?php endif; ?>
        </td>
        <td class="text-center">
          <?php if (!empty($promo['is_new'])): ?>
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-100 text-sky-800">ใหม่</span>
          <?php else: ?>
          <span class="text-slate-300">—</span>
          <?php endif; ?>
        </td>
        <td class="text-center">
          <?php if (!empty($promo['is_active'])): ?>
          <span class="admin-badge-green">เปิดใช้งาน</span>
          <?php else: ?>
          <span class="admin-badge-red">ปิดใช้งาน</span>
          <?php endif; ?>
        </td>
        <td class="text-right whitespace-nowrap">
          <a href="<?= ADMIN_URL ?>/promo-edit.php?id=<?= urlencode($promo['id']) ?>" class="text-brand text-sm hover:underline mr-3">แก้ไข</a>
          <form method="POST" class="inline" onsubmit="return confirm('ต้องการลบโปรโมชันนี้?')">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="delete_id" value="<?= htmlspecialchars($promo['id']) ?>">
            <button type="submit" class="text-red-500 text-sm hover:underline">ลบ</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<div class="admin-card p-6" id="promo-filters-section" data-feedback-id="promo-filters-section">
  <div class="flex items-center justify-between mb-4">
    <div>
      <h3 class="text-base font-semibold text-slate-800">ตัวกรองโปรโมชัน (Filter)</h3>
      <p class="text-sm text-slate-500 mt-0.5">แท็บกรองที่แสดงในหน้าโปรโมชันของเว็บไซต์</p>
    </div>
  </div>
  <form method="POST">
    <input type="hidden" name="action" value="save_filters">
    <div class="space-y-3 mb-4" id="filterRows">
      <?php foreach ($filters as $f): ?>
      <div class="grid grid-cols-12 gap-2 items-center">
        <input type="text" name="filter_id[]" value="<?= htmlspecialchars($f['id']) ?>" placeholder="id" class="admin-input col-span-3 text-sm">
        <input type="text" name="filter_label[]" value="<?= htmlspecialchars($f['label']) ?>" placeholder="ชื่อแสดง" class="admin-input col-span-6 text-sm">
        <input type="number" name="filter_order[]" value="<?= (int) $f['sort_order'] ?>" class="admin-input col-span-2 text-sm">
      </div>
      <?php endforeach; ?>
    </div>
    <div class="flex flex-wrap gap-3">
      <button type="button" class="admin-btn-outline text-sm" onclick="addFilterRow()">+ เพิ่มแถว</button>
      <button type="submit" class="admin-btn-primary text-sm">บันทึกตัวกรอง</button>
      <a href="<?= ADMIN_URL ?>/api/publish.php" onclick="return confirm('เผยแพร่เว็บไซต์?')" class="admin-btn-outline text-sm">เผยแพร่</a>
    </div>
  </form>
</div>

<script>
function addFilterRow() {
  var row = document.createElement('div');
  row.className = 'grid grid-cols-12 gap-2 items-center';
  row.innerHTML = '<input type="text" name="filter_id[]" placeholder="id" class="admin-input col-span-3 text-sm"><input type="text" name="filter_label[]" placeholder="ชื่อแสดง" class="admin-input col-span-6 text-sm"><input type="number" name="filter_order[]" value="0" class="admin-input col-span-2 text-sm">';
  document.getElementById('filterRows').appendChild(row);
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
