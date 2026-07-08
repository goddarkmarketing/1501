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
            if (in_array($newStatus, ['contacted', 'closed'])) {
                query('UPDATE contact_submissions SET status = ? WHERE id = ?', [$newStatus, $_POST['contact_id']]);
                $message = 'อัปเดตสถานะเรียบร้อยแล้ว';
                $messageType = 'success';
            }
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
$statusColors = [
    'new' => 'bg-blue-900/30 text-blue-400',
    'contacted' => 'bg-yellow-900/30 text-yellow-400',
    'closed' => 'bg-gray-700/30 text-gray-400',
];

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
<div class="mb-6 px-4 py-3 rounded-lg text-sm <?= $messageType === 'success' ? 'bg-emerald-900/30 border border-emerald-800 text-emerald-300' : 'bg-red-900/30 border border-red-800 text-red-300' ?>">
  <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<div class="flex items-center justify-between mb-6">
  <p class="text-sm text-gray-400">ข้อความติดต่อจากลูกค้า</p>
</div>

<?php if ($dbError): ?>
<div class="bg-gray-900 border border-gray-800 rounded-xl p-8 text-center">
  <p class="text-gray-400 mb-2">กรุณาตั้งค่าฐานข้อมูลก่อน</p>
  <p class="text-sm text-gray-500">รัน <code class="bg-gray-800 px-2 py-0.5 rounded text-xs text-gray-300">setup.sql</code> เพื่อสร้างตารางที่จำเป็น</p>
</div>
<?php elseif (empty($contacts)): ?>
<div class="bg-gray-900 border border-gray-800 rounded-xl p-12 text-center">
  <p class="text-gray-500">ยังไม่มีข้อความติดต่อ</p>
</div>
<?php else: ?>
<div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
  <div class="overflow-x-auto">
    <table class="min-w-full">
      <thead class="bg-gray-800/50">
        <tr>
          <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase text-left">ชื่อ</th>
          <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase text-left">เบอร์โทร</th>
          <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase text-left">อีเมล</th>
          <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase text-left">ประเภทประกัน</th>
          <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase text-center">สถานะ</th>
          <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase text-left">วันที่</th>
          <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase text-right">จัดการ</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-800">
        <?php foreach ($contacts as $contact): ?>
        <tr class="hover:bg-gray-800/30 transition">
          <td class="px-4 py-3 text-sm text-white font-medium"><?= htmlspecialchars($contact['name'] ?? '-') ?></td>
          <td class="px-4 py-3 text-sm"><?= htmlspecialchars($contact['phone'] ?? '-') ?></td>
          <td class="px-4 py-3 text-sm text-gray-400"><?= htmlspecialchars($contact['email'] ?? '-') ?></td>
          <td class="px-4 py-3 text-sm text-gray-400"><?= htmlspecialchars($contact['insurance_type'] ?? '-') ?></td>
          <td class="px-4 py-3 text-sm text-center">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusColors[$contact['status']] ?? 'bg-gray-700/30 text-gray-400' ?>">
              <?= $statusLabels[$contact['status']] ?? $contact['status'] ?>
            </span>
          </td>
          <td class="px-4 py-3 text-sm text-gray-400"><?= date('d/m/Y H:i', strtotime($contact['created_at'])) ?></td>
          <td class="px-4 py-3 text-sm text-right">
            <div class="flex items-center justify-end gap-2">
              <?php if ($contact['status'] === 'new'): ?>
              <form method="POST" class="inline">
                <input type="hidden" name="contact_id" value="<?= $contact['id'] ?>">
                <input type="hidden" name="update_status" value="contacted">
                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 border border-gray-700 text-gray-300 hover:bg-gray-800 text-xs rounded-lg transition">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                  ติดต่อแล้ว
                </button>
              </form>
              <?php endif; ?>
              <?php if ($contact['status'] !== 'closed'): ?>
              <form method="POST" class="inline">
                <input type="hidden" name="contact_id" value="<?= $contact['id'] ?>">
                <input type="hidden" name="update_status" value="closed">
                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 border border-gray-700 text-gray-300 hover:bg-gray-800 text-xs rounded-lg transition">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                  ปิด
                </button>
              </form>
              <?php endif; ?>
              <form method="POST" onsubmit="return confirm('ต้องการลบข้อความนี้?')" class="inline">
                <input type="hidden" name="delete_id" value="<?= $contact['id'] ?>">
                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs rounded-lg transition">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                  ลบ
                </button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
