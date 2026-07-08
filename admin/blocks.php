<?php
$pageTitle = 'บล็อกเนื้อหา';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cms.php';

$defs = getCmsBlockDefinitions();
$grouped = [];
foreach ($defs as $key => $def) {
    $grouped[$def['group']][$key] = $def;
}

require_once __DIR__ . '/includes/header.php';
?>

<p class="text-sm text-slate-500 mb-6">แก้ไขส่วนเนื้อหาที่ใช้ร่วมกันหลายหน้า เช่น Footer, เมนู, ส่วนหน้าแรก และเกี่ยวกับเรา</p>

<div class="space-y-8">
  <?php foreach ($grouped as $group => $items): ?>
  <div>
    <h3 class="text-sm font-semibold text-slate-700 mb-3"><?= htmlspecialchars($group) ?></h3>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php foreach ($items as $key => $def): ?>
      <a href="<?= ADMIN_URL ?>/block-edit.php?key=<?= urlencode($key) ?>" class="admin-card p-5 hover:border-brand/40 transition-colors block">
        <h4 class="font-medium text-slate-800 mb-1"><?= htmlspecialchars($def['label']) ?></h4>
        <p class="text-xs text-slate-400"><?= htmlspecialchars($def['description']) ?></p>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
