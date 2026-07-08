<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/ui.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$item = null;
$message = '';

if ($id) {
    $item = fetchOne('SELECT * FROM faq_items WHERE id = ?', [$id]);
    if (!$item) {
        header('Location: ' . ADMIN_URL . '/faq.php');
        exit;
    }
}

$pageTitle = $item ? 'แก้ไขคำถาม' : 'เพิ่มคำถาม';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim($_POST['question'] ?? '');
    $answer = trim($_POST['answer'] ?? '');
    $sortOrder = (int) ($_POST['sort_order'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($question === '' || $answer === '') {
        $message = 'กรุณากรอกคำถามและคำตอบ';
        $messageType = 'error';
    } else {
        try {
            if ($id) {
                query(
                    'UPDATE faq_items SET question = ?, answer = ?, sort_order = ?, is_active = ? WHERE id = ?',
                    [$question, $answer, $sortOrder, $isActive, $id]
                );
            } else {
                query(
                    'INSERT INTO faq_items (question, answer, sort_order, is_active) VALUES (?, ?, ?, ?)',
                    [$question, $answer, $sortOrder, $isActive]
                );
                $id = (int) insertId();
            }
            $_SESSION['flash'] = 'บันทึกคำถามเรียบร้อยแล้ว';
            header('Location: ' . ADMIN_URL . '/faq.php');
            exit;
        } catch (Throwable $e) {
            $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
<?= adminAlert($message, $messageType ?? 'error') ?>
<?php endif; ?>

<div class="mb-6">
  <a href="<?= ADMIN_URL ?>/faq.php" class="text-sm text-slate-500 hover:text-brand">← กลับรายการ FAQ</a>
</div>

<form method="POST" class="admin-card p-6 space-y-5 max-w-2xl">
  <div>
    <label class="admin-label" for="question">คำถาม</label>
    <textarea id="question" name="question" rows="2" class="admin-input" required><?= htmlspecialchars($item['question'] ?? $_POST['question'] ?? '') ?></textarea>
  </div>
  <div>
    <label class="admin-label" for="answer">คำตอบ</label>
    <textarea id="answer" name="answer" rows="5" class="admin-input" required><?= htmlspecialchars($item['answer'] ?? $_POST['answer'] ?? '') ?></textarea>
  </div>
  <div class="grid sm:grid-cols-2 gap-4">
    <div>
      <label class="admin-label" for="sort_order">ลำดับการแสดง</label>
      <input type="number" id="sort_order" name="sort_order" class="admin-input" value="<?= (int) ($item['sort_order'] ?? $_POST['sort_order'] ?? 0) ?>">
    </div>
    <div class="flex items-end pb-2">
      <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
        <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-brand focus:ring-brand"
          <?= ($item['is_active'] ?? 1) ? 'checked' : '' ?>>
        แสดงบนหน้าเว็บ
      </label>
    </div>
  </div>
  <div class="flex gap-3 pt-2">
    <button type="submit" class="admin-btn-primary">บันทึก</button>
    <a href="<?= ADMIN_URL ?>/faq.php" class="admin-btn-outline">ยกเลิก</a>
  </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
