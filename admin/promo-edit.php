<?php
$pageTitle = 'จัดการโปรโมชัน';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/upload.php';

$id = $_GET['id'] ?? '';
$isEdit = !empty($id);
$promo = null;
$bullets = [];

if ($isEdit) {
    $promo = fetchOne('SELECT * FROM promotions WHERE id = ?', [$id]);
    if (!$promo) {
        header('Location: ' . ADMIN_URL . '/promotions.php?error=' . urlencode('ไม่พบโปรโมชันนี้'));
        exit;
    }
    $bullets = fetchAll('SELECT bullet_text FROM promotion_bullets WHERE promotion_id = ? ORDER BY sort_order', [$id]);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = getDB();
        $db->beginTransaction();

        $promoId       = trim($_POST['id'] ?? '');
        $title         = trim($_POST['title'] ?? '');
        $highlight     = trim($_POST['highlight'] ?? '');
        $descHtml      = trim($_POST['description_html'] ?? '');
        $category      = trim($_POST['category'] ?? '');
        $categoryKey   = trim($_POST['category_key'] ?? '');
        $badge         = trim($_POST['badge'] ?? '');
        $badgeType     = $_POST['badge_type'] ?? '';
        $image         = trim($_POST['image'] ?? '');
        $uploadedImage = handleImageUpload($_FILES['image_file'] ?? null, 'promos');
        if ($uploadedImage) {
            $image = $uploadedImage;
        }
        $cta           = trim($_POST['cta'] ?? '');
        $ctaHref       = trim($_POST['cta_href'] ?? '');
        $promoCode     = trim($_POST['promo_code'] ?? '');
        $validUntil    = trim($_POST['valid_until'] ?? '');
        $installment   = trim($_POST['installment'] ?? '');
        $isPopular     = isset($_POST['is_popular']) ? 1 : 0;
        $isNew         = isset($_POST['is_new']) ? 1 : 0;
        $isActive      = isset($_POST['is_active']) ? 1 : 0;
        $sortOrder     = intval($_POST['sort_order'] ?? 0);

        if (empty($promoId) || empty($title)) {
            throw new Exception('กรุณากรอก ID และชื่อโปรโมชัน');
        }

        $existing = fetchOne('SELECT id FROM promotions WHERE id = ?', [$promoId]);
        if ($existing) {
            query('UPDATE promotions SET title=?, highlight=?, description_html=?, category=?, category_key=?, badge=?, badge_type=?, image=?, cta=?, cta_href=?, promo_code=?, valid_until=?, installment=?, is_popular=?, is_new=?, is_active=?, sort_order=? WHERE id=?', [
                $title, $highlight, $descHtml, $category, $categoryKey, $badge, $badgeType, $image, $cta, $ctaHref, $promoCode, $validUntil, $installment, $isPopular, $isNew, $isActive, $sortOrder, $promoId
            ]);
        } else {
            query('INSERT INTO promotions (id, title, highlight, description_html, category, category_key, badge, badge_type, image, cta, cta_href, promo_code, valid_until, installment, is_popular, is_new, is_active, sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [
                $promoId, $title, $highlight, $descHtml, $category, $categoryKey, $badge, $badgeType, $image, $cta, $ctaHref, $promoCode, $validUntil, $installment, $isPopular, $isNew, $isActive, $sortOrder
            ]);
        }

        // Bullets
        query('DELETE FROM promotion_bullets WHERE promotion_id = ?', [$promoId]);
        $lines = array_filter(array_map('trim', explode("\n", $_POST['bullets'] ?? '')));
        foreach ($lines as $i => $line) {
            query('INSERT INTO promotion_bullets (promotion_id, bullet_text, sort_order) VALUES (?,?,?)', [$promoId, $line, $i]);
        }

        $db->commit();
        header('Location: ' . ADMIN_URL . '/promotions.php?success=' . urlencode('บันทึกโปรโมชัน "' . $title . '" เรียบร้อยแล้ว'));
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
    <a href="<?= ADMIN_URL ?>/promotions.php" class="text-sm text-gray-400 hover:text-white transition">← กลับไปรายการโปรโมชัน</a>
    <h1 class="text-xl font-bold text-white mt-1"><?= $isEdit ? 'แก้ไขโปรโมชัน: ' . htmlspecialchars($promo['title']) : 'เพิ่มโปรโมชันใหม่' ?></h1>
  </div>
</div>

<form method="POST" enctype="multipart/form-data" class="space-y-6">

  <!-- Basic Info -->
  <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 space-y-4">
    <h3 class="text-white font-semibold text-base mb-4">ข้อมูลพื้นฐาน</h3>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">ID (slug) <span class="text-red-400">*</span></label>
        <input type="text" name="id" value="<?= htmlspecialchars($promo['id'] ?? '') ?>" required <?= $isEdit ? 'readonly' : '' ?>
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none <?= $isEdit ? 'opacity-60 cursor-not-allowed' : '' ?>"
          placeholder="เช่น summer-sale-2025">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">ชื่อโปรโมชัน <span class="text-red-400">*</span></label>
        <input type="text" name="title" value="<?= htmlspecialchars($promo['title'] ?? '') ?>" required
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none">
      </div>
      <div class="lg:col-span-2">
        <label class="block text-sm font-medium text-gray-300 mb-1.5">ข้อความไฮไลท์</label>
        <input type="text" name="highlight" value="<?= htmlspecialchars($promo['highlight'] ?? '') ?>"
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"
          placeholder="เช่น ลดสูงสุด 50%">
      </div>
      <div class="lg:col-span-2">
        <label for="promo-description-html" class="block text-sm font-medium text-gray-300 mb-1.5">รายละเอียด</label>
        <p class="text-xs text-slate-500 mb-2">พิมพ์และจัดรูปแบบข้อความได้เลย ไม่ต้องเขียนโค้ด — ใช้แถบเครื่องมือด้านบนสำหรับตัวหนา รายการ ลิงก์ ฯลฯ</p>
        <textarea id="promo-description-html" name="description_html" rows="10"
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"
          placeholder="อธิบายรายละเอียดโปรโมชัน เช่น เงื่อนไข ส่วนลด ของสมนาคุณ"><?= htmlspecialchars($promo['description_html'] ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <!-- Category & Badge -->
  <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 space-y-4">
    <h3 class="text-white font-semibold text-base mb-4">หมวดหมู่ & ป้าย</h3>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">หมวดหมู่</label>
        <input type="text" name="category" value="<?= htmlspecialchars($promo['category'] ?? '') ?>"
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"
          placeholder="เช่น ประกันสุขภาพ">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">Category Key</label>
        <input type="text" name="category_key" value="<?= htmlspecialchars($promo['category_key'] ?? '') ?>"
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"
          placeholder="เช่น health">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">ข้อความป้าย</label>
        <input type="text" name="badge" value="<?= htmlspecialchars($promo['badge'] ?? '') ?>"
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"
          placeholder="เช่น ขายดี, ใหม่">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">ประเภทป้าย</label>
        <select name="badge_type"
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none">
          <option value="">-- ไม่มี --</option>
          <option value="hot" <?= ($promo['badge_type'] ?? '') === 'hot' ? 'selected' : '' ?>>Hot 🔥</option>
          <option value="new" <?= ($promo['badge_type'] ?? '') === 'new' ? 'selected' : '' ?>>New ✨</option>
          <option value="limited" <?= ($promo['badge_type'] ?? '') === 'limited' ? 'selected' : '' ?>>Limited ⏳</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Image & CTA -->
  <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 space-y-4">
    <h3 class="text-white font-semibold text-base mb-4">รูปภาพ & ปุ่มกดดำเนินการ</h3>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <div class="lg:col-span-2">
        <?php renderImageField('image', $promo['image'] ?? '', 'รูปภาพโปรโมชัน', 'image_file'); ?>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">ข้อความปุ่ม CTA</label>
        <input type="text" name="cta" value="<?= htmlspecialchars($promo['cta'] ?? '') ?>"
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"
          placeholder="เช่น ดูรายละเอียด">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">ลิงก์ CTA</label>
        <input type="text" name="cta_href" value="<?= htmlspecialchars($promo['cta_href'] ?? '') ?>"
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"
          placeholder="/1501/plans/...">
      </div>
    </div>
  </div>

  <!-- Promo Details -->
  <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 space-y-4">
    <h3 class="text-white font-semibold text-base mb-4">รายละเอียดโปรโมชัน</h3>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">รหัสโปรโมชัน</label>
        <input type="text" name="promo_code" value="<?= htmlspecialchars($promo['promo_code'] ?? '') ?>"
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"
          placeholder="เช่น SUMMER50">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">หมดอายุ</label>
        <input type="text" name="valid_until" value="<?= htmlspecialchars($promo['valid_until'] ?? '') ?>"
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"
          placeholder="เช่น 31 ธ.ค. 2568">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">ผ่อนชำระ</label>
        <input type="text" name="installment" value="<?= htmlspecialchars($promo['installment'] ?? '') ?>"
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"
          placeholder="เช่น ผ่อน 0% 10 เดือน">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">ลำดับการแสดง</label>
        <input type="number" name="sort_order" value="<?= htmlspecialchars($promo['sort_order'] ?? '0') ?>"
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none">
      </div>
    </div>
    <div class="flex flex-wrap gap-6 pt-2">
      <label class="relative inline-flex items-center cursor-pointer">
        <input type="checkbox" name="is_popular" value="1" <?= ($promo['is_popular'] ?? 0) ? 'checked' : '' ?> class="sr-only peer">
        <div class="w-11 h-6 bg-gray-700 peer-focus:ring-2 peer-focus:ring-brand/30 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand"></div>
        <span class="ms-3 text-sm font-medium text-gray-300">ยอดนิยม</span>
      </label>
      <label class="relative inline-flex items-center cursor-pointer">
        <input type="checkbox" name="is_new" value="1" <?= ($promo['is_new'] ?? 0) ? 'checked' : '' ?> class="sr-only peer">
        <div class="w-11 h-6 bg-gray-700 peer-focus:ring-2 peer-focus:ring-brand/30 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand"></div>
        <span class="ms-3 text-sm font-medium text-gray-300">ใหม่</span>
      </label>
      <label class="relative inline-flex items-center cursor-pointer">
        <input type="checkbox" name="is_active" value="1" <?= ($promo['is_active'] ?? 1) ? 'checked' : '' ?> class="sr-only peer">
        <div class="w-11 h-6 bg-gray-700 peer-focus:ring-2 peer-focus:ring-brand/30 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand"></div>
        <span class="ms-3 text-sm font-medium text-gray-300">เปิดใช้งาน</span>
      </label>
    </div>
  </div>

  <!-- Bullets -->
  <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 space-y-4">
    <h3 class="text-white font-semibold text-base mb-4">รายการจุดเด่น (Bullets)</h3>
    <p class="text-xs text-gray-500 -mt-2">ใส่ 1 รายการต่อบรรทัด</p>
    <textarea name="bullets" rows="6"
      class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"
      placeholder="ลดค่าเบี้ย 30%&#10;ฟรี! ตรวจสุขภาพ&#10;คุ้มครองทันที"><?= htmlspecialchars(implode("\n", array_column($bullets, 'bullet_text'))) ?></textarea>
  </div>

  <!-- Submit -->
  <div class="flex items-center gap-4">
    <button type="submit" class="admin-btn-primary px-6 py-3">
      <?= $isEdit ? 'บันทึกการเปลี่ยนแปลง' : 'สร้างโปรโมชันใหม่' ?>
    </button>
    <a href="<?= ADMIN_URL ?>/promotions.php" class="text-slate-500 hover:text-brand text-sm">ยกเลิก</a>
  </div>
</form>

<?php
ob_start();
?>
<link rel="stylesheet" href="<?= ADMIN_URL ?>/assets/admin-editor.css">
<script src="https://cdn.jsdelivr.net/npm/tinymce@7.6.1/tinymce.min.js" referrerpolicy="origin"></script>
<script>
(function () {
  if (typeof tinymce === 'undefined') return;

  tinymce.init({
    selector: '#promo-description-html',
    license_key: 'gpl',
    height: 380,
    menubar: false,
    branding: false,
    promotion: false,
    plugins: 'lists link table code autoresize',
    toolbar:
      'undo redo | blocks | bold italic underline strikethrough | ' +
      'alignleft aligncenter alignright | bullist numlist | ' +
      'link table | removeformat | code',
    block_formats: 'ย่อหน้า=p; หัวข้อ 2=h2; หัวข้อ 3=h3; หัวข้อ 4=h4',
    content_style:
      'body { font-family: "Sarabun", "Segoe UI", sans-serif; font-size: 15px; line-height: 1.6; color: #1e293b; padding: 12px; }' +
      'p { margin: 0 0 0.75em; } ul, ol { margin: 0 0 0.75em; padding-left: 1.4em; }',
    language: 'en',
    convert_urls: false,
    relative_urls: false,
    entity_encoding: 'raw',
    valid_elements: '*[*]',
    extended_valid_elements: '*[*]',
    setup: function (editor) {
      editor.on('change keyup SetContent', function () {
        editor.save();
      });
    },
  });

  var form = document.querySelector('form');
  if (form) {
    form.addEventListener('submit', function () {
      if (window.tinymce) tinymce.triggerSave();
    });
  }
})();
</script>
<?php
$extraScripts = ob_get_clean();
require_once __DIR__ . '/includes/footer.php';
?>
