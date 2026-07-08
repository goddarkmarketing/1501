document.addEventListener('DOMContentLoaded', () => {
  initLucideIcons();
  initSiteContent();
  if (typeof initHeroFormSelects === 'function') initHeroFormSelects();
  if (typeof initPlanNavDropdown === 'function') initPlanNavDropdown();
  initMobileMenu();
  initTimeSlots();
  initTimeline();
  initTestimonialSlider();
  initPasswordToggle();
  initConsultFormTerms();
  initHeroFabContact();
  if (typeof initPlanListing === 'function') initPlanListing();
  if (typeof initBlogListing === 'function') initBlogListing();
  if (typeof initBlogDetail === 'function') initBlogDetail();
  if (typeof initPlanDetail === 'function') initPlanDetail();
  if (typeof initPlanCategory === 'function') initPlanCategory();
  if (typeof initPromotionListing === 'function') initPromotionListing();
  if (typeof initHospitalLocator === 'function') initHospitalLocator();
});
function initLucideIcons() {
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }
}

function initSiteContent() {
  if (typeof SITE_PAGES === 'undefined') return;

  const page = document.body.dataset.page;
  const sections = page ? getSitePage(page) : {};

  document.querySelectorAll('[data-content]').forEach((el) => {
    const key = el.dataset.content;
    const val = sections[key];
    if (val === undefined || val === '') return;

    if (el.dataset.contentHtml !== undefined || /<[a-z][\s\S]*>/i.test(val)) {
      el.innerHTML = val;
    } else {
      el.textContent = val;
    }
  });

  document.querySelectorAll('[data-content-href]').forEach((el) => {
    const key = el.dataset.contentHref;
    const val = sections[key];
    if (val) el.setAttribute('href', val);
  });

  document.querySelectorAll('[data-content-bg]').forEach((el) => {
    const key = el.dataset.contentBg;
    const val = sections[key];
    if (val) el.style.backgroundImage = `url('${val}')`;
  });

  document.querySelectorAll('[data-content-src]').forEach((el) => {
    const key = el.dataset.contentSrc;
    const val = sections[key];
    if (val) el.setAttribute('src', val);
  });

  if (typeof SITE_SETTINGS !== 'undefined') {
    document.querySelectorAll('[data-setting]').forEach((el) => {
      const key = el.dataset.setting;
      const val = SITE_SETTINGS[key];
      if (val === undefined || val === '') return;

      const attr = el.dataset.settingAttr;
      if (attr === 'href') {
        const prefix = el.dataset.settingPrefix || '';
        el.setAttribute('href', prefix + val);
      } else if (attr === 'src') {
        el.setAttribute('src', val);
      } else {
        el.textContent = val;
      }
    });
  }

  if (typeof SITE_FAQ_ITEMS !== 'undefined' && SITE_FAQ_ITEMS.length) {
    renderSiteFaq();
  } else {
    initFaq();
  }
}

function renderSiteFaq() {
  const grid = document.getElementById('faqGrid');
  if (!grid) return;

  const half = Math.ceil(SITE_FAQ_ITEMS.length / 2);
  const cols = [SITE_FAQ_ITEMS.slice(0, half), SITE_FAQ_ITEMS.slice(half)];

  grid.innerHTML = cols.map((items, colIdx) => {
    const colHtml = items.map((item, idx) => {
      const active = colIdx === 0 && idx === 0 ? ' active' : '';
      return `<div class="faq-item${active}">
        <div class="faq-item__question">
          <span>${escapeHtml(item.q)}</span>
          <span class="faq-item__icon"><i data-lucide="${active ? 'x' : 'plus'}"></i></span>
        </div>
        <div class="faq-item__answer"><p>${escapeHtml(item.a)}</p></div>
      </div>`;
    }).join('');
    return `<div class="faq-col">${colHtml}</div>`;
  }).join('');

  initFaq();
  initLucideIcons();
}

function escapeHtml(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function initMobileMenu() {
  const hamburger = document.querySelector('.header__hamburger');
  const nav = document.querySelector('.header__nav');
  if (!hamburger || !nav) return;

  const closeDropdowns = () => {
    nav.querySelectorAll('.header__nav-item--dropdown.is-open').forEach((item) => {
      item.classList.remove('is-open');
      item.querySelector('.header__nav-link--dropdown')?.setAttribute('aria-expanded', 'false');
    });
  };

  const closeMenu = () => {
    hamburger.classList.remove('active');
    nav.classList.remove('open');
    setMenuIcon(false);
    closeDropdowns();
  };

  const setMenuIcon = (open) => {
    const icon = hamburger.querySelector('[data-lucide]');
    if (icon) {
      icon.setAttribute('data-lucide', open ? 'x' : 'menu');
      initLucideIcons();
    }
  };

  hamburger.addEventListener('click', () => {
    const open = !nav.classList.contains('open');
    hamburger.classList.toggle('active', open);
    nav.classList.toggle('open', open);
    setMenuIcon(open);
    if (!open) closeDropdowns();
  });

  nav.addEventListener('click', (e) => {
    if (e.target.closest('.header__nav-link--dropdown')) {
      return;
    }

    if (e.target.closest('.header__nav-mega-link, .header__nav-submenu-link')) {
      closeMenu();
      return;
    }

    if (e.target.closest('.header__nav-link')) {
      closeMenu();
    }
  });
}

function initFaq() {
  document.querySelectorAll('.faq-item__question').forEach(question => {
    question.addEventListener('click', () => {
      const item = question.closest('.faq-item');
      const wasActive = item.classList.contains('active');

      document.querySelectorAll('.faq-item').forEach(i => {
        i.classList.remove('active');
        const icon = i.querySelector('.faq-item__icon [data-lucide]');
        if (icon) icon.setAttribute('data-lucide', 'plus');
      });

      if (!wasActive) {
        item.classList.add('active');
        const icon = item.querySelector('.faq-item__icon [data-lucide]');
        if (icon) icon.setAttribute('data-lucide', 'x');
      }

      initLucideIcons();
    });
  });
}

function initTimeSlots() {
  document.querySelectorAll('.time-slot').forEach(slot => {
    slot.addEventListener('click', () => {
      slot.closest('.time-slots')?.querySelectorAll('.time-slot').forEach(s => s.classList.remove('active'));
      slot.classList.add('active');
    });
  });
}

function initTimeline() {
  document.querySelectorAll('.timeline__year').forEach(year => {
    year.addEventListener('click', () => {
      document.querySelectorAll('.timeline__year').forEach(y => y.classList.remove('active'));
      year.classList.add('active');

      const yearValue = year.dataset.year;
      const bigYear = document.querySelector('.timeline__year-big');
      if (bigYear && yearValue) bigYear.textContent = yearValue;
    });
  });
}

function initTestimonialSlider() {
  document.querySelectorAll('.testimonials-slider').forEach((slider) => {
    const track = slider.querySelector('.testimonials-track');
    const cards = [...slider.querySelectorAll('.testimonial-card')];
    const dotsWrap = slider.parentElement?.querySelector('.testimonial-dots');
    const dots = dotsWrap ? [...dotsWrap.querySelectorAll('.testimonial-dot')] : [];
    if (!track || cards.length === 0) return;

    let current = 0;

    const getPerView = () => {
      if (window.matchMedia('(max-width: 768px)').matches) return 1;
      if (window.matchMedia('(max-width: 1024px)').matches) return 2;
      return parseInt(slider.dataset.perView, 10) || 3;
    };

    const getSlideCount = () => Math.ceil(cards.length / getPerView());

    const update = () => {
      const perView = getPerView();
      const gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap) || 22;
      const cardWidth = (slider.clientWidth - gap * (perView - 1)) / perView;

      cards.forEach((card) => {
        card.style.width = `${cardWidth}px`;
        card.style.flexBasis = `${cardWidth}px`;
      });

      const slideCount = getSlideCount();
      if (current >= slideCount) current = slideCount - 1;
      if (current < 0) current = 0;

      track.style.transform = `translateX(-${current * slider.clientWidth}px)`;

      dots.forEach((dot, i) => {
        const visible = i < slideCount;
        dot.hidden = !visible;
        dot.classList.toggle('active', visible && i === current);
      });
    };

    dots.forEach((dot, i) => {
      dot.addEventListener('click', () => {
        current = i;
        update();
      });
    });

    window.addEventListener('resize', update);
    update();
  });
}

function initConsultFormTerms() {
  const modal = document.getElementById('consultTermsModal');
  if (!modal) return;

  const openModal = (e) => {
    e.preventDefault();
    e.stopPropagation();
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    initLucideIcons();
  };

  const closeModal = () => {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  };

  document.querySelectorAll('.consult-form__terms, .hero-form__terms').forEach((link) => {
    link.addEventListener('click', openModal);
  });

  modal.querySelectorAll('[data-close-terms]').forEach((el) => {
    el.addEventListener('click', closeModal);
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('is-open')) {
      closeModal();
    }
  });
}

function initHeroFabContact() {
  const wrap = document.getElementById('heroFabWrap');
  const btn = document.getElementById('heroFabBtn');
  const panel = document.getElementById('heroFabPanel');
  if (!wrap || !btn || !panel) return;

  const setOpen = (open) => {
    btn.classList.toggle('is-open', open);
    panel.classList.toggle('is-open', open);
    btn.setAttribute('aria-expanded', String(open));
    panel.setAttribute('aria-hidden', String(!open));

    const icon = btn.querySelector('[data-lucide]');
    if (icon) {
      icon.setAttribute('data-lucide', open ? 'x' : 'headphones');
      initLucideIcons();
    }
  };

  btn.addEventListener('click', (e) => {
    e.stopPropagation();
    setOpen(!btn.classList.contains('is-open'));
  });

  document.addEventListener('click', (e) => {
    if (!wrap.contains(e.target)) {
      setOpen(false);
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      setOpen(false);
    }
  });

  panel.querySelectorAll('.hero-fab-channel').forEach((link) => {
    link.addEventListener('click', () => setOpen(false));
  });
}

function initPasswordToggle() {
  document.querySelectorAll('.password-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const inputId = btn.dataset.target;
      const input = document.getElementById(inputId);
      if (!input) return;

      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';

      const icon = btn.querySelector('[data-lucide]');
      if (icon) icon.setAttribute('data-lucide', isPassword ? 'eye-off' : 'eye');
      initLucideIcons();
    });
  });
}

function togglePassword(inputId) {
  const input = document.getElementById(inputId);
  if (!input) return;
  input.type = input.type === 'password' ? 'text' : 'password';
}
