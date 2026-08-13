<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/hospitals-lib.php';

$id = trim((string) ($_GET['id'] ?? ''));
$existing = $id !== '' ? hospitalFindById($id) : null;
$isEdit = $existing !== null;
$pageTitle = $isEdit ? 'แก้ไขสถานพยาบาล' : 'เพิ่มสถานพยาบาล';

$message = '';
$messageType = '';
$form = $existing ?? [
    'id' => '',
    'name' => '',
    'province' => 'กรุงเทพมหานคร',
    'facilities' => [],
    'streetNumber' => '',
    'road' => '',
    'area' => '',
    'district' => '',
    'county' => '',
    'postalCode' => '',
    'latitude' => '',
    'longitude' => '',
    'telephone' => '',
    'mobile' => '',
    'fax' => '',
];

$supportIndividual = $existing ? hospitalSupportsIndividual($existing) : true;
$supportGroup = $existing ? hospitalSupportsGroup($existing) : true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $payload = $_POST;
        $payload['id'] = $isEdit ? ($existing['id'] ?? '') : '';
        $payload['facilities'] = $_POST['facilities'] ?? [];
        if (!empty($_POST['facilities_extra'])) {
            $payload['facilities'] = array_merge(
                (array) $payload['facilities'],
                preg_split('/[,،\n]+/u', (string) $_POST['facilities_extra']) ?: []
            );
        }
        $record = hospitalBuildRecord($payload, $existing);
        hospitalUpsert($record);
        $_SESSION['flash'] = $isEdit ? 'บันทึกการแก้ไขเรียบร้อยแล้ว' : 'เพิ่มสถานพยาบาลเรียบร้อยแล้ว';
        header('Location: ' . ADMIN_URL . '/hospitals.php?q=' . urlencode($record['name']));
        exit;
    } catch (Throwable $e) {
        $message = $e->getMessage();
        $messageType = 'error';
        $form = array_merge($form, [
            'name' => $_POST['name'] ?? '',
            'province' => $_POST['province'] ?? '',
            'facilities' => hospitalNormalizeFacilities($_POST['facilities'] ?? []),
            'streetNumber' => $_POST['streetNumber'] ?? '',
            'road' => $_POST['road'] ?? '',
            'area' => $_POST['area'] ?? '',
            'district' => $_POST['district'] ?? '',
            'county' => $_POST['county'] ?? '',
            'postalCode' => $_POST['postalCode'] ?? '',
            'latitude' => $_POST['latitude'] ?? '',
            'longitude' => $_POST['longitude'] ?? '',
            'telephone' => $_POST['telephone'] ?? '',
            'mobile' => $_POST['mobile'] ?? '',
            'fax' => $_POST['fax'] ?? '',
        ]);
        if (!empty($_POST['facilities_extra'])) {
            $form['facilities'] = array_merge(
                $form['facilities'],
                hospitalNormalizeFacilities($_POST['facilities_extra'])
            );
        }
        $supportIndividual = !empty($_POST['support_individual']);
        $supportGroup = !empty($_POST['support_group']);
    }
}

$all = hospitalAll();
$provinces = hospitalProvinces($all);
$facilityOptions = hospitalFacilityOptions($all);
$selectedFacilities = $form['facilities'] ?? [];

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
<?= adminAlert($message, $messageType) ?>
<?php endif; ?>

<div class="mb-6">
  <a href="<?= ADMIN_URL ?>/hospitals.php" class="text-sm text-slate-500 hover:text-brand">← กลับรายการโรงพยาบาล</a>
</div>

<form method="POST" class="space-y-6" data-feedback-id="hospital-edit-form">
  <div class="admin-card p-6 border-brand/20">
    <h3 class="text-base font-semibold text-slate-800 mb-1">วางลิงก์ Google Maps (อัตโนมัติ)</h3>
    <p class="text-sm text-slate-500 mb-4">วางลิงก์หรือพิกัดจาก Google Maps แล้วกด “ดึงข้อมูล” — ระบบจะเติมพิกัดและพยายามอ่านชื่อสถานที่ให้</p>
    <div class="flex flex-col sm:flex-row gap-2">
      <input type="text" id="gmapsPaste" class="admin-input flex-1" placeholder="เช่น https://maps.google.com/... หรือ 13.7563, 100.5018">
      <button type="button" id="gmapsFillBtn" class="admin-btn-primary whitespace-nowrap">ดึงข้อมูล</button>
    </div>
    <p id="gmapsHint" class="text-xs text-slate-400 mt-2"></p>
  </div>

  <div class="admin-card p-6">
    <h3 class="text-base font-semibold text-slate-800 mb-4">ข้อมูลหลัก</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div class="sm:col-span-2">
        <label class="admin-label" for="name">ชื่อสถานพยาบาล <span class="text-red-500">*</span></label>
        <input class="admin-input" type="text" id="name" name="name" required value="<?= htmlspecialchars((string) ($form['name'] ?? '')) ?>">
      </div>
      <div>
        <label class="admin-label" for="province">จังหวัด <span class="text-red-500">*</span></label>
        <input class="admin-input" type="text" id="province" name="province" list="province-list" required value="<?= htmlspecialchars((string) ($form['province'] ?? '')) ?>" placeholder="เช่น กรุงเทพมหานคร">
        <datalist id="province-list">
          <?php foreach ($provinces as $p): ?>
          <option value="<?= htmlspecialchars($p) ?>">
          <?php endforeach; ?>
        </datalist>
      </div>
      <div>
        <label class="admin-label" for="telephone">เบอร์โทร</label>
        <input class="admin-input" type="text" id="telephone" name="telephone" value="<?= htmlspecialchars((string) ($form['telephone'] ?? '')) ?>">
      </div>
      <div>
        <label class="admin-label" for="mobile">มือถือ</label>
        <input class="admin-input" type="text" id="mobile" name="mobile" value="<?= htmlspecialchars((string) ($form['mobile'] ?? '')) ?>">
      </div>
      <div>
        <label class="admin-label" for="fax">แฟกซ์</label>
        <input class="admin-input" type="text" id="fax" name="fax" value="<?= htmlspecialchars((string) ($form['fax'] ?? '')) ?>">
      </div>
    </div>
  </div>

  <div class="admin-card p-6">
    <h3 class="text-base font-semibold text-slate-800 mb-4">ที่อยู่</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div class="sm:col-span-2">
        <label class="admin-label" for="streetNumber">เลขที่ / อาคาร / ชั้น</label>
        <input class="admin-input" type="text" id="streetNumber" name="streetNumber" value="<?= htmlspecialchars((string) ($form['streetNumber'] ?? '')) ?>">
      </div>
      <div>
        <label class="admin-label" for="road">ถนน</label>
        <input class="admin-input" type="text" id="road" name="road" value="<?= htmlspecialchars((string) ($form['road'] ?? '')) ?>">
      </div>
      <div>
        <label class="admin-label" for="county">แขวง / ตำบล</label>
        <input class="admin-input" type="text" id="county" name="county" value="<?= htmlspecialchars((string) ($form['county'] ?? '')) ?>">
      </div>
      <div>
        <label class="admin-label" for="district">เขต / อำเภอ</label>
        <input class="admin-input" type="text" id="district" name="district" value="<?= htmlspecialchars((string) ($form['district'] ?? '')) ?>">
      </div>
      <div>
        <label class="admin-label" for="postalCode">รหัสไปรษณีย์</label>
        <input class="admin-input" type="text" id="postalCode" name="postalCode" value="<?= htmlspecialchars((string) ($form['postalCode'] ?? '')) ?>">
      </div>
      <div>
        <label class="admin-label" for="area">พื้นที่ (area)</label>
        <input class="admin-input" type="text" id="area" name="area" value="<?= htmlspecialchars((string) ($form['area'] ?? '')) ?>" placeholder="มักใส่ชื่อจังหวัด">
      </div>
    </div>
  </div>

  <div class="admin-card p-6">
    <h3 class="text-base font-semibold text-slate-800 mb-1">พิกัดแผนที่</h3>
    <p class="text-sm text-slate-500 mb-4">จำเป็นสำหรับแสดงหมุดบนแผนที่ — คัดลอกจาก Google Maps ได้ (คลิกขวาที่จุด → พิกัด)</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="admin-label" for="latitude">Latitude <span class="text-red-500">*</span></label>
        <input class="admin-input" type="text" id="latitude" name="latitude" required value="<?= htmlspecialchars((string) ($form['latitude'] ?? '')) ?>" placeholder="13.7563">
      </div>
      <div>
        <label class="admin-label" for="longitude">Longitude <span class="text-red-500">*</span></label>
        <input class="admin-input" type="text" id="longitude" name="longitude" required value="<?= htmlspecialchars((string) ($form['longitude'] ?? '')) ?>" placeholder="100.5018">
      </div>
    </div>
  </div>

  <div class="admin-card p-6">
    <h3 class="text-base font-semibold text-slate-800 mb-4">ประเภท & แผนประกัน</h3>
    <div class="flex flex-wrap gap-4 mb-4">
      <label class="inline-flex items-center gap-2 text-sm text-slate-700">
        <input type="checkbox" name="support_individual" value="1" <?= $supportIndividual ? 'checked' : '' ?>>
        รองรับแผนรายบุคคล
      </label>
      <label class="inline-flex items-center gap-2 text-sm text-slate-700">
        <input type="checkbox" name="support_group" value="1" <?= $supportGroup ? 'checked' : '' ?>>
        รองรับแผนกลุ่ม
      </label>
    </div>
    <p class="admin-label mb-2">ประเภทสถานพยาบาล</p>
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-4">
      <?php foreach ($facilityOptions as $opt): ?>
      <label class="inline-flex items-center gap-2 text-sm text-slate-700 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
        <input type="checkbox" name="facilities[]" value="<?= htmlspecialchars($opt) ?>" <?= in_array($opt, $selectedFacilities, true) ? 'checked' : '' ?>>
        <?= htmlspecialchars($opt) ?>
      </label>
      <?php endforeach; ?>
    </div>
    <label class="admin-label" for="facilities_extra">เพิ่มประเภทอื่น (คั่นด้วยจุลภาค)</label>
    <input class="admin-input" type="text" id="facilities_extra" name="facilities_extra" placeholder="เช่น ศูนย์กายภาพ, ห้องแล็บ">
  </div>

  <div class="flex items-center gap-4">
    <button type="submit" class="admin-btn-primary px-6 py-3">
      <?= $isEdit ? 'บันทึกการเปลี่ยนแปลง' : 'เพิ่มสถานพยาบาล' ?>
    </button>
    <a href="<?= ADMIN_URL ?>/hospitals.php" class="text-slate-500 hover:text-brand text-sm">ยกเลิก</a>
    <?php if ($isEdit && !empty($form['latitude']) && !empty($form['longitude'])): ?>
    <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($form['latitude'] . ',' . $form['longitude']) ?>" target="_blank" class="admin-btn-outline text-sm ms-auto">ดูบน Google Maps ↗</a>
    <?php endif; ?>
  </div>
</form>

<script>
(function () {
  var paste = document.getElementById('gmapsPaste');
  var btn = document.getElementById('gmapsFillBtn');
  var hint = document.getElementById('gmapsHint');
  if (!paste || !btn) return;

  function setVal(id, value) {
    var el = document.getElementById(id);
    if (el && value) el.value = value;
  }

  function parsePaste(raw) {
    raw = String(raw || '').trim();
    var result = { lat: '', lng: '', name: '', address: '' };
    if (!raw) return result;

    // Plain "lat, lng"
    var pair = raw.match(/(-?\d{1,2}\.\d+)\s*,\s*(-?\d{1,3}\.\d+)/);
    if (pair && !/maps\.|goo\.gl|google/i.test(raw)) {
      result.lat = pair[1];
      result.lng = pair[2];
      return result;
    }

    // Google Maps URL patterns
    var at = raw.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
    if (at) {
      result.lat = at[1];
      result.lng = at[2];
    }
    if (!result.lat) {
      var qll = raw.match(/[?&](?:q|query)=(-?\d+\.\d+),(-?\d+\.\d+)/i);
      if (qll) {
        result.lat = qll[1];
        result.lng = qll[2];
      }
    }
    if (!result.lat) {
      var d3 = raw.match(/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/);
      if (d3) {
        result.lat = d3[1];
        result.lng = d3[2];
      }
    }
    if (!result.lat && pair) {
      result.lat = pair[1];
      result.lng = pair[2];
    }

    // Place name from /place/Name/
    var place = raw.match(/\/place\/([^\/?#]+)/);
    if (place) {
      try {
        result.name = decodeURIComponent(place[1].replace(/\+/g, ' ')).replace(/,/g, ' ').trim();
      } catch (e) {
        result.name = place[1].replace(/\+/g, ' ');
      }
    }

    // Query text
    if (!result.name) {
      var qText = raw.match(/[?&](?:q|query)=([^&]+)/i);
      if (qText && !/^-?\d+\.\d+,-?\d+\.\d+$/.test(decodeURIComponent(qText[1]))) {
        try {
          result.address = decodeURIComponent(qText[1].replace(/\+/g, ' '));
        } catch (e) {
          result.address = qText[1];
        }
      }
    }

    return result;
  }

  function apply() {
    var data = parsePaste(paste.value);
    if (!data.lat || !data.lng) {
      hint.textContent = 'ไม่พบพิกัดในลิงก์ — ลองวางลิงก์ที่มี @lat,lng หรือพิกัดแบบ 13.75, 100.50';
      hint.className = 'text-xs text-amber-600 mt-2';
      return;
    }
    setVal('latitude', data.lat);
    setVal('longitude', data.lng);
    if (data.name) {
      var nameEl = document.getElementById('name');
      if (nameEl && !nameEl.value.trim()) nameEl.value = data.name;
    }
    if (data.address) {
      var street = document.getElementById('streetNumber');
      if (street && !street.value.trim()) street.value = data.address;
    }
    hint.textContent = 'เติมพิกัดแล้ว: ' + data.lat + ', ' + data.lng + (data.name ? ' · ' + data.name : '');
    hint.className = 'text-xs text-emerald-600 mt-2';
  }

  btn.addEventListener('click', apply);
  paste.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      apply();
    }
  });
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
