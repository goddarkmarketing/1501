/**
 * Site chrome renderer — footer, header settings, CMS blocks
 * Requires: site-content.js (SITE_SETTINGS, SITE_BLOCKS, SITE_NAV_MENUS)
 */
document.addEventListener('DOMContentLoaded', () => {
  initSiteSettings();
  initSiteFooter();
  initSiteBlocks();
  initHomeFeaturedPlans();
  initConsultForms();
});

function esc(str) {
  return String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function initSiteSettings() {
  if (typeof SITE_SETTINGS === 'undefined') return;

  const s = SITE_SETTINGS;

  document.querySelectorAll('[data-setting]').forEach((el) => {
    const key = el.dataset.setting;
    const val = s[key];
    if (val === undefined || val === '') return;
    const attr = el.dataset.settingAttr;
    if (attr === 'href') {
      el.setAttribute('href', (el.dataset.settingPrefix || '') + val);
    } else if (attr === 'src') {
      el.setAttribute('src', val);
    } else {
      el.textContent = val;
    }
  });

  const fab = typeof SITE_BLOCKS !== 'undefined' ? SITE_BLOCKS.hero_fab : null;
  if (fab && fab.items) {
    const fabIcons = {
      buy: 'shield-plus',
      agent: 'user-plus',
      facebook: 'facebook',
      line: 'message-circle',
      tiktok: 'music-2',
      email: 'mail',
      phone: 'phone',
    };
    const list = document.querySelector('.hero-fab-panel__list');
    if (list) {
      list.innerHTML = fab.items.map((ch) => {
        const href = String(ch.href || '#');
        const external = /^(https?:|mailto:|tel:)/i.test(href);
        const icon = fabIcons[ch.channel] || 'link';
        return `
        <li>
          <a href="${esc(href)}" class="hero-fab-channel hero-fab-channel--${esc(ch.channel)}" ${external && href.startsWith('http') ? 'target="_blank" rel="noopener noreferrer"' : ''}>
            <span class="hero-fab-channel__icon" aria-hidden="true">
              <i data-lucide="${esc(icon)}"></i>
            </span>
            <span class="hero-fab-channel__body">
              <span class="hero-fab-channel__label">${esc(ch.label)}</span>
              <span class="hero-fab-channel__value">${esc(ch.value)}</span>
            </span>
          </a>
        </li>`;
      }).join('');
      if (typeof lucide !== 'undefined') lucide.createIcons();
    }
    const fabTitle = document.querySelector('.hero-fab-panel__title');
    if (fabTitle && fab.title) fabTitle.textContent = fab.title;
  }
}

function getSocialBrandIcon(channel) {
  const icons = {
    facebook: '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M24 12.073c0-6.627-5.373-12-12-12S0 5.446 0 12.073c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
    line: '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63h2.386c.346 0 .627.285.627.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63.346 0 .628.285.628.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/></svg>',
    youtube: '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
    instagram: '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>',
    tiktok: '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/></svg>',
  };
  return icons[channel] || '';
}

function renderFooterSocial(wrap, settings = {}) {
  const items = [
    { cls: 'social-fb', channel: 'facebook', label: 'Facebook', href: settings.facebook_url || 'https://www.facebook.com/AgentThailandFWD' },
    { cls: 'social-line', channel: 'line', label: 'LINE', href: settings.line_url || 'https://line.me/ti/p/~@agentthailand' },
    { cls: 'social-yt', channel: 'youtube', label: 'YouTube', href: settings.youtube_url || '#' },
    { cls: 'social-ig', channel: 'instagram', label: 'Instagram', href: settings.instagram_url || '#' },
    { cls: 'social-tt', channel: 'tiktok', label: 'TikTok', href: settings.tiktok_url || 'https://www.tiktok.com/@agentthailand' },
  ];

  wrap.innerHTML = items.map((item) => {
    const href = String(item.href || '#').trim() || '#';
    const external = /^https?:/i.test(href);
    return `<a href="${esc(href)}" class="footer__social-link ${item.cls}" aria-label="${esc(item.label)}"${external ? ' target="_blank" rel="noopener noreferrer"' : ''}>${getSocialBrandIcon(item.channel)}</a>`;
  }).join('');
}

function initSiteFooter() {
  if (typeof SITE_BLOCKS === 'undefined' || !SITE_BLOCKS.footer) return;
  const footer = document.querySelector('.footer');
  if (!footer) return;

  const f = SITE_BLOCKS.footer;
  const s = typeof SITE_SETTINGS !== 'undefined' ? SITE_SETTINGS : {};

  const tagline = footer.querySelector('.footer__tagline');
  if (tagline && f.tagline) tagline.textContent = f.tagline;

  const addressEl = footer.querySelector('.footer__contact-item .footer__contact-icon + span, .footer__contact-item > span:last-child');
  const addressItem = footer.querySelector('.footer__contact-item');
  if (s.address) {
    const items = footer.querySelectorAll('.footer__contact-item');
    items.forEach((item) => {
      const icon = item.querySelector('[data-lucide="map-pin"]');
      if (icon) {
        const span = item.querySelector('span:last-child');
        if (span) span.innerHTML = esc(s.address).replace(/\n/g, '<br>');
      }
    });
  }

  const emailLink = footer.querySelector('.footer__contact-item a[href^="mailto"]');
  if (emailLink && s.email) {
    emailLink.href = 'mailto:' + s.email;
    emailLink.textContent = s.email;
  }

  const phoneItem = footer.querySelector('.footer__contact-item [data-lucide="phone"]');
  if (phoneItem) {
    const parent = phoneItem.closest('.footer__contact-item');
    const span = parent?.querySelector('span:last-child');
    if (span) {
      const phones = [s.phone, s.phone2].filter(Boolean);
      span.innerHTML = phones.map((p, i) => {
        const tel = p.replace(/[^\d+]/g, '');
        const link = `<a href="tel:${tel}">${esc(p)}</a>`;
        return i > 0 ? `<span class="footer__contact-sep">/</span>${link}` : link;
      }).join('');
    }
  }

  const hoursItem = footer.querySelector('.footer__contact-item [data-lucide="clock"]');
  if (hoursItem && s.business_hours) {
    const span = hoursItem.closest('.footer__contact-item')?.querySelector('span:last-child');
    if (span) span.textContent = s.business_hours;
  }

  const socialWrap = footer.querySelector('.footer__social');
  if (socialWrap) {
    renderFooterSocial(socialWrap, s);
  } else {
    const socialMap = {
      'social-fb': s.facebook_url,
      'social-line': s.line_url,
      'social-yt': s.youtube_url,
      'social-ig': s.instagram_url,
      'social-tt': s.tiktok_url,
    };
    Object.entries(socialMap).forEach(([cls, url]) => {
      const link = footer.querySelector(`.footer__social-link.${cls}`);
      if (link && url) link.href = url;
    });
  }

  const cols = footer.querySelectorAll('.footer__col');
  (f.columns || []).forEach((col, i) => {
    if (!cols[i]) return;
    const title = cols[i].querySelector('.footer__col-title');
    const list = cols[i].querySelector('.footer__links');
    if (title) title.textContent = col.title;
    if (list && col.links) {
      list.innerHTML = col.links.map((l) => `<li><a href="${esc(l.href)}">${esc(l.label)}</a></li>`).join('');
    }
  });

  const cta = footer.querySelector('.footer__cta');
  if (cta && f.cta_text) {
    cta.textContent = f.cta_text;
    if (f.cta_href) cta.href = f.cta_href;
  }

  const copyright = footer.querySelector('.footer__copyright');
  if (copyright) copyright.textContent = s.copyright || f.copyright || copyright.textContent;

  const legal = footer.querySelector('.footer__legal');
  if (legal && f.legal) {
    legal.innerHTML = f.legal.map((l) => `<a href="${esc(l.href)}">${esc(l.label)}</a>`).join('');
  }
}

function initSiteBlocks() {
  if (typeof SITE_BLOCKS === 'undefined') return;

  document.querySelectorAll('[data-block]').forEach((el) => {
    const key = el.dataset.block;
    const data = SITE_BLOCKS[key];
    if (!data) return;

    switch (key) {
      case 'home_services':
        renderHomeServices(el, data);
        break;
      case 'home_features':
        renderHomeFeatures(el, data);
        break;
      case 'home_about_cta':
        renderHomeCta(el, data);
        break;
      case 'about_stats':
        renderAboutStats(el, data);
        break;
      case 'about_pillars':
        renderAboutPillars(el, data);
        break;
      case 'about_why':
        renderAboutWhy(el, data);
        break;
      case 'about_ethics':
        renderAboutEthics(el, data);
        break;
      case 'about_milestones':
        renderAboutMilestones(el, data);
        break;
      case 'about_quote':
        renderAboutQuote(el, data);
        break;
      case 'consult_aside':
        renderConsultAside(el, data);
        break;
      case 'register_content':
        renderRegisterContent(el, data);
        break;
      case 'login_content':
        renderLoginContent(el, data);
        break;
      default:
        break;
    }
  });

  if (SITE_BLOCKS.consult_terms) {
    const termsBody = document.querySelector('.consult-terms-modal__body');
    if (termsBody) termsBody.innerHTML = SITE_BLOCKS.consult_terms;
  }
}

function renderHomeServices(el, data) {
  const grid = el.querySelector('.hero__services-grid') || el;
  if (!data.items) return;
  const label = el.querySelector('.hero__services-label');
  if (label && data.label) label.textContent = data.label;
  grid.innerHTML = data.items.map((item) => `
    <a href="${esc(item.href || '#')}" class="hero__service-card">
      <span class="hero__service-icon"><i data-lucide="${esc(item.icon || 'circle')}"></i></span>
      <span class="hero__service-text">${esc(item.text)}</span>
    </a>`).join('');
  if (typeof lucide !== 'undefined') lucide.createIcons();
}

function renderHomeFeatures(el, data) {
  const label = el.querySelector('.feature-grid__label, .feature-grid__eyebrow, .section-label');
  if (label && data.eyebrow) label.textContent = data.eyebrow;

  const title = el.querySelector('.feature-grid__title, #feature-section-title');
  if (title && data.title) {
    title.innerHTML = esc(data.title)
      + (data.highlight ? ` <span class="feature-grid__highlight">${esc(data.highlight)}</span>` : '');
  }

  const lead = el.querySelector('.feature-grid__lead');
  if (lead && data.lead) lead.textContent = data.lead;

  const img = el.querySelector('.feature-grid__image img, .feature-section img');
  if (img && data.image) {
    const src = String(data.image).replace(/\.jpg$/i, '.png');
    img.src = src;
  }

  const grid = el.querySelector('.feature-cards, .feature-grid__cards, .feature-grid__list');
  if (grid && Array.isArray(data.items) && data.items.length) {
    grid.innerHTML = data.items.map((item) => `
      <article class="feature-card">
        <span class="feature-card__icon"><i data-lucide="${esc(item.icon || 'star')}" aria-hidden="true"></i></span>
        <div>
          <h3 class="feature-card__title">${esc(item.title)}</h3>
          <p class="feature-card__text">${esc(item.text)}</p>
        </div>
      </article>`).join('');
  }

  const link = el.querySelector('.feature-grid__link');
  if (link && data.cta?.label) {
    link.href = data.cta.href || '#';
    link.innerHTML = `${esc(data.cta.label)} <i data-lucide="arrow-right" aria-hidden="true"></i>`;
  }

  if (typeof lucide !== 'undefined') lucide.createIcons();
}

function renderHomeCta(el, data) {
  const title = el.querySelector('.about-cta__title, h2');
  const text = el.querySelector('.about-cta__text, p');
  if (title && data.title) title.textContent = data.title;
  if (text && data.text) text.textContent = data.text;
  const btns = el.querySelectorAll('.about-cta__actions a, .btn');
  if (btns[0] && data.primary) {
    btns[0].textContent = data.primary.label;
    btns[0].href = data.primary.href || '#';
  }
  if (btns[1] && data.secondary) {
    btns[1].textContent = data.secondary.label;
    btns[1].href = data.secondary.href || '#';
  }
}

function renderAboutStats(el, data) {
  const grid = el.querySelector('.about-stats') || el;
  if (!data.items) return;
  grid.innerHTML = data.items.map((item) => `
    <article class="about-stat">
      <span class="about-stat__value">${esc(item.value)}</span>
      <span class="about-stat__label">${esc(item.label)}</span>
      <p class="about-stat__desc">${esc(item.desc)}</p>
    </article>`).join('');
}

function renderAboutPillars(el, data) {
  const label = el.querySelector('.about-section-head__label');
  const title = el.querySelector('.about-section-head__title');
  if (label && data.label) label.textContent = data.label;
  if (title && data.title) title.textContent = data.title;
  const grid = el.querySelector('.about-pillars__grid') || el;
  if (data.items) {
    grid.innerHTML = data.items.map((item) => `
      <article class="about-pillar">
        <div class="about-pillar__icon"><i data-lucide="${esc(item.icon || 'circle')}"></i></div>
        <h3 class="about-pillar__title">${esc(item.title)}</h3>
        <p class="about-pillar__text">${esc(item.text)}</p>
      </article>`).join('');
  }
  if (typeof lucide !== 'undefined') lucide.createIcons();
}

function renderAboutWhy(el, data) {
  const label = el.querySelector('.about-section-head__label');
  const title = el.querySelector('.about-section-head__title');
  if (label && data.label) label.textContent = data.label;
  if (title && data.title) title.textContent = data.title;
  const grid = el.querySelector('.about-why__grid, .about-features__grid') || el;
  if (data.items) {
    grid.innerHTML = data.items.map((item) => `
      <article class="about-feature">
        <div class="about-feature__icon"><i data-lucide="${esc(item.icon || 'star')}"></i></div>
        <h3 class="about-feature__title">${esc(item.title)}</h3>
        <p class="about-feature__text">${esc(item.text)}</p>
      </article>`).join('');
  }
  if (typeof lucide !== 'undefined') lucide.createIcons();
}

function renderAboutEthics(el, data) {
  const label = el.querySelector('.about-section-head__label');
  const title = el.querySelector('.about-section-head__title');
  if (label && data.label) label.textContent = data.label;
  if (title && data.title) title.textContent = data.title;
  const list = el.querySelector('.about-ethics__list, ul');
  if (list && data.items) {
    list.innerHTML = data.items.map((item) => `<li>${esc(item)}</li>`).join('');
  }
}

function renderAboutMilestones(el, data) {
  const label = el.querySelector('.about-section-head__label');
  const title = el.querySelector('.about-section-head__title');
  if (label && data.label) label.textContent = data.label;
  if (title && data.title) title.textContent = data.title;
  const track = el.querySelector('.about-timeline, .about-milestones__track') || el;
  if (data.items) {
    track.innerHTML = data.items.map((item) => `
      <article class="about-timeline__item">
        <span class="about-timeline__year">${esc(item.year)}</span>
        <h3 class="about-timeline__title">${esc(item.title)}</h3>
        <p class="about-timeline__text">${esc(item.text)}</p>
      </article>`).join('');
  }
}

function renderAboutQuote(el, data) {
  const quote = el.querySelector('.about-quote__text, blockquote');
  const name = el.querySelector('.about-quote__name, cite');
  const role = el.querySelector('.about-quote__role');
  if (quote && data.text) quote.textContent = data.text;
  if (name && data.name) name.textContent = data.name;
  if (role && data.role) role.textContent = data.role;
}

function renderConsultAside(el, data) {
  const img = el.querySelector('.consult-aside-card__media img, img');
  if (img && data.image) { img.src = data.image; }
  const title = el.querySelector('.consult-aside-card__title');
  const text = el.querySelector('.consult-aside-card__text');
  if (title && data.title) title.textContent = data.title;
  if (text && data.text) text.textContent = data.text;
  const list = el.querySelector('.consult-aside-card__list');
  if (list && data.bullets) {
    list.innerHTML = data.bullets.map((b) => `<li>${esc(b)}</li>`).join('');
  }
  const link = el.querySelector('.consult-aside-card__link');
  if (link && data.link_label) {
    link.childNodes[0].textContent = data.link_label + ' ';
    if (data.link_href) link.href = data.link_href;
  }
}

function renderRegisterContent(el, data) {
  const heroTitle = el.querySelector('.register-hero__title, h1');
  const heroText = el.querySelector('.register-hero__text, .register-hero p');
  const heroImg = el.querySelector('.register-hero__image img, .register-hero img');
  if (heroTitle && data.hero_title) heroTitle.textContent = data.hero_title;
  if (heroText && data.hero_text) heroText.textContent = data.hero_text;
  if (heroImg && data.hero_image) heroImg.src = data.hero_image;
  const perksGrid = el.querySelector('.register-perks__grid');
  if (perksGrid && data.perks) {
    perksGrid.innerHTML = data.perks.map((p) => `
      <article class="register-perk">
        <div class="register-perk__icon"><i data-lucide="${esc(p.icon || 'star')}"></i></div>
        <h3 class="register-perk__title">${esc(p.title)}</h3>
        <p class="register-perk__text">${esc(p.text)}</p>
      </article>`).join('');
  }
  if (typeof lucide !== 'undefined') lucide.createIcons();
}

function renderLoginContent(el, data) {
  const title = el.querySelector('.auth-card__title, h1');
  const subtitle = el.querySelector('.auth-card__subtitle');
  if (title && data.title) title.textContent = data.title;
  if (subtitle && data.subtitle) subtitle.textContent = data.subtitle;
}

function planPromoBadgeText(plan) {
  const promo = plan?.promo;
  if (!promo) return '';
  if (promo.badge) return String(promo.badge).trim();
  const text = String(promo.text || '').replace(/\s+/g, ' ').trim();
  if (!text) return '';
  const discount = text.match(/รับส่วนลด\s*\d+\s*%/);
  if (discount) return discount[0].trim();
  if (/ส่วนลด/.test(text)) return 'รับส่วนลดพิเศษ';
  if (text.length <= 22) return text;
  return `${text.slice(0, 20).trim()}…`;
}

function initHomeFeaturedPlans() {
  const grid = document.getElementById('homeFeaturedPlans');
  if (!grid || typeof SITE_BLOCKS === 'undefined') return;
  const ids = SITE_BLOCKS.home_featured_plans?.plan_ids || [];
  if (!ids.length || typeof getPlanProduct !== 'function') return;

  grid.innerHTML = ids.map((id) => {
    const plan = getPlanProduct(id);
    if (!plan) return '';
    const badgeText = planPromoBadgeText(plan);
    const promo = badgeText
      ? `<span class="plan-card__badge plan-card__badge--discount">${esc(badgeText)}</span>`
      : '';
    const img = plan.heroImage || 'assets/img/plans/default.jpg';
    const desc = plan.headline || plan.tagline || '';
    return `<article class="plan-card">
      <div class="plan-card__image">
        <img src="${esc(img)}" alt="${esc(plan.name)}" width="313" height="174" loading="lazy">
        ${promo}
      </div>
      <div class="plan-card__body">
        <h3 class="plan-card__title">${esc(plan.name)}</h3>
        <p class="plan-card__desc">${esc(desc)}</p>
        <a href="plan-details.html?id=${esc(id)}" class="btn plan-card__btn">ดูรายละเอียด</a>
      </div>
    </article>`;
  }).join('');
}

function initConsultForms() {
  document.querySelectorAll('.consult-form, form.consult-form').forEach((form) => {
    if (form.dataset.bound) return;
    form.dataset.bound = '1';
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(form);
      const payload = {
        name: (fd.get('fullname') || [fd.get('firstname'), fd.get('lastname')].filter(Boolean).join(' ')).trim(),
        phone: fd.get('phone') || '',
        email: fd.get('email') || '',
        insurance_type: fd.get('insurance') || '',
        province: fd.get('province') || '',
        age: fd.get('age') || '',
        callback_time: fd.get('contact_time') || '',
        message: fd.get('message') || '',
      };
      try {
        const res = await fetch('admin/api/contact.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (data.ok) {
          alert('ส่งข้อมูลเรียบร้อยแล้ว เราจะติดต่อกลับโดยเร็ว');
          form.reset();
        } else {
          alert(data.error || 'เกิดข้อผิดพลาด กรุณาลองใหม่');
        }
      } catch {
        alert('ไม่สามารถส่งข้อมูลได้ กรุณาลองใหม่ภายหลัง');
      }
    });
  });
}
