<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/upload.php';

$id = trim($_GET['id'] ?? '');
$isNew = isset($_GET['new']) || $id === '';
$cat = null;
$features = [];
$message = '';
$messageType = '';

if (!$isNew) {
    $cat = fetchOne('SELECT * FROM plan_categories WHERE id = ?', [$id]);
    if (!$cat) {
        header('Location: ' . ADMIN_URL . '/categories.php');
        exit;
    }
    $features = fetchAll('SELECT * FROM plan_category_features WHERE category_id = ? ORDER BY sort_order, id', [$id]);
} else {
    $cat = [
        'id' => '',
        'label' => '',
        'headline' => '',
        'hero_image' => '',
        'intro_title' => '',
        'intro_text' => '',
        'promo_section' => '',
        'why_section' => '',
        'listing_goals' => '',
        'icon' => '',
        'sort_order' => 0,
    ];
}

$pageTitle = $isNew ? 'เพิ่มหมวดหมู่แผน' : ('แก้ไขหมวด: ' . $cat['label']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $newId = $isNew
            ? strtolower(trim(preg_replace('/[^a-zA-Z0-9\-]+/', '-', $_POST['id'] ?? '') ?? '', '-'))
            : $id;
        $label = trim($_POST['label'] ?? '');

        if ($newId === '' || $label === '') {
            throw new Exception('กรุณากรอก ID และชื่อหมวดหมู่');
        }
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $newId)) {
            throw new Exception('ID ใช้ได้เฉพาะ a-z, 0-9 และขีดกลาง เช่น critical-illness');
        }

        if ($isNew) {
            $exists = fetchOne('SELECT id FROM plan_categories WHERE id = ?', [$newId]);
            if ($exists) {
                throw new Exception('มีหมวดหมู่นี้แล้ว (ID: ' . $newId . ')');
            }
        }

        $heroImage = trim($_POST['hero_image'] ?? '');
        $uploaded = handleImageUpload($_FILES['hero_image_file'] ?? null, 'categories');
        if ($uploaded) {
            $heroImage = $uploaded;
        }

        $fields = [
            $label,
            trim($_POST['headline'] ?? ''),
            $heroImage,
            trim($_POST['intro_title'] ?? ''),
            trim($_POST['intro_text'] ?? ''),
            trim($_POST['promo_section'] ?? ''),
            trim($_POST['why_section'] ?? ''),
            trim($_POST['listing_goals'] ?? ''),
            trim($_POST['icon'] ?? ''),
            (int) ($_POST['sort_order'] ?? 0),
        ];

        if ($isNew) {
            query(
                'INSERT INTO plan_categories (id, label, headline, hero_image, intro_title, intro_text, promo_section, why_section, listing_goals, icon, sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?)',
                [$newId, ...$fields]
            );
            $id = $newId;
        } else {
            query(
                'UPDATE plan_categories SET label=?, headline=?, hero_image=?, intro_title=?, intro_text=?, promo_section=?, why_section=?, listing_goals=?, icon=?, sort_order=? WHERE id=?',
                [...$fields, $id]
            );
        }

        query('DELETE FROM plan_category_features WHERE category_id = ?', [$id]);
        $titles = $_POST['feature_title'] ?? [];
        $descs = $_POST['feature_desc'] ?? [];
        foreach ($titles as $i => $title) {
            $title = trim($title);
            $desc = trim($descs[$i] ?? '');
            if ($title === '' && $desc === '') continue;
            query(
                'INSERT INTO plan_category_features (category_id, title, description, sort_order) VALUES (?,?,?,?)',
                [$id, $title, $desc, $i]
            );
        }

        // เพิ่มกลุ่มใน Mega Menu ผลิตภัณฑ์ถ้ายังไม่มี
        try {
            require_once __DIR__ . '/includes/cms.php';
            $nav = getCmsBlock('plan_nav_menu');
            if (!is_array($nav)) {
                $nav = [];
            }
            $found = false;
            foreach ($nav as &$column) {
                if (!is_array($column) || !isset($column['groups']) || !is_array($column['groups'])) continue;
                foreach ($column['groups'] as $group) {
                    if (($group['category'] ?? '') === $id) {
                        $found = true;
                        break 2;
                    }
                }
            }
            unset($column);
            if (!$found) {
                $newGroup = [
                    'category' => $id,
                    'title' => $label,
                    'viewAllHref' => 'plan-category.html?category=' . $id,
                    'products' => [],
                ];
                if (!$nav) {
                    $nav = [['groups' => [$newGroup]]];
                } else {
                    $last = count($nav) - 1;
                    if (!isset($nav[$last]['groups']) || !is_array($nav[$last]['groups'])) {
                        $nav[$last]['groups'] = [];
                    }
                    // ถ้าคอลัมน์สุดท้ายมีครบ 2 กลุ่มแล้ว ให้เปิดคอลัมน์ใหม่
                    if (count($nav[$last]['groups']) >= 2) {
                        $nav[] = ['groups' => [$newGroup]];
                    } else {
                        $nav[$last]['groups'][] = $newGroup;
                    }
                }
                saveCmsBlock('plan_nav_menu', $nav);
            }
        } catch (Throwable $e) {
            // เมนูไม่บังคับ
        }

        $_SESSION['flash'] = $isNew ? 'เพิ่มหมวดหมู่เรียบร้อยแล้ว — กดเผยแพร่เพื่ออัปเดตหน้าบ้าน' : 'บันทึกหมวดหมู่เรียบร้อยแล้ว';
        header('Location: ' . ADMIN_URL . '/categories.php');
        exit;
    } catch (Throwable $e) {
        $message = $e->getMessage();
        $messageType = 'error';
        $cat = array_merge($cat, [
            'id' => $_POST['id'] ?? ($cat['id'] ?? ''),
            'label' => $_POST['label'] ?? '',
            'headline' => $_POST['headline'] ?? '',
            'hero_image' => $_POST['hero_image'] ?? ($cat['hero_image'] ?? ''),
            'intro_title' => $_POST['intro_title'] ?? '',
            'intro_text' => $_POST['intro_text'] ?? '',
            'promo_section' => $_POST['promo_section'] ?? '',
            'why_section' => $_POST['why_section'] ?? '',
            'listing_goals' => $_POST['listing_goals'] ?? '',
            'icon' => $_POST['icon'] ?? '',
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ]);
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
<?= adminAlert($message, $messageType === 'error' ? 'error' : 'success') ?>
<?php endif; ?>

<div class="mb-6">
  <a href="<?= ADMIN_URL ?>/categories.php" class="text-sm text-slate-500 hover:text-brand">← กลับหมวดหมู่</a>
</div>

<form method="POST" enctype="multipart/form-data" class="admin-card p-6 space-y-5 max-w-2xl">
  <div class="grid sm:grid-cols-2 gap-4">
    <div>
      <label class="admin-label" for="id">ID หมวด (ภาษาอังกฤษ)</label>
      <?php if ($isNew): ?>
      <input type="text" id="id" name="id" value="<?= htmlspecialchars($cat['id'] ?? '') ?>" class="admin-input" required placeholder="เช่น dental-care" pattern="[a-z0-9]+(-[a-z0-9]+)*">
      <p class="text-xs text-slate-400 mt-1">ใช้ใน URL เช่น plan-category.html?category=dental-care</p>
      <?php else: ?>
      <input type="text" value="<?= htmlspecialchars($cat['id']) ?>" class="admin-input opacity-60" readonly>
      <?php endif; ?>
    </div>
    <div>
      <label class="admin-label" for="sort_order">ลำดับแสดง</label>
      <input type="number" id="sort_order" name="sort_order" value="<?= (int) ($cat['sort_order'] ?? 0) ?>" class="admin-input">
    </div>
  </div>

  <div>
    <label class="admin-label" for="label">ชื่อหมวดหมู่</label>
    <input type="text" id="label" name="label" value="<?= htmlspecialchars($cat['label'] ?? '') ?>" class="admin-input" required placeholder="เช่น ประกันทันตกรรม">
  </div>

  <div>
    <label class="admin-label" for="headline">หัวข้อ Hero</label>
    <input type="text" id="headline" name="headline" value="<?= htmlspecialchars($cat['headline'] ?? '') ?>" class="admin-input">
  </div>

  <?php renderImageField('hero_image', $cat['hero_image'] ?? '', 'รูป Hero หมวดหมู่', 'hero_image_file'); ?>

  <div>
    <label class="admin-label" for="icon">ไอคอน Lucide</label>
    <input type="text" id="icon" name="icon" value="<?= htmlspecialchars($cat['icon'] ?? '') ?>" class="admin-input" placeholder="heart-handshake">
  </div>

  <div>
    <label class="admin-label" for="intro_title">หัวข้อ Intro</label>
    <input type="text" id="intro_title" name="intro_title" value="<?= htmlspecialchars($cat['intro_title'] ?? '') ?>" class="admin-input">
  </div>

  <div>
    <label class="admin-label" for="intro_text">ข้อความ Intro</label>
    <textarea id="intro_text" name="intro_text" rows="4" class="admin-input"><?= htmlspecialchars($cat['intro_text'] ?? '') ?></textarea>
  </div>

  <div class="grid sm:grid-cols-2 gap-4">
    <div>
      <label class="admin-label" for="promo_section">หัวข้อส่วนโปรโมชัน</label>
      <input type="text" id="promo_section" name="promo_section" value="<?= htmlspecialchars($cat['promo_section'] ?? '') ?>" class="admin-input">
    </div>
    <div>
      <label class="admin-label" for="why_section">หัวข้อส่วน Why</label>
      <input type="text" id="why_section" name="why_section" value="<?= htmlspecialchars($cat['why_section'] ?? '') ?>" class="admin-input">
    </div>
  </div>

  <div>
    <label class="admin-label" for="listing_goals">เป้าหมายการออม (listing goals)</label>
    <input type="text" id="listing_goals" name="listing_goals" value="<?= htmlspecialchars($cat['listing_goals'] ?? '') ?>" class="admin-input">
  </div>

  <div class="border-t border-slate-100 pt-5">
    <h4 class="font-medium text-slate-800 mb-3">การ์ดจุดเด่น (Features)</h4>
    <div class="space-y-3" id="featureRows">
      <?php foreach ($features as $f): ?>
      <div class="grid sm:grid-cols-2 gap-2">
        <input type="text" name="feature_title[]" value="<?= htmlspecialchars($f['title']) ?>" placeholder="หัวข้อ" class="admin-input text-sm">
        <input type="text" name="feature_desc[]" value="<?= htmlspecialchars($f['description']) ?>" placeholder="คำอธิบาย" class="admin-input text-sm">
      </div>
      <?php endforeach; ?>
      <?php if (!$features): ?>
      <div class="grid sm:grid-cols-2 gap-2">
        <input type="text" name="feature_title[]" placeholder="หัวข้อ" class="admin-input text-sm">
        <input type="text" name="feature_desc[]" placeholder="คำอธิบาย" class="admin-input text-sm">
      </div>
      <?php endif; ?>
    </div>
    <button type="button" class="admin-btn-outline text-xs mt-2" onclick="addFeatureRow()">+ เพิ่มการ์ด</button>
  </div>

  <div class="flex gap-3 pt-2">
    <button type="submit" class="admin-btn-primary"><?= $isNew ? 'เพิ่มหมวดหมู่' : 'บันทึก' ?></button>
    <a href="<?= ADMIN_URL ?>/categories.php" class="admin-btn-outline">ยกเลิก</a>
  </div>
</form>

<script>
function addFeatureRow() {
  const row = document.createElement('div');
  row.className = 'grid sm:grid-cols-2 gap-2';
  row.innerHTML = '<input type="text" name="feature_title[]" placeholder="หัวข้อ" class="admin-input text-sm"><input type="text" name="feature_desc[]" placeholder="คำอธิบาย" class="admin-input text-sm">';
  document.getElementById('featureRows').appendChild(row);
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
