<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/ui.php';
require_once __DIR__ . '/includes/upload.php';

$pageSlug = $_GET['page'] ?? '';
$definitions = getPageDefinitions();
if (!isset($definitions[$pageSlug])) {
    header('Location: ' . ADMIN_URL . '/pages.php');
    exit;
}

$pageDef = $definitions[$pageSlug];
$pageTitle = 'แก้ไขแบบเห็นจริง: ' . $pageDef['label'];
$message = '';
$messageType = '';
$defaults = getDefaultSectionValues($pageSlug);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($pageDef['sections'] as $section) {
            $key = $section['key'];
            $value = $_POST['sections'][$key] ?? '';
            if ($section['type'] === 'image') {
                $uploaded = handleImageUpload($_FILES['section_file_' . $key] ?? null, 'pages');
                if ($uploaded) {
                    $value = $uploaded;
                } elseif ($value === '' && !empty($_POST['sections_keep'][$key])) {
                    $value = $_POST['sections_keep'][$key];
                }
            }
            saveSectionValue($pageSlug, $key, $value, $section['label'], $section['type']);
        }
        $message = 'บันทึกเรียบร้อย — กดเผยแพร่เพื่ออัปเดตหน้าบ้าน';
        $messageType = 'success';
    } catch (Throwable $e) {
        $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        $messageType = 'error';
    }
}

$values = [];
$labels = [];
$types = [];
foreach ($pageDef['sections'] as $section) {
    $values[$section['key']] = getSectionValue(
        $pageSlug,
        $section['key'],
        $defaults[$section['key']] ?? ''
    );
    $labels[$section['key']] = strip_tags($section['label']);
    $types[$section['key']] = $section['type'];
}

$previewUrl = $pageDef['url'];
$previewUrl .= (strpos($previewUrl, '?') === false ? '?' : '&') . 'cms_preview=1&t=' . time();

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
<?= adminAlert($message, $messageType === 'error' ? 'error' : 'success') ?>
<?php endif; ?>

<div class="admin-visual-toolbar">
  <div>
    <a href="<?= ADMIN_URL ?>/pages.php" class="text-sm text-slate-500 hover:text-brand">← กลับรายการหน้า</a>
    <h3 class="text-lg font-semibold text-slate-800 mt-1"><?= htmlspecialchars($pageDef['label']) ?></h3>
    <p class="text-sm text-slate-500">พรีวิวเหมือนหน้าบ้าน — คลิกข้อความที่มีกรอบบนพรีวิวเพื่อแก้ไข</p>
  </div>
  <div class="flex flex-wrap gap-2">
    <button type="button" id="visualRefreshBtn" class="admin-btn-outline text-sm">รีเฟรชพรีวิว</button>
    <a href="<?= htmlspecialchars($pageDef['url']) ?>" target="_blank" class="admin-btn-outline text-sm">ดูหน้าจริง ↗</a>
    <button type="submit" form="visualEditForm" class="admin-btn-primary text-sm">บันทึก</button>
    <a href="<?= ADMIN_URL ?>/api/publish.php" onclick="return confirm('เผยแพร่เว็บไซต์ตอนนี้?')" class="admin-btn-outline text-sm border-emerald-200 bg-emerald-50 text-emerald-700">เผยแพร่</a>
  </div>
</div>

<form id="visualEditForm" method="POST" enctype="multipart/form-data" class="admin-visual-layout">
  <div class="admin-visual-preview-card">
    <div class="admin-visual-preview-bar">
      <span class="admin-visual-badge">คลิกเพื่อแก้ไข</span>
      <span class="text-xs text-slate-500 hidden sm:inline">ชี้เมาส์ที่ข้อความ — จะมีกรอบสีน้ำเงิน</span>
      <div class="admin-visual-zoom ms-auto" role="group" aria-label="ขนาดพรีวิว">
        <button type="button" class="admin-visual-zoom-btn" data-zoom-delta="-10" title="ย่อ">−</button>
        <select id="visualZoomSelect" class="admin-visual-zoom-select" aria-label="ซูมพรีวิว">
          <option value="40">40%</option>
          <option value="50">50%</option>
          <option value="60">60%</option>
          <option value="75">75%</option>
          <option value="90">90%</option>
          <option value="100" selected>100%</option>
          <option value="125">125%</option>
          <option value="150">150%</option>
        </select>
        <button type="button" class="admin-visual-zoom-btn" data-zoom-delta="10" title="ขยาย">+</button>
        <button type="button" class="admin-visual-zoom-fit" id="visualZoomFit" title="พอดีหน้าจอ">พอดี</button>
      </div>
    </div>
    <div class="admin-visual-viewport" id="visualViewport">
      <div class="admin-visual-sizer" id="visualSizer">
        <div class="admin-visual-scale" id="visualScaleWrap">
          <iframe
            id="visualPreview"
            title="Page preview"
            src="<?= htmlspecialchars($previewUrl) ?>"
            class="admin-visual-iframe"
          ></iframe>
        </div>
      </div>
    </div>
  </div>

  <aside class="admin-visual-panel space-y-4">
    <div class="admin-card p-4 space-y-3">
      <h4 class="font-semibold text-slate-800">แผงแก้ไข</h4>
      <p id="visualSelectedLabel" class="text-sm text-slate-500">คลิกข้อความหรือปุ่มบนพรีวิวเพื่อแก้ไขที่นี่</p>

      <div id="visualEditorWrap" class="hidden space-y-2">
        <label class="admin-label" for="visualLiveInput">ค่าที่เลือก</label>
        <textarea id="visualLiveInput" class="admin-input" rows="5"></textarea>
        <p class="text-xs text-slate-400">คีย์: <code id="visualSelectedKey"></code></p>
      </div>

      <div id="visualBlockEditor" class="hidden space-y-3">
        <div id="visualBlockFields" class="space-y-3"></div>
        <button type="button" id="visualBlockSaveBtn" class="admin-btn-primary w-full text-sm">บันทึกรายการนี้</button>
        <p id="visualBlockStatus" class="text-xs text-slate-400"></p>
      </div>
    </div>

    <div class="admin-card p-4 space-y-3">
      <h4 class="font-semibold text-slate-800">ฟิลด์ข้อความหน้านี้</h4>
      <?php foreach ($pageDef['sections'] as $section):
        $key = $section['key'];
        $type = $section['type'];
        $isImage = $type === 'image';
        $isMulti = in_array($type, ['textarea', 'html'], true);
      ?>
      <div class="admin-visual-field" data-field-key="<?= htmlspecialchars($key) ?>">
        <button type="button" class="admin-visual-field-btn" data-select-key="<?= htmlspecialchars($key) ?>" <?= $isImage ? 'disabled' : '' ?>>
          <span class="font-medium"><?= htmlspecialchars(strip_tags($section['label'])) ?></span>
          <span class="admin-visual-field-preview"><?= $isImage ? 'รูปภาพ' : htmlspecialchars(mb_strimwidth(strip_tags($values[$key]), 0, 40, '…')) ?></span>
        </button>

        <?php if ($isImage): ?>
          <div class="mt-2">
            <input type="hidden" name="sections_keep[<?= htmlspecialchars($key) ?>]" value="<?= htmlspecialchars($values[$key]) ?>">
            <?php renderImageField('sections[' . $key . ']', $values[$key], $section['label'], 'section_file_' . $key); ?>
          </div>
        <?php elseif ($isMulti): ?>
          <textarea
            id="sec_<?= htmlspecialchars($key) ?>"
            name="sections[<?= htmlspecialchars($key) ?>]"
            rows="<?= $type === 'html' ? 5 : 4 ?>"
            class="admin-input mt-2 visual-field-input"
            data-key="<?= htmlspecialchars($key) ?>"
            data-multiline="1"
          ><?= htmlspecialchars($values[$key]) ?></textarea>
        <?php else: ?>
          <input
            type="text"
            id="sec_<?= htmlspecialchars($key) ?>"
            name="sections[<?= htmlspecialchars($key) ?>]"
            value="<?= htmlspecialchars($values[$key]) ?>"
            class="admin-input mt-2 visual-field-input"
            data-key="<?= htmlspecialchars($key) ?>"
          >
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>

    <div className="flex gap-2 pt-1">
      <button type="submit" class="admin-btn-primary flex-1">บันทึก</button>
    </div>
  </aside>
</form>

<script>
(function () {
  const pageSlug = <?= json_encode($pageSlug, JSON_UNESCAPED_UNICODE) ?>;
  const labels = <?= json_encode($labels, JSON_UNESCAPED_UNICODE) ?>;
  const types = <?= json_encode($types, JSON_UNESCAPED_UNICODE) ?>;
  const draftFields = <?= json_encode($values, JSON_UNESCAPED_UNICODE) ?>;
  const iframe = document.getElementById('visualPreview');
  const liveInput = document.getElementById('visualLiveInput');
  const editorWrap = document.getElementById('visualEditorWrap');
  const blockEditor = document.getElementById('visualBlockEditor');
  const blockFields = document.getElementById('visualBlockFields');
  const blockStatus = document.getElementById('visualBlockStatus');
  const selectedLabel = document.getElementById('visualSelectedLabel');
  const selectedKeyEl = document.getElementById('visualSelectedKey');
  const viewport = document.getElementById('visualViewport');
  const scaleWrap = document.getElementById('visualScaleWrap');
  const sizer = document.getElementById('visualSizer');
  const zoomSelect = document.getElementById('visualZoomSelect');
  const blockSaveUrl = <?= json_encode(ADMIN_URL . '/api/cms-block-save.php') ?>;
  let selectedKey = null;
  let selectedBlock = null;
  let zoomPercent = Number(localStorage.getItem('admin_visual_zoom') || 75);
  let contentHeight = 2400;

  function measureIframeHeight() {
    try {
      const doc = iframe.contentDocument || iframe.contentWindow?.document;
      if (!doc) return contentHeight;
      const h = Math.max(
        doc.body?.scrollHeight || 0,
        doc.documentElement?.scrollHeight || 0,
        doc.body?.offsetHeight || 0,
        1200,
      );
      contentHeight = h + 24;
      return contentHeight;
    } catch {
      return contentHeight;
    }
  }

  function applyZoom(percent) {
    zoomPercent = Math.max(30, Math.min(200, Math.round(percent)));
    const scale = zoomPercent / 100;
    localStorage.setItem('admin_visual_zoom', String(zoomPercent));
    if (![...zoomSelect.options].some((o) => Number(o.value) === zoomPercent)) {
      const opt = document.createElement('option');
      opt.value = String(zoomPercent);
      opt.textContent = zoomPercent + '%';
      zoomSelect.appendChild(opt);
    }
    zoomSelect.value = String(zoomPercent);

    const vw = viewport.clientWidth || 900;
    const pageWidth = Math.max(vw / scale, 1100);
    const pageHeight = measureIframeHeight();

    scaleWrap.style.width = pageWidth + 'px';
    scaleWrap.style.height = pageHeight + 'px';
    scaleWrap.style.transform = 'scale(' + scale + ')';

    sizer.style.width = Math.ceil(pageWidth * scale) + 'px';
    sizer.style.height = Math.ceil(pageHeight * scale) + 'px';

    iframe.style.width = '100%';
    iframe.style.height = pageHeight + 'px';
  }

  function fitZoom() {
    // show roughly full desktop width feel at ~50-60% depending on panel
    applyZoom(viewport.clientWidth < 700 ? 50 : 60);
  }

  zoomSelect.addEventListener('change', () => applyZoom(Number(zoomSelect.value)));
  document.querySelectorAll('[data-zoom-delta]').forEach((btn) => {
    btn.addEventListener('click', () => applyZoom(zoomPercent + Number(btn.dataset.zoomDelta)));
  });
  document.getElementById('visualZoomFit').addEventListener('click', fitZoom);
  window.addEventListener('resize', () => applyZoom(zoomPercent));
  // default slightly zoomed out so layout is easier to see
  if (!localStorage.getItem('admin_visual_zoom')) zoomPercent = 75;
  applyZoom(zoomPercent);

  function fieldInput(key) {
    return document.querySelector('.visual-field-input[data-key="' + key + '"]');
  }

  function pushUpdate(key, value) {
    if (!iframe.contentWindow) return;
    iframe.contentWindow.postMessage({
      source: 'agent-cms-parent',
      type: 'update-page',
      page: pageSlug,
      key: key,
      value: value,
    }, '*');
  }

  function hydrate() {
    if (!iframe.contentWindow) return;
    const fields = {};
    Object.keys(draftFields).forEach((key) => {
      const input = fieldInput(key);
      fields[key] = input ? input.value : draftFields[key];
    });
    iframe.contentWindow.postMessage({
      source: 'agent-cms-parent',
      type: 'hydrate',
      fields: fields,
    }, '*');
  }

  function selectKey(key, valueFromPreview) {
    if (!key || types[key] === 'image') return;
    selectedKey = key;
    selectedBlock = null;
    blockEditor.classList.add('hidden');
    const input = fieldInput(key);
    const value = valueFromPreview !== undefined ? valueFromPreview : (input ? input.value : '');
    if (input && valueFromPreview !== undefined) {
      input.value = valueFromPreview;
    }
    editorWrap.classList.remove('hidden');
    selectedLabel.textContent = labels[key] || key;
    selectedKeyEl.textContent = key;
    liveInput.value = value;
    liveInput.rows = (types[key] === 'html' || types[key] === 'textarea' || value.length > 60) ? 6 : 3;

    document.querySelectorAll('.admin-visual-field').forEach((el) => {
      el.classList.toggle('is-active', el.dataset.fieldKey === key);
    });

    if (iframe.contentWindow) {
      iframe.contentWindow.postMessage({
        source: 'agent-cms-parent',
        type: 'highlight',
        key: key,
      }, '*');
    }
  }

  function fieldRow(name, label, value) {
    const wrap = document.createElement('div');
    wrap.className = 'space-y-1';
    const lab = document.createElement('label');
    lab.className = 'admin-label';
    lab.textContent = label;
    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'admin-input visual-block-input';
    input.dataset.name = name;
    input.value = value || '';
    wrap.appendChild(lab);
    wrap.appendChild(input);
    return wrap;
  }

  function selectBlock(payload) {
    selectedKey = null;
    selectedBlock = payload;
    editorWrap.classList.add('hidden');
    blockEditor.classList.remove('hidden');
    blockStatus.textContent = '';
    selectedLabel.textContent = payload.label || payload.block || payload.kind || 'รายการ';
    document.querySelectorAll('.admin-visual-field').forEach((el) => el.classList.remove('is-active'));

    blockFields.innerHTML = '';
    if (payload.kind === 'dom') {
      blockFields.appendChild(fieldRow('value', 'ข้อความ / HTML', payload.value || payload.text || ''));
      if (payload.href != null || (payload.item && payload.item.href != null) || (payload.selector && document.querySelector(payload.selector)?.tagName === 'A')) {
        blockFields.appendChild(fieldRow('href', 'ลิงก์', payload.href || payload.item?.href || ''));
      }
    } else if (payload.kind === 'faq' && payload.item) {
      blockFields.appendChild(fieldRow('q', 'คำถาม', payload.item.q || ''));
      const aWrap = document.createElement('div');
      aWrap.className = 'space-y-1';
      const lab = document.createElement('label');
      lab.className = 'admin-label';
      lab.textContent = 'คำตอบ';
      const ta = document.createElement('textarea');
      ta.className = 'admin-input visual-block-input';
      ta.dataset.name = 'a';
      ta.rows = 4;
      ta.value = payload.item.a || '';
      aWrap.appendChild(lab);
      aWrap.appendChild(ta);
      blockFields.appendChild(aWrap);
    } else if (payload.field && payload.item == null && payload.value !== undefined) {
      blockFields.appendChild(fieldRow('value', 'ข้อความ', payload.value));
    } else if (payload.item && typeof payload.item === 'object') {
      Object.keys(payload.item).forEach((name) => {
        const labelsMap = { text: 'ข้อความ', href: 'ลิงก์', icon: 'ไอคอน (Lucide)', title: 'หัวข้อ', label: 'ป้ายปุ่ม', value: 'รายละเอียด', q: 'คำถาม', a: 'คำตอบ' };
        blockFields.appendChild(fieldRow(name, labelsMap[name] || name, payload.item[name]));
      });
    }

    blockFields.querySelectorAll('.visual-block-input').forEach((input) => {
      input.addEventListener('input', () => {
        pushBlockLive();
      });
    });
  }

  function readBlockForm() {
    if (!selectedBlock) return null;
    const payload = {
      kind: selectedBlock.kind || 'block',
      block: selectedBlock.block,
      field: selectedBlock.field,
      itemIndex: selectedBlock.itemIndex,
      page: selectedBlock.page || pageSlug,
      selector: selectedBlock.selector,
    };
    if (selectedBlock.kind === 'dom') {
      const valueInput = blockFields.querySelector('.visual-block-input[data-name="value"]');
      const hrefInput = blockFields.querySelector('.visual-block-input[data-name="href"]');
      payload.value = valueInput ? valueInput.value : '';
      payload.text = payload.value.replace(/<[^>]+>/g, '');
      if (hrefInput) payload.href = hrefInput.value;
      selectedBlock.value = payload.value;
      selectedBlock.href = payload.href;
      return payload;
    }
    if (selectedBlock.kind === 'faq') {
      payload.item = {};
      blockFields.querySelectorAll('.visual-block-input').forEach((input) => {
        payload.item[input.dataset.name] = input.value;
      });
      selectedBlock.item = payload.item;
      return payload;
    }
    if (selectedBlock.item && typeof selectedBlock.item === 'object') {
      payload.item = { ...selectedBlock.item };
      blockFields.querySelectorAll('.visual-block-input').forEach((input) => {
        payload.item[input.dataset.name] = input.value;
      });
      selectedBlock.item = payload.item;
    } else {
      const valueInput = blockFields.querySelector('.visual-block-input[data-name="value"]');
      payload.value = valueInput ? valueInput.value : '';
      selectedBlock.value = payload.value;
    }
    return payload;
  }

  function pushBlockLive() {
    const payload = readBlockForm();
    if (!payload || !iframe.contentWindow) return;
    if (payload.kind === 'dom') {
      iframe.contentWindow.postMessage({
        source: 'agent-cms-parent',
        type: 'update-dom',
        selector: payload.selector,
        value: payload.value,
        text: payload.text,
        href: payload.href,
      }, '*');
      return;
    }
    if (payload.kind === 'faq') {
      iframe.contentWindow.postMessage({
        source: 'agent-cms-parent',
        type: 'update-faq',
        itemIndex: payload.itemIndex,
        item: payload.item,
      }, '*');
      return;
    }
    iframe.contentWindow.postMessage({
      source: 'agent-cms-parent',
      type: 'update-block-item',
      ...payload,
    }, '*');
  }

  async function saveBlock() {
    const payload = readBlockForm();
    if (!payload) return;
    blockStatus.textContent = 'กำลังบันทึก...';
    blockStatus.className = 'text-xs text-slate-400';
    try {
      const res = await fetch(blockSaveUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const data = await res.json();
      if (!data.ok) throw new Error(data.message || 'บันทึกไม่สำเร็จ');
      blockStatus.textContent = 'บันทึกแล้ว — กดเผยแพร่เพื่ออัปเดตหน้าบ้าน';
      blockStatus.className = 'text-xs text-emerald-600';
      pushBlockLive();
    } catch (err) {
      blockStatus.textContent = err.message || 'บันทึกไม่สำเร็จ';
      blockStatus.className = 'text-xs text-red-600';
    }
  }

  document.getElementById('visualBlockSaveBtn').addEventListener('click', () => void saveBlock());

  liveInput.addEventListener('input', () => {
    if (!selectedKey) return;
    const input = fieldInput(selectedKey);
    if (input) input.value = liveInput.value;
    pushUpdate(selectedKey, liveInput.value);
    const preview = document.querySelector('.admin-visual-field[data-field-key="' + selectedKey + '"] .admin-visual-field-preview');
    if (preview) {
      const t = liveInput.value.replace(/<[^>]+>/g, '');
      preview.textContent = t.length > 40 ? t.slice(0, 40) + '…' : (t || '—');
    }
  });

  document.querySelectorAll('.visual-field-input').forEach((input) => {
    input.addEventListener('input', () => {
      const key = input.dataset.key;
      pushUpdate(key, input.value);
      if (selectedKey === key) liveInput.value = input.value;
    });
  });

  document.querySelectorAll('[data-select-key]').forEach((btn) => {
    btn.addEventListener('click', () => selectKey(btn.dataset.selectKey));
  });

  window.addEventListener('message', (event) => {
    const data = event.data;
    if (!data || data.source !== 'agent-cms-preview') return;
    if (data.type === 'ready') {
      hydrate();
      return;
    }
    if (data.type === 'select-field') {
      if (data.kind === 'page') {
        if (data.page && data.page !== pageSlug) return;
        if (!labels[data.key]) return;
        selectKey(data.key, data.value);
        return;
      }
      if (data.kind === 'block' || data.kind === 'dom' || data.kind === 'faq') {
        selectBlock(data);
      }
    }
  });

  document.getElementById('visualRefreshBtn').addEventListener('click', () => {
    const url = new URL(iframe.src);
    url.searchParams.set('t', String(Date.now()));
    iframe.src = url.toString();
  });

  iframe.addEventListener('load', () => {
    setTimeout(hydrate, 200);
    setTimeout(() => {
      applyZoom(zoomPercent);
      if (iframe.contentWindow) {
        iframe.contentWindow.postMessage({ source: 'agent-cms-parent', type: 'remark' }, '*');
      }
    }, 400);
    setTimeout(() => applyZoom(zoomPercent), 1000);
    setTimeout(() => applyZoom(zoomPercent), 2000);
  });
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
