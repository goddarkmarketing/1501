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

$filters = [
    'q' => trim((string) ($_GET['q'] ?? '')),
    'category' => trim((string) ($_GET['category'] ?? '')),
    'status' => trim((string) ($_GET['status'] ?? '')),
    'price' => trim((string) ($_GET['price'] ?? '')),
];

$plans = [];
$categories = [];
$dbError = false;
try {
    $plans = fetchAll('SELECT p.*, c.label as category_label FROM plan_products p LEFT JOIN plan_categories c ON p.category_id = c.id ORDER BY p.sort_order, p.name');
    $categories = fetchAll('SELECT id, label FROM plan_categories ORDER BY sort_order, label');
} catch (Exception $e) {
    $dbError = true;
}

$filtered = array_values(array_filter($plans, static function ($plan) use ($filters) {
    if ($filters['q'] !== '') {
        $hay = mb_strtolower(($plan['name'] ?? '') . ' ' . ($plan['id'] ?? '') . ' ' . ($plan['category_label'] ?? ''), 'UTF-8');
        if (mb_strpos($hay, mb_strtolower($filters['q'], 'UTF-8'), 0, 'UTF-8') === false) {
            return false;
        }
    }
    if ($filters['category'] !== '' && ($plan['category_id'] ?? '') !== $filters['category']) {
        return false;
    }
    if ($filters['status'] === 'active' && empty($plan['is_active'])) {
        return false;
    }
    if ($filters['status'] === 'inactive' && !empty($plan['is_active'])) {
        return false;
    }
    $price = (float) ($plan['price_from'] ?? 0);
    if ($filters['price'] === 'lt5000' && $price >= 5000) {
        return false;
    }
    if ($filters['price'] === '5to15' && ($price < 5000 || $price > 15000)) {
        return false;
    }
    if ($filters['price'] === 'gt15000' && $price <= 15000) {
        return false;
    }
    return true;
}));

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
<?= adminAlert($message, $messageType) ?>
<?php endif; ?>

<div class="flex items-center justify-between mb-6">
  <div>
    <p class="text-sm text-slate-500">แก้ไขแผนประกันและรูปภาพ — กดเผยแพร่หลังบันทึก</p>
    <p class="text-xs text-slate-400 mt-1">แสดง <?= number_format(count($filtered)) ?> / <?= number_format(count($plans)) ?> รายการ</p>
  </div>
  <a href="<?= ADMIN_URL ?>/plan-edit.php" class="admin-btn-primary">+ เพิ่มแผนประกัน</a>
</div>

<?php if (!$dbError): ?>
<form method="GET" class="admin-card p-4 mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3" data-feedback-id="plans-filters">
  <div class="lg:col-span-2">
    <label class="admin-label" for="q">ค้นหา</label>
    <input class="admin-input" type="search" id="q" name="q" value="<?= htmlspecialchars($filters['q']) ?>" placeholder="ชื่อแผน หรือรหัส…">
  </div>
  <div>
    <label class="admin-label" for="category">หมวดหมู่</label>
    <select class="admin-input" id="category" name="category">
      <option value="">ทั้งหมด</option>
      <?php foreach ($categories as $cat): ?>
      <option value="<?= htmlspecialchars($cat['id']) ?>" <?= $filters['category'] === $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['label']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="admin-label" for="price">ราคาเริ่มต้น</label>
    <select class="admin-input" id="price" name="price">
      <option value="">ทั้งหมด</option>
      <option value="lt5000" <?= $filters['price'] === 'lt5000' ? 'selected' : '' ?>>ต่ำกว่า 5,000</option>
      <option value="5to15" <?= $filters['price'] === '5to15' ? 'selected' : '' ?>>5,000 – 15,000</option>
      <option value="gt15000" <?= $filters['price'] === 'gt15000' ? 'selected' : '' ?>>มากกว่า 15,000</option>
    </select>
  </div>
  <div>
    <label class="admin-label" for="status">สถานะ</label>
    <select class="admin-input" id="status" name="status">
      <option value="">ทั้งหมด</option>
      <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>เปิด</option>
      <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>ปิด</option>
    </select>
  </div>
  <div class="sm:col-span-2 lg:col-span-5 flex gap-2">
    <button type="submit" class="admin-btn-primary text-sm">ค้นหา</button>
    <a href="<?= ADMIN_URL ?>/plans.php" class="admin-btn-outline text-sm">ล้างตัวกรอง</a>
  </div>
</form>
<?php endif; ?>

<?php if ($dbError): ?>
<div class="admin-card p-8 text-center">
  <p class="text-slate-500 mb-2">กรุณาติดตั้งฐานข้อมูลก่อน</p>
  <a href="<?= ADMIN_URL ?>/install.php" class="text-brand text-sm hover:underline">ไปหน้าติดตั้ง →</a>
</div>
<?php elseif (empty($filtered)): ?>
<div class="admin-card p-12 text-center text-slate-400">
  <?= empty($plans) ? 'ยังไม่มีแผนประกัน — <a href="' . ADMIN_URL . '/seed.php" class="text-brand hover:underline">นำเข้าข้อมูลเริ่มต้น</a>' : 'ไม่พบแผนที่ตรงกับตัวกรอง' ?>
</div>
<?php else: ?>
<div class="admin-card overflow-hidden" data-feedback-id="package-list">
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
      <?php foreach ($filtered as $plan): ?>
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
        <td class="text-slate-700"><?= number_format((float) $plan['price_from'], 0) ?> ฿</td>
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
