<?php
$pageTitle = 'ข้อความติดต่อ';
require_once __DIR__ . '/includes/auth.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['delete_id'])) {
            query('DELETE FROM contact_submissions WHERE id = ?', [$_POST['delete_id']]);
            $message = 'ลบข้อความเรียบร้อยแล้ว';
            $messageType = 'success';
        } elseif (isset($_POST['update_status'], $_POST['contact_id'])) {
            $newStatus = $_POST['update_status'];
            if (in_array($newStatus, ['contacted', 'closed'], true)) {
                query('UPDATE contact_submissions SET status = ? WHERE id = ?', [$newStatus, $_POST['contact_id']]);
                $message = 'อัปเดตสถานะเรียบร้อยแล้ว';
                $messageType = 'success';
            }
        } elseif (isset($_POST['insert_sample'])) {
            query(
                "INSERT INTO contact_submissions (name, phone, email, insurance_type, province, age, callback_time, message, status) VALUES (?,?,?,?,?,?,?,?, 'new')",
                [
                    'คุณสมชาย ใจดี (ตัวอย่าง)',
                    '081-234-5678',
                    'somchai.demo@email.com',
                    'ประกันสุขภาพ',
                    'กรุงเทพมหานคร',
                    35,
                    'ช่วงเย็นหลัง 17:00 น.',
                    'สนใจแผนสุขภาพสำหรับครอบครัว ขอให้ติดต่อกลับเพื่อปรึกษาเพิ่มเติม',
                ]
            );
            $message = 'สร้างข้อความตัวอย่างเรียบร้อยแล้ว';
            $messageType = 'success';
        }
    } catch (Exception $e) {
        $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        $messageType = 'error';
    }
}

$contacts = [];
$dbError = false;
try {
    $contacts = fetchAll('SELECT * FROM contact_submissions ORDER BY created_at DESC');
} catch (Exception $e) {
    $dbError = true;
}

$statusLabels = ['new' => 'ใหม่', 'contacted' => 'ติดต่อแล้ว', 'closed' => 'ปิดแล้ว'];
$statusBadge = [
    'new' => 'bg-sky-100 text-sky-800',
    'contacted' => 'bg-amber-100 text-amber-800',
    'closed' => 'bg-slate-100 text-slate-600',
];

$sampleRows = [
    [
        'name' => 'คุณสมชาย ใจดี',
        'phone' => '081-234-5678',
        'email' => 'somchai.demo@email.com',
        'insurance_type' => 'ประกันสุขภาพ',
        'status' => 'new',
        'created_at' => date('Y-m-d H:i:s'),
        'message' => 'สนใจแผนสุขภาพสำหรับครอบครัว',
    ],
    [
        'name' => 'คุณมาลี รักดี',
        'phone' => '089-111-2222',
        'email' => 'malee.demo@email.com',
        'insurance_type' => 'ประกันชีวิต',
        'status' => 'contacted',
        'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        'message' => 'ขอใบเสนอราคา',
    ],
];

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
<?= adminAlert($message, $messageType) ?>
<?php endif; ?>

<div class="flex items-center justify-between mb-6">
  <p class="text-sm text-slate-500">ข้อความติดต่อจากลูกค้าผ่านแบบฟอร์มหน้าเว็บ</p>
</div>

<?php if ($dbError): ?>
<div class="admin-card p-8 text-center">
  <p class="text-slate-500 mb-2">กรุณาตั้งค่าฐานข้อมูลก่อน</p>
</div>
<?php elseif (empty($contacts)): ?>
<div class="admin-card p-8 text-center mb-6">
  <p class="text-slate-600 mb-2">ยังไม่มีข้อความติดต่อจริง</p>
  <p class="text-sm text-slate-400 mb-4">ด้านล่างคือตัวอย่างหน้าตาเมื่อมีข้อความเข้ามา — กดปุ่มเพื่อสร้างข้อความตัวอย่างในระบบได้</p>
  <form method="POST">
    <button type="submit" name="insert_sample" value="1" class="admin-btn-primary">สร้างข้อความตัวอย่าง</button>
  </form>
</div>

<div class="admin-card overflow-hidden opacity-90" data-feedback-id="contacts-preview">
  <div class="px-4 py-3 border-b border-slate-100 bg-slate-50">
    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">ตัวอย่างการแสดงผล (ยังไม่ใช่ข้อมูลจริง)</p>
  </div>
  <table class="admin-table w-full">
    <thead>
      <tr>
        <th>ชื่อ</th>
        <th>เบอร์โทร</th>
        <th>อีเมล</th>
        <th>ประเภทประกัน</th>
        <th class="text-center">สถานะ</th>
        <th>วันที่</th>
        <th class="text-right">จัดการ</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($sampleRows as $contact): ?>
      <tr>
        <td class="font-medium text-slate-800"><?= htmlspecialchars($contact['name']) ?></td>
        <td><?= htmlspecialchars($contact['phone']) ?></td>
        <td class="text-slate-500"><?= htmlspecialchars($contact['email']) ?></td>
        <td class="text-slate-600"><?= htmlspecialchars($contact['insurance_type']) ?></td>
        <td class="text-center">
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $statusBadge[$contact['status']] ?>">
            <?= $statusLabels[$contact['status']] ?>
          </span>
        </td>
        <td class="text-slate-500 text-sm"><?= date('d/m/Y H:i', strtotime($contact['created_at'])) ?></td>
        <td class="text-right text-slate-400 text-sm">ติดต่อแล้ว · ปิด · ลบ</td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="admin-card overflow-hidden">
  <table class="admin-table w-full">
    <thead>
      <tr>
        <th>ชื่อ</th>
        <th>เบอร์โทร</th>
        <th>อีเมล</th>
        <th>ประเภทประกัน</th>
        <th class="text-center">สถานะ</th>
        <th>วันที่</th>
        <th class="text-right">จัดการ</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($contacts as $contact): ?>
      <tr>
        <td class="font-medium text-slate-800"><?= htmlspecialchars($contact['name'] ?? '-') ?></td>
        <td><?= htmlspecialchars($contact['phone'] ?? '-') ?></td>
        <td class="text-slate-500"><?= htmlspecialchars($contact['email'] ?? '-') ?></td>
        <td class="text-slate-600"><?= htmlspecialchars($contact['insurance_type'] ?? '-') ?></td>
        <td class="text-center">
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $statusBadge[$contact['status']] ?? 'bg-slate-100 text-slate-600' ?>">
            <?= $statusLabels[$contact['status']] ?? $contact['status'] ?>
          </span>
        </td>
        <td class="text-slate-500 text-sm"><?= date('d/m/Y H:i', strtotime($contact['created_at'])) ?></td>
        <td class="text-right whitespace-nowrap">
          <?php if (($contact['status'] ?? '') === 'new'): ?>
          <form method="POST" class="inline">
            <input type="hidden" name="contact_id" value="<?= (int) $contact['id'] ?>">
            <input type="hidden" name="update_status" value="contacted">
            <button type="submit" class="text-brand text-sm hover:underline mr-2">ติดต่อแล้ว</button>
          </form>
          <?php endif; ?>
          <?php if (($contact['status'] ?? '') !== 'closed'): ?>
          <form method="POST" class="inline">
            <input type="hidden" name="contact_id" value="<?= (int) $contact['id'] ?>">
            <input type="hidden" name="update_status" value="closed">
            <button type="submit" class="text-slate-500 text-sm hover:underline mr-2">ปิด</button>
          </form>
          <?php endif; ?>
          <form method="POST" class="inline" onsubmit="return confirm('ต้องการลบข้อความนี้?')">
            <input type="hidden" name="delete_id" value="<?= (int) $contact['id'] ?>">
            <button type="submit" class="text-red-500 text-sm hover:underline">ลบ</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
