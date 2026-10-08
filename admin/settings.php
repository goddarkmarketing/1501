<?php
$pageTitle = 'ตั้งค่าเว็บไซต์';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/upload.php';
require_once __DIR__ . '/includes/site-settings-lib.php';

$message = '';
$messageType = '';

$labelMap = [
    'site_name'    => 'ชื่อเว็บไซต์',
    'site_tagline' => 'คำอธิบาย',
    'phone'        => 'เบอร์โทรหลัก',
    'phone2'       => 'เบอร์โทรสำรอง',
    'business_hours' => 'เวลาทำการ',
    'copyright'    => 'ข้อความลิขสิทธิ์',
    'email'        => 'อีเมล',
    'line_id'      => 'LINE ID',
    'facebook'     => 'Facebook (ชื่อ)',
    'tiktok'       => 'TikTok (ชื่อ)',
    'facebook_url' => 'ลิงก์ Facebook',
    'line_url'     => 'ลิงก์ LINE',
    'tiktok_url'   => 'ลิงก์ TikTok',
    'youtube_url'  => 'ลิงก์ YouTube',
    'instagram_url'=> 'ลิงก์ Instagram',
    'privacy_url'  => 'ลิงก์นโยบายความเป็นส่วนตัว',
    'terms_url'    => 'ลิงก์ข้อกำหนดการใช้งาน',
    'logo_url'     => 'โลโก้เว็บไซต์',
    'address'      => 'ที่อยู่',
    'primary_color'=> 'สีหลัก',
];

$groupLabels = [
    'general' => 'ทั่วไป',
    'contact' => 'ข้อมูลติดต่อ',
    'social'  => 'โซเชียลมีเดีย',
    'theme'   => 'ธีม',
    'legal'   => 'กฎหมาย',
];

$groupOrder = ['general', 'contact', 'social', 'theme', 'legal'];

$groupMap = [
    'site_name' => 'general',
    'site_tagline' => 'general',
    'logo_url' => 'general',
    'copyright' => 'general',
    'phone' => 'contact',
    'phone2' => 'contact',
    'email' => 'contact',
    'line_id' => 'contact',
    'address' => 'contact',
    'business_hours' => 'contact',
    'facebook' => 'social',
    'tiktok' => 'social',
    'facebook_url' => 'social',
    'line_url' => 'social',
    'tiktok_url' => 'social',
    'youtube_url' => 'social',
    'instagram_url' => 'social',
    'primary_color' => 'theme',
    'privacy_url' => 'legal',
    'terms_url' => 'legal',
];

function saveSiteSettingValue(string $key, string $value, array $groupMap): void {
    $group = $groupMap[$key] ?? 'general';
    query(
        'INSERT INTO site_settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), setting_group = VALUES(setting_group)',
        [$key, $value, $group]
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['seed_defaults'])) {
    try {
        seedDefaultSiteSettings();
        header('Location: ' . ADMIN_URL . '/settings.php?seeded=1');
        exit;
    } catch (Exception $e) {
        $message = 'สร้างค่าเริ่มต้นไม่สำเร็จ: ' . $e->getMessage();
        $messageType = 'error';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['settings'])) {
    try {
        foreach ($_POST['settings'] as $key => $value) {
            if (!isset($labelMap[$key])) {
                continue;
            }
            saveSiteSettingValue($key, $value, $groupMap);
        }
        $uploadedLogo = handleImageUpload($_FILES['logo_url_file'] ?? null, 'branding');
        if ($uploadedLogo) {
            saveSiteSettingValue('logo_url', $uploadedLogo, $groupMap);
        }

        define('CMS_INTERNAL_PUBLISH', true);
        require_once __DIR__ . '/api/publish.php';

        $message = 'บันทึกและเผยแพร่เรียบร้อยแล้ว — โลโก้และการตั้งค่าอัปเดตบนหน้าเว็บแล้ว (รีเฟรชหน้าเว็บด้วย Ctrl+F5)';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        $messageType = 'error';
    }
}

$settings = [];
$grouped = [];
$dbError = false;
$seedError = '';
try {
    ensureSiteSettingsTable();
    if (countVisibleSiteSettings() === 0) {
        seedDefaultSiteSettings();
    }
    $settings = fetchAll('SELECT * FROM site_settings ORDER BY setting_group, setting_key');
    foreach ($settings as $s) {
        $group = $s['setting_group'] ?? 'general';
        // Hide internal feedback secrets from this UI
        if ($group === 'feedback') {
            continue;
        }
        $grouped[$group][] = $s;
    }
} catch (Exception $e) {
    $dbError = true;
    $seedError = $e->getMessage();
}

$orderedGroups = [];
foreach ($groupOrder as $key) {
    if (!empty($grouped[$key])) {
        $orderedGroups[$key] = $grouped[$key];
        unset($grouped[$key]);
    }
}
foreach ($grouped as $key => $items) {
    $orderedGroups[$key] = $items;
}

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
<?= adminAlert($message, $messageType) ?>
<?php endif; ?>
<?php if (!empty($_GET['seeded'])): ?>
<div class="mb-6 admin-alert-success">สร้างค่าตั้งค่าเริ่มต้นเรียบร้อยแล้ว — แก้ไขและอัปโหลดโลโก้ได้ด้านล่าง</div>
<?php endif; ?>

<?php if ($dbError): ?>
<div class="admin-card p-8 text-center">
  <p class="text-slate-500 mb-2">กรุณาตั้งค่าฐานข้อมูลก่อน</p>
  <p class="text-sm text-slate-400">รัน <code class="bg-slate-100 px-2 py-0.5 rounded text-xs">setup.sql</code> เพื่อสร้างตารางที่จำเป็น</p>
</div>
<?php elseif (empty($orderedGroups)): ?>
<div class="admin-card p-8 text-center">
  <p class="text-slate-500 mb-2">ยังโหลดข้อมูลการตั้งค่าไม่ได้</p>
  <?php if ($seedError): ?>
  <p class="text-sm text-red-600 mb-4"><?= htmlspecialchars($seedError) ?></p>
  <?php endif; ?>
  <p class="text-sm text-slate-400 mb-4">ลองสร้างค่าเริ่มต้น หรือรัน migrate บนเซิร์ฟเวอร์ (ไม่ลบข้อมูลเดิม)</p>
  <form method="POST" class="inline-block me-2">
    <button type="submit" name="seed_defaults" value="1" class="admin-btn-primary">สร้างค่าเริ่มต้น</button>
  </form>
  <a href="<?= ADMIN_URL ?>/migrate.php" class="admin-btn-outline inline-flex" target="_blank" rel="noopener">เปิด migrate.php</a>
</div>
<?php else: ?>
<form method="POST" enctype="multipart/form-data" data-feedback-id="settings-form">
  <div class="space-y-6">
    <?php foreach ($orderedGroups as $group => $items): ?>
    <div class="admin-card p-6" data-feedback-id="settings-group-<?= htmlspecialchars($group) ?>">
      <h3 class="text-base font-semibold text-slate-800 mb-5"><?= htmlspecialchars($groupLabels[$group] ?? ucfirst((string) $group)) ?></h3>
      <div class="space-y-4">
        <?php foreach ($items as $setting):
          $key = $setting['setting_key'];
          $val = (string) ($setting['setting_value'] ?? '');
        ?>
        <div>
          <label for="setting_<?= htmlspecialchars($key) ?>" class="admin-label">
            <?= htmlspecialchars($labelMap[$key] ?? $key) ?>
          </label>
          <?php if ($key === 'primary_color'): ?>
          <div class="flex items-center gap-3">
            <input type="color"
                   id="setting_<?= htmlspecialchars($key) ?>"
                   name="settings[<?= htmlspecialchars($key) ?>]"
                   value="<?= htmlspecialchars($val !== '' ? $val : '#150f96') ?>"
                   class="w-12 h-10 rounded-lg border border-slate-200 cursor-pointer p-1 bg-white">
            <input type="text"
                   value="<?= htmlspecialchars($val) ?>"
                   class="admin-input flex-1"
                   oninput="document.getElementById('setting_<?= htmlspecialchars($key) ?>').value = this.value"
                   onchange="document.getElementById('setting_<?= htmlspecialchars($key) ?>').value = this.value">
          </div>
          <?php elseif ($key === 'logo_url'): ?>
          <div class="rounded-lg border border-slate-200 bg-slate-50/80 p-4">
            <?php renderImageField('settings[logo_url]', $val, 'โลโก้เว็บไซต์ (Header + Footer)', 'logo_url_file'); ?>
            <p class="text-xs text-slate-500 mt-2">อัปโหลดไฟล์ PNG/JPG/WebP แนะนำพื้นหลังโปร่งใส กว้างประมาณ 180–360 px — กดบันทึกแล้วระบบจะเผยแพร่ให้อัตโนมัติ</p>
          </div>
          <?php elseif ($key === 'address' || $key === 'business_hours' || $key === 'copyright' || $key === 'site_tagline'): ?>
          <textarea id="setting_<?= htmlspecialchars($key) ?>"
                    name="settings[<?= htmlspecialchars($key) ?>]"
                    rows="2"
                    class="admin-input"><?= htmlspecialchars($val) ?></textarea>
          <?php else: ?>
          <input type="text"
                 id="setting_<?= htmlspecialchars($key) ?>"
                 name="settings[<?= htmlspecialchars($key) ?>]"
                 value="<?= htmlspecialchars($val) ?>"
                 class="admin-input">
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="mt-6 flex justify-end">
    <button type="submit" class="admin-btn-primary">
      <i data-lucide="check" class="w-4 h-4" aria-hidden="true"></i>
      บันทึกการตั้งค่า
    </button>
  </div>
</form>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
