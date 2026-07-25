/**
 * Injected into the live site when ?cms_preview=1
 * Highlights editable nodes and talks to the CMS parent frame.
 */
(function () {
  const params = new URLSearchParams(window.location.search)
  if (params.get('cms_preview') !== '1') return
  if (window.parent === window) return

  const style = document.createElement('style')
  style.textContent = `
    [data-cms-editable] {
      outline: 1px dashed transparent;
      outline-offset: 3px;
      cursor: pointer !important;
      transition: outline-color .15s ease, background-color .15s ease;
    }
    [data-cms-editable]:hover {
      outline-color: rgba(21,15,150,.55);
      background-color: rgba(21,15,150,.06);
    }
    [data-cms-editable].is-cms-active {
      outline: 2px solid #150f96;
      background-color: rgba(21,15,150,.08);
    }
    .cms-preview-banner {
      position: fixed; top: 0; left: 0; right: 0; z-index: 99999;
      background: #150f96; color: #fff; font: 600 13px/1.4 Sarabun, system-ui, sans-serif;
      padding: 8px 14px; text-align: center;
    }
    body { padding-top: 36px !important; }
  `
  document.head.appendChild(style)

  const banner = document.createElement('div')
  banner.className = 'cms-preview-banner'
  banner.textContent = 'โหมดแก้ไขหลังบ้าน — คลิกข้อความเพื่อแก้ไข'
  document.body.prepend(banner)

  function mark(el: Element, payload: Record<string, unknown>) {
    el.setAttribute('data-cms-editable', '1')
    ;(el as HTMLElement).dataset.cmsPayload = JSON.stringify(payload)
  }

  document.querySelectorAll('[data-content]').forEach((el) => {
    const key = (el as HTMLElement).dataset.content || ''
    mark(el, {
      kind: 'page',
      page: document.body.dataset.page || 'home',
      key,
      label: key,
      value: el.innerHTML,
      multiline: /title|text|headline/i.test(key),
    })
  })

  document.querySelectorAll('[data-setting]').forEach((el) => {
    const key = (el as HTMLElement).dataset.setting || ''
    mark(el, {
      kind: 'setting',
      key,
      label: key,
      value: el.textContent || '',
      multiline: false,
    })
  })

  document.addEventListener(
    'click',
    (e) => {
      const target = (e.target as HTMLElement).closest('[data-cms-editable]') as HTMLElement | null
      if (!target) return
      e.preventDefault()
      e.stopPropagation()
      document.querySelectorAll('.is-cms-active').forEach((n) => n.classList.remove('is-cms-active'))
      target.classList.add('is-cms-active')
      try {
        const payload = JSON.parse(target.dataset.cmsPayload || '{}')
        window.parent.postMessage({ source: 'agent-cms-preview', type: 'select-field', ...payload }, '*')
      } catch {
        /* ignore */
      }
    },
    true,
  )

  window.addEventListener('message', (event) => {
    const data = event.data
    if (!data || data.source !== 'agent-cms-parent') return
    if (data.type === 'update-page' && data.key) {
      document.querySelectorAll(`[data-content="${data.key}"]`).forEach((el) => {
        el.innerHTML = String(data.value ?? '')
      })
    }
    if (data.type === 'update-setting' && data.key) {
      document.querySelectorAll(`[data-setting="${data.key}"]`).forEach((el) => {
        if ((el as HTMLElement).dataset.settingAttr === 'href') {
          const prefix = (el as HTMLElement).dataset.settingPrefix || ''
          el.setAttribute('href', prefix + String(data.value ?? ''))
        } else {
          el.textContent = String(data.value ?? '')
        }
      })
    }
  })
})()
