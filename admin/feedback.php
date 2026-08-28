<?php
$pageTitle = 'Feedback';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/feedback-lib.php';

if (feedbackPreviewRequested()) {
    feedbackActivatePreview();
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['set_review_password'])) {
        $pass = $_POST['review_password'] ?? '';
        if (strlen($pass) < 4) {
            $message = 'รหัสผ่านต้องมีอย่างน้อย 4 ตัวอักษร';
            $messageType = 'error';
        } else {
            feedbackSetReviewPassword($pass);
            $message = 'บันทึกรหัสผ่านลิงก์ตรวจงานเรียบร้อยแล้ว';
            $messageType = 'success';
        }
    }
}

$filters = [
    'page' => $_GET['page'] ?? '',
    'category' => $_GET['category'] ?? '',
    'priority' => $_GET['priority'] ?? '',
    'status' => $_GET['status'] ?? '',
];
$items = array_reverse(feedbackFilterItems(feedbackLoadItems(), $filters));
$pages = array_unique(array_filter(array_map(static function ($i) {
    return $i['page'] ?? '';
}, feedbackLoadItems())));

$categories = feedbackCategories();
$priorities = feedbackPriorities();
$statuses = feedbackStatuses();
$reviewUrl = feedbackReviewUrl();
$exportBase = ADMIN_URL . '/api/feedback-export.php';
$exportQs = http_build_query(array_filter($filters));
$toolEnabled = feedbackToolEnabled();

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
<div class="mb-6 <?= $messageType === 'success' ? 'admin-alert-success' : 'admin-alert-error' ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="admin-card p-6 mb-6" data-feedback-id="feedback-tool-toggle">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h3 class="text-base font-semibold text-slate-800 mb-1">ปุ่มแจ้งแก้ไข</h3>
      <p class="text-sm text-slate-500">เปิดเมื่อต้องการให้แสดงปุ่ม <strong>แจ้งแก้ไข</strong> ในทุกหน้าหลังบ้าน (และลิงก์ตรวจงานลูกค้า) — ปิดไว้ตามค่าเริ่มต้น</p>
    </div>
    <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
      <input type="checkbox" id="fb-tool-enabled" class="sr-only peer" <?= $toolEnabled ? 'checked' : '' ?>>
      <div class="w-11 h-6 bg-slate-200 peer-focus:ring-2 peer-focus:ring-brand/30 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand"></div>
      <span class="ms-3 text-sm font-medium text-slate-700" id="fb-tool-enabled-label"><?= $toolEnabled ? 'เปิดใช้งาน' : 'ปิดอยู่' ?></span>
    </label>
  </div>
</div>

<div class="admin-card p-6 mb-6 border-brand/20" data-feedback-id="feedback-howto">
  <h3 class="text-base font-semibold text-slate-800 mb-2">วิธีสร้าง Feedback</h3>
  <ol class="text-sm text-slate-600 space-y-1.5 list-decimal list-inside mb-3">
    <li>เปิดสวิตช์ <strong>ปุ่มแจ้งแก้ไข</strong> ด้านบน (ถ้ายังไม่ได้เปิด)</li>
    <li>กดปุ่มสีน้ำเงิน <strong>แจ้งแก้ไข</strong> มุมซ้ายล่าง</li>
    <li>เลื่อนเมาส์ไปวางบนส่วนใดก็ได้ — จะขึ้น<strong>กรอบสีเหลือง</strong></li>
    <li>คลิกส่วนนั้น แล้วพิมพ์รายละเอียดที่ต้องการแก้</li>
    <li>กดบันทึก (ระบบแคปหน้าจอให้อัตโนมัติ) หรือกด Esc เพื่อยกเลิก</li>
  </ol>
  <p class="text-xs text-slate-400">ใช้ได้ทุกหน้าในหลังบ้านขณะเปิดสวิตช์อยู่</p>
</div>

<div class="admin-card p-6 mb-6" data-feedback-id="feedback-settings">
  <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
    <div>
      <h3 class="text-base font-semibold text-slate-800 mb-1">ลิงก์ตรวจงานสำหรับลูกค้า</h3>
      <p class="text-sm text-slate-500 mb-3">ส่งลิงก์นี้ให้ลูกค้าเพื่อเปิดโหมดแจ้งแก้ไขในหลังบ้าน</p>
      <div class="flex flex-wrap items-center gap-2">
        <input class="admin-input max-w-xl" type="text" readonly value="<?= htmlspecialchars($reviewUrl) ?>" id="review-url">
        <button type="button" class="admin-btn-outline" onclick="navigator.clipboard.writeText(document.getElementById('review-url').value)">คัดลอก</button>
        <a href="<?= ADMIN_URL ?>/index.php" class="admin-btn-primary">ไปแดชบอร์ดแล้วลองแจ้งแก้ไข</a>
      </div>
    </div>
    <form method="POST" class="lg:w-72">
      <label class="admin-label" for="review_password">ตั้งรหัสผ่านลิงก์ตรวจงาน</label>
      <div class="flex gap-2">
        <input class="admin-input" type="password" id="review_password" name="review_password" placeholder="รหัสผ่านใหม่" data-sensitive>
        <button type="submit" name="set_review_password" value="1" class="admin-btn-outline whitespace-nowrap">บันทึก</button>
      </div>
    </form>
  </div>
</div>

<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
  <p class="text-sm text-slate-500">รายการ Feedback ทั้งหมด <?= count($items) ?> รายการ</p>
  <div class="flex flex-wrap gap-2">
    <a href="<?= $exportBase ?>?type=json&amp;<?= $exportQs ?>" class="admin-btn-outline text-xs">Export JSON</a>
    <a href="<?= $exportBase ?>?type=markdown&amp;<?= $exportQs ?>" class="admin-btn-outline text-xs">Export Markdown</a>
    <a href="<?= $exportBase ?>?type=screenshots&amp;<?= $exportQs ?>" class="admin-btn-outline text-xs">Download Screenshots</a>
    <a href="<?= $exportBase ?>?type=package&amp;<?= $exportQs ?>" class="admin-btn-primary text-xs">Export Package</a>
  </div>
</div>

<form method="GET" class="admin-card p-4 mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3" data-feedback-id="feedback-filters">
  <div>
    <label class="admin-label">หน้า</label>
    <select name="page" class="admin-input" onchange="this.form.submit()">
      <option value="">ทั้งหมด</option>
      <?php foreach ($pages as $p): ?>
      <option value="<?= htmlspecialchars($p) ?>" <?= $filters['page'] === $p ? 'selected' : '' ?>><?= htmlspecialchars($p) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="admin-label">ประเภท</label>
    <select name="category" class="admin-input" onchange="this.form.submit()">
      <option value="">ทั้งหมด</option>
      <?php foreach ($categories as $k => $v): ?>
      <option value="<?= $k ?>" <?= $filters['category'] === $k ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="admin-label">ความสำคัญ</label>
    <select name="priority" class="admin-input" onchange="this.form.submit()">
      <option value="">ทั้งหมด</option>
      <?php foreach ($priorities as $k => $v): ?>
      <option value="<?= $k ?>" <?= $filters['priority'] === $k ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="admin-label">สถานะ</label>
    <select name="status" class="admin-input" onchange="this.form.submit()">
      <option value="">ทั้งหมด</option>
      <?php foreach ($statuses as $k => $v): ?>
      <option value="<?= $k ?>" <?= $filters['status'] === $k ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
</form>

<?php if (empty($items)): ?>
<div class="admin-card p-12 text-center text-slate-500">ยังไม่มีรายการ Feedback</div>
<?php else: ?>
<?php
$statusMeta = [
  'pending' => ['label' => 'รอดำเนินการ', 'icon' => 'clock', 'badge' => 'bg-slate-100 text-slate-600', 'card' => ''],
  'in-progress' => ['label' => 'กำลังดำเนินการ', 'icon' => 'loader', 'badge' => 'bg-sky-100 text-sky-700', 'card' => 'border-sky-200'],
  'completed' => ['label' => 'แก้ไขแล้ว', 'icon' => 'circle-check', 'badge' => 'bg-emerald-100 text-emerald-700', 'card' => 'border-emerald-300 bg-emerald-50/40'],
  'rejected' => ['label' => 'ปฏิเสธ', 'icon' => 'circle-x', 'badge' => 'bg-rose-100 text-rose-700', 'card' => 'border-rose-200 opacity-80'],
];
?>
<div class="space-y-4" data-feedback-id="feedback-list">
  <?php foreach ($items as $fb):
    $fbId = (string) ($fb['id'] ?? '');
    if ($fbId === '') {
        continue;
    }
    $imgUrl = !empty($fb['screenshot'])
      ? ADMIN_URL . '/data/feedback/' . $fb['screenshot']
      : '';
    $prio = $fb['priority'] ?? 'medium';
    $prioClass = $prio === 'high' ? 'bg-red-100 text-red-700' : ($prio === 'low' ? 'bg-slate-100 text-slate-600' : 'bg-amber-100 text-amber-700');
    $st = $fb['status'] ?? 'pending';
    if (!isset($statusMeta[$st])) {
        $st = 'pending';
    }
    $meta = $statusMeta[$st];
  ?>
  <div class="admin-card p-4 sm:p-5 fb-item-card <?= htmlspecialchars($meta['card']) ?>" data-feedback-id="feedback-item-<?= htmlspecialchars($fbId) ?>" data-status="<?= htmlspecialchars($st) ?>">
    <div class="flex flex-col lg:flex-row gap-4">
      <?php if ($imgUrl): ?>
      <button type="button" class="flex-shrink-0 fb-preview-thumb text-left" data-src="<?= htmlspecialchars($imgUrl) ?>" data-alt="<?= htmlspecialchars($fbId) ?>">
        <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($fbId) ?>" class="w-full lg:w-48 h-auto rounded-lg border border-slate-200 object-cover pointer-events-none">
      </button>
      <?php endif; ?>
      <div class="flex-1 min-w-0">
        <div class="flex flex-wrap items-center gap-2 mb-2">
          <span class="font-bold text-brand"><?= htmlspecialchars($fbId) ?></span>
          <span class="fb-status-badge inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full <?= htmlspecialchars($meta['badge']) ?>">
            <i data-lucide="<?= htmlspecialchars($meta['icon']) ?>" class="w-3.5 h-3.5"></i>
            <span class="fb-status-badge-label"><?= htmlspecialchars($meta['label']) ?></span>
          </span>
          <span class="text-xs px-2 py-0.5 rounded-full <?= $prioClass ?>"><?= htmlspecialchars($priorities[$prio] ?? $prio) ?></span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-600"><?= htmlspecialchars($categories[$fb['category'] ?? ''] ?? $fb['category'] ?? '') ?></span>
        </div>
        <p class="text-sm font-medium text-slate-800 mb-1"><?= htmlspecialchars($fb['page'] ?? '') ?></p>
        <p class="text-xs text-slate-500 mb-2"><?= htmlspecialchars($fb['section'] ?? $fb['feedbackId'] ?? '') ?> · <code class="text-xs"><?= htmlspecialchars($fb['selector'] ?? '') ?></code></p>
        <p class="text-sm text-slate-700 mb-3"><?= nl2br(htmlspecialchars($fb['comment'] ?? '')) ?></p>
        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400">
          <?php if (!empty($fb['clientName'])): ?>
          <span>ผู้แจ้ง: <?= htmlspecialchars($fb['clientName']) ?></span>
          <?php endif; ?>
          <span><?= !empty($fb['createdAt']) ? date('d/m/Y H:i', strtotime($fb['createdAt'])) : '' ?></span>
        </div>
      </div>
      <div class="flex flex-col gap-2 lg:w-44">
        <label class="text-xs text-slate-500">เปลี่ยนสถานะ</label>
        <select class="admin-input text-sm fb-status-select" data-id="<?= htmlspecialchars($fbId) ?>" aria-label="สถานะ <?= htmlspecialchars($fbId) ?>">
          <?php foreach ($statuses as $k => $v): ?>
          <option value="<?= $k ?>" <?= $st === $k ? 'selected' : '' ?>><?= htmlspecialchars($k === 'completed' ? 'แก้ไขแล้ว' : $v) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="button" class="admin-btn-outline text-xs fb-delete-btn" data-id="<?= htmlspecialchars($fbId) ?>">ลบ</button>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
(function () {
  var toolToggle = document.getElementById('fb-tool-enabled');
  if (toolToggle) {
    toolToggle.addEventListener('change', function () {
      var enabled = toolToggle.checked;
      var label = document.getElementById('fb-tool-enabled-label');
      toolToggle.disabled = true;
      fetch('<?= ADMIN_URL ?>/api/feedback-update.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ action: 'tool_enabled', enabled: enabled }),
      }).then(function (r) { return r.json(); }).then(function (d) {
        if (!d.ok) {
          toolToggle.checked = !enabled;
          alert(d.error || 'บันทึกไม่สำเร็จ');
          return;
        }
        if (label) label.textContent = enabled ? 'เปิดใช้งาน' : 'ปิดอยู่';
        location.reload();
      }).catch(function () {
        toolToggle.checked = !enabled;
        alert('บันทึกไม่สำเร็จ');
      }).finally(function () {
        toolToggle.disabled = false;
      });
    });
  }

  var lightbox = document.createElement('div');
  lightbox.id = 'fb-img-lightbox';
  lightbox.className = 'fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/70 p-4';
  lightbox.innerHTML =
    '<button type="button" class="absolute top-4 right-4 text-white text-3xl leading-none px-2" aria-label="ปิด">&times;</button>' +
    '<img src="" alt="" class="max-w-full max-h-[90vh] rounded-lg shadow-xl object-contain bg-white">';
  document.body.appendChild(lightbox);

  var img = lightbox.querySelector('img');
  var closeBtn = lightbox.querySelector('button');

  function closeLightbox() {
    lightbox.classList.add('hidden');
    lightbox.classList.remove('flex');
    img.src = '';
  }

  function openLightbox(src, alt) {
    img.src = src;
    img.alt = alt || '';
    lightbox.classList.remove('hidden');
    lightbox.classList.add('flex');
  }

  document.querySelectorAll('.fb-preview-thumb').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      openLightbox(btn.dataset.src, btn.dataset.alt);
    });
  });

  closeBtn.addEventListener('click', closeLightbox);
  lightbox.addEventListener('click', function (e) {
    if (e.target === lightbox) closeLightbox();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeLightbox();
  });

  document.querySelectorAll('.fb-status-select').forEach(function (sel) {
    sel.addEventListener('change', function () {
      var card = sel.closest('.fb-item-card');
      var status = sel.value;
      fetch('<?= ADMIN_URL ?>/api/feedback-update.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ action: 'status', id: sel.dataset.id, status: status }),
      }).then(function (r) { return r.json(); }).then(function (d) {
        if (!d.ok) {
          alert(d.error || 'อัปเดตไม่สำเร็จ');
          return;
        }
        if (card) applyFeedbackStatusUI(card, status);
      });
    });
  });

  var statusUI = {
    pending: { label: 'รอดำเนินการ', icon: 'clock', badge: 'bg-slate-100 text-slate-600', card: '' },
    'in-progress': { label: 'กำลังดำเนินการ', icon: 'loader', badge: 'bg-sky-100 text-sky-700', card: 'border-sky-200' },
    completed: { label: 'แก้ไขแล้ว', icon: 'circle-check', badge: 'bg-emerald-100 text-emerald-700', card: 'border-emerald-300 bg-emerald-50/40' },
    rejected: { label: 'ปฏิเสธ', icon: 'circle-x', badge: 'bg-rose-100 text-rose-700', card: 'border-rose-200 opacity-80' }
  };

  function applyFeedbackStatusUI(card, status) {
    var meta = statusUI[status] || statusUI.pending;
    card.dataset.status = status;
    var badge = card.querySelector('.fb-status-badge');
    if (badge) {
      badge.className = 'fb-status-badge inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full ' + meta.badge;
      badge.innerHTML = '<i data-lucide="' + meta.icon + '" class="w-3.5 h-3.5"></i><span class="fb-status-badge-label">' + meta.label + '</span>';
      if (typeof lucide !== 'undefined') lucide.createIcons();
    }
  }
  document.querySelectorAll('.fb-delete-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!confirm('ลบรายการ ' + btn.dataset.id + '?')) return;
      fetch('<?= ADMIN_URL ?>/api/feedback-update.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ action: 'delete', id: btn.dataset.id }),
      }).then(function (r) { return r.json(); }).then(function (d) {
        if (d.ok) location.reload();
        else alert(d.error || 'ลบไม่สำเร็จ');
      });
    });
  });
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
