<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/ui.php';
require_once __DIR__ . '/feedback-lib.php';

if (feedbackPreviewRequested()) {
    feedbackActivatePreview();
}

requireAdminAccess();
$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?? 'Admin' ?> — Agent Thailand</title>
  <link href="https://cdn.jsdelivr.net/npm/preline@3/dist/preline.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{brand:'#150f96','brand-light':'#2a1fbb','brand-dark':'#0e0a6e'}}}}</script>
  <link rel="stylesheet" href="<?= ADMIN_URL ?>/assets/admin.css">
  <link rel="stylesheet" href="<?= ADMIN_URL ?>/assets/admin-images.css">
  <meta name="site-url" content="<?= SITE_URL ?>">
</head>
<body class="admin-app">

<aside id="hs-sidebar" data-feedback-id="sidebar-navigation" class="hs-overlay [--auto-close:lg] hs-overlay-open:translate-x-0 -translate-x-full transition-all duration-300 transform fixed top-0 start-0 bottom-0 z-[60] w-64 bg-white border-e border-slate-200 lg:block lg:translate-x-0 lg:end-auto lg:bottom-0 shadow-sm" role="dialog" tabindex="-1" aria-label="Sidebar">
  <div class="flex flex-col h-full">
    <div class="px-5 pt-5 pb-4 border-b border-slate-100">
      <a href="<?= ADMIN_URL ?>/" class="text-lg font-bold text-slate-800">Agent Thailand</a>
      <p class="text-xs text-slate-400 mt-0.5">ระบบจัดการเว็บไซต์</p>
    </div>
    <nav class="flex-1 overflow-y-auto p-3 space-y-4 text-sm">
      <?php
      $adminNavIcon = static function (string $name): string {
        $map = [
          'dashboard' => 'layout-dashboard',
          'pages' => 'file-text',
          'faq' => 'circle-help',
          'plans' => 'shield-check',
          'categories' => 'folder-tree',
          'blogs' => 'newspaper',
          'promos' => 'badge-percent',
          'blocks' => 'boxes',
          'hospitals' => 'hospital',
          'filters' => 'funnel',
          'contacts' => 'mail',
          'users' => 'users',
          'settings' => 'settings',
          'seed' => 'cloud-download',
          'publish' => 'upload',
          'feedback' => 'message-square',
        ];
        $icon = $map[$name] ?? 'file-text';
        return '<i data-lucide="' . htmlspecialchars($icon) . '" class="admin-sidebar-icon" aria-hidden="true"></i>';
      };
      $navLink = fn($page, $label, $icon) => [
        'href' => feedbackAppendPreview(ADMIN_URL . '/' . $page . '.php'),
        'active' => $currentPage === $page,
        'label' => $label,
        'icon' => $icon,
      ];
      $groups = [
        'ภาพรวม' => [
          $navLink('index', 'แดชบอร์ด', 'dashboard'),
          $navLink('feedback', 'Feedback', 'feedback'),
        ],
        'เนื้อหาเว็บไซต์' => [
          $navLink('pages', 'จัดการหน้าเว็บ', 'pages'),
          $navLink('faq', 'คำถามที่พบบ่อย', 'faq'),
        ],
        'ผลิตภัณฑ์' => [
          $navLink('plans', 'แผนประกัน', 'plans'),
          $navLink('categories', 'หมวดหมู่แผน', 'categories'),
        ],
        'บทความ & โปรโมชัน' => [
          $navLink('blogs', 'บทความ', 'blogs'),
          $navLink('promotions', 'โปรโมชัน', 'promos'),
        ],
        'ระบบ' => [
          $navLink('blocks', 'บล็อกเนื้อหา', 'blocks'),
          $navLink('hospitals', 'โรงพยาบาล', 'hospitals'),
          $navLink('promo-filters', 'ตัวกรองโปรโมชัน', 'filters'),
          $navLink('contacts', 'ข้อความติดต่อ', 'contacts'),
          $navLink('users', 'ผู้ดูแลระบบ', 'users'),
          $navLink('settings', 'ตั้งค่าเว็บไซต์', 'settings'),
        ],
      ];
      foreach ($groups as $groupLabel => $navItems): ?>
      <div>
        <p class="px-3 mb-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400"><?= $groupLabel ?></p>
        <div class="space-y-0.5">
          <?php foreach ($navItems as $item): ?>
          <a href="<?= $item['href'] ?>"
             class="admin-sidebar-link <?= $item['active'] ? 'admin-sidebar-link--active' : '' ?>">
            <?= $adminNavIcon($item['icon']) ?>
            <span class="leading-snug"><?= $item['label'] ?></span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>

      <div class="pt-2 border-t border-slate-100 space-y-1">
        <a href="<?= ADMIN_URL ?>/seed.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-500 hover:bg-slate-50 text-xs">
          <?= $adminNavIcon('seed') ?>
          นำเข้าข้อมูลเริ่มต้น
        </a>
        <a href="<?= ADMIN_URL ?>/api/publish.php" onclick="return confirm('เผยแพร่การเปลี่ยนแปลงทั้งหมดไปยังเว็บไซต์?')"
           class="flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-medium text-xs">
          <?= $adminNavIcon('publish') ?>
          เผยแพร่เว็บไซต์
        </a>
      </div>
    </nav>
    <div class="p-3 border-t border-slate-100">
      <div class="flex items-center justify-between px-2">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-full bg-brand text-white flex items-center justify-center text-xs font-bold"><?= mb_substr(adminName(), 0, 1) ?></div>
          <span class="text-sm text-slate-700"><?= htmlspecialchars(adminName()) ?></span>
        </div>
        <a href="<?= ADMIN_URL ?>/logout.php" class="text-slate-400 hover:text-red-500" title="ออกจากระบบ">
          <i data-lucide="log-out" class="admin-sidebar-icon" aria-hidden="true"></i>
        </a>
      </div>
    </div>
  </div>
</aside>

<div class="lg:ps-64 min-h-screen">
  <header data-feedback-id="page-header" class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-slate-200 px-4 sm:px-6 py-3 flex items-center gap-4">
    <button type="button" class="lg:hidden text-slate-500" data-hs-overlay="#hs-sidebar">
      <i data-lucide="menu" class="w-6 h-6" aria-hidden="true"></i>
    </button>
    <h2 class="text-lg font-semibold text-slate-800"><?= $pageTitle ?? '' ?></h2>
    <div class="ms-auto flex items-center gap-3">
      <?php if (feedbackCanUseTool()): ?>
      <span class="hidden sm:inline text-xs text-brand font-medium">กดปุ่ม “แจ้งแก้ไข” มุมขวาล่างเพื่อแคปและพิมพ์รายละเอียด</span>
      <?php endif; ?>
      <a href="<?= SITE_URL ?>/" target="_blank" class="text-xs text-slate-500 hover:text-brand font-medium">ดูเว็บไซต์ ↗</a>
    </div>
  </header>

  <main data-feedback-id="main-content" class="p-4 sm:p-6 w-full">
  <?php if (!empty($_SESSION['flash'])): ?>
  <div class="admin-alert-success mb-6"><?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="admin-alert-error mb-6"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
  <?php endif; ?>
