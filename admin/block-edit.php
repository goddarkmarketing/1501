<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cms.php';

$key = $_GET['key'] ?? '';
$defs = getCmsBlockDefinitions();
if (!isset($defs[$key])) {
    header('Location: ' . ADMIN_URL . '/blocks.php');
    exit;
}

$def = $defs[$key];
$pageTitle = 'แก้ไข: ' . $def['label'];
$message = '';
$data = getCmsBlock($key);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($def['type'] === 'plan_ids') {
            $lines = array_filter(array_map('trim', explode("\n", $_POST['plan_ids'] ?? '')));
            $data = ['plan_ids' => $lines];
        } elseif ($def['type'] === 'html') {
            $data = $_POST['html_content'] ?? '';
        } elseif ($def['type'] === 'footer') {
            $data = [
                'tagline' => trim($_POST['tagline'] ?? ''),
                'copyright' => trim($_POST['copyright'] ?? ''),
                'cta_text' => trim($_POST['cta_text'] ?? ''),
                'cta_href' => trim($_POST['cta_href'] ?? ''),
                'columns' => json_decode($_POST['columns_json'] ?? '[]', true) ?: [],
                'legal' => json_decode($_POST['legal_json'] ?? '[]', true) ?: [],
            ];
        } else {
            $decoded = json_decode($_POST['json_content'] ?? '', true);
            if (!is_array($decoded) && $def['type'] !== 'html') {
                throw new Exception('JSON ไม่ถูกต้อง');
            }
            $data = $decoded;
        }
        saveCmsBlock($key, $data);
        $message = 'บันทึกเรียบร้อย — กดเผยแพร่เว็บไซต์เพื่อให้แสดงบนหน้าบ้าน';
    } catch (Throwable $e) {
        $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        $messageType = 'error';
    }
    $data = getCmsBlock($key);
}

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
<?= adminAlert($message, ($messageType ?? '') === 'error' ? 'error' : 'success') ?>
<?php endif; ?>

<div class="flex items-center justify-between mb-6">
  <a href="<?= ADMIN_URL ?>/blocks.php" class="text-sm text-slate-500 hover:text-brand">← กลับบล็อกเนื้อหา</a>
</div>

<form method="POST" class="admin-card p-6 space-y-5 max-w-3xl">
  <p class="text-sm text-slate-500"><?= htmlspecialchars($def['description']) ?></p>

  <?php if ($def['type'] === 'plan_ids'): ?>
  <div>
    <label class="admin-label">รหัสแผนประกัน (หนึ่งบรรทัดต่อหนึ่งแผน)</label>
    <textarea name="plan_ids" rows="6" class="admin-input font-mono text-sm"><?= htmlspecialchars(implode("\n", $data['plan_ids'] ?? [])) ?></textarea>
    <p class="text-xs text-slate-400 mt-1">ใช้ ID จากหน้าแผนประกัน เช่น easy-e-health, fwd-precious-care</p>
  </div>

  <?php elseif ($def['type'] === 'html'): ?>
  <div>
    <label class="admin-label">เนื้อหา HTML</label>
    <textarea name="html_content" rows="12" class="admin-input font-mono text-sm"><?= htmlspecialchars(is_string($data) ? $data : '') ?></textarea>
  </div>

  <?php elseif ($def['type'] === 'footer'): ?>
  <div>
    <label class="admin-label">คำอธิบายแบรนด์</label>
    <textarea name="tagline" rows="3" class="admin-input"><?= htmlspecialchars($data['tagline'] ?? '') ?></textarea>
  </div>
  <div class="grid sm:grid-cols-2 gap-4">
    <div>
      <label class="admin-label">ข้อความปุ่ม CTA</label>
      <input type="text" name="cta_text" value="<?= htmlspecialchars($data['cta_text'] ?? '') ?>" class="admin-input">
    </div>
    <div>
      <label class="admin-label">ลิงก์ CTA</label>
      <input type="text" name="cta_href" value="<?= htmlspecialchars($data['cta_href'] ?? '') ?>" class="admin-input">
    </div>
  </div>
  <div>
    <label class="admin-label">ลิขสิทธิ์ (สำรอง — ใช้จากตั้งค่าเว็บไซต์เป็นหลัก)</label>
    <input type="text" name="copyright" value="<?= htmlspecialchars($data['copyright'] ?? '') ?>" class="admin-input">
  </div>
  <div>
    <label class="admin-label">คอลัมน์ลิงก์ (JSON)</label>
    <textarea name="columns_json" rows="10" class="admin-input font-mono text-xs"><?= htmlspecialchars(json_encode($data['columns'] ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) ?></textarea>
  </div>
  <div>
    <label class="admin-label">ลิงก์กฎหมาย (JSON)</label>
    <textarea name="legal_json" rows="4" class="admin-input font-mono text-xs"><?= htmlspecialchars(json_encode($data['legal'] ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) ?></textarea>
  </div>

  <?php else: ?>
  <div>
    <label class="admin-label">ข้อมูล JSON</label>
    <textarea name="json_content" rows="20" class="admin-input font-mono text-xs"><?= htmlspecialchars(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></textarea>
    <p class="text-xs text-slate-400 mt-1">แก้ไขโครงสร้าง JSON แล้วบันทึก — ระวังรูปแบบต้องถูกต้อง</p>
  </div>
  <?php endif; ?>

  <div class="flex gap-3 pt-2">
    <button type="submit" class="admin-btn-primary">บันทึก</button>
    <a href="<?= ADMIN_URL ?>/api/publish.php" onclick="return confirm('เผยแพร่เว็บไซต์?')" class="admin-btn-outline">บันทึกแล้วเผยแพร่</a>
  </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
