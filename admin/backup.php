<?php
$pageTitle = 'แบ็คอัพข้อมูล';
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/includes/backup-lib.php';

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'create') {
            $result = backupCreate();
            $plans = (int) ($result['counts']['plan_products'] ?? 0);
            $blogs = (int) ($result['counts']['blog_articles'] ?? 0);
            $promos = (int) ($result['counts']['promotions'] ?? 0);
            $_SESSION['flash'] = 'สร้างแบ็คอัพแล้ว — แผนประกัน ' . $plans . ' รายการ, บทความ ' . $blogs . ' รายการ, โปรโมชัน ' . $promos . ' รายการ';
            header('Location: ' . ADMIN_URL . '/backup.php');
            exit;
        }
        if ($action === 'restore') {
            $name = (string) ($_POST['name'] ?? '');
            $result = backupRestore($name);
            $plans = (int) ($result['tables']['plan_products'] ?? 0);
            $blogs = (int) ($result['tables']['blog_articles'] ?? 0);
            $_SESSION['flash'] = 'กู้คืนข้อมูลแล้ว — แผนประกัน ' . $plans . ' รายการ, บทความ ' . $blogs . ' รายการ, ไฟล์ ' . (int) $result['fileCount'] . ' ไฟล์';
            header('Location: ' . ADMIN_URL . '/backup.php');
            exit;
        }
        if ($action === 'delete') {
            backupDelete((string) ($_POST['name'] ?? ''));
            $_SESSION['flash'] = 'ลบแบ็คอัพแล้ว';
            header('Location: ' . ADMIN_URL . '/backup.php');
            exit;
        }
        throw new RuntimeException('ไม่รู้จักคำสั่ง');
    } catch (Throwable $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

$backups = [];
$listError = '';
try {
    $backups = backupList();
} catch (Throwable $e) {
    $listError = $e->getMessage();
}

$included = backupContentTables();
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
<?= adminAlert($message, $messageType) ?>
<?php endif; ?>
<?php if ($listError): ?>
<?= adminAlert($listError, 'error') ?>
<?php endif; ?>

<div class="grid lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 space-y-6">
    <div class="admin-card p-6" data-feedback-id="backup-create">
      <h3 class="text-base font-semibold text-slate-800 mb-2">สร้างแบ็คอัพ</h3>
      <p class="text-sm text-slate-500 mb-4">สำรองแผนประกัน บทความ โปรโมชัน หน้าเว็บ บล็อกเนื้อหา การตั้งค่า ข้อความติดต่อ โรงพยาบาล และรูปที่ใช้งานอยู่ เป็นไฟล์ซิปชุดเดียว</p>
      <form method="POST">
        <input type="hidden" name="action" value="create">
        <button type="submit" class="admin-btn-primary">สร้างแบ็คอัพตอนนี้</button>
      </form>
    </div>

    <div class="admin-card overflow-hidden" data-feedback-id="backup-list">
      <div class="px-6 py-4 border-b border-slate-100">
        <h3 class="text-base font-semibold text-slate-800">แบ็คอัพที่บันทึกไว้</h3>
      </div>
      <?php if (!$backups): ?>
      <div class="px-6 py-12 text-center text-slate-400">ยังไม่มีแบ็คอัพ</div>
      <?php else: ?>
      <div class="overflow-x-auto">
        <table class="admin-table w-full">
          <thead>
            <tr>
              <th>วันที่</th>
              <th>เนื้อหา</th>
              <th>ขนาด</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($backups as $item):
              $counts = $item['counts'];
              $when = strtotime((string) $item['createdAt']);
            ?>
            <tr>
              <td class="whitespace-nowrap">
                <p class="font-medium text-slate-800"><?= $when ? date('d/m/Y H:i', $when) : '-' ?></p>
                <p class="text-xs text-slate-400"><?= htmlspecialchars($item['name']) ?></p>
              </td>
              <td class="text-sm text-slate-600">
                แผน <?= (int) ($counts['plan_products'] ?? 0) ?>
                · บทความ <?= (int) ($counts['blog_articles'] ?? 0) ?>
                · โปรโมชัน <?= (int) ($counts['promotions'] ?? 0) ?>
                · ไฟล์ <?= (int) $item['fileCount'] ?>
              </td>
              <td class="whitespace-nowrap"><?= htmlspecialchars(backupFormatBytes((int) $item['bytes'])) ?></td>
              <td class="whitespace-nowrap text-right">
                <a class="text-sm text-brand hover:underline" href="<?= ADMIN_URL ?>/api/backup-download.php?file=<?= rawurlencode($item['name']) ?>">ดาวน์โหลด</a>
                <form method="POST" class="inline" onsubmit="return confirm('กู้คืนข้อมูลจากแบ็คอัพนี้? ข้อมูลปัจจุบันของแผนประกัน บทความ และเนื้อหาอื่นจะถูกแทนที่')">
                  <input type="hidden" name="action" value="restore">
                  <input type="hidden" name="name" value="<?= htmlspecialchars($item['name']) ?>">
                  <button type="submit" class="text-sm text-emerald-700 hover:underline ms-3">กู้คืน</button>
                </form>
                <form method="POST" class="inline" onsubmit="return confirm('ลบแบ็คอัพนี้?')">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="name" value="<?= htmlspecialchars($item['name']) ?>">
                  <button type="submit" class="text-sm text-red-600 hover:underline ms-3">ลบ</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <aside class="admin-card p-6 h-fit" data-feedback-id="backup-contents">
    <h3 class="text-base font-semibold text-slate-800 mb-3">สิ่งที่อยู่ในแบ็คอัพ</h3>
    <ul class="space-y-1.5 text-sm text-slate-600">
      <?php foreach ($included as $label): ?>
      <li><?= htmlspecialchars($label) ?></li>
      <?php endforeach; ?>
      <li>รายชื่อโรงพยาบาล</li>
      <li>รูปภาพที่เนื้อหาอ้างถึง</li>
      <li>ไฟล์เว็บที่เผยแพร่แล้ว</li>
    </ul>
    <p class="text-xs text-slate-400 mt-4">ระบบเก็บแบ็คอัพล่าสุด 20 ไฟล์ รหัสผ่านผู้ดูแลไม่ถูกสำรองในไฟล์นี้</p>
  </aside>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
