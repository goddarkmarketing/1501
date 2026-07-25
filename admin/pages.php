<?php
$pageTitle = 'จัดการหน้าเว็บ';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/ui.php';
require_once __DIR__ . '/includes/header.php';

$pages = getPageDefinitions();
$totalSections = array_sum(array_map(fn($p) => count($p['sections']), $pages));
?>

<div class="admin-page-toolbar">
  <div>
    <p class="admin-page-toolbar__lead">แก้ไขเนื้อหาแบบเห็นจริง — พรีวิวเหมือนหน้าบ้าน คลิกข้อความเพื่อแก้ไข แล้วกดเผยแพร่หลังบันทึก</p>
    <div class="admin-page-toolbar__meta">
      <span class="admin-stat-pill"><?= count($pages) ?> หน้า</span>
      <span class="admin-stat-pill"><?= $totalSections ?> ส่วนที่แก้ไขได้</span>
    </div>
  </div>
  <a href="<?= ADMIN_URL ?>/api/publish.php" onclick="return confirm('เผยแพร่การเปลี่ยนแปลงทั้งหมดไปยังเว็บไซต์?')" class="admin-btn-primary shrink-0">
    เผยแพร่เว็บไซต์
  </a>
</div>

<div class="admin-pages-grid">
  <?php foreach ($pages as $slug => $page):
    $stats = getPageSectionStats($slug, count($page['sections']));
    $pct = $stats['total'] > 0 ? min(100, round(($stats['saved'] / $stats['total']) * 100)) : 0;
    $pathLabel = parse_url($page['url'], PHP_URL_PATH) ?: $page['url'];
    $sectionLabels = array_slice(array_column($page['sections'], 'label'), 0, 3);
  ?>
  <article class="admin-page-tile">
    <div class="admin-page-tile__head">
      <div class="admin-page-tile__icon" aria-hidden="true">
        <?= adminPageIcon($page['icon'] ?? 'home') ?>
      </div>
      <div class="admin-page-tile__title-wrap">
        <h3 class="admin-page-tile__title"><?= htmlspecialchars($page['label']) ?></h3>
        <code class="admin-page-tile__path"><?= htmlspecialchars($pathLabel) ?></code>
      </div>
    </div>

    <p class="admin-page-tile__desc"><?= htmlspecialchars($page['description'] ?? '') ?></p>

    <div class="admin-page-tile__tags">
      <?php foreach ($sectionLabels as $label): ?>
      <span class="admin-tag"><?= htmlspecialchars(strip_tags($label)) ?></span>
      <?php endforeach; ?>
      <?php if (count($page['sections']) > 3): ?>
      <span class="admin-tag admin-tag--muted">+<?= count($page['sections']) - 3 ?></span>
      <?php endif; ?>
    </div>

    <div class="admin-page-tile__progress">
      <div class="admin-page-tile__progress-label">
        <span>บันทึกแล้ว <?= $stats['saved'] ?>/<?= $stats['total'] ?> ส่วน</span>
        <span><?= $pct ?>%</span>
      </div>
      <div class="admin-progress" role="progressbar" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100">
        <div class="admin-progress__bar" style="width:<?= $pct ?>%"></div>
      </div>
    </div>

    <div class="admin-page-tile__actions">
      <a href="<?= ADMIN_URL ?>/page-edit.php?page=<?= urlencode($slug) ?>" class="admin-btn-primary admin-btn-primary--sm">
        แก้ไขแบบเห็นจริง
      </a>
      <a href="<?= htmlspecialchars($page['url']) ?>" target="_blank" rel="noopener" class="admin-btn-ghost admin-btn-ghost--sm">
        ดูหน้าจริง ↗
      </a>
    </div>
  </article>
  <?php endforeach; ?>
</div>

<section class="admin-related" aria-labelledby="related-heading">
  <div class="admin-related__head">
    <h2 id="related-heading" class="admin-related__title">เนื้อหาที่จัดการในเมนูอื่น</h2>
    <p class="admin-related__sub">ส่วนเหล่านี้แยกจาก Hero ของแต่ละหน้า</p>
  </div>
  <div class="admin-related__grid">
    <?php
    $related = [
      ['href' => ADMIN_URL . '/plans.php', 'label' => 'แผนประกัน', 'desc' => 'รายละเอียดผลิตภัณฑ์ทุกแผน', 'icon' => 'grid'],
      ['href' => ADMIN_URL . '/categories.php', 'label' => 'หมวดหมู่แผน', 'desc' => 'รูปและข้อความหมวดประกัน', 'icon' => 'grid'],
      ['href' => ADMIN_URL . '/blogs.php', 'label' => 'บทความ', 'desc' => 'บทความและความรู้', 'icon' => 'mail'],
      ['href' => ADMIN_URL . '/promotions.php', 'label' => 'โปรโมชัน', 'desc' => 'โปรโมชันออนไลน์', 'icon' => 'help'],
      ['href' => ADMIN_URL . '/faq.php', 'label' => 'FAQ', 'desc' => 'รายการคำถาม-คำตอบ', 'icon' => 'help'],
      ['href' => ADMIN_URL . '/settings.php', 'label' => 'ตั้งค่าเว็บไซต์', 'desc' => 'โทร อีเมล โซเชียล Footer', 'icon' => 'info'],
    ];
    foreach ($related as $item):
    ?>
    <a href="<?= $item['href'] ?>" class="admin-related__item">
      <span class="admin-related__icon"><?= adminPageIcon($item['icon']) ?></span>
      <span class="admin-related__body">
        <span class="admin-related__label"><?= htmlspecialchars($item['label']) ?></span>
        <span class="admin-related__desc"><?= htmlspecialchars($item['desc']) ?></span>
      </span>
      <span class="admin-related__arrow" aria-hidden="true">→</span>
    </a>
    <?php endforeach; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
