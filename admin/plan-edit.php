<?php
$pageTitle = 'จัดการแผนประกัน';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/upload.php';

$id = $_GET['id'] ?? '';
$isEdit = !empty($id);
$plan = null;
$benefits = [];
$highlights = [];
$tiers = [];
$coverageSummary = [];
$coverageRows = [];
$conditions = [];
$faqs = [];
$renewal = [];
$why = [];
$categories = fetchAll('SELECT id, label FROM plan_categories ORDER BY sort_order');

if ($isEdit) {
    $plan = fetchOne('SELECT * FROM plan_products WHERE id = ?', [$id]);
    if (!$plan) {
        header('Location: ' . ADMIN_URL . '/plans.php?error=' . urlencode('ไม่พบแผนนี้'));
        exit;
    }
    $benefits = fetchAll('SELECT benefit_text FROM plan_benefits WHERE plan_id = ? ORDER BY sort_order', [$id]);
    $highlights = fetchAll('SELECT highlight_text FROM plan_highlights WHERE plan_id = ? ORDER BY sort_order', [$id]);
    $tiers = fetchAll('SELECT * FROM plan_tiers WHERE plan_id = ? ORDER BY sort_order', [$id]);
    $coverageSummary = fetchAll('SELECT * FROM plan_coverage_summary WHERE plan_id = ? ORDER BY sort_order', [$id]);
    $coverageRows = fetchAll('SELECT * FROM plan_coverage_rows WHERE plan_id = ? ORDER BY sort_order', [$id]);
    $conditions = fetchAll('SELECT condition_text FROM plan_conditions WHERE plan_id = ? ORDER BY sort_order', [$id]);
    $faqs = fetchAll('SELECT question, answer FROM plan_faqs WHERE plan_id = ? ORDER BY sort_order', [$id]);
    $renewal = fetchAll('SELECT renewal_text FROM plan_renewal WHERE plan_id = ? ORDER BY sort_order', [$id]);
    $why = fetchAll('SELECT why_text FROM plan_why WHERE plan_id = ? ORDER BY sort_order', [$id]);
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = getDB();
        $db->beginTransaction();

        $planId = trim($_POST['id'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $categoryId = $_POST['category_id'] ?? '';
        $tagline = trim($_POST['tagline'] ?? '');
        $headline = trim($_POST['headline'] ?? '');
        $priceFrom = floatval($_POST['price_from'] ?? 0);
        $priceNote = trim($_POST['price_note'] ?? '');
        $heroImage = trim($_POST['hero_image'] ?? '');
        $uploadedHero = handleImageUpload($_FILES['hero_image_file'] ?? null, 'plans');
        if ($uploadedHero) {
            $heroImage = $uploadedHero;
        }
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $promoText = trim($_POST['promo_text'] ?? '');
        $promoCode = trim($_POST['promo_code'] ?? '');
        $promoUntil = trim($_POST['promo_until'] ?? '');

        if (empty($planId) || empty($name)) {
            throw new Exception('กรุณากรอก ID และชื่อแผน');
        }

        $existing = fetchOne('SELECT id FROM plan_products WHERE id = ?', [$planId]);
        if ($existing) {
            query('UPDATE plan_products SET name=?, category_id=?, tagline=?, headline=?, price_from=?, price_note=?, hero_image=?, promo_text=?, promo_code=?, promo_until=?, is_active=?, sort_order=? WHERE id=?', [
                $name, $categoryId, $tagline, $headline, $priceFrom, $priceNote, $heroImage, $promoText, $promoCode, $promoUntil, $isActive, $sortOrder, $planId
            ]);
        } else {
            query('INSERT INTO plan_products (id, name, category_id, tagline, headline, price_from, price_note, hero_image, promo_text, promo_code, promo_until, is_active, sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)', [
                $planId, $name, $categoryId, $tagline, $headline, $priceFrom, $priceNote, $heroImage, $promoText, $promoCode, $promoUntil, $isActive, $sortOrder
            ]);
        }

        // Benefits
        query('DELETE FROM plan_benefits WHERE plan_id = ?', [$planId]);
        $lines = array_filter(array_map('trim', explode("\n", $_POST['benefits'] ?? '')));
        foreach ($lines as $i => $line) {
            query('INSERT INTO plan_benefits (plan_id, benefit_text, sort_order) VALUES (?,?,?)', [$planId, $line, $i]);
        }

        // Highlights
        query('DELETE FROM plan_highlights WHERE plan_id = ?', [$planId]);
        $lines = array_filter(array_map('trim', explode("\n", $_POST['highlights'] ?? '')));
        foreach ($lines as $i => $line) {
            query('INSERT INTO plan_highlights (plan_id, highlight_text, sort_order) VALUES (?,?,?)', [$planId, $line, $i]);
        }

        // Tiers
        query('DELETE FROM plan_tiers WHERE plan_id = ?', [$planId]);
        $tierIds = $_POST['tier_id'] ?? [];
        $tierLabels = $_POST['tier_label'] ?? [];
        $tierAmounts = $_POST['tier_amount'] ?? [];
        $tierUnits = $_POST['tier_unit'] ?? [];
        $tierPopular = $_POST['tier_popular'] ?? [];
        foreach ($tierIds as $i => $tid) {
            $tid = trim($tid);
            if (empty($tid) && empty(trim($tierLabels[$i] ?? ''))) continue;
            query('INSERT INTO plan_tiers (plan_id, tier_id, label, amount, unit, is_popular, sort_order) VALUES (?,?,?,?,?,?,?)', [
                $planId, $tid, trim($tierLabels[$i] ?? ''), trim($tierAmounts[$i] ?? ''), trim($tierUnits[$i] ?? ''), in_array((string)$i, $tierPopular) ? 1 : 0, $i
            ]);
        }

        // Coverage Summary
        query('DELETE FROM plan_coverage_summary WHERE plan_id = ?', [$planId]);
        $csLabels = $_POST['cs_label'] ?? [];
        $csValues = $_POST['cs_value'] ?? [];
        $csUnits = $_POST['cs_unit'] ?? [];
        foreach ($csLabels as $i => $lbl) {
            if (empty(trim($lbl))) continue;
            query('INSERT INTO plan_coverage_summary (plan_id, label, value, unit, sort_order) VALUES (?,?,?,?,?)', [
                $planId, trim($lbl), trim($csValues[$i] ?? ''), trim($csUnits[$i] ?? ''), $i
            ]);
        }

        // Coverage Rows
        query('DELETE FROM plan_coverage_rows WHERE plan_id = ?', [$planId]);
        $crLabels = $_POST['cr_label'] ?? [];
        $crValues = $_POST['cr_values'] ?? [];
        foreach ($crLabels as $i => $lbl) {
            if (empty(trim($lbl))) continue;
            query('INSERT INTO plan_coverage_rows (plan_id, label, values_json, sort_order) VALUES (?,?,?,?)', [
                $planId, trim($lbl), trim($crValues[$i] ?? ''), $i
            ]);
        }

        // Conditions
        query('DELETE FROM plan_conditions WHERE plan_id = ?', [$planId]);
        $lines = array_filter(array_map('trim', explode("\n", $_POST['conditions'] ?? '')));
        foreach ($lines as $i => $line) {
            query('INSERT INTO plan_conditions (plan_id, condition_text, sort_order) VALUES (?,?,?)', [$planId, $line, $i]);
        }

        // FAQs
        query('DELETE FROM plan_faqs WHERE plan_id = ?', [$planId]);
        $faqQ = $_POST['faq_question'] ?? [];
        $faqA = $_POST['faq_answer'] ?? [];
        foreach ($faqQ as $i => $q) {
            $q = trim($q);
            if (empty($q)) continue;
            query('INSERT INTO plan_faqs (plan_id, question, answer, sort_order) VALUES (?,?,?,?)', [
                $planId, $q, trim($faqA[$i] ?? ''), $i
            ]);
        }

        // Renewal
        query('DELETE FROM plan_renewal WHERE plan_id = ?', [$planId]);
        $lines = array_filter(array_map('trim', explode("\n", $_POST['renewal'] ?? '')));
        foreach ($lines as $i => $line) {
            query('INSERT INTO plan_renewal (plan_id, renewal_text, sort_order) VALUES (?,?,?)', [$planId, $line, $i]);
        }

        // Why
        query('DELETE FROM plan_why WHERE plan_id = ?', [$planId]);
        $lines = array_filter(array_map('trim', explode("\n", $_POST['why'] ?? '')));
        foreach ($lines as $i => $line) {
            query('INSERT INTO plan_why (plan_id, why_text, sort_order) VALUES (?,?,?)', [$planId, $line, $i]);
        }

        $db->commit();
        header('Location: ' . ADMIN_URL . '/plans.php?success=' . urlencode('บันทึกแผน "' . $name . '" เรียบร้อยแล้ว'));
        exit;
    } catch (Exception $e) {
        if (isset($db) && $db->inTransaction()) $db->rollBack();
        $error = $e->getMessage();
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($error): ?>
<?= adminAlert($error, 'error') ?>
<?php endif; ?>

<div class="flex items-center justify-between mb-6">
  <div>
    <a href="<?= ADMIN_URL ?>/plans.php" class="text-sm text-slate-500 hover:text-brand">← กลับรายการแผน</a>
    <h1 class="text-xl font-bold text-slate-800 mt-1"><?= $isEdit ? 'แก้ไขแผน: ' . htmlspecialchars($plan['name']) : 'เพิ่มแผนใหม่' ?></h1>
  </div>
  <?php if ($isEdit && !empty($plan['hero_image'])): ?>
  <img src="<?= htmlspecialchars(imagePublicUrl($plan['hero_image'])) ?>" alt="" class="w-20 h-14 rounded-lg object-cover border border-slate-200">
  <?php endif; ?>
</div>

<form method="POST" enctype="multipart/form-data" class="space-y-6" id="planForm">

  <!-- Basic Info -->
  <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 space-y-4">
    <h3 class="text-white font-semibold text-base mb-4">ข้อมูลพื้นฐาน</h3>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">ID (slug) <span class="text-red-400">*</span></label>
        <input type="text" name="id" value="<?= htmlspecialchars($plan['id'] ?? '') ?>" required <?= $isEdit ? 'readonly' : '' ?>
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none <?= $isEdit ? 'opacity-60 cursor-not-allowed' : '' ?>"
          placeholder="เช่น fwd-easy-e-care">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">ชื่อแผน <span class="text-red-400">*</span></label>
        <input type="text" name="name" value="<?= htmlspecialchars($plan['name'] ?? '') ?>" required
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"
          placeholder="ชื่อแผนประกัน">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">หมวดหมู่</label>
        <select name="category_id" class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none">
          <option value="">-- เลือกหมวดหมู่ --</option>
          <?php foreach ($categories as $cat): ?>
          <option value="<?= htmlspecialchars($cat['id']) ?>" <?= ($plan['category_id'] ?? '') === $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">Tagline</label>
        <input type="text" name="tagline" value="<?= htmlspecialchars($plan['tagline'] ?? '') ?>"
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"
          placeholder="ข้อความสั้นๆ เช่น คุ้มค่าที่สุด">
      </div>
      <div class="lg:col-span-2">
        <label class="block text-sm font-medium text-gray-300 mb-1.5">Headline</label>
        <textarea name="headline" rows="2"
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"
          placeholder="หัวข้อหลักของแผน"><?= htmlspecialchars($plan['headline'] ?? '') ?></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">ราคาเริ่มต้น (บาท)</label>
        <input type="number" name="price_from" step="0.01" value="<?= htmlspecialchars($plan['price_from'] ?? '0') ?>"
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">หมายเหตุราคา</label>
        <input type="text" name="price_note" value="<?= htmlspecialchars($plan['price_note'] ?? '') ?>"
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"
          placeholder="เช่น ต่อเดือน / ต่อปี">
      </div>
      <div class="lg:col-span-2">
        <?php renderImageField('hero_image', $plan['hero_image'] ?? '', 'รูปแผนประกัน (Hero)', 'hero_image_file'); ?>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">ลำดับแสดง</label>
        <input type="number" name="sort_order" value="<?= (int) ($plan['sort_order'] ?? 0) ?>"
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none">
      </div>
      <div class="flex items-center gap-3 pt-6">
        <label class="relative inline-flex items-center cursor-pointer">
          <input type="checkbox" name="is_active" value="1" <?= ($plan['is_active'] ?? 1) ? 'checked' : '' ?> class="sr-only peer">
          <div class="w-11 h-6 bg-gray-700 peer-focus:ring-2 peer-focus:ring-brand/30 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand"></div>
          <span class="ms-3 text-sm font-medium text-gray-300">เปิดใช้งาน</span>
        </label>
      </div>
    </div>
  </div>

  <!-- Promo Section (Collapsible) -->
  <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
    <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('svg').classList.toggle('rotate-180')"
      class="w-full px-6 py-4 flex items-center justify-between text-left">
      <h3 class="text-white font-semibold text-base">โปรโมชัน</h3>
      <svg class="w-5 h-5 text-gray-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>
    <div class="px-6 pb-6 space-y-4 <?= empty($plan['promo_text']) && empty($plan['promo_code']) ? 'hidden' : '' ?>">
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">ข้อความโปรโมชัน</label>
        <input type="text" name="promo_text" value="<?= htmlspecialchars($plan['promo_text'] ?? '') ?>"
          class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none">
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-1.5">รหัสโปรโมชัน</label>
          <input type="text" name="promo_code" value="<?= htmlspecialchars($plan['promo_code'] ?? '') ?>"
            class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-1.5">หมดอายุ</label>
          <input type="text" name="promo_until" value="<?= htmlspecialchars($plan['promo_until'] ?? '') ?>"
            class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"
            placeholder="เช่น 31 ธ.ค. 2568">
        </div>
      </div>
    </div>
  </div>

  <!-- Benefits -->
  <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 space-y-4">
    <h3 class="text-white font-semibold text-base mb-4">สิทธิประโยชน์</h3>
    <p class="text-xs text-gray-500 -mt-2">ใส่ 1 รายการต่อบรรทัด</p>
    <textarea name="benefits" rows="6"
      class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"
      placeholder="ค่ารักษาพยาบาล IPD&#10;ค่าห้องพักเดี่ยว&#10;ค่าผ่าตัด"><?= htmlspecialchars(implode("\n", array_column($benefits, 'benefit_text'))) ?></textarea>
  </div>

  <!-- Highlights -->
  <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 space-y-4">
    <h3 class="text-white font-semibold text-base mb-4">จุดเด่น</h3>
    <p class="text-xs text-gray-500 -mt-2">ใส่ 1 รายการต่อบรรทัด</p>
    <textarea name="highlights" rows="4"
      class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"
      placeholder="ไม่ต้องตรวจสุขภาพ&#10;คุ้มครองทันที"><?= htmlspecialchars(implode("\n", array_column($highlights, 'highlight_text'))) ?></textarea>
  </div>

  <!-- Tiers -->
  <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 space-y-4">
    <h3 class="text-white font-semibold text-base mb-4">ระดับความคุ้มครอง (Tiers)</h3>
    <div id="tiersContainer">
      <?php if (!empty($tiers)): ?>
        <?php foreach ($tiers as $i => $tier): ?>
        <div class="tier-row grid grid-cols-12 gap-3 items-end mb-3">
          <div class="col-span-2">
            <?php if ($i === 0): ?><label class="block text-xs text-gray-400 mb-1">Tier ID</label><?php endif; ?>
            <input type="text" name="tier_id[]" value="<?= htmlspecialchars($tier['tier_id'] ?? '') ?>"
              class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none" placeholder="ID">
          </div>
          <div class="col-span-3">
            <?php if ($i === 0): ?><label class="block text-xs text-gray-400 mb-1">ชื่อ</label><?php endif; ?>
            <input type="text" name="tier_label[]" value="<?= htmlspecialchars($tier['label'] ?? '') ?>"
              class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none" placeholder="ชื่อแพ็กเกจ">
          </div>
          <div class="col-span-2">
            <?php if ($i === 0): ?><label class="block text-xs text-gray-400 mb-1">จำนวน</label><?php endif; ?>
            <input type="text" name="tier_amount[]" value="<?= htmlspecialchars($tier['amount'] ?? '') ?>"
              class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none" placeholder="1,000,000">
          </div>
          <div class="col-span-2">
            <?php if ($i === 0): ?><label class="block text-xs text-gray-400 mb-1">หน่วย</label><?php endif; ?>
            <input type="text" name="tier_unit[]" value="<?= htmlspecialchars($tier['unit'] ?? '') ?>"
              class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none" placeholder="บาท">
          </div>
          <div class="col-span-2 flex items-center gap-2">
            <?php if ($i === 0): ?><label class="block text-xs text-gray-400 mb-1 w-full">ยอดนิยม</label><?php endif; ?>
            <input type="checkbox" name="tier_popular[]" value="<?= $i ?>" <?= $tier['is_popular'] ? 'checked' : '' ?>
              class="w-4 h-4 rounded border-gray-600 bg-gray-700 text-brand focus:ring-brand/30">
          </div>
          <div class="col-span-1">
            <button type="button" onclick="this.closest('.tier-row').remove()" class="text-red-400 hover:text-red-300 p-1">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <button type="button" onclick="addTierRow()"
      class="border border-dashed border-gray-700 text-gray-400 hover:text-white hover:border-gray-500 w-full py-2 rounded-lg text-sm transition">+ เพิ่มระดับ</button>
  </div>

  <!-- Coverage Summary -->
  <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 space-y-4">
    <h3 class="text-white font-semibold text-base mb-4">สรุปความคุ้มครอง</h3>
    <div id="csSummaryContainer">
      <?php if (!empty($coverageSummary)): ?>
        <?php foreach ($coverageSummary as $i => $cs): ?>
        <div class="cs-row grid grid-cols-12 gap-3 items-end mb-3">
          <div class="col-span-5">
            <?php if ($i === 0): ?><label class="block text-xs text-gray-400 mb-1">รายการ</label><?php endif; ?>
            <input type="text" name="cs_label[]" value="<?= htmlspecialchars($cs['label'] ?? '') ?>"
              class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none" placeholder="ค่ารักษาพยาบาล">
          </div>
          <div class="col-span-3">
            <?php if ($i === 0): ?><label class="block text-xs text-gray-400 mb-1">ค่า</label><?php endif; ?>
            <input type="text" name="cs_value[]" value="<?= htmlspecialchars($cs['value'] ?? '') ?>"
              class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none" placeholder="1,000,000">
          </div>
          <div class="col-span-3">
            <?php if ($i === 0): ?><label class="block text-xs text-gray-400 mb-1">หน่วย</label><?php endif; ?>
            <input type="text" name="cs_unit[]" value="<?= htmlspecialchars($cs['unit'] ?? '') ?>"
              class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none" placeholder="บาท/ครั้ง">
          </div>
          <div class="col-span-1">
            <button type="button" onclick="this.closest('.cs-row').remove()" class="text-red-400 hover:text-red-300 p-1">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <button type="button" onclick="addCsRow()"
      class="border border-dashed border-gray-700 text-gray-400 hover:text-white hover:border-gray-500 w-full py-2 rounded-lg text-sm transition">+ เพิ่มรายการ</button>
  </div>

  <!-- Coverage Comparison -->
  <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 space-y-4">
    <h3 class="text-white font-semibold text-base mb-4">ตารางเปรียบเทียบความคุ้มครอง</h3>
    <p class="text-xs text-gray-500 -mt-2">ค่าแต่ละ Tier คั่นด้วยคอมมา</p>
    <div id="crRowsContainer">
      <?php if (!empty($coverageRows)): ?>
        <?php foreach ($coverageRows as $i => $cr): ?>
        <div class="cr-row grid grid-cols-12 gap-3 items-end mb-3">
          <div class="col-span-4">
            <?php if ($i === 0): ?><label class="block text-xs text-gray-400 mb-1">รายการ</label><?php endif; ?>
            <input type="text" name="cr_label[]" value="<?= htmlspecialchars($cr['label'] ?? '') ?>"
              class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none" placeholder="ค่ารักษา IPD">
          </div>
          <div class="col-span-7">
            <?php if ($i === 0): ?><label class="block text-xs text-gray-400 mb-1">ค่า (คั่นด้วย ,)</label><?php endif; ?>
            <input type="text" name="cr_values[]" value="<?= htmlspecialchars($cr['values_json'] ?? '') ?>"
              class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none" placeholder="100,000, 300,000, 500,000">
          </div>
          <div class="col-span-1">
            <button type="button" onclick="this.closest('.cr-row').remove()" class="text-red-400 hover:text-red-300 p-1">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <button type="button" onclick="addCrRow()"
      class="border border-dashed border-gray-700 text-gray-400 hover:text-white hover:border-gray-500 w-full py-2 rounded-lg text-sm transition">+ เพิ่มแถว</button>
  </div>

  <!-- Conditions -->
  <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 space-y-4">
    <h3 class="text-white font-semibold text-base mb-4">เงื่อนไข</h3>
    <p class="text-xs text-gray-500 -mt-2">ใส่ 1 เงื่อนไขต่อบรรทัด</p>
    <textarea name="conditions" rows="4"
      class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"
      placeholder="อายุ 16-65 ปี&#10;ไม่ต้องตรวจสุขภาพ"><?= htmlspecialchars(implode("\n", array_column($conditions, 'condition_text'))) ?></textarea>
  </div>

  <!-- FAQs -->
  <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 space-y-4">
    <h3 class="text-white font-semibold text-base mb-4">คำถามที่พบบ่อย (FAQs)</h3>
    <div id="faqsContainer">
      <?php if (!empty($faqs)): ?>
        <?php foreach ($faqs as $i => $faq): ?>
        <div class="faq-row bg-gray-800/50 rounded-lg p-4 mb-3 space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-xs text-gray-500 font-medium">FAQ #<?= $i + 1 ?></span>
            <button type="button" onclick="this.closest('.faq-row').remove()" class="text-red-400 hover:text-red-300 text-xs">ลบ</button>
          </div>
          <div>
            <label class="block text-xs text-gray-400 mb-1">คำถาม</label>
            <textarea name="faq_question[]" rows="2"
              class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"><?= htmlspecialchars($faq['question']) ?></textarea>
          </div>
          <div>
            <label class="block text-xs text-gray-400 mb-1">คำตอบ</label>
            <textarea name="faq_answer[]" rows="3"
              class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"><?= htmlspecialchars($faq['answer']) ?></textarea>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <button type="button" onclick="addFaqRow()"
      class="border border-dashed border-gray-700 text-gray-400 hover:text-white hover:border-gray-500 w-full py-2 rounded-lg text-sm transition">+ เพิ่มคำถาม</button>
  </div>

  <!-- Renewal -->
  <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 space-y-4">
    <h3 class="text-white font-semibold text-base mb-4">การต่ออายุ</h3>
    <p class="text-xs text-gray-500 -mt-2">ใส่ 1 รายการต่อบรรทัด</p>
    <textarea name="renewal" rows="4"
      class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"
      placeholder="ต่ออายุได้ทุกปี&#10;ไม่ต้องตรวจสุขภาพซ้ำ"><?= htmlspecialchars(implode("\n", array_column($renewal, 'renewal_text'))) ?></textarea>
  </div>

  <!-- Why -->
  <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 space-y-4">
    <h3 class="text-white font-semibold text-base mb-4">ทำไมต้องเลือกแผนนี้</h3>
    <p class="text-xs text-gray-500 -mt-2">ใส่ 1 เหตุผลต่อบรรทัด</p>
    <textarea name="why" rows="4"
      class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-4 py-2.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"
      placeholder="คุ้มครองครอบคลุม&#10;เบี้ยประกันต่ำ"><?= htmlspecialchars(implode("\n", array_column($why, 'why_text'))) ?></textarea>
  </div>

  <!-- Submit -->
  <div class="flex items-center gap-4 pt-2">
    <button type="submit" class="admin-btn-primary px-6 py-3">
      <?= $isEdit ? 'บันทึกการเปลี่ยนแปลง' : 'สร้างแผนใหม่' ?>
    </button>
    <a href="<?= ADMIN_URL ?>/plans.php" class="text-slate-500 hover:text-brand text-sm">ยกเลิก</a>
    <a href="<?= ADMIN_URL ?>/api/publish.php" onclick="return confirm('เผยแพร่เว็บไซต์ตอนนี้?')" class="admin-btn-outline text-sm ms-auto">บันทึกแล้วเผยแพร่</a>
  </div>
</form>

<?php
$extraScripts = <<<'SCRIPT'
<script>
let tierCounter = document.querySelectorAll('.tier-row').length;
let faqCounter = document.querySelectorAll('.faq-row').length;

function addTierRow() {
  const idx = document.querySelectorAll('.tier-row').length;
  const html = `<div class="tier-row grid grid-cols-12 gap-3 items-end mb-3">
    <div class="col-span-2"><input type="text" name="tier_id[]" class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none" placeholder="ID"></div>
    <div class="col-span-3"><input type="text" name="tier_label[]" class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none" placeholder="ชื่อแพ็กเกจ"></div>
    <div class="col-span-2"><input type="text" name="tier_amount[]" class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none" placeholder="1,000,000"></div>
    <div class="col-span-2"><input type="text" name="tier_unit[]" class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none" placeholder="บาท"></div>
    <div class="col-span-2 flex items-center gap-2"><input type="checkbox" name="tier_popular[]" value="${idx}" class="w-4 h-4 rounded border-gray-600 bg-gray-700 text-brand focus:ring-brand/30"></div>
    <div class="col-span-1"><button type="button" onclick="this.closest('.tier-row').remove()" class="text-red-400 hover:text-red-300 p-1"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></div>
  </div>`;
  document.getElementById('tiersContainer').insertAdjacentHTML('beforeend', html);
}

function addCsRow() {
  const html = `<div class="cs-row grid grid-cols-12 gap-3 items-end mb-3">
    <div class="col-span-5"><input type="text" name="cs_label[]" class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none" placeholder="ค่ารักษาพยาบาล"></div>
    <div class="col-span-3"><input type="text" name="cs_value[]" class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none" placeholder="1,000,000"></div>
    <div class="col-span-3"><input type="text" name="cs_unit[]" class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none" placeholder="บาท/ครั้ง"></div>
    <div class="col-span-1"><button type="button" onclick="this.closest('.cs-row').remove()" class="text-red-400 hover:text-red-300 p-1"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></div>
  </div>`;
  document.getElementById('csSummaryContainer').insertAdjacentHTML('beforeend', html);
}

function addCrRow() {
  const html = `<div class="cr-row grid grid-cols-12 gap-3 items-end mb-3">
    <div class="col-span-4"><input type="text" name="cr_label[]" class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none" placeholder="ค่ารักษา IPD"></div>
    <div class="col-span-7"><input type="text" name="cr_values[]" class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none" placeholder="100,000, 300,000, 500,000"></div>
    <div class="col-span-1"><button type="button" onclick="this.closest('.cr-row').remove()" class="text-red-400 hover:text-red-300 p-1"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></div>
  </div>`;
  document.getElementById('crRowsContainer').insertAdjacentHTML('beforeend', html);
}

function addFaqRow() {
  const num = document.querySelectorAll('.faq-row').length + 1;
  const html = `<div class="faq-row bg-gray-800/50 rounded-lg p-4 mb-3 space-y-3">
    <div class="flex items-center justify-between">
      <span class="text-xs text-gray-500 font-medium">FAQ #${num}</span>
      <button type="button" onclick="this.closest('.faq-row').remove()" class="text-red-400 hover:text-red-300 text-xs">ลบ</button>
    </div>
    <div>
      <label class="block text-xs text-gray-400 mb-1">คำถาม</label>
      <textarea name="faq_question[]" rows="2" class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"></textarea>
    </div>
    <div>
      <label class="block text-xs text-gray-400 mb-1">คำตอบ</label>
      <textarea name="faq_answer[]" rows="3" class="w-full rounded-lg border border-gray-700 bg-gray-800 text-white px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 outline-none"></textarea>
    </div>
  </div>`;
  document.getElementById('faqsContainer').insertAdjacentHTML('beforeend', html);
}
</script>
SCRIPT;
require_once __DIR__ . '/includes/footer.php';
?>
