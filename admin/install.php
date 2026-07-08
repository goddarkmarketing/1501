<?php

define('ADMIN_URL', '/1501/admin');



$step = 'form';

$message = '';



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $host = '127.0.0.1';

        $pdo = new PDO("mysql:host={$host};charset=utf8mb4", 'root', '', [

            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

        ]);



        $pdo->exec('CREATE DATABASE IF NOT EXISTS agent1501 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        $pdo->exec('USE agent1501');



        $sqlFile = __DIR__ . '/setup.sql';

        if (!file_exists($sqlFile)) {

            throw new Exception('ไม่พบไฟล์ setup.sql');

        }



        $sql = file_get_contents($sqlFile);

        $sql = preg_replace('/^CREATE DATABASE.*?;\s*/im', '', $sql);

        $sql = preg_replace('/^USE\s+.*?;\s*/im', '', $sql);



        $statements = array_filter(array_map('trim', explode(';', $sql)));

        foreach ($statements as $stmt) {

            if (!empty($stmt)) {

                $pdo->exec($stmt);

            }

        }



        $hash = password_hash('admin123', PASSWORD_DEFAULT);

        $pdo->exec("UPDATE admin_users SET password_hash = " . $pdo->quote($hash) . " WHERE username = 'admin'");



        $step = 'success';

        $message = 'ติดตั้งฐานข้อมูลเรียบร้อยแล้ว! บัญชี: admin / admin123';

    } catch (Exception $e) {

        $step = 'error';

        $message = $e->getMessage();

    }

}

?>

<!DOCTYPE html>

<html lang="th">

<head>

  <meta charset="UTF-8">

  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>ติดตั้งระบบ — Agent Thailand</title>

  <link href="https://cdn.jsdelivr.net/npm/preline@3/dist/preline.min.css" rel="stylesheet">

  <script src="https://cdn.tailwindcss.com"></script>

  <script>tailwind.config={theme:{extend:{colors:{brand:'#150f96','brand-light':'#2a1fbb'}}}}</script>

  <link rel="stylesheet" href="<?= ADMIN_URL ?>/assets/admin.css">

</head>

<body class="admin-app flex items-center justify-center min-h-screen">

  <div class="w-full max-w-lg p-6">

    <div class="admin-card p-8 shadow-lg">

      <div class="text-center mb-8">

        <div class="w-16 h-16 bg-brand/10 rounded-2xl flex items-center justify-center mx-auto mb-4">

          <svg class="w-8 h-8 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375"/></svg>

        </div>

        <h1 class="text-2xl font-bold text-slate-800">ติดตั้งระบบ</h1>

        <p class="text-slate-400 mt-1 text-sm">Agent Thailand Admin Panel</p>

      </div>



      <?php if ($step === 'form'): ?>

        <div class="space-y-4 mb-8 text-sm text-slate-600">

          <p>การติดตั้งจะสร้างฐานข้อมูล <code class="bg-slate-100 px-1.5 py-0.5 rounded text-xs">agent1501</code> และตารางทั้งหมดจาก setup.sql</p>

          <div class="admin-alert-error text-xs">

            ก่อนติดตั้ง: เปิด <strong>Apache</strong> และ <strong>MySQL</strong> ใน XAMPP Control Panel

          </div>

        </div>

        <form method="POST">

          <button type="submit" class="admin-btn-primary w-full justify-center py-3">ติดตั้งฐานข้อมูล</button>

        </form>



      <?php elseif ($step === 'success'): ?>

        <div class="admin-alert-success mb-6"><?= htmlspecialchars($message) ?></div>

        <div class="bg-slate-50 rounded-lg p-4 mb-6 text-sm">

          <p class="text-slate-500 mb-2">ขั้นตอนถัดไป:</p>

          <ol class="list-decimal list-inside text-slate-600 space-y-1">

            <li>เข้าสู่ระบบ admin / admin123</li>

            <li>กดนำเข้าข้อมูลเริ่มต้น</li>

            <li>แก้ไขเนื้อหาแล้วกดเผยแพร่เว็บไซต์</li>

          </ol>

        </div>

        <a href="<?= ADMIN_URL ?>/login.php" class="admin-btn-primary w-full justify-center py-3">ไปหน้าเข้าสู่ระบบ</a>



      <?php elseif ($step === 'error'): ?>

        <div class="admin-alert-error mb-6">

          <p class="font-medium mb-1">เกิดข้อผิดพลาด</p>

          <p class="text-xs break-all"><?= htmlspecialchars($message) ?></p>

        </div>

        <?php if (str_contains($message, '2002') || str_contains($message, 'refused')): ?>

        <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-3 mb-6 text-xs">

          MySQL ยังไม่ทำงาน — เปิด XAMPP แล้วกด Start ที่ MySQL ก่อนลองใหม่

        </div>

        <?php endif; ?>

        <form method="POST">

          <button type="submit" class="admin-btn-primary w-full justify-center py-3">ลองใหม่อีกครั้ง</button>

        </form>

      <?php endif; ?>

    </div>

  </div>

</body>

</html>

