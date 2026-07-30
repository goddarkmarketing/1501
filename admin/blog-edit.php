<?php
$pageTitle = 'จัดการบทความ';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/upload.php';

$id = $_GET['id'] ?? '';
$isEdit = !empty($id);
$article = null;
$tags = [];
$contentBlocks = [];

$categoryMap = [
    'life'     => 'ประกันชีวิต',
    'health'   => 'ประกันสุขภาพ',
    'planning' => 'วางแผนการเงิน',
    'claim'    => 'เคลม & สินไหม',
];

$thaiMonths = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
    5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
    9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
];

if ($isEdit) {
    $article = fetchOne('SELECT * FROM blog_articles WHERE id = ?', [$id]);
    if (!$article) {
        header('Location: ' . ADMIN_URL . '/blogs.php?error=' . urlencode('ไม่พบบทความนี้'));
        exit;
    }
    $tags = fetchAll('SELECT tag FROM blog_tags WHERE article_id = ? ORDER BY id', [$id]);
    $contentBlocks = fetchAll('SELECT * FROM blog_content WHERE article_id = ? ORDER BY sort_order', [$id]);
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = getDB();
        $db->beginTransaction();

        $articleId = trim($_POST['id'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $pubDate = $_POST['pub_date'] ?? '';
        $category = $_POST['category'] ?? '';
        $categoryLabel = $categoryMap[$category] ?? $category;
        $image = trim($_POST['image'] ?? '');
        $uploadedImage = handleImageUpload($_FILES['image_file'] ?? null, 'blogs');
        if ($uploadedImage) {
            $image = $uploadedImage;
        }
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (empty($articleId) || empty($title)) {
            throw new Exception('กรุณากรอก ID และชื่อบทความ');
        }

        $dateLabel = '';
        if (!empty($pubDate)) {
            $ts = strtotime($pubDate);
            $day = (int)date('j', $ts);
            $month = $thaiMonths[(int)date('n', $ts)];
            $year = (int)date('Y', $ts) + 543;
            $dateLabel = "$day $month $year";
        }

        $existing = fetchOne('SELECT id FROM blog_articles WHERE id = ?', [$articleId]);
        if ($existing) {
            query('UPDATE blog_articles SET title=?, excerpt=?, pub_date=?, date_label=?, category=?, category_label=?, image=?, is_active=? WHERE id=?', [
                $title, $excerpt, $pubDate ?: null, $dateLabel, $category, $categoryLabel, $image, $isActive, $articleId
            ]);
        } else {
            query('INSERT INTO blog_articles (id, title, excerpt, pub_date, date_label, category, category_label, image, is_active) VALUES (?,?,?,?,?,?,?,?,?)', [
                $articleId, $title, $excerpt, $pubDate ?: null, $dateLabel, $category, $categoryLabel, $image, $isActive
            ]);
        }

        // Tags
        query('DELETE FROM blog_tags WHERE article_id = ?', [$articleId]);
        $tagList = array_filter(array_map('trim', explode(',', $_POST['tags'] ?? '')));
        foreach ($tagList as $tag) {
            query('INSERT INTO blog_tags (article_id, tag) VALUES (?,?)', [$articleId, $tag]);
        }

        // Content blocks
        query('DELETE FROM blog_content WHERE article_id = ?', [$articleId]);
        $blockTypes = $_POST['block_type'] ?? [];
        $blockContents = $_POST['block_content'] ?? [];
        foreach ($blockTypes as $i => $type) {
            $content = trim($blockContents[$i] ?? '');
            if (empty($content)) continue;
            query('INSERT INTO blog_content (article_id, block_type, content, sort_order) VALUES (?,?,?,?)', [
                $articleId, $type, $content, $i
            ]);
        }

        $db->commit();
        header('Location: ' . ADMIN_URL . '/blogs.php?success=' . urlencode('บันทึกบทความ "' . $title . '" เรียบร้อยแล้ว'));
        exit;
    } catch (Exception $e) {
        if (isset($db) && $db->inTransaction()) $db->rollBack();
        $error = $e->getMessage();
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($error): ?>
<div class="bg-red-900/30 border border-red-800 text-red-300 rounded-lg p-3 mb-6 text-sm"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="flex items-center justify-between mb-6">
  <div>
    <a href="<?= ADMIN_URL ?>/blogs.php" class="text-sm text-gray-400 hover:text-white transition">← กลับไปรายการบทความ</a>
    <h1 class="text-xl font-bold text-white mt-1"><?= $isEdit ? 'แก้ไขบทความ: ' . htmlspecialchars($article['title']) : 'เพิ่มบทความใหม่' ?></h1>
  </div>
</div>

<form method="POST" enctype="multipart/form-data" class="space-y-6">

  <!-- Basic Info -->
  <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 space-y-4">
    <h3 class="text-white font-semibold text-base mb-4">ข้อมูลบทความ</h3>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">ID (slug) <span class="text-red-400">*</span></label>
        <input type="text" name="id" value="<?= htmlspecialchars($article['id'] ?? '') ?>" required <?= $isEdit ? 'readonly' : '' ?>
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none <?= $isEdit ? 'opacity-60 cursor-not-allowed' : '' ?>"
          placeholder="เช่น health-insurance-guide-2025">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">ชื่อบทความ <span class="text-red-400">*</span></label>
        <input type="text" name="title" value="<?= htmlspecialchars($article['title'] ?? '') ?>" required
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none">
      </div>
      <div class="lg:col-span-2">
        <label class="block text-sm font-medium text-gray-300 mb-1.5">เนื้อหาย่อ (Excerpt)</label>
        <textarea name="excerpt" rows="2"
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"
          placeholder="คำอธิบายสั้นๆ ของบทความ"><?= htmlspecialchars($article['excerpt'] ?? '') ?></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">วันที่เผยแพร่</label>
        <input type="date" name="pub_date" value="<?= htmlspecialchars($article['pub_date'] ?? date('Y-m-d')) ?>"
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">หมวดหมู่</label>
        <select name="category"
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none">
          <?php foreach ($categoryMap as $key => $label): ?>
          <option value="<?= $key ?>" <?= ($article['category'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <?php renderImageField('image', $article['image'] ?? '', 'รูปภาพบทความ', 'image_file'); ?>
      </div>
      <div class="flex items-center gap-3 pt-6">
        <label class="relative inline-flex items-center cursor-pointer">
          <input type="checkbox" name="is_active" value="1" <?= ($article['is_active'] ?? 1) ? 'checked' : '' ?> class="sr-only peer">
          <div class="w-11 h-6 bg-gray-700 peer-focus:ring-2 peer-focus:ring-brand/30 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand"></div>
          <span class="ms-3 text-sm font-medium text-gray-300">เผยแพร่</span>
        </label>
      </div>
    </div>
  </div>

  <!-- Tags -->
  <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 space-y-4">
    <h3 class="text-white font-semibold text-base mb-4">แท็ก</h3>
    <p class="text-xs text-gray-500 -mt-2">คั่นด้วยคอมมา เช่น ประกันสุขภาพ, FWD, ลดภาษี</p>
    <input type="text" name="tags" value="<?= htmlspecialchars(implode(', ', array_column($tags, 'tag'))) ?>"
      class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"
      placeholder="ประกันสุขภาพ, FWD, ลดภาษี">
  </div>

  <!-- Content Blocks -->
  <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 space-y-4">
    <h3 class="text-white font-semibold text-base mb-4">เนื้อหาบทความ</h3>
    <div id="contentBlocksContainer">
      <?php if (!empty($contentBlocks)): ?>
        <?php foreach ($contentBlocks as $i => $block): ?>
        <div class="content-block bg-gray-800/50 rounded-lg p-4 mb-3 space-y-3">
          <div class="flex items-center justify-between gap-3">
            <select name="block_type[]"
              class="rounded-lg border border-gray-700 bg-gray-800 text-white px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none w-40">
              <option value="p" <?= $block['block_type'] === 'p' ? 'selected' : '' ?>>ย่อหน้า (p)</option>
              <option value="h2" <?= $block['block_type'] === 'h2' ? 'selected' : '' ?>>หัวข้อ (h2)</option>
              <option value="h3" <?= $block['block_type'] === 'h3' ? 'selected' : '' ?>>หัวข้อย่อย (h3)</option>
              <option value="blockquote" <?= $block['block_type'] === 'blockquote' ? 'selected' : '' ?>>อ้างอิง (blockquote)</option>
            </select>
            <button type="button" onclick="this.closest('.content-block').remove()" class="text-red-400 hover:text-red-300 text-xs">ลบ</button>
          </div>
          <textarea name="block_content[]" rows="3"
            class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"><?= htmlspecialchars($block['content']) ?></textarea>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <button type="button" onclick="addContentBlock()"
      class="border border-dashed border-gray-700 text-gray-400 hover:text-white hover:border-gray-500 w-full py-2 rounded-lg text-sm transition">+ เพิ่มบล็อกเนื้อหา</button>
  </div>

  <!-- Submit -->
  <div class="flex items-center gap-4">
    <button type="submit" class="admin-btn-primary px-6 py-3">
      <?= $isEdit ? 'บันทึกการเปลี่ยนแปลง' : 'สร้างบทความใหม่' ?>
    </button>
    <a href="<?= ADMIN_URL ?>/blogs.php" class="text-slate-500 hover:text-brand text-sm">ยกเลิก</a>
  </div>
</form>

<?php
$extraScripts = <<<'SCRIPT'
<script>
function addContentBlock() {
  const html = `<div class="content-block bg-gray-800/50 rounded-lg p-4 mb-3 space-y-3">
    <div class="flex items-center justify-between gap-3">
      <select name="block_type[]" class="rounded-lg border border-gray-700 bg-gray-800 text-white px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none w-40">
        <option value="p">ย่อหน้า (p)</option>
        <option value="h2">หัวข้อ (h2)</option>
        <option value="h3">หัวข้อย่อย (h3)</option>
        <option value="blockquote">อ้างอิง (blockquote)</option>
      </select>
      <button type="button" onclick="this.closest('.content-block').remove()" class="text-red-400 hover:text-red-300 text-xs">ลบ</button>
    </div>
    <textarea name="block_content[]" rows="3" class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none" placeholder="เนื้อหา..."></textarea>
  </div>`;
  document.getElementById('contentBlocksContainer').insertAdjacentHTML('beforeend', html);
}
</script>
SCRIPT;
require_once __DIR__ . '/includes/footer.php';
?>
