<?php
$pageTitle = 'ผู้ดูแลระบบ';
require_once __DIR__ . '/includes/auth.php';

$message = '';
$users = fetchAll('SELECT id, username, display_name, created_at FROM admin_users ORDER BY id');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        if ($action === 'add') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $display = trim($_POST['display_name'] ?? $username);
            if ($username === '' || strlen($password) < 6) {
                throw new Exception('ชื่อผู้ใช้และรหัสผ่าน (อย่างน้อย 6 ตัว) จำเป็น');
            }
            $hash = password_hash($password, PASSWORD_DEFAULT);
            query('INSERT INTO admin_users (username, password_hash, display_name) VALUES (?,?,?)', [$username, $hash, $display]);
            $_SESSION['flash'] = 'เพิ่มผู้ใช้เรียบร้อย';
            header('Location: ' . ADMIN_URL . '/users.php');
            exit;
        }
        if ($action === 'password') {
            $id = (int) ($_POST['user_id'] ?? 0);
            $password = $_POST['password'] ?? '';
            if ($id < 1 || strlen($password) < 6) {
                throw new Exception('รหัสผ่านต้องมีอย่างน้อย 6 ตัว');
            }
            $hash = password_hash($password, PASSWORD_DEFAULT);
            query('UPDATE admin_users SET password_hash = ? WHERE id = ?', [$hash, $id]);
            $_SESSION['flash'] = 'เปลี่ยนรหัสผ่านเรียบร้อย';
            header('Location: ' . ADMIN_URL . '/users.php');
            exit;
        }
        if ($action === 'delete') {
            $id = (int) ($_POST['user_id'] ?? 0);
            if ($id === (int) ($_SESSION['admin_id'] ?? 0)) {
                throw new Exception('ไม่สามารถลบบัญชีตัวเอง');
            }
            $count = fetchOne('SELECT COUNT(*) AS c FROM admin_users');
            if ((int) $count['c'] <= 1) {
                throw new Exception('ต้องมีผู้ดูแลอย่างน้อย 1 คน');
            }
            query('DELETE FROM admin_users WHERE id = ?', [$id]);
            $_SESSION['flash'] = 'ลบผู้ใช้เรียบร้อย';
            header('Location: ' . ADMIN_URL . '/users.php');
            exit;
        }
    } catch (Throwable $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
    $users = fetchAll('SELECT id, username, display_name, created_at FROM admin_users ORDER BY id');
}

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
<?= adminAlert($message, $messageType ?? 'error') ?>
<?php endif; ?>

<div class="grid lg:grid-cols-2 gap-6">
  <div class="admin-card p-6">
    <h3 class="font-semibold text-slate-800 mb-4">รายการผู้ดูแล</h3>
    <div class="space-y-3">
      <?php foreach ($users as $u): ?>
      <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg text-sm">
        <div>
          <p class="font-medium text-slate-800"><?= htmlspecialchars($u['display_name'] ?: $u['username']) ?></p>
          <p class="text-xs text-slate-400"><?= htmlspecialchars($u['username']) ?></p>
        </div>
        <div class="flex gap-2">
          <form method="POST" class="inline" onsubmit="return confirm('เปลี่ยนรหัสผ่าน?')">
            <input type="hidden" name="action" value="password">
            <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
            <input type="password" name="password" placeholder="รหัสใหม่" class="admin-input text-xs w-28 py-1" minlength="6" required>
            <button type="submit" class="text-xs text-brand hover:underline">บันทึก</button>
          </form>
          <?php if ((int) $u['id'] !== (int) ($_SESSION['admin_id'] ?? 0)): ?>
          <form method="POST" class="inline" onsubmit="return confirm('ลบผู้ใช้นี้?')">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
            <button type="submit" class="text-xs text-red-500 hover:underline">ลบ</button>
          </form>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="admin-card p-6">
    <h3 class="font-semibold text-slate-800 mb-4">เพิ่มผู้ดูแลใหม่</h3>
    <form method="POST" class="space-y-4">
      <input type="hidden" name="action" value="add">
      <div>
        <label class="admin-label">ชื่อผู้ใช้</label>
        <input type="text" name="username" class="admin-input" required>
      </div>
      <div>
        <label class="admin-label">ชื่อแสดง</label>
        <input type="text" name="display_name" class="admin-input">
      </div>
      <div>
        <label class="admin-label">รหัสผ่าน</label>
        <input type="password" name="password" class="admin-input" minlength="6" required>
      </div>
      <button type="submit" class="admin-btn-primary">เพิ่มผู้ใช้</button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
