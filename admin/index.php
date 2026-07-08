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
    ['label' => 'แผนประกัน', 'value' => $stats['plans'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>'],
    ['label' => 'บทความ', 'value' => $stats['blogs'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6V7.5Z"/>'],
    ['label' => 'โปรโมชัน', 'value' => $stats['promos'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/>'],
    ['label' => 'ข้อความใหม่', 'value' => $stats['contacts'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>'],
    ['label' => 'โรงพยาบาล', 'value' => $stats['hospitals'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z"/>'],
    ['label' => 'การตั้งค่า', 'value' => $stats['settings'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.212-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>'],
];

$statusLabels = ['new' => 'ใหม่', 'contacted' => 'ติดต่อแล้ว', 'closed' => 'ปิดแล้ว'];
$statusColors = ['new' => 'bg-blue-900/30 text-blue-400', 'contacted' => 'bg-yellow-900/30 text-yellow-400', 'closed' => 'bg-gray-700/30 text-gray-400'];
?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
  <?php foreach ($cards as $card): ?>
  <div class="admin-card p-6 flex items-center gap-4">
    <div class="w-12 h-12 rounded-lg bg-brand/10 flex items-center justify-center flex-shrink-0">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-brand"><?= $card['icon'] ?></svg>
    </div>
    <div>
      <p class="text-2xl font-bold text-brand"><?= number_format($card['value']) ?></p>
      <p class="text-sm text-slate-500"><?= $card['label'] ?></p>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Quick Actions -->
<div class="admin-card p-6 mb-8">
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
<div class="admin-card overflow-hidden">
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
