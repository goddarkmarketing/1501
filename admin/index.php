<?php
$pageTitle = 'แดชบอร์ด';
require_once __DIR__ . '/includes/auth.php';

$stats = [];
try {
    $stats['plans'] = fetchOne('SELECT COUNT(*) as c FROM plan_products')['c'] ?? 0;
} catch (Exception $e) { $stats['plans'] = 0; }

try {
    $stats['blogs'] = fetchOne('SELECT COUNT(*) as c FROM blog_articles')['c'] ?? 0;
} catch (Exception $e) { $stats['blogs'] = 0; }

try {
    $stats['promos'] = fetchOne('SELECT COUNT(*) as c FROM promotions')['c'] ?? 0;
} catch (Exception $e) { $stats['promos'] = 0; }

try {
    $stats['contacts'] = fetchOne("SELECT COUNT(*) as c FROM contact_submissions WHERE status='new'")['c'] ?? 0;
} catch (Exception $e) { $stats['contacts'] = 0; }

try {
    $jsonPath = SITE_ROOT . '/assets/data/hospital-locator.json';
    $hospitalData = file_exists($jsonPath) ? json_decode(file_get_contents($jsonPath), true) : [];
    $stats['hospitals'] = is_array($hospitalData) ? count($hospitalData) : 0;
} catch (Exception $e) { $stats['hospitals'] = 0; }

try {
    $stats['settings'] = fetchOne('SELECT COUNT(*) as c FROM site_settings')['c'] ?? 0;
} catch (Exception $e) { $stats['settings'] = 0; }

$recentContacts = [];
try {
    $recentContacts = fetchAll('SELECT name, phone, insurance_type, status, created_at FROM contact_submissions ORDER BY created_at DESC LIMIT 5');
} catch (Exception $e) {}

require_once __DIR__ . '/includes/header.php';

$cards = [
    ['label' => 'แผนประกัน', 'value' => $stats['plans'], 'icon' => 'shield-check'],
    ['label' => 'บทความ', 'value' => $stats['blogs'], 'icon' => 'newspaper'],
    ['label' => 'โปรโมชัน', 'value' => $stats['promos'], 'icon' => 'badge-percent'],
    ['label' => 'ข้อความใหม่', 'value' => $stats['contacts'], 'icon' => 'mail'],
    ['label' => 'โรงพยาบาล', 'value' => $stats['hospitals'], 'icon' => 'hospital'],
    ['label' => 'การตั้งค่า', 'value' => $stats['settings'], 'icon' => 'settings'],
];

$statusLabels = ['new' => 'ใหม่', 'contacted' => 'ติดต่อแล้ว', 'closed' => 'ปิดแล้ว'];
$statusColors = ['new' => 'bg-blue-900/30 text-blue-400', 'contacted' => 'bg-yellow-900/30 text-yellow-400', 'closed' => 'bg-gray-700/30 text-gray-400'];
?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8" data-feedback-id="dashboard-summary">
  <?php foreach ($cards as $card): ?>
  <div class="admin-card p-6 flex items-center gap-4">
    <div class="w-12 h-12 rounded-lg bg-brand/10 flex items-center justify-center flex-shrink-0">
      <i data-lucide="<?= htmlspecialchars($card['icon']) ?>" class="w-6 h-6 text-brand" aria-hidden="true"></i>
    </div>
    <div>
      <p class="text-2xl font-bold text-brand"><?= number_format($card['value']) ?></p>
      <p class="text-sm text-slate-500"><?= $card['label'] ?></p>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Quick Actions -->
<div class="admin-card p-6 mb-8" data-feedback-id="dashboard-quick-actions">
  <h3 class="text-base font-semibold text-slate-800 mb-4">ทางลัด</h3>
  <div class="flex flex-wrap gap-3">
    <a href="<?= ADMIN_URL ?>/pages.php" class="admin-btn-outline">จัดการหน้าเว็บ</a>
    <a href="<?= ADMIN_URL ?>/plan-edit.php" class="admin-btn-primary">+ แผนประกัน</a>
    <a href="<?= ADMIN_URL ?>/blog-edit.php" class="admin-btn-primary">+ บทความ</a>
    <a href="<?= ADMIN_URL ?>/promo-edit.php" class="admin-btn-primary">+ โปรโมชัน</a>
    <a href="<?= ADMIN_URL ?>/api/publish.php" onclick="return confirm('เผยแพร่การเปลี่ยนแปลงทั้งหมดไปยังเว็บไซต์?')" class="inline-flex items-center gap-2 px-4 py-2.5 border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-sm font-medium rounded-lg transition">
      เผยแพร่เว็บไซต์
    </a>
  </div>
</div>

<!-- Recent Contacts -->
<div class="admin-card overflow-hidden" data-feedback-id="dashboard-recent-contacts">
  <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
    <h3 class="text-base font-semibold text-slate-800">ข้อความติดต่อล่าสุด</h3>
    <a href="<?= ADMIN_URL ?>/contacts.php" class="text-sm text-brand hover:underline">ดูทั้งหมด →</a>
  </div>
  <?php if (empty($recentContacts)): ?>
  <div class="px-6 py-12 text-center text-slate-400">ยังไม่มีข้อมูล</div>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table class="admin-table w-full">
      <thead>
        <tr>
          <th>ชื่อ</th>
          <th>เบอร์โทร</th>
          <th>ประเภทประกัน</th>
          <th>สถานะ</th>
          <th>วันที่</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentContacts as $row): ?>
        <tr>
          <td class="font-medium text-slate-800"><?= htmlspecialchars($row['name'] ?? '-') ?></td>
          <td><?= htmlspecialchars($row['phone'] ?? '-') ?></td>
          <td><?= htmlspecialchars($row['insurance_type'] ?? '-') ?></td>
          <td>
            <?php
            $statusClass = match($row['status']) {
                'new' => 'admin-badge-green',
                'contacted' => 'bg-amber-50 text-amber-700 text-xs px-2 py-0.5 rounded-full',
                default => 'bg-slate-100 text-slate-500 text-xs px-2 py-0.5 rounded-full',
            };
            ?>
            <span class="<?= $statusClass ?>"><?= $statusLabels[$row['status']] ?? $row['status'] ?></span>
          </td>
          <td class="text-slate-400 text-sm"><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
