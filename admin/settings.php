<?php
$pageTitle = 'ตั้งค่าเว็บไซต์';
require_once __DIR__ . '/includes/auth.php';

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
    'logo_url'     => 'โลโก้ (path)',
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['settings'])) {
    try {
        foreach ($_POST['settings'] as $key => $value) {
            query('UPDATE site_settings SET setting_value = ? WHERE setting_key = ?', [$value, $key]);
        }
        $message = 'บันทึกการตั้งค่าเรียบร้อยแล้ว';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        $messageType = 'error';
    }
}

$settings = [];
$grouped = [];
$dbError = false;
try {
    $settings = fetchAll('SELECT * FROM site_settings ORDER BY setting_group, setting_key');
    foreach ($settings as $s) {
        $grouped[$s['setting_group']][] = $s;
    }
} catch (Exception $e) {
    $dbError = true;
}

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
<div class="mb-6 px-4 py-3 rounded-lg text-sm <?= $messageType === 'success' ? 'bg-emerald-900/30 border border-emerald-800 text-emerald-300' : 'bg-red-900/30 border border-red-800 text-red-300' ?>">
  <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<?php if ($dbError): ?>
<div class="bg-gray-900 border border-gray-800 rounded-xl p-8 text-center">
  <p class="text-gray-400 mb-2">กรุณาตั้งค่าฐานข้อมูลก่อน</p>
  <p class="text-sm text-gray-500">รัน <code class="bg-gray-800 px-2 py-0.5 rounded text-xs text-gray-300">setup.sql</code> เพื่อสร้างตารางที่จำเป็น</p>
</div>
<?php else: ?>
<form method="POST">
  <div class="space-y-6">
    <?php foreach ($grouped as $group => $items): ?>
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
      <h3 class="text-base font-semibold text-white mb-5"><?= $groupLabels[$group] ?? ucfirst($group) ?></h3>
      <div class="space-y-4">
        <?php foreach ($items as $setting): ?>
        <div>
          <label for="setting_<?= $setting['setting_key'] ?>" class="block text-sm font-medium text-gray-300 mb-1.5">
            <?= $labelMap[$setting['setting_key']] ?? $setting['setting_key'] ?>
          </label>
          <?php if ($setting['setting_key'] === 'primary_color'): ?>
          <div class="flex items-center gap-3">
            <input type="color"
                   id="setting_<?= $setting['setting_key'] ?>"
                   name="settings[<?= $setting['setting_key'] ?>]"
                   value="<?= htmlspecialchars($setting['setting_value']) ?>"
                   class="w-12 h-10 rounded-lg border border-gray-700 bg-gray-800 cursor-pointer p-1">
            <input type="text"
                   value="<?= htmlspecialchars($setting['setting_value']) ?>"
                   class="flex-1 bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-brand focus:border-brand transition"
                   oninput="document.getElementById('setting_<?= $setting['setting_key'] ?>').value = this.value"
                   onchange="document.querySelector('input[name=\'settings[<?= $setting['setting_key'] ?>]\']').value = this.value">
          </div>
          <?php elseif ($setting['setting_key'] === 'address'): ?>
          <textarea id="setting_<?= $setting['setting_key'] ?>"
                    name="settings[<?= $setting['setting_key'] ?>]"
                    rows="2"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-brand focus:border-brand transition"><?= htmlspecialchars($setting['setting_value']) ?></textarea>
          <?php else: ?>
          <input type="text"
                 id="setting_<?= $setting['setting_key'] ?>"
                 name="settings[<?= $setting['setting_key'] ?>]"
                 value="<?= htmlspecialchars($setting['setting_value']) ?>"
                 class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-brand focus:border-brand transition">
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="mt-6 flex justify-end">
    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-brand hover:bg-brand-light text-white text-sm font-medium rounded-lg transition">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
      บันทึกการตั้งค่า
    </button>
  </div>
</form>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
