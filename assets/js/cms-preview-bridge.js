/**
 * Visual-edit bridge — ?cms_preview=1 inside admin iframe.
 * Editable almost everything except plan cards, blog cards, and forms.
 */
(function () {
  var params = new URLSearchParams(window.location.search)
  if (params.get('cms_preview') !== '1') return
  if (window.parent === window) return

  document.documentElement.classList.add('cms-preview-mode')

  var EXCLUDE_SEL = [
    'form',
    '.consult-form',
    '.plan-card',
    '.blog-card',
    '#homeFeaturedPlans',
    '.card-grid--rec .plan-card',
    'input',
    'select',
    'textarea',
    'option',
    '.cms-preview-banner',
    '.hero__slider-nav',
    '.testimonial-dots',
    '.header__hamburger',
    'script',
    'style',
    'svg',
    'img',
    'i[data-lucide]',
    '.hero__service-icon',
    '.faq-item__icon',
  ].join(',')

  var style = document.createElement('style')
  style.textContent =
    'html.cms-preview-mode a{cursor:pointer!important}' +
    '[data-cms-editable]{outline:1px dashed rgba(21,15,150,.25);outline-offset:3px;cursor:pointer!important;transition:outline-color .15s ease,background-color .15s ease,box-shadow .15s ease;border-radius:4px}' +
    '[data-cms-editable]:hover{outline:2px solid rgba(59,130,246,.85)!important;background-color:rgba(191,219,254,.55)!important;box-shadow:0 0 0 4px rgba(147,197,253,.45);position:relative;z-index:5}' +
    '[data-cms-editable]:hover::after{content:"คลิกเพื่อแก้ไข";position:absolute;top:-1.6rem;left:0;z-index:6;padding:2px 8px;border-radius:999px;background:#2563eb;color:#fff;font:600 11px/1.4 Sarabun,system-ui,sans-serif;white-space:nowrap;pointer-events:none;box-shadow:0 2px 6px rgba(37,99,235,.35)}' +
    '[data-cms-editable].is-cms-active{outline:2px solid #150f96!important;background-color:rgba(191,219,254,.7)!important;box-shadow:0 0 0 4px rgba(21,15,150,.2)}' +
    '.cms-preview-banner{position:fixed;top:0;left:0;right:0;z-index:99999;background:#150f96;color:#fff;font:600 13px/1.4 Sarabun,system-ui,sans-serif;padding:8px 14px;text-align:center}' +
    'body{padding-top:36px!important}'
  document.head.appendChild(style)

  var banner = document.createElement('div')
  banner.className = 'cms-preview-banner'
  banner.textContent = 'โหมดแก้ไข — คลิกข้อความ/ปุ่มเพื่อแก้ (ยกเว้นการ์ดแผนประกัน การ์ดบทความ และแบบฟอร์ม)'
  document.body.prepend(banner)

  function isExcluded(el) {
    if (!el || el.nodeType !== 1) return true
    if (el.closest(EXCLUDE_SEL)) return true
    if (el.classList && el.classList.contains('cms-preview-banner')) return true
    return false
  }

  function cssPath(el) {
    if (el.id) return '#' + CSS.escape(el.id)
    if (el.dataset && el.dataset.content) return '[data-content="' + el.dataset.content + '"]'
    if (el.dataset && el.dataset.setting) return '[data-setting="' + el.dataset.setting + '"]'
    if (el.dataset && el.dataset.contentHref) return '[data-content-href="' + el.dataset.contentHref + '"]'

    var parts = []
    var node = el
    var depth = 0
    while (node && node.nodeType === 1 && node !== document.body && depth < 8) {
      var part = node.tagName.toLowerCase()
      if (node.id) {
        parts.unshift('#' + CSS.escape(node.id))
        break
      }
      var cls = (node.className && typeof node.className === 'string')
        ? node.className.trim().split(/\s+/).filter(function (c) {
            return c && c.indexOf('is-') !== 0 && c !== 'active' && c.indexOf('cms-') !== 0
          }).slice(0, 2)
        : []
      if (cls.length) part += '.' + cls.map(function (c) { return CSS.escape(c) }).join('.')
      var parent = node.parentElement
      if (parent) {
        var siblings = Array.prototype.filter.call(parent.children, function (c) {
          return c.tagName === node.tagName
        })
        if (siblings.length > 1) {
          part += ':nth-of-type(' + (siblings.indexOf(node) + 1) + ')'
        }
      }
      parts.unshift(part)
      node = parent
      depth++
    }
    return parts.join(' > ')
  }

  function mark(el, payload) {
    if (!el || isExcluded(el)) return
    // don't mark if an ancestor is already editable with same purpose (prefer leaf)
    el.setAttribute('data-cms-editable', '1')
    el.dataset.cmsPayload = JSON.stringify(payload)
  }

  function shortLabel(el, fallback) {
    var t = (el.textContent || '').replace(/\s+/g, ' ').trim()
    if (!t) return fallback || el.tagName.toLowerCase()
    return t.length > 36 ? t.slice(0, 36) + '…' : t
  }

  function markEditables() {
    // clear previous auto marks that are not special? keep all and re-set
    document.querySelectorAll('[data-cms-editable]').forEach(function (el) {
      el.removeAttribute('data-cms-editable')
      delete el.dataset.cmsPayload
    })

    // 1) Known page fields
    document.querySelectorAll('[data-content]').forEach(function (el) {
      if (isExcluded(el)) return
      var key = el.dataset.content || ''
      mark(el, {
        kind: 'page',
        page: document.body.dataset.page || '',
        key: key,
        label: key,
        value: el.innerHTML,
        multiline: /title|text|headline|html/i.test(key) || (el.innerHTML || '').length > 60,
      })
    })

    document.querySelectorAll('[data-content-href]').forEach(function (el) {
      if (isExcluded(el)) return
      var key = el.dataset.contentHref || ''
      mark(el, {
        kind: 'page',
        page: document.body.dataset.page || '',
        key: key,
        label: key + ' (ลิงก์)',
        value: el.getAttribute('href') || '',
        isHref: true,
      })
    })

    document.querySelectorAll('[data-setting]').forEach(function (el) {
      if (isExcluded(el)) return
      var key = el.dataset.setting || ''
      mark(el, {
        kind: 'setting',
        key: key,
        label: 'ตั้งค่า: ' + key,
        value: el.dataset.settingAttr === 'href' ? el.getAttribute('href') || '' : el.textContent || '',
      })
    })

    // 2) Blocks: home_services
    document.querySelectorAll('[data-block="home_services"]').forEach(function (block) {
      var labelEl = block.querySelector('.hero__services-label')
      if (labelEl && !isExcluded(labelEl)) {
        mark(labelEl, {
          kind: 'block',
          block: 'home_services',
          field: 'label',
          label: 'หัวข้อบริการของเรา',
          value: labelEl.textContent || '',
        })
      }
      block.querySelectorAll('.hero__service-card').forEach(function (card, index) {
        if (isExcluded(card)) return
        var textEl = card.querySelector('.hero__service-text')
        var iconEl = card.querySelector('[data-lucide]')
        mark(card, {
          kind: 'block',
          block: 'home_services',
          itemIndex: index,
          label: 'บริการ #' + (index + 1),
          item: {
            text: textEl ? textEl.textContent.trim() : '',
            href: card.getAttribute('href') || '',
            icon: iconEl ? iconEl.getAttribute('data-lucide') || '' : '',
          },
        })
      })
    })

    // home_features cards
    document.querySelectorAll('[data-block="home_features"] .feature-card').forEach(function (card, index) {
      if (isExcluded(card)) return
      var title = card.querySelector('.feature-card__title')
      var text = card.querySelector('.feature-card__text')
      var iconEl = card.querySelector('[data-lucide]')
      mark(card, {
        kind: 'block',
        block: 'home_features',
        itemIndex: index,
        label: 'จุดเด่น #' + (index + 1),
        item: {
          title: title ? title.textContent.trim() : '',
          text: text ? text.textContent.trim() : '',
          icon: iconEl ? iconEl.getAttribute('data-lucide') || '' : '',
        },
      })
    })

    // home_about_cta
    document.querySelectorAll('[data-block="home_about_cta"]').forEach(function (block) {
      var title = block.querySelector('.about-cta__title, h2')
      var text = block.querySelector('.about-cta__text, p')
      if (title) {
        mark(title, { kind: 'block', block: 'home_about_cta', field: 'title', label: 'หัวข้อ CTA', value: title.textContent || '' })
      }
      if (text) {
        mark(text, { kind: 'block', block: 'home_about_cta', field: 'text', label: 'ข้อความ CTA', value: text.textContent || '', multiline: true })
      }
      block.querySelectorAll('.about-cta__actions a, .btn').forEach(function (btn, index) {
        mark(btn, {
          kind: 'block',
          block: 'home_about_cta',
          field: index === 0 ? 'primary' : 'secondary',
          label: index === 0 ? 'ปุ่มหลัก CTA' : 'ปุ่มรอง CTA',
          item: { label: (btn.textContent || '').trim(), href: btn.getAttribute('href') || '' },
        })
      })
    })

    // hero FAB channels
    document.querySelectorAll('.hero-fab-channel').forEach(function (el, index) {
      if (isExcluded(el)) return
      var label = el.querySelector('.hero-fab-channel__label')
      var value = el.querySelector('.hero-fab-channel__value')
      mark(el, {
        kind: 'block',
        block: 'hero_fab',
        itemIndex: index,
        label: 'ปุ่มลอย #' + (index + 1),
        item: {
          label: label ? label.textContent.trim() : '',
          value: value ? value.textContent.trim() : '',
          href: el.getAttribute('href') || '',
        },
      })
    })
    var fabTitle = document.querySelector('.hero-fab-panel__title')
    if (fabTitle) {
      mark(fabTitle, { kind: 'block', block: 'hero_fab', field: 'title', label: 'หัวข้อปุ่มลอย', value: fabTitle.textContent || '' })
    }

    // FAQ items
    document.querySelectorAll('.faq-item').forEach(function (item, index) {
      if (isExcluded(item)) return
      var q = item.querySelector('.faq-item__question > span:first-child, .faq-item__question span:not(.faq-item__icon)')
      var a = item.querySelector('.faq-item__answer p, .faq-item__answer')
      // Prefer marking the whole item for combined edit
      mark(item, {
        kind: 'faq',
        itemIndex: index,
        label: 'FAQ #' + (index + 1),
        item: {
          q: q ? q.textContent.trim() : '',
          a: a ? a.textContent.trim() : '',
        },
      })
    })

    // 3) Auto-scan remaining text elements (DOM overrides)
    var candidates = document.querySelectorAll(
      'h1,h2,h3,h4,h5,h6,p,a,li,span,button,label,.section-title,.section-label,.section-view-all,' +
      '.product-category__name,.header__nav-link,.header__agent-link,.footer__col-title,.footer__links a,' +
      '.footer__cta,.footer__contact-item span:not(.footer__contact-icon),.footer__tagline,' +
      '.testimonial-card__text,.testimonial-card__author,.testimonial-card__company,' +
      '.faq-section__intro,.consult-card__title'
    )

    candidates.forEach(function (el) {
      if (isExcluded(el)) return
      if (el.hasAttribute('data-cms-editable')) return
      if (el.closest('[data-cms-editable]')) return
      if (el.querySelector('[data-cms-editable]')) return
      // skip empty / icon-only
      var text = (el.textContent || '').replace(/\s+/g, ' ').trim()
      if (!text || text.length < 2) return
      // skip if only contains icons
      if (el.children.length && !Array.prototype.some.call(el.childNodes, function (n) { return n.nodeType === 3 && n.textContent.trim() })) {
        // has element children only - still ok for headings with nested spans
        if (!/^(H[1-6]|P|A|SPAN|BUTTON|LABEL|LI)$/i.test(el.tagName)) return
      }
      // skip nav dropdown buttons' chevron wrappers
      if (el.classList.contains('header__nav-chevron')) return
      // Prefer not marking tiny nested spans inside already-markable parents of same text
      if (el.tagName === 'SPAN' && el.parentElement && /^(A|BUTTON|H1|H2|H3|H4)$/i.test(el.parentElement.tagName)) {
        if (!el.parentElement.hasAttribute('data-cms-editable') && !el.closest('[data-cms-editable]')) {
          // mark parent instead later - skip leaf span inside link if parent will be marked
        }
      }

      var isLink = el.tagName === 'A'
      var payload = {
        kind: 'dom',
        page: document.body.dataset.page || 'home',
        selector: cssPath(el),
        label: shortLabel(el, 'ข้อความ'),
        value: el.innerHTML,
        text: el.textContent.trim(),
        multiline: text.length > 50 || /^(P|H1|H2)$/i.test(el.tagName),
      }
      if (isLink) {
        payload.href = el.getAttribute('href') || ''
        payload.item = { label: text, href: payload.href }
      }
      mark(el, payload)
    })
  }

  function selectEditable(target) {
    document.querySelectorAll('.is-cms-active').forEach(function (n) {
      n.classList.remove('is-cms-active')
    })
    target.classList.add('is-cms-active')
    try {
      var payload = JSON.parse(target.dataset.cmsPayload || '{}')
      if (payload.kind === 'page' && payload.isHref) {
        payload.value = target.getAttribute('href') || ''
      } else if (payload.kind === 'page') {
        payload.value = target.innerHTML
      } else if (payload.kind === 'dom') {
        payload.value = target.innerHTML
        payload.text = (target.textContent || '').trim()
        if (target.tagName === 'A') {
          payload.href = target.getAttribute('href') || ''
          payload.item = { label: payload.text, href: payload.href }
        }
        payload.selector = cssPath(target)
      } else if (payload.kind === 'faq') {
        var q = target.querySelector('.faq-item__question > span:first-child')
        var a = target.querySelector('.faq-item__answer p, .faq-item__answer')
        payload.item = {
          q: q ? q.textContent.trim() : (payload.item && payload.item.q) || '',
          a: a ? a.textContent.trim() : (payload.item && payload.item.a) || '',
        }
      }
      window.parent.postMessage(Object.assign({ source: 'agent-cms-preview', type: 'select-field' }, payload), '*')
    } catch (err) {}
  }

  document.addEventListener(
    'click',
    function (e) {
      var editable = e.target.closest('[data-cms-editable]')
      var link = e.target.closest('a, button, [onclick]')

      if (editable) {
        e.preventDefault()
        e.stopPropagation()
        e.stopImmediatePropagation()
        selectEditable(editable)
        return
      }
      if (link) {
        e.preventDefault()
        e.stopPropagation()
        e.stopImmediatePropagation()
      }
    },
    true,
  )

  document.addEventListener('submit', function (e) {
    e.preventDefault()
    e.stopPropagation()
  }, true)

  document.addEventListener('auxclick', function (e) {
    if (e.target.closest('a')) {
      e.preventDefault()
      e.stopPropagation()
    }
  }, true)

  window.addEventListener('message', function (event) {
    var data = event.data
    if (!data || data.source !== 'agent-cms-parent') return

    if (data.type === 'hydrate' && data.fields) {
      Object.keys(data.fields).forEach(function (key) {
        var value = data.fields[key]
        document.querySelectorAll('[data-content="' + key + '"]').forEach(function (el) {
          el.innerHTML = String(value ?? '')
        })
        document.querySelectorAll('[data-content-href="' + key + '"]').forEach(function (el) {
          el.setAttribute('href', String(value ?? ''))
        })
      })
      markEditables()
      window.parent.postMessage({ source: 'agent-cms-preview', type: 'ready' }, '*')
      return
    }

    if (data.type === 'update-page' && data.key) {
      document.querySelectorAll('[data-content="' + data.key + '"]').forEach(function (el) {
        el.innerHTML = String(data.value ?? '')
      })
      document.querySelectorAll('[data-content-href="' + data.key + '"]').forEach(function (el) {
        el.setAttribute('href', String(data.value ?? ''))
      })
    }

    if (data.type === 'update-dom' && data.selector) {
      try {
        var el = document.querySelector(data.selector)
        if (el) {
          if (data.href != null && el.tagName === 'A') el.setAttribute('href', data.href)
          if (data.value != null) el.innerHTML = String(data.value)
          else if (data.text != null) el.textContent = String(data.text)
          markEditables()
          el.classList.add('is-cms-active')
        }
      } catch (err) {}
    }

    if (data.type === 'update-faq' && typeof data.itemIndex === 'number' && data.item) {
      var items = document.querySelectorAll('.faq-item')
      var item = items[data.itemIndex]
      if (item) {
        var q = item.querySelector('.faq-item__question > span:first-child')
        var a = item.querySelector('.faq-item__answer p')
        if (q && data.item.q != null) q.textContent = data.item.q
        if (a && data.item.a != null) a.textContent = data.item.a
        markEditables()
      }
    }

    if (data.type === 'update-block-item' && data.block != null) {
      if (data.block === 'home_services' && typeof data.itemIndex === 'number' && data.item) {
        var cards = document.querySelectorAll('[data-block="home_services"] .hero__service-card')
        var card = cards[data.itemIndex]
        if (card) {
          if (data.item.href != null) card.setAttribute('href', data.item.href)
          var textEl = card.querySelector('.hero__service-text')
          if (textEl && data.item.text != null) textEl.textContent = data.item.text
          var iconEl = card.querySelector('[data-lucide]')
          if (iconEl && data.item.icon != null) {
            iconEl.setAttribute('data-lucide', data.item.icon)
            if (typeof lucide !== 'undefined') lucide.createIcons()
          }
        }
      }
      if (data.block === 'home_services' && data.field === 'label') {
        var labelEl = document.querySelector('[data-block="home_services"] .hero__services-label')
        if (labelEl) labelEl.textContent = data.value || ''
      }
      if (data.block === 'hero_fab' && typeof data.itemIndex === 'number' && data.item) {
        var ch = document.querySelectorAll('.hero-fab-channel')[data.itemIndex]
        if (ch) {
          if (data.item.href != null) ch.setAttribute('href', data.item.href)
          var lab = ch.querySelector('.hero-fab-channel__label')
          var val = ch.querySelector('.hero-fab-channel__value')
          if (lab && data.item.label != null) lab.textContent = data.item.label
          if (val && data.item.value != null) val.textContent = data.item.value
        }
      }
      if (data.block === 'hero_fab' && data.field === 'title') {
        var ft = document.querySelector('.hero-fab-panel__title')
        if (ft) ft.textContent = data.value || ''
      }
      if (data.block === 'home_features' && typeof data.itemIndex === 'number' && data.item) {
        var fcard = document.querySelectorAll('[data-block="home_features"] .feature-card')[data.itemIndex]
        if (fcard) {
          var ft2 = fcard.querySelector('.feature-card__title')
          var fx = fcard.querySelector('.feature-card__text')
          if (ft2 && data.item.title != null) ft2.textContent = data.item.title
          if (fx && data.item.text != null) fx.textContent = data.item.text
        }
      }
      if (data.block === 'home_about_cta') {
        var cta = document.querySelector('[data-block="home_about_cta"]')
        if (cta) {
          if (data.field === 'title') {
            var t = cta.querySelector('.about-cta__title, h2')
            if (t) t.textContent = data.value || ''
          }
          if (data.field === 'text') {
            var p = cta.querySelector('.about-cta__text, p')
            if (p) p.textContent = data.value || ''
          }
          if ((data.field === 'primary' || data.field === 'secondary') && data.item) {
            var btns = cta.querySelectorAll('.about-cta__actions a, .btn')
            var bi = data.field === 'primary' ? 0 : 1
            if (btns[bi]) {
              if (data.item.label != null) btns[bi].textContent = data.item.label
              if (data.item.href != null) btns[bi].setAttribute('href', data.item.href)
            }
          }
        }
      }
      markEditables()
    }

    if (data.type === 'highlight' && data.key) {
      document.querySelectorAll('.is-cms-active').forEach(function (n) { n.classList.remove('is-cms-active') })
      var el = document.querySelector('[data-content="' + data.key + '"]') || document.querySelector('[data-content-href="' + data.key + '"]')
      if (el) {
        el.classList.add('is-cms-active')
        el.scrollIntoView({ behavior: 'smooth', block: 'center' })
      }
    }

    if (data.type === 'remark') markEditables()
  })

  function boot() {
    markEditables()
    window.parent.postMessage({ source: 'agent-cms-preview', type: 'ready', page: document.body.dataset.page || '' }, '*')
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      setTimeout(boot, 200)
      setTimeout(markEditables, 600)
      setTimeout(markEditables, 1400)
    })
  } else {
    setTimeout(boot, 200)
    setTimeout(markEditables, 600)
    setTimeout(markEditables, 1400)
  }

  try {
    var mo = new MutationObserver(function () {
      clearTimeout(window.__cmsRemarkTimer)
      window.__cmsRemarkTimer = setTimeout(markEditables, 120)
    })
    mo.observe(document.body, { childList: true, subtree: true })
  } catch (err) {}
})()
