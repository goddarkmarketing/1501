/* Website Feedback Tool — pick any admin UI element */
(function () {
  'use strict';

  var cfg = window.__FEEDBACK_CONFIG__ || {};
  if (!cfg.enabled) return;

  var state = {
    active: false,
    hoverEl: null,
    selected: null,
    selectedRect: null,
    overlay: null,
    highlight: null,
    label: null,
    fab: null,
    modal: null,
  };

  var CATEGORIES = [
    { value: 'text', label: 'ข้อความ' },
    { value: 'image', label: 'รูปภาพ' },
    { value: 'layout', label: 'Layout' },
    { value: 'color', label: 'สี' },
    { value: 'function', label: 'ฟังก์ชัน' },
    { value: 'mobile', label: 'Mobile' },
    { value: 'other', label: 'อื่น ๆ' },
  ];

  var PRIORITIES = [
    { value: 'low', label: 'ต่ำ' },
    { value: 'medium', label: 'ปานกลาง' },
    { value: 'high', label: 'สูง' },
  ];

  function el(tag, cls, html) {
    var node = document.createElement(tag);
    if (cls) node.className = cls;
    if (html != null) node.innerHTML = html;
    return node;
  }

  function isFeedbackUi(node) {
    if (!node || node.nodeType !== 1) return true;
    return !!node.closest('.fb-tool-root, .fb-fab-wrap, .fb-modal-backdrop, .fb-toast, .fb-highlight, .fb-overlay, .fb-hover-label');
  }

  function resolveTarget(raw) {
    var node = raw;
    if (!node) return null;
    if (node.nodeType !== 1) node = node.parentElement;
    if (!node || isFeedbackUi(node)) return null;
    if (node === document.body || node === document.documentElement) return null;

    // Climb out of icon/svg internals so the highlight wraps a useful box
    while (node && node !== document.body) {
      var tag = node.tagName;
      if (tag === 'PATH' || tag === 'CIRCLE' || tag === 'LINE' || tag === 'POLYLINE' || tag === 'POLYGON' || tag === 'TSPAN') {
        node = node.parentElement;
        continue;
      }
      break;
    }
    if (!node || isFeedbackUi(node) || node === document.body) return null;

    // Prefer a reasonably sized box (avoid 1px wrappers)
    var cur = node;
    for (var i = 0; i < 4 && cur && cur !== document.body; i++) {
      if (isFeedbackUi(cur)) break;
      var r = cur.getBoundingClientRect();
      if (r.width >= 16 && r.height >= 12) {
        return cur;
      }
      cur = cur.parentElement;
    }
    return node;
  }

  function elementFromPointDeep(x, y) {
    var stack = [];
    var elFound = document.elementFromPoint(x, y);
    while (elFound && isFeedbackUi(elFound)) {
      stack.push(elFound);
      elFound.style.setProperty('pointer-events', 'none', 'important');
      elFound = document.elementFromPoint(x, y);
    }
    stack.forEach(function (n) {
      n.style.removeProperty('pointer-events');
    });
    return resolveTarget(elFound);
  }

  function getCssSelector(node) {
    if (!node || node.nodeType !== 1) return '';
    if (typeof CSS !== 'undefined' && CSS.escape && node.id) {
      return '#' + CSS.escape(node.id);
    }
    if (node.id) return '#' + node.id;
    var parts = [];
    var cur = node;
    while (cur && cur.nodeType === 1 && cur !== document.body) {
      var part = cur.tagName.toLowerCase();
      if (cur.id) {
        parts.unshift('#' + cur.id);
        break;
      }
      var fbId = cur.getAttribute('data-feedback-id');
      if (fbId) {
        parts.unshift('[data-feedback-id="' + fbId + '"]');
        break;
      }
      var parent = cur.parentElement;
      if (parent) {
        var siblings = Array.prototype.filter.call(parent.children, function (c) {
          return c.tagName === cur.tagName;
        });
        if (siblings.length > 1) {
          part += ':nth-of-type(' + (siblings.indexOf(cur) + 1) + ')';
        }
      }
      parts.unshift(part);
      cur = parent;
      if (parts.length >= 6) break;
    }
    return parts.join(' > ');
  }

  function getComponentName(node) {
    var cur = node;
    while (cur && cur !== document.body) {
      var fb = cur.getAttribute('data-feedback-id');
      if (fb) return fb;
      cur = cur.parentElement;
    }
    return '';
  }

  function getElementText(node) {
    var text = (node.innerText || node.textContent || '').replace(/\s+/g, ' ').trim();
    return text.length > 120 ? text.slice(0, 120) + '…' : text;
  }

  function getRoute() {
    var path = window.location.pathname.replace(/\\/g, '/');
    var base = (cfg.adminPath || '/admin').replace(/\/$/, '');
    var idx = path.indexOf(base);
    if (idx >= 0) {
      return path.slice(idx + base.length) || '/';
    }
    return path;
  }

  function maskSensitive(root) {
    root.querySelectorAll('input[type="password"], [data-sensitive]').forEach(function (input) {
      input.setAttribute('data-fb-masked', '1');
      input.style.webkitTextSecurity = 'disc';
      input.style.textSecurity = 'disc';
    });
    root.querySelectorAll('input[type="hidden"]').forEach(function (input) {
      input.style.visibility = 'hidden';
    });
  }

  function unmaskSensitive(root) {
    root.querySelectorAll('[data-fb-masked]').forEach(function (input) {
      input.removeAttribute('data-fb-masked');
      input.style.webkitTextSecurity = '';
      input.style.textSecurity = '';
    });
    root.querySelectorAll('input[type="hidden"]').forEach(function (input) {
      input.style.visibility = '';
    });
  }

  function positionHighlight(target) {
    if (!target || !state.highlight) return;
    var rect = target.getBoundingClientRect();
    if (rect.width < 1 && rect.height < 1) {
      hideHighlight();
      return;
    }
    state.highlight.style.display = 'block';
    state.highlight.style.top = Math.max(0, rect.top) + 'px';
    state.highlight.style.left = Math.max(0, rect.left) + 'px';
    state.highlight.style.width = rect.width + 'px';
    state.highlight.style.height = rect.height + 'px';

    if (state.label) {
      var name = target.getAttribute('data-feedback-id') || target.tagName.toLowerCase();
      state.label.textContent = name;
      state.label.style.display = 'block';
      var top = Math.max(0, rect.top - 22);
      var left = Math.max(0, rect.left);
      state.label.style.top = top + 'px';
      state.label.style.left = left + 'px';
    }
  }

  function hideHighlight() {
    if (state.highlight) state.highlight.style.display = 'none';
    if (state.label) state.label.style.display = 'none';
  }

  function buildUi() {
    var root = el('div', 'fb-tool-root');
    root.setAttribute('data-feedback-id', 'feedback-tool');

    state.fab = el('div', 'fb-fab-wrap');
    state.fab.innerHTML =
      '<button type="button" class="fb-fab-secondary" id="fb-general">แจ้งทั่วไป</button>' +
      '<button type="button" class="fb-fab" id="fb-fab-main" title="แจ้งแก้ไข">' +
        '<i data-lucide="message-square-plus"></i><span>แจ้งแก้ไข</span>' +
      '</button>';

    state.overlay = el('div', 'fb-overlay');
    state.highlight = el('div', 'fb-highlight');
    state.label = el('div', 'fb-hover-label');

    state.modal = el('div', 'fb-modal-backdrop');
    state.modal.innerHTML =
      '<div class="fb-modal" role="dialog" aria-modal="true" aria-labelledby="fb-modal-title">' +
        '<div class="fb-modal-header">' +
          '<h3 id="fb-modal-title">แจ้งแก้ไข</h3>' +
          '<button type="button" class="fb-modal-close" aria-label="ปิด">&times;</button>' +
        '</div>' +
        '<div class="fb-modal-body">' +
          '<div class="fb-selected-info" id="fb-selected-info"></div>' +
          '<label class="fb-label">รายละเอียดที่ต้องการแก้ไข <span class="fb-req">*</span></label>' +
          '<textarea class="fb-input" id="fb-comment" rows="4" placeholder="อธิบายสิ่งที่ต้องการแก้ไข"></textarea>' +
          '<div class="fb-grid">' +
            '<div><label class="fb-label">ประเภท</label><select class="fb-input" id="fb-category"></select></div>' +
            '<div><label class="fb-label">ความสำคัญ</label><select class="fb-input" id="fb-priority"></select></div>' +
          '</div>' +
          '<label class="fb-label">ชื่อลูกค้า</label>' +
          '<input class="fb-input" id="fb-client" type="text" placeholder="ชื่อผู้แจ้ง">' +
        '</div>' +
        '<div class="fb-modal-footer">' +
          '<button type="button" class="fb-btn fb-btn-ghost" id="fb-cancel">ยกเลิก</button>' +
          '<button type="button" class="fb-btn fb-btn-primary" id="fb-save">บันทึก</button>' +
        '</div>' +
      '</div>';

    document.body.appendChild(root);
    document.body.appendChild(state.fab);
    document.body.appendChild(state.overlay);
    document.body.appendChild(state.highlight);
    document.body.appendChild(state.label);
    document.body.appendChild(state.modal);

    var catSel = document.getElementById('fb-category');
    CATEGORIES.forEach(function (c) {
      catSel.appendChild(new Option(c.label, c.value));
    });
    var prioSel = document.getElementById('fb-priority');
    PRIORITIES.forEach(function (p) {
      prioSel.appendChild(new Option(p.label, p.value));
    });
    prioSel.value = 'medium';

    document.getElementById('fb-fab-main').addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      setPickMode(!state.active);
    });
    document.getElementById('fb-general').addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      setPickMode(false);
      openModal(null, true);
    });
    state.modal.querySelector('.fb-modal-close').addEventListener('click', closeModal);
    document.getElementById('fb-cancel').addEventListener('click', closeModal);
    document.getElementById('fb-save').addEventListener('click', saveFeedback);
    state.modal.addEventListener('click', function (e) {
      if (e.target === state.modal) closeModal();
    });

    document.addEventListener('mousemove', onMouseMove, true);
    document.addEventListener('mousedown', onMouseDown, true);
    document.addEventListener('click', onClick, true);
    document.addEventListener('keydown', onKeyDown, true);
    window.addEventListener('scroll', onScroll, true);
    window.addEventListener('resize', onScroll, true);
  }

  function setPickMode(on) {
    state.active = !!on;
    document.body.classList.toggle('fb-mode-active', state.active);
    var main = document.getElementById('fb-fab-main');
    if (main) {
      main.classList.toggle('fb-fab--active', state.active);
      var span = main.querySelector('span');
      if (span) span.textContent = state.active ? 'กำลังเลือก…' : 'แจ้งแก้ไข';
    }
    if (!state.active) {
      hideHighlight();
      state.hoverEl = null;
    } else {
      showToast('เลื่อนเมาส์เพื่อดูกรอบสีเหลือง แล้วคลิกส่วนที่ต้องการแก้ (Esc เพื่อยกเลิก)');
    }
  }

  function onKeyDown(e) {
    if (e.key === 'Escape') {
      if (state.modal.classList.contains('fb-modal-backdrop--open')) {
        closeModal();
        return;
      }
      if (state.active) {
        setPickMode(false);
      }
    }
  }

  function onMouseMove(e) {
    if (!state.active || state.modal.classList.contains('fb-modal-backdrop--open')) return;
    var t = elementFromPointDeep(e.clientX, e.clientY);
    if (!t) {
      hideHighlight();
      state.hoverEl = null;
      return;
    }
    if (state.hoverEl === t) {
      positionHighlight(t);
      return;
    }
    state.hoverEl = t;
    positionHighlight(t);
  }

  function onScroll() {
    if (state.active && state.hoverEl) positionHighlight(state.hoverEl);
  }

  function onMouseDown(e) {
    if (!state.active) return;
    if (isFeedbackUi(e.target)) return;
    // Prevent focus / drag while picking
    e.preventDefault();
  }

  function onClick(e) {
    if (!state.active) return;
    if (isFeedbackUi(e.target)) return;
    e.preventDefault();
    e.stopPropagation();
    if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();

    var t = elementFromPointDeep(e.clientX, e.clientY) || resolveTarget(e.target);
    if (!t) return;
    state.selected = t;
    setPickMode(false);
    openModal(t, false);
  }

  function openModal(target, isGeneral) {
    var info = document.getElementById('fb-selected-info');
    if (isGeneral || !target) {
      info.innerHTML = '<div><strong>ประเภท:</strong> แจ้งแบบทั่วไป (ทั้งหน้า)</div>';
      state.selected = null;
      state.selectedRect = {
        top: 0,
        left: 0,
        width: window.innerWidth,
        height: window.innerHeight,
      };
    } else {
      var rect = target.getBoundingClientRect();
      var fbId = target.getAttribute('data-feedback-id') || getComponentName(target) || '—';
      info.innerHTML =
        '<div><strong>ส่วน:</strong> ' + escapeHtml(fbId) + '</div>' +
        '<div><strong>องค์ประกอบ:</strong> ' + escapeHtml(target.tagName.toLowerCase()) + '</div>' +
        '<div><strong>ข้อความ:</strong> ' + escapeHtml(getElementText(target) || '—') + '</div>';
      state.selectedRect = {
        top: rect.top,
        left: rect.left,
        width: rect.width,
        height: rect.height,
        right: rect.right,
        bottom: rect.bottom,
      };
    }
    document.getElementById('fb-comment').value = '';
    state.modal.classList.add('fb-modal-backdrop--open');
    document.getElementById('fb-comment').focus();
  }

  function closeModal() {
    state.modal.classList.remove('fb-modal-backdrop--open');
    state.selected = null;
  }

  function escapeHtml(s) {
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }

  function captureScreenshot(rect, feedbackNum) {
    return new Promise(function (resolve) {
      if (typeof html2canvas !== 'function') {
        resolve('');
        return;
      }
      maskSensitive(document.body);
      html2canvas(document.body, {
        useCORS: true,
        allowTaint: false,
        scale: Math.min(window.devicePixelRatio || 1, 2),
        logging: false,
        ignoreElements: function (node) {
          return node.classList && (
            node.classList.contains('fb-fab') ||
            node.classList.contains('fb-fab-wrap') ||
            node.classList.contains('fb-fab-secondary') ||
            node.classList.contains('fb-highlight') ||
            node.classList.contains('fb-hover-label') ||
            node.classList.contains('fb-overlay') ||
            node.classList.contains('fb-modal-backdrop') ||
            node.classList.contains('fb-tool-root') ||
            node.classList.contains('fb-toast')
          );
        },
      }).then(function (canvas) {
        unmaskSensitive(document.body);
        var ctx = canvas.getContext('2d');
        var dpr = Math.min(window.devicePixelRatio || 1, 2);
        var x = (rect.left + window.scrollX) * dpr;
        var y = (rect.top + window.scrollY) * dpr;
        var w = rect.width * dpr;
        var h = rect.height * dpr;
        ctx.strokeStyle = '#eab308';
        ctx.lineWidth = 3 * dpr;
        ctx.strokeRect(x, y, w, h);
        ctx.fillStyle = '#eab308';
        ctx.font = 'bold ' + (14 * dpr) + 'px sans-serif';
        var label = feedbackNum || 'FB';
        var tw = ctx.measureText(label).width;
        ctx.fillRect(x, Math.max(0, y - 22 * dpr), tw + 12 * dpr, 20 * dpr);
        ctx.fillStyle = '#1e293b';
        ctx.fillText(label, x + 6 * dpr, Math.max(14 * dpr, y - 6 * dpr));
        resolve(canvas.toDataURL('image/png'));
      }).catch(function () {
        unmaskSensitive(document.body);
        resolve('');
      });
    });
  }

  function saveFeedback() {
    var comment = document.getElementById('fb-comment').value.trim();
    if (!comment) {
      alert('กรุณากรอกรายละเอียด');
      return;
    }
    var target = state.selected;
    var rect = state.selectedRect || {
      top: 0, left: 0, width: window.innerWidth, height: window.innerHeight,
    };

    var payload = {
      id: cfg.nextId || '',
      page: document.title,
      url: window.location.href,
      route: getRoute(),
      section: target ? getComponentName(target) : 'general',
      feedbackId: target ? (target.getAttribute('data-feedback-id') || '') : 'general',
      selector: target ? getCssSelector(target) : 'body',
      elementText: target ? getElementText(target) : '',
      componentName: target ? getComponentName(target) : 'general',
      comment: comment,
      category: document.getElementById('fb-category').value,
      priority: document.getElementById('fb-priority').value,
      clientName: document.getElementById('fb-client').value.trim(),
      boundingRect: {
        top: rect.top,
        left: rect.left,
        width: rect.width,
        height: rect.height,
      },
      viewport: { width: window.innerWidth, height: window.innerHeight },
      scrollPosition: { x: window.scrollX, y: window.scrollY },
      screenshot: '',
    };

    var btn = document.getElementById('fb-save');
    btn.disabled = true;
    btn.textContent = 'กำลังบันทึก…';

    captureScreenshot(rect, cfg.nextId || 'FB').then(function (shot) {
      payload.screenshot = shot;
      return fetch(cfg.submitUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
        credentials: 'same-origin',
      });
    }).then(function (res) { return res.json(); })
      .then(function (data) {
        if (!data.ok) throw new Error(data.error || 'บันทึกไม่สำเร็จ');
        closeModal();
        if (data.item && data.item.id) {
          var m = data.item.id.match(/^FB-(\d+)$/);
          if (m) {
            cfg.nextId = 'FB-' + String(parseInt(m[1], 10) + 1).padStart(3, '0');
          }
        }
        showToast('บันทึก ' + data.item.id + ' เรียบร้อยแล้ว');
      })
      .catch(function (err) {
        alert(err.message || 'บันทึกไม่สำเร็จ');
      })
      .finally(function () {
        btn.disabled = false;
        btn.textContent = 'บันทึก';
      });
  }

  function showToast(msg) {
    var existing = document.querySelector('.fb-toast');
    if (existing) existing.remove();
    var t = el('div', 'fb-toast', escapeHtml(msg));
    document.body.appendChild(t);
    requestAnimationFrame(function () { t.classList.add('fb-toast--show'); });
    setTimeout(function () {
      t.classList.remove('fb-toast--show');
      setTimeout(function () { t.remove(); }, 300);
    }, 3200);
  }

  buildUi();
  if (typeof lucide !== 'undefined') lucide.createIcons();
})();
