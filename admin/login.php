<?php

require_once __DIR__ . '/includes/auth.php';



if (isLoggedIn()) { header('Location: ' . ADMIN_URL . '/'); exit; }



$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $u = trim($_POST['username'] ?? '');

    $p = $_POST['password'] ?? '';

    if (attempt($u, $p)) {

        header('Location: ' . ADMIN_URL . '/');

        exit;

    }

    $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';

}

?>

<!DOCTYPE html>

<html lang="th">

<head>

  <meta charset="UTF-8">

  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>เข้าสู่ระบบ — Admin</title>

  <link href="https://cdn.jsdelivr.net/npm/preline@3/dist/preline.min.css" rel="stylesheet">

  <script src="https://cdn.tailwindcss.com"></script>

  <script>tailwind.config={theme:{extend:{colors:{brand:'#150f96','brand-light':'#2a1fbb'}}}}</script>

  <link rel="stylesheet" href="<?= ADMIN_URL ?>/assets/admin.css">

</head>

<body class="admin-app flex items-center justify-center min-h-screen">

  <div class="w-full max-w-md p-6">

    <div class="admin-card p-8 shadow-lg">

      <div class="text-center mb-8">

        <div class="w-14 h-14 rounded-2xl bg-brand/10 flex items-center justify-center mx-auto mb-4">

          <span class="text-brand font-bold text-xl">AT</span>

        </div>

        <h1 class="text-2xl font-bold text-slate-800">Agent Thailand</h1>

        <p class="text-slate-400 mt-1 text-sm">ระบบจัดการเว็บไซต์</p>

      </div>



      <?php if ($error): ?>

      <div class="admin-alert-error mb-6"><?= htmlspecialchars($error) ?></div>

      <?php endif; ?>



      <form method="POST" class="space-y-5">

        <div>

          <label class="admin-label">ชื่อผู้ใช้</label>

          <input type="text" name="username" required autofocus class="admin-input" placeholder="admin">

        </div>

        <div>

          <label class="admin-label">รหัสผ่าน</label>

          <input type="password" name="password" required class="admin-input" placeholder="••••••••">

        </div>

        <button type="submit" class="admin-btn-primary w-full justify-center py-3">เข้าสู่ระบบ</button>

      </form>

    </div>

    <p class="text-center text-slate-400 text-xs mt-6">ค่าเริ่มต้น: admin / admin123</p>

  </div>

</body>

</html>

