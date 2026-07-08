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
    const list = document.querySelector('.hero-fab-panel__list');
    if (list) {
      list.innerHTML = fab.items.map((ch) => `
        <li>
          <a href="${esc(ch.href)}" class="hero-fab-channel hero-fab-channel--${esc(ch.channel)}" ${ch.href.startsWith('http') ? 'target="_blank" rel="noopener noreferrer"' : ''}>
            <span class="hero-fab-channel__body">
              <span class="hero-fab-channel__label">${esc(ch.label)}</span>
              <span class="hero-fab-channel__value">${esc(ch.value)}</span>
            </span>
          </a>
        </li>`).join('');
    }
    const fabTitle = document.querySelector('.hero-fab-panel__title');
    if (fabTitle && fab.title) fabTitle.textContent = fab.title;
  }
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
  if (data.eyebrow) {
    const eyebrow = el.querySelector('.feature-grid__eyebrow, .section-label');
    if (eyebrow) eyebrow.textContent = data.eyebrow;
  }
  const title = el.querySelector('.feature-grid__title, #feature-section-title');
  if (title && data.title) {
    title.innerHTML = esc(data.title) + (data.highlight ? ` <span class="feature-grid__highlight">${esc(data.highlight)}</span>` : '');
  }
  const img = el.querySelector('.feature-grid__image img, .feature-section img');
  if (img && data.image) img.src = data.image;
  const grid = el.querySelector('.feature-grid__cards, .feature-grid__list');
  if (grid && data.items) {
    grid.innerHTML = data.items.map((item) => `
      <article class="feature-card">
        <div class="feature-card__icon"><i data-lucide="${esc(item.icon || 'star')}"></i></div>
        <h3 class="feature-card__title">${esc(item.title)}</h3>
        <p class="feature-card__text">${esc(item.text)}</p>
      </article>`).join('');
  }
  const cta = el.querySelector('.feature-grid__cta a, .feature-section .btn');
  if (cta && data.cta) {
    cta.textContent = data.cta.label;
    cta.href = data.cta.href || '#';
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

function initHomeFeaturedPlans() {
  const grid = document.getElementById('homeFeaturedPlans');
  if (!grid || typeof SITE_BLOCKS === 'undefined') return;
  const ids = SITE_BLOCKS.home_featured_plans?.plan_ids || [];
  if (!ids.length || typeof getPlanProduct !== 'function') return;

  grid.innerHTML = ids.map((id) => {
    const plan = getPlanProduct(id);
    if (!plan) return '';
    const promo = plan.promo?.text ? `<span class="plan-card__badge plan-card__badge--discount">${esc(plan.promo.text)}</span>` : '';
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
