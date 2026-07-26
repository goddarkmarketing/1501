<?php
/**
 * Standalone DB installer for hosting.
 * https://prakandd-thailand.com/admin/install.php
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

header('Content-Type: text/html; charset=utf-8');

$lockFile = __DIR__ . '/install.lock';
$force = isset($_GET['force']) && $_GET['force'] === '1';
$step = 'form';
$message = '';
$errors = array();
$seedNote = '';

$scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/admin/install.php';
$adminUrl = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
if ($adminUrl === '' || $adminUrl === '.') {
    $adminUrl = '/admin';
}
$siteUrl = dirname($adminUrl);
if ($siteUrl === '/' || $siteUrl === '\\' || $siteUrl === '.') {
    $siteUrl = '';
}

$prefill = array(
    'db_host' => 'localhost',
    'db_name' => '',
    'db_user' => '',
    'db_pass' => '',
    'admin_user' => 'admin',
    'admin_pass' => 'admin123',
    'admin_name' => 'Administrator',
    'run_seed' => '1',
);

if (is_file($lockFile) && !$force && (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST')) {
    $step = 'locked';
}

function inst_h($s) {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function inst_write_config($data) {
    $code = '<' . '?php' . "\nreturn " . var_export($data, true) . ";\n";
    $ok = @file_put_contents(__DIR__ . '/config.local.php', $code);
    if ($ok === false) {
        throw new Exception('Cannot write config.local.php - set write permission on admin/');
    }
}

function inst_run_sql_file($pdo, $file) {
    if (!is_file($file)) {
        throw new Exception('setup.sql not found');
    }
    $sql = file_get_contents($file);
    $sql = preg_replace('/^CREATE DATABASE.*?;\s*/im', '', $sql);
    $sql = preg_replace('/^USE\s+.*?;\s*/im', '', $sql);
    $parts = preg_split('/;\s*[\r\n]+/', $sql);
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part === '' || strpos($part, '--') === 0) {
            continue;
        }
        try {
            $pdo->exec($part);
        } catch (Exception $e) {
            $m = $e->getMessage();
            if (strpos($m, 'already exists') === false && strpos($m, 'Duplicate') === false) {
                throw $e;
            }
        }
    }
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array('db_host', 'db_name', 'db_user', 'db_pass', 'admin_user', 'admin_pass', 'admin_name') as $k) {
        if (isset($_POST[$k])) {
            $prefill[$k] = is_string($_POST[$k]) ? trim($_POST[$k]) : '';
        }
    }
    // keep password spaces if any
    if (isset($_POST['db_pass'])) {
        $prefill['db_pass'] = (string) $_POST['db_pass'];
    }
    if (isset($_POST['admin_pass'])) {
        $prefill['admin_pass'] = (string) $_POST['admin_pass'];
    }
    $prefill['run_seed'] = isset($_POST['run_seed']) ? '1' : '';

    if ($prefill['db_host'] === '') {
        $errors[] = 'DB Host required';
    }
    if ($prefill['db_name'] === '') {
        $errors[] = 'Database name required (from cPanel/Plesk)';
    }
    if ($prefill['db_user'] === '') {
        $errors[] = 'DB Username required (do NOT use root)';
    }
    if ($prefill['admin_user'] === '') {
        $errors[] = 'Admin username required';
    }
    if (strlen($prefill['admin_pass']) < 6) {
        $errors[] = 'Admin password min 6 chars';
    }

    if (!$errors) {
        try {
            if (!class_exists('PDO')) {
                throw new Exception('PDO not enabled');
            }
            $pdo = new PDO(
                'mysql:host=' . $prefill['db_host'] . ';charset=utf8mb4',
                $prefill['db_user'],
                $prefill['db_pass'],
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
            $db = str_replace('`', '``', $prefill['db_name']);
            try {
                $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . $db . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            } catch (Exception $e) {
                // ignore - shared hosting
            }
            $pdo->exec('USE `' . $db . '`');
            inst_run_sql_file($pdo, __DIR__ . '/setup.sql');

            inst_write_config(array(
                'DB_HOST' => $prefill['db_host'],
                'DB_NAME' => $prefill['db_name'],
                'DB_USER' => $prefill['db_user'],
                'DB_PASS' => $prefill['db_pass'],
                'SITE_URL' => $siteUrl,
                'ADMIN_URL' => $adminUrl,
            ));

            $hash = password_hash($prefill['admin_pass'], PASSWORD_DEFAULT);
            $st = $pdo->prepare('SELECT id FROM admin_users WHERE username = ?');
            $st->execute(array($prefill['admin_user']));
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $u = $pdo->prepare('UPDATE admin_users SET password_hash = ?, display_name = ? WHERE id = ?');
                $u->execute(array($hash, $prefill['admin_name'], $row['id']));
            } else {
                $i = $pdo->prepare('INSERT INTO admin_users (username, password_hash, display_name) VALUES (?,?,?)');
                $i->execute(array($prefill['admin_user'], $hash, $prefill['admin_name']));
            }

            $faqCount = (int) $pdo->query('SELECT COUNT(*) FROM faq_items')->fetchColumn();
            if ($faqCount === 0) {
                $pdo->exec("INSERT INTO faq_items (question, answer, sort_order, is_active) VALUES
                ('ประกันชีวิตคืออะไร และทำไมถึงสำคัญ?', 'ประกันชีวิตเป็นเครื่องมือทางการเงินที่ช่วยคุ้มครองครอบครัวของคุณในกรณีที่เกิดเหตุไม่คาดฝัน เป็นการวางแผนทางการเงินที่สำคัญเพื่อให้คนที่คุณรักมีความมั่นคงในอนาคต', 1, 1),
                ('ต้องใช้เอกสารอะไรบ้างในการสมัครประกัน?', 'เอกสารที่ต้องใช้ ได้แก่ บัตรประชาชน สำเนาทะเบียนบ้าน และเอกสารทางการแพทย์ (ถ้ามี)', 2, 1),
                ('สามารถเปลี่ยนแผนประกันได้หรือไม่?', 'สามารถเปลี่ยนแผนประกันได้ตามเงื่อนไขของกรมธรรม์ โดยติดต่อตัวแทนหรือฝ่ายบริการลูกค้า', 3, 1),
                ('เคลมประกันใช้เวลานานเท่าไหร่?', 'ระยะเวลาในการพิจารณาเคลมโดยทั่วไปใช้เวลา 7-15 วันทำการ', 4, 1),
                ('มีบริการปรึกษาฟรีหรือไม่?', 'มีบริการปรึกษาฟรีจากทีมผู้เชี่ยวชาญของเรา ติดต่อได้ผ่านแบบฟอร์ม โทรศัพท์ หรือ Line Official', 5, 1),
                ('สามารถจ่ายเบี้ยประกันรายเดือนได้หรือไม่?', 'สามารถเลือกชำระเบี้ยได้หลายรูปแบบ ทั้งรายเดือน ราย 3 เดือน ราย 6 เดือน หรือรายปี ตามแผนที่คุณสมัคร', 6, 1),
                ('ประกันคุ้มครองตั้งแต่เมื่อไหร่?', 'ความคุ้มครองเริ่มต้นตามเงื่อนไขในกรมธรรม์ โดยทั่วไปมีผลหลังอนุมัติและชำระเบี้ยงวดแรกเรียบร้อยแล้ว', 7, 1),
                ('ยกเลิกกรมธรรม์ได้หรือไม่?', 'สามารถยกเลิกกรมธรรม์ได้ตามเงื่อนไขที่ระบุในกรมธรรม์ โดยแนะนำให้ปรึกษาตัวแทนก่อนตัดสินใจ', 8, 1)");
            }

            if ($prefill['run_seed'] === '1' && is_file(__DIR__ . '/includes/seed-import.php')) {
                require_once __DIR__ . '/config.php';
                require_once __DIR__ . '/includes/db.php';
                require_once __DIR__ . '/includes/seed-import.php';
                $categories = require __DIR__ . '/seed-categories.php';
                $stats = runFullSeedImport($categories);
                $seedNote = 'Seed OK: plans=' . (int) $stats['plans'] . ', blogs=' . (int) $stats['blogs'] . ', promos=' . (int) $stats['promos'];
            }

            @file_put_contents($lockFile, date('c') . "\n");
            $step = 'success';
            $message = 'Install completed';
        } catch (Exception $e) {
            $step = 'error';
            $message = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Install - Agent Thailand</title>
<style>
body{font-family:system-ui,sans-serif;background:#f1f5f9;margin:0;padding:24px}
.card{max-width:520px;margin:0 auto;background:#fff;border-radius:16px;padding:28px;box-shadow:0 8px 24px rgba(0,0,0,.08)}
h1{margin:0 0 8px;font-size:22px} .sub{color:#64748b;margin:0 0 20px;font-size:14px}
label{display:block;font-size:13px;font-weight:600;margin:0 0 6px;color:#334155}
input{width:100%;box-sizing:border-box;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;margin:0 0 14px}
.btn{width:100%;border:0;border-radius:10px;padding:12px;background:#150f96;color:#fff;font-weight:700;cursor:pointer}
.ok{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;padding:12px;border-radius:10px;margin:0 0 14px}
.err{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px;border-radius:10px;margin:0 0 14px}
.hint{background:#f8fafc;color:#475569;padding:10px;border-radius:8px;font-size:12px;margin:0 0 14px}
a{color:#150f96}
</style>
</head>
<body>
<div class="card">
  <h1>ติดตั้งระบบ</h1>
  <p class="sub">prakandd-thailand.com · PHP <?php echo inst_h(PHP_VERSION); ?></p>

  <?php if ($step === 'locked'): ?>
    <div class="ok">ติดตั้งแล้ว</div>
    <p><a href="<?php echo inst_h($adminUrl . '/login.php'); ?>">ไปหน้าเข้าสู่ระบบ</a></p>
    <p class="hint">ติดตั้งใหม่: ลบ admin/install.lock หรือเปิด ?force=1</p>

  <?php elseif ($step === 'success'): ?>
    <div class="ok"><?php echo inst_h($message); ?></div>
    <?php if ($seedNote): ?><div class="ok"><?php echo inst_h($seedNote); ?></div><?php endif; ?>
    <p>หลังบ้าน: <a href="<?php echo inst_h($adminUrl . '/login.php'); ?>"><?php echo inst_h($adminUrl . '/login.php'); ?></a></p>
    <p>User: <strong><?php echo inst_h($prefill['admin_user']); ?></strong></p>

  <?php else: ?>
    <?php if ($step === 'error'): ?>
      <div class="err"><strong>ติดตั้งไม่สำเร็จ</strong><br><?php echo inst_h($message); ?></div>
      <div class="hint">สร้าง Database + User ใน cPanel/Plesk ก่อน แล้วใส่ค่าตรงนี้ (ห้ามใช้ root)</div>
    <?php endif; ?>
    <?php if ($errors): ?>
      <div class="err"><?php foreach ($errors as $e): ?><div><?php echo inst_h($e); ?></div><?php endforeach; ?></div>
    <?php endif; ?>

    <div class="hint">SITE_URL=<?php echo inst_h($siteUrl === '' ? '(root)' : $siteUrl); ?> · ADMIN_URL=<?php echo inst_h($adminUrl); ?></div>

    <form method="post">
      <label>DB Host</label>
      <input name="db_host" value="<?php echo inst_h($prefill['db_host']); ?>" required>

      <label>ชื่อฐานข้อมูล</label>
      <input name="db_name" value="<?php echo inst_h($prefill['db_name']); ?>" required placeholder="จาก MySQL ในโฮสต์">

      <label>DB Username (ห้าม root)</label>
      <input name="db_user" value="<?php echo inst_h($prefill['db_user']); ?>" required>

      <label>DB Password</label>
      <input type="password" name="db_pass" value="<?php echo inst_h($prefill['db_pass']); ?>">

      <label>Admin username</label>
      <input name="admin_user" value="<?php echo inst_h($prefill['admin_user']); ?>" required>

      <label>Admin password</label>
      <input name="admin_pass" value="<?php echo inst_h($prefill['admin_pass']); ?>" required>

      <label>Display name</label>
      <input name="admin_name" value="<?php echo inst_h($prefill['admin_name']); ?>">

      <label style="font-weight:500"><input type="checkbox" name="run_seed" value="1" <?php echo $prefill['run_seed'] ? 'checked' : ''; ?>> นำเข้าข้อมูลเริ่มต้น</label>

      <button class="btn" type="submit">ติดตั้งฐานข้อมูล</button>
    </form>
  <?php endif; ?>
</div>
</body>
</html>
