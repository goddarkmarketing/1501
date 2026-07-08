<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/ui.php';
require_once __DIR__ . '/includes/upload.php';

$pageSlug = $_GET['page'] ?? '';
$definitions = getPageDefinitions();
if (!isset($definitions[$pageSlug])) {
    header('Location: ' . ADMIN_URL . '/pages.php');
    exit;
}

$pageDef = $definitions[$pageSlug];
$pageTitle = 'แก้ไข: ' . $pageDef['label'];
$message = '';
$defaults = getDefaultSectionValues($pageSlug);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($pageDef['sections'] as $section) {
            $key = $section['key'];
            $value = $_POST['sections'][$key] ?? '';
            if ($section['type'] === 'image') {
                $uploaded = handleImageUpload($_FILES['section_file_' . $key] ?? null, 'pages');
                if ($uploaded) {
                    $value = $uploaded;
                }
            }
            saveSectionValue($pageSlug, $key, $value, $section['label'], $section['type']);
        }
        $message = 'บันทึกเรียบร้อย — กดเผยแพร่เว็บไซต์เพื่อให้แสดงบนหน้าบ้าน';
    } catch (Throwable $e) {
        $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        $messageType = 'error';
    }
}

$values = [];
foreach ($pageDef['sections'] as $section) {
    $values[$section['key']] = getSectionValue(
        $pageSlug,
        $section['key'],
        $defaults[$section['key']] ?? ''
    );
}

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
<?= adminAlert($message, ($messageType ?? '') === 'error' ? 'error' : 'success') ?>
<?php endif; ?>

<div class="flex items-center justify-between mb-6">
  <a href="<?= ADMIN_URL ?>/pages.php" class="text-sm text-slate-500 hover:text-brand">← กลับรายการหน้า</a>
  <a href="<?= htmlspecialchars($pageDef['url']) ?>" target="_blank" class="admin-btn-outline text-sm">ดูหน้าจริง ↗</a>
</div>

<form method="POST" enctype="multipart/form-data" class="admin-card p-6 space-y-5">
  <?php foreach ($pageDef['sections'] as $section): ?>
  <div>
    <?php if ($section['type'] === 'image'): ?>
      <?php renderImageField('sections[' . $section['key'] . ']', $values[$section['key']], $section['label'], 'section_file_' . $section['key']); ?>
    <?php elseif ($section['type'] === 'textarea' || $section['type'] === 'html'): ?>
    <label class="admin-label" for="sec_<?= $section['key'] ?>"><?= htmlspecialchars($section['label']) ?></label>
    <textarea id="sec_<?= $section['key'] ?>" name="sections[<?= $section['key'] ?>]" rows="<?= $section['type'] === 'html' ? 5 : 4 ?>" class="admin-input"><?= htmlspecialchars($values[$section['key']]) ?></textarea>
    <?php else: ?>
    <label class="admin-label" for="sec_<?= $section['key'] ?>"><?= htmlspecialchars($section['label']) ?></label>
    <input type="text" id="sec_<?= $section['key'] ?>" name="sections[<?= $section['key'] ?>]" value="<?= htmlspecialchars($values[$section['key']]) ?>" class="admin-input">
    <?php endif; ?>
  </div>
  <?php endforeach; ?>

  <div class="flex gap-3 pt-2">
    <button type="submit" class="admin-btn-primary">บันทึก</button>
    <a href="<?= ADMIN_URL ?>/api/publish.php" onclick="return confirm('เผยแพร่เว็บไซต์ตอนนี้?')" class="admin-btn-outline">บันทึกแล้วเผยแพร่</a>
  </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
