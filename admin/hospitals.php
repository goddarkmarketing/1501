<?php
$pageTitle = 'โรงพยาบาล';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/hospitals-lib.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';

        if ($action === 'delete' && !empty($_POST['delete_id'])) {
            if (!hospitalDelete((string) $_POST['delete_id'])) {
                throw new Exception('ไม่พบรายการที่จะลบ');
            }
            $_SESSION['flash'] = 'ลบสถานพยาบาลเรียบร้อยแล้ว';
            header('Location: ' . ADMIN_URL . '/hospitals.php?' . http_build_query(array_filter([
                'q' => $_GET['q'] ?? '',
                'province' => $_GET['province'] ?? '',
                'page' => $_GET['page'] ?? '',
            ])));
            exit;
        }

        if ($action === 'upload') {
            if (empty($_FILES['json_file']['tmp_name'])) {
                throw new Exception('กรุณาเลือกไฟล์ JSON');
            }
            $uploaded = file_get_contents($_FILES['json_file']['tmp_name']);
            $test = json_decode($uploaded, true);
            if (!is_array($test) || !isset($test['results']) || !is_array($test['results'])) {
                throw new Exception('ไฟล์ JSON ไม่ถูกต้อง ต้องมีคีย์ results เป็นรายการสถานพยาบาล');
            }
            $path = hospitalJsonPath();
            $backup = $path . '.bak.' . date('YmdHis');
            if (is_file($path)) {
                copy($path, $backup);
            }
            hospitalSaveData($test);
            $_SESSION['flash'] = 'อัปโหลดไฟล์โรงพยาบาลเรียบร้อยแล้ว (' . count($test['results']) . ' รายการ)';
            header('Location: ' . ADMIN_URL . '/hospitals.php');
            exit;
        }
    } catch (Throwable $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

$filters = [
    'q' => trim((string) ($_GET['q'] ?? '')),
    'province' => trim((string) ($_GET['province'] ?? '')),
];
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 30;

$all = hospitalAll();
$provinces = hospitalProvinces($all);
$filtered = hospitalFilterRows($all, $filters);
$total = count($filtered);
$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;
$pageRows = array_slice($filtered, $offset, $perPage);

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
<?= adminAlert($message, $messageType) ?>
<?php endif; ?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6" data-feedback-id="hospital-toolbar">
  <div>
    <p class="text-sm text-slate-500">จัดการสถานพยาบาล — รายการและแผนที่หน้าเว็บอัปเดตจากไฟล์เดียวกันทันทีหลังบันทึก</p>
    <p class="text-xs text-slate-400 mt-1">ทั้งหมด <?= number_format(count($all)) ?> แห่ง<?= $filters['q'] !== '' || $filters['province'] !== '' ? ' · พบ ' . number_format($total) . ' รายการที่ตรงตัวกรอง' : '' ?></p>
  </div>
  <div class="flex flex-wrap gap-2">
    <a href="<?= SITE_URL ?>/hospitals.html" target="_blank" class="admin-btn-outline text-sm">ดูหน้าเว็บ ↗</a>
    <a href="<?= ADMIN_URL ?>/hospital-edit.php" class="admin-btn-primary text-sm">+ เพิ่มสถานพยาบาล</a>
  </div>
</div>

<form method="GET" class="admin-card p-4 mb-6 grid grid-cols-1 sm:grid-cols-3 gap-3" data-feedback-id="hospital-filters">
  <div class="sm:col-span-2">
    <label class="admin-label" for="q">ค้นหา</label>
    <input class="admin-input" type="search" id="q" name="q" value="<?= htmlspecialchars($filters['q']) ?>" placeholder="ชื่อ, ที่อยู่, ประเภท…">
  </div>
  <div>
    <label class="admin-label" for="province">จังหวัด</label>
    <select class="admin-input" id="province" name="province" onchange="this.form.submit()">
      <option value="">ทั้งหมด</option>
      <?php foreach ($provinces as $p): ?>
      <option value="<?= htmlspecialchars($p) ?>" <?= $filters['province'] === $p ? 'selected' : '' ?>><?= htmlspecialchars($p) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="sm:col-span-3 flex gap-2">
    <button type="submit" class="admin-btn-primary text-sm">ค้นหา</button>
    <a href="<?= ADMIN_URL ?>/hospitals.php" class="admin-btn-outline text-sm">ล้างตัวกรอง</a>
  </div>
</form>

<?php if (empty($pageRows)): ?>
<div class="admin-card p-12 text-center text-slate-500">ไม่พบสถานพยาบาล<?= $total === 0 && count($all) === 0 ? ' — <a href="' . ADMIN_URL . '/hospital-edit.php" class="text-brand hover:underline">เพิ่มรายการแรก</a>' : '' ?></div>
<?php else: ?>
<div class="admin-card overflow-hidden" data-feedback-id="hospital-list">
  <div class="overflow-x-auto">
    <table class="admin-table w-full">
      <thead>
        <tr>
          <th>ชื่อ</th>
          <th>จังหวัด</th>
          <th>ประเภท</th>
          <th>แผนที่</th>
          <th class="text-right">จัดการ</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pageRows as $row):
          $hasMap = is_numeric($row['latitude'] ?? null) && is_numeric($row['longitude'] ?? null);
          $types = $row['facilities'] ?? [];
        ?>
        <tr>
          <td>
            <p class="font-medium text-slate-800"><?= htmlspecialchars($row['name'] ?? '') ?></p>
            <p class="text-xs text-slate-400 mt-0.5"><?= htmlspecialchars(hospitalFormatAddress($row)) ?></p>
          </td>
          <td class="whitespace-nowrap"><?= htmlspecialchars($row['province'] ?? '—') ?></td>
          <td>
            <div class="flex flex-wrap gap-1">
              <?php foreach (array_slice($types, 0, 3) as $t): ?>
              <span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600"><?= htmlspecialchars($t) ?></span>
              <?php endforeach; ?>
              <?php if (count($types) > 3): ?>
              <span class="text-[11px] text-slate-400">+<?= count($types) - 3 ?></span>
              <?php endif; ?>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">
              <?= hospitalSupportsIndividual($row) ? 'รายบุคคล' : '' ?>
              <?= hospitalSupportsIndividual($row) && hospitalSupportsGroup($row) ? ' · ' : '' ?>
              <?= hospitalSupportsGroup($row) ? 'กลุ่ม' : '' ?>
            </p>
          </td>
          <td class="text-center">
            <?php if ($hasMap): ?>
            <span class="text-xs text-emerald-600">มีพิกัด</span>
            <?php else: ?>
            <span class="text-xs text-amber-600">ไม่มีพิกัด</span>
            <?php endif; ?>
          </td>
          <td class="text-right whitespace-nowrap">
            <a href="<?= ADMIN_URL ?>/hospital-edit.php?id=<?= urlencode((string) ($row['id'] ?? '')) ?>" class="admin-btn-outline text-xs">แก้ไข</a>
            <form method="POST" class="inline" onsubmit="return confirm('ลบ <?= htmlspecialchars($row['name'] ?? '', ENT_QUOTES) ?>?');">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="delete_id" value="<?= htmlspecialchars((string) ($row['id'] ?? '')) ?>">
              <button type="submit" class="admin-btn-outline text-xs text-red-600 border-red-200 hover:bg-red-50">ลบ</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($totalPages > 1): ?>
<div class="flex items-center justify-center gap-2 mt-4">
  <?php
  $qs = static function (int $p) use ($filters): string {
      return http_build_query(array_filter([
          'q' => $filters['q'],
          'province' => $filters['province'],
          'page' => $p > 1 ? $p : null,
      ]));
  };
  ?>
  <?php if ($page > 1): ?>
  <a class="admin-btn-outline text-xs" href="?<?= $qs($page - 1) ?>">ก่อนหน้า</a>
  <?php endif; ?>
  <span class="text-sm text-slate-500">หน้า <?= $page ?> / <?= $totalPages ?></span>
  <?php if ($page < $totalPages): ?>
  <a class="admin-btn-outline text-xs" href="?<?= $qs($page + 1) ?>">ถัดไป</a>
  <?php endif; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<details class="admin-card p-6 mt-8" data-feedback-id="hospital-bulk-upload">
  <summary class="font-semibold text-slate-800 cursor-pointer">อัปโหลด JSON ทั้งชุด (ขั้นสูง)</summary>
  <p class="text-sm text-slate-500 mt-3 mb-4">แทนที่ข้อมูลทั้งหมดด้วยไฟล์ JSON ใหม่ ระบบจะสำรองไฟล์เดิมไว้ก่อน ใช้เมื่อมีรายการจำนวนมากจากไฟล์ต้นทาง</p>
  <form method="POST" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3">
    <input type="hidden" name="action" value="upload">
    <div class="flex-1 min-w-[200px]">
      <label class="admin-label" for="json_file">ไฟล์ JSON</label>
      <input type="file" id="json_file" name="json_file" accept=".json,application/json" class="block w-full text-sm text-slate-600" required>
    </div>
    <button type="submit" class="admin-btn-outline" onclick="return confirm('แทนที่ข้อมูลโรงพยาบาลทั้งหมด?')">อัปโหลด JSON</button>
  </form>
  <p class="text-xs text-slate-400 mt-3">ไฟล์: <code>assets/data/hospital-locator.json</code></p>
</details>

<p class="text-xs text-slate-400 mt-6">แก้หัวข้อ/Hero หน้าโรงพยาบาลได้ที่ <a href="<?= ADMIN_URL ?>/page-edit.php?page=hospitals" class="text-brand hover:underline">จัดการหน้าเว็บ → โรงพยาบาล</a></p>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
