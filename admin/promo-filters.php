<?php
$pageTitle = 'ตัวกรองโปรโมชัน';
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action']) && $_POST['action'] === 'save') {
            $ids = $_POST['filter_id'] ?? [];
            $labels = $_POST['filter_label'] ?? [];
            $orders = $_POST['filter_order'] ?? [];
            query('DELETE FROM promotion_filter_items');
            foreach ($ids as $i => $id) {
                $id = trim($id);
                $label = trim($labels[$i] ?? '');
                if ($id === '' || $label === '') continue;
                query('INSERT INTO promotion_filter_items (id, label, sort_order) VALUES (?,?,?)', [$id, $label, (int) ($orders[$i] ?? $i)]);
            }
            $_SESSION['flash'] = 'บันทึกตัวกรองเรียบร้อย';
            header('Location: ' . ADMIN_URL . '/promo-filters.php');
            exit;
        }
    } catch (Throwable $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

$filters = [];
try {
    $filters = fetchAll('SELECT * FROM promotion_filter_items ORDER BY sort_order, id');
} catch (Throwable $e) {
    $filters = [
        ['id' => 'all', 'label' => 'ทั้งหมด', 'sort_order' => 0],
    ];
}

require_once __DIR__ . '/includes/header.php';
?>

<form method="POST" class="admin-card p-6 max-w-2xl">
  <input type="hidden" name="action" value="save">
  <p class="text-sm text-slate-500 mb-4">แก้ไขแท็บกรองในหน้าโปรโมชัน</p>
  <div class="space-y-3 mb-4" id="filterRows">
    <?php foreach ($filters as $i => $f): ?>
    <div class="grid grid-cols-12 gap-2 items-center">
      <input type="text" name="filter_id[]" value="<?= htmlspecialchars($f['id']) ?>" placeholder="id" class="admin-input col-span-3 text-sm">
      <input type="text" name="filter_label[]" value="<?= htmlspecialchars($f['label']) ?>" placeholder="ชื่อแสดง" class="admin-input col-span-6 text-sm">
      <input type="number" name="filter_order[]" value="<?= (int) $f['sort_order'] ?>" class="admin-input col-span-2 text-sm">
    </div>
    <?php endforeach; ?>
  </div>
  <div class="flex gap-3">
    <button type="button" class="admin-btn-outline text-sm" onclick="addFilterRow()">+ เพิ่มแถว</button>
    <button type="submit" class="admin-btn-primary">บันทึก</button>
    <a href="<?= ADMIN_URL ?>/api/publish.php" onclick="return confirm('เผยแพร่?')" class="admin-btn-outline">เผยแพร่</a>
  </div>
</form>

<script>
function addFilterRow() {
  const row = document.createElement('div');
  row.className = 'grid grid-cols-12 gap-2 items-center';
  row.innerHTML = '<input type="text" name="filter_id[]" placeholder="id" class="admin-input col-span-3 text-sm"><input type="text" name="filter_label[]" placeholder="ชื่อแสดง" class="admin-input col-span-6 text-sm"><input type="number" name="filter_order[]" value="0" class="admin-input col-span-2 text-sm">';
  document.getElementById('filterRows').appendChild(row);
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
