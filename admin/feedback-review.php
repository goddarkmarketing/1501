<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/feedback-lib.php';

$token = $_GET['token'] ?? '';
$expected = feedbackReviewToken();
$error = '';

if ($token === '' || !hash_equals($expected, $token)) {
    http_response_code(403);
    $error = 'ลิงก์ตรวจงานไม่ถูกต้อง';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === '') {
    $password = $_POST['password'] ?? '';
    if (feedbackVerifyReviewPassword($password)) {
        $_SESSION['feedback_review'] = true;
        feedbackActivatePreview();
        header('Location: ' . ADMIN_URL . '/index.php?feedback_preview=1');
        exit;
    }
    $error = 'รหัสผ่านไม่ถูกต้อง';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ตรวจงาน — Agent Thailand</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{brand:'#150f96'}}}}</script>
  <link rel="stylesheet" href="<?= ADMIN_URL ?>/assets/admin.css">
</head>
<body class="admin-app min-h-screen flex items-center justify-center p-4">
  <div class="admin-card w-full max-w-md p-8">
    <h1 class="text-xl font-bold text-slate-800 mb-1">ตรวจงานเว็บไซต์</h1>
    <p class="text-sm text-slate-500 mb-6">กรอกรหัสผ่านเพื่อเข้าสู่โหมดแจ้งแก้ไข</p>

    <?php if ($error): ?>
    <div class="admin-alert-error mb-4"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($error !== 'ลิงก์ตรวจงานไม่ถูกต้อง'): ?>
    <form method="POST" class="space-y-4">
      <div>
        <label class="admin-label" for="password">รหัสผ่าน</label>
        <input class="admin-input" type="password" id="password" name="password" required autofocus data-sensitive>
      </div>
      <button type="submit" class="admin-btn-primary w-full justify-center">เข้าสู่ระบบตรวจงาน</button>
    </form>
    <?php endif; ?>
  </div>
</body>
</html>
