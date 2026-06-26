function escapePlanHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function formatBaht(value) {
  const num = Number(String(value).replace(/,/g, ''));
  if (!Number.isFinite(num)) return value;
  return num.toLocaleString('th-TH');
}

function initLucideIcons() {
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }
}

function renderPlanList(items) {
  return (items || [])
    .map((item) => `<li><img src="assets/img/plan-detail/IconCheck.1f1c7f4b.svg" alt="" width="20" height="20"><span>${escapePlanHtml(item)}</span></li>`)
    .join('');
}

function formatBahtUnit(value, unit) {
  return `${formatBaht(value)} ${unit}`;
}

function formatQuoteStat(value, unit, slider) {
  if (slider?.format === 'plain' || unit === 'กลุ่ม' || unit === 'ปี' || unit === 'ครั้ง') {
    return `${formatBaht(value)} ${unit}`;
  }
  return formatBahtUnit(value, unit);
}

function formatSliderValue(value, slider) {
  if (slider?.format === 'plain') {
    return formatBaht(value);
  }
  return formatBaht(value);
}

function calcPlanPremium(state, config) {
  const { main, opd, daily, age, gender } = state;
  const discount = config.discountPercent || 0;
  const baseRate = config.baseRate || 12000;
  const mainBase = config.mainBase || 500000;
  const mainFactor = mainBase >= 100000 ? main / mainBase : 1 + (main / mainBase - 1) * 0.6;
  const opdWeight = config.labels?.stat1Unit === 'กลุ่ม' || config.labels?.stat1Unit === 'ครั้ง' ? 120 : 1.85;
  const dailyWeight = config.labels?.stat2Unit === 'ปี' ? 180 : 2.946;

  const before = Math.round(
    (baseRate * mainFactor + opd * opdWeight + daily * dailyWeight) *
      (1 + Math.max(0, age - 30) * 0.028 - Math.max(0, 30 - age) * 0.02) *
      (gender === 'female' ? 1.06 : 1)
  );
  const after = Math.round(before * (1 - discount / 100));
  return { before, after, savings: before - after };
}

function getSliderIndex(values, current) {
  const index = values.indexOf(current);
  return index >= 0 ? index : 0;
}

function initPlanQuoteWidget(root, product) {
  const widgetEl = root.querySelector('[data-plan-quote-widget]');
  if (!widgetEl) return;

  const config = product.quoteConfig;
  if (!config) {
    widgetEl.classList.add('fwd-plan-widget--simple');
    widgetEl.innerHTML = `
        <p class="fwd-plan-widget__desc">สร้างแผนของคุณ</p>
        <p class="fwd-plan-widget__simple-price">เบี้ยเริ่มต้น <strong>${formatBaht(product.priceFrom)}</strong> บาท/เดือน</p>
        <a href="contact.html" class="btn btn--primary fwd-plan-widget__buy">สนใจสมัคร</a>`;
    return;
  }

  const tiers = product.tiers || [];
  const state = {
    tier: config.defaultTier || tiers[0]?.id || 'basic',
    main: config.tierPresets[config.defaultTier || 'basic']?.main || 500000,
    opd: config.tierPresets[config.defaultTier || 'basic']?.opd || 0,
    daily: config.tierPresets[config.defaultTier || 'basic']?.daily || 0,
    gender: 'male',
    age: 30,
  };

  const getGender = () =>
    root.querySelector('.fwd-plan-gender__btn.is-active')?.dataset.gender || state.gender;

  const getAge = () => {
    const ageInput = root.querySelector('#planAge');
    const age = Number(ageInput?.value || state.age);
    return Number.isFinite(age) ? Math.min(99, Math.max(1, age)) : state.age;
  };

  const syncTierFromMain = () => {
    const tierMap = config.tierMainValues || {};
    const match = Object.entries(tierMap).find(([, amount]) => amount === state.main);
    if (match) state.tier = match[0];
  };

  const labels = config.labels || {
    mainLimit: 'วงเงินค่ารักษาเหมาจ่ายสูงสุด',
    mainUnit: 'บาท/ปี',
    stat1: 'ค่ารักษาพยาบาลแบบผู้ป่วยนอก OPD',
    stat1Unit: 'บาท/ครั้ง',
    stat2: 'ค่าชดเชยรายวัน',
    stat2Unit: 'บาท/วัน',
  };

  const getSliderMeta = (id) => config.sliders.find((item) => item.id === id);

  const applyTierPreset = (tierId) => {
    const preset = config.tierPresets[tierId];
    if (!preset) return;

    state.tier = tierId;
    state.main = preset.main;
    state.opd = preset.opd;
    state.daily = preset.daily;

    config.sliders.forEach((slider) => {
      const index = getSliderIndex(slider.values, state[slider.id]);
      const input = widgetEl.querySelector(`[data-slider="${slider.id}"]`);
      if (input) input.value = String(index);
      const display = widgetEl.querySelector(`[data-slider-display="${slider.id}"]`);
      if (display) display.textContent = formatSliderValue(slider.values[index], slider);
    });

    updateSummary();
  };

  const renderWidget = () => {
    const premium = calcPlanPremium(
      { ...state, gender: getGender(), age: getAge() },
      config
    );
    const showDiscount = config.discountPercent > 0 && premium.savings > 0;

    widgetEl.innerHTML = `
        <p class="fwd-plan-widget__desc">สร้างแผนของคุณ</p>
        <div class="fwd-plan-widget__summary">
          <div class="fwd-plan-widget__limit">
            <span class="fwd-plan-widget__limit-label">${escapePlanHtml(labels.mainLimit)}</span>
            <strong class="fwd-plan-widget__limit-value" data-quote-main>${formatQuoteStat(state.main, labels.mainUnit)}</strong>
          </div>
          <div class="fwd-plan-widget__premium">
            <span class="fwd-plan-widget__premium-label">เบี้ยประกันของคุณ</span>
            <div class="fwd-plan-widget__premium-body">
              <strong class="fwd-plan-widget__premium-value" data-quote-after>${formatBahtUnit(premium.after, 'บาท/ปี')}</strong>
              ${
                showDiscount
                  ? `<div class="fwd-plan-widget__discount">
                      <span class="fwd-plan-widget__discount-badge">✔ ${config.discountPercent}% off <s data-quote-before>${formatBaht(premium.before)} บาท</s></span>
                      <span class="fwd-plan-widget__discount-save" data-quote-save>ประหยัด ${formatBaht(premium.savings)} บาท</span>
                    </div>`
                  : ''
              }
            </div>
          </div>
          <div class="fwd-plan-widget__stats">
            <div class="fwd-plan-widget__stat">
              <span class="fwd-plan-widget__stat-label">${escapePlanHtml(labels.stat1)}</span>
              <strong data-quote-opd>${formatQuoteStat(state.opd, labels.stat1Unit, getSliderMeta('opd'))}</strong>
            </div>
            <div class="fwd-plan-widget__stat">
              <span class="fwd-plan-widget__stat-label">${escapePlanHtml(labels.stat2)}</span>
              <strong data-quote-daily>${formatQuoteStat(state.daily, labels.stat2Unit, getSliderMeta('daily'))}</strong>
            </div>
          </div>
        </div>

        <div class="fwd-plan-widget__step">
          <h4 class="fwd-plan-widget__step-title"><span class="fwd-plan-widget__step-num">1</span> เลือกแผนของคุณ</h4>
          <div class="fwd-plan-widget__tiers" role="group" aria-label="เลือกแผนความคุ้มครอง">
            ${tiers
              .map(
                (tier) => `
              <button type="button" class="fwd-plan-widget__tier${state.tier === tier.id ? ' is-active' : ''}${tier.popular ? ' is-popular' : ''}" data-tier-id="${escapePlanHtml(tier.id)}">
                ${tier.popular ? '<span class="fwd-plan-widget__tier-badge">เป็นที่นิยม</span>' : ''}
                ${escapePlanHtml(tier.label)}
              </button>`
              )
              .join('')}
          </div>
        </div>

        <div class="fwd-plan-widget__step">
          <h4 class="fwd-plan-widget__step-title"><span class="fwd-plan-widget__step-num">2</span> ปรับแต่งแผน</h4>
          <div class="fwd-plan-widget__sliders">
          ${config.sliders
            .map((slider) => {
              const values = slider.values;
              const current = state[slider.id];
              const index = getSliderIndex(values, current);
              return `
              <div class="fwd-plan-widget__slider" data-slider-group="${escapePlanHtml(slider.id)}">
                <div class="fwd-plan-widget__slider-head">
                  <span>${escapePlanHtml(slider.label)}</span>
                  <strong data-slider-display="${escapePlanHtml(slider.id)}">${formatSliderValue(values[index], slider)}</strong>
                </div>
                <input
                  type="range"
                  class="fwd-plan-widget__slider-input"
                  min="0"
                  max="${values.length - 1}"
                  step="1"
                  value="${index}"
                  data-slider="${escapePlanHtml(slider.id)}"
                  aria-label="${escapePlanHtml(slider.label)}"
                >
                <div class="fwd-plan-widget__slider-range">
                  <span>${formatSliderValue(values[0], slider)}</span>
                  <span>${formatSliderValue(values[values.length - 1], slider)}</span>
                </div>
                ${slider.hint ? `<p class="fwd-plan-widget__slider-hint">${escapePlanHtml(slider.hint)}</p>` : ''}
              </div>`;
            })
            .join('')}
          </div>
        </div>

        <a href="contact.html" class="btn btn--primary fwd-plan-widget__buy">ซื้อออนไลน์</a>`;

    widgetEl.querySelectorAll('[data-tier-id]').forEach((btn) => {
      btn.addEventListener('click', () => {
        applyTierPreset(btn.dataset.tierId);
      });
    });

    bindWidgetEvents();
  };

  const updateSummary = () => {
    const premium = calcPlanPremium(
      { ...state, gender: getGender(), age: getAge() },
      config
    );

    const mainEl = widgetEl.querySelector('[data-quote-main]');
    if (mainEl) mainEl.textContent = formatQuoteStat(state.main, labels.mainUnit);

    const afterEl = widgetEl.querySelector('[data-quote-after]');
    if (afterEl) afterEl.textContent = formatBahtUnit(premium.after, 'บาท/ปี');

    const beforeEl = widgetEl.querySelector('[data-quote-before]');
    if (beforeEl) beforeEl.textContent = `${formatBaht(premium.before)} บาท`;

    const saveEl = widgetEl.querySelector('[data-quote-save]');
    if (saveEl) saveEl.textContent = `ประหยัด ${formatBaht(premium.savings)} บาท`;

    const opdEl = widgetEl.querySelector('[data-quote-opd]');
    if (opdEl) opdEl.textContent = formatQuoteStat(state.opd, labels.stat1Unit, getSliderMeta('opd'));

    const dailyEl = widgetEl.querySelector('[data-quote-daily]');
    if (dailyEl) dailyEl.textContent = formatQuoteStat(state.daily, labels.stat2Unit, getSliderMeta('daily'));

    widgetEl.querySelectorAll('[data-tier-id]').forEach((btn) => {
      btn.classList.toggle('is-active', btn.dataset.tierId === state.tier);
    });
  };

  const bindWidgetEvents = () => {
    widgetEl.querySelectorAll('[data-slider]').forEach((input) => {
      input.addEventListener('input', () => {
        const sliderId = input.dataset.slider;
        const slider = config.sliders.find((item) => item.id === sliderId);
        if (!slider) return;

        const index = Number(input.value);
        const value = slider.values[index];
        state[sliderId] = value;

        const display = widgetEl.querySelector(`[data-slider-display="${sliderId}"]`);
        if (display) display.textContent = formatSliderValue(value, slider);

        if (sliderId === 'main') syncTierFromMain();
        updateSummary();
      });
    });
  };

  const bindFormEvents = () => {
    root.querySelectorAll('.fwd-plan-gender__btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        root.querySelectorAll('.fwd-plan-gender__btn').forEach((el) => {
          const active = el === btn;
          el.classList.toggle('is-active', active);
          el.setAttribute('aria-pressed', String(active));
        });
        updateSummary();
      });
    });

    const ageInput = root.querySelector('#planAge');
    ageInput?.addEventListener('input', updateSummary);
    ageInput?.addEventListener('change', updateSummary);
  };

  renderWidget();
  bindFormEvents();
}

function initPlanDetail() {
  const root = document.getElementById('planDetail');
  if (!root) return;

  const params = new URLSearchParams(window.location.search);
  const id = params.get('id');
  const product = getPlanProduct(id) || getPlanProduct('easy-e-health');
  if (!product) return;

  document.title = `${product.name} - Agent Thailand`;

  const categoryLabel = PLAN_CATEGORY_LABELS[product.category] || product.category;

  const setText = (selector, text) => {
    root.querySelectorAll(selector).forEach((el) => {
      el.textContent = text;
    });
  };

  setText('[data-plan-name]', product.name);
  setText('[data-plan-tagline]', product.tagline);
  setText('[data-plan-headline]', product.headline);
  setText('[data-plan-category]', categoryLabel);

  const categoryLink = root.querySelector('[data-plan-category-link]');
  if (categoryLink) {
    categoryLink.href = `plan-category.html?category=${product.category}`;
    categoryLink.textContent = categoryLabel;
  }

  const heroImg = root.querySelector('[data-plan-hero-image]');
  if (heroImg) {
    const categoryHero =
      typeof PLAN_CATEGORY_META !== 'undefined' && PLAN_CATEGORY_META[product.category]
        ? PLAN_CATEGORY_META[product.category].heroImage
        : '';
    const fallbackHero = categoryHero || 'assets/img/hero-bg.jpg';

    heroImg.alt = product.name;
    heroImg.addEventListener(
      'error',
      () => {
        if (!heroImg.dataset.fallbackApplied) {
          heroImg.dataset.fallbackApplied = '1';
          heroImg.src = fallbackHero;
        }
      },
      { once: true }
    );
    heroImg.src = product.heroImage || fallbackHero;
  }

  const benefitsEl = root.querySelector('[data-plan-benefits]');
  if (benefitsEl) {
    benefitsEl.innerHTML = (product.benefits || [])
      .map((item) => `<li><img src="assets/img/plan-detail/IconCheck.1f1c7f4b.svg" alt="" width="20" height="20"><span>${escapePlanHtml(item)}</span></li>`)
      .join('');
  }

  const promoInlineEl = root.querySelector('[data-plan-promo-inline]');
  if (promoInlineEl && product.promo) {
    promoInlineEl.innerHTML = `
      <p class="fwd-plan-quote__promo-text">${escapePlanHtml(product.promo.text)}</p>
      <div class="fwd-plan-quote__promo-meta">
        <span>โปรโมชันถึงวันที่ ${escapePlanHtml(product.promo.until)}</span>
        <span class="fwd-plan-quote__promo-code">รหัส ${escapePlanHtml(product.promo.code)}</span>
      </div>`;
  }

  const requirementsEl = root.querySelector('[data-plan-requirements]');
  if (requirementsEl) {
    requirementsEl.innerHTML = (product.requirements || [])
      .map(
        (item) => `
        <div class="fwd-plan-req">
          <img class="fwd-plan-req__icon" src="${escapePlanHtml(item.icon)}" alt="" width="40" height="40">
          <div>
            <h5 class="fwd-plan-req__title">${escapePlanHtml(item.title)}</h5>
            <p class="fwd-plan-req__desc">${escapePlanHtml(item.desc)}</p>
          </div>
        </div>`
      )
      .join('');
  }

  initPlanQuoteWidget(root, product);

  const highlightsEl = root.querySelector('[data-plan-highlights]');
  if (highlightsEl) {
    highlightsEl.innerHTML = (product.highlights || [])
      .map(
        (item) => `
        <div class="fwd-plan-highlight">
          <img src="assets/img/plan-detail/IconCheck.1f1c7f4b.svg" alt="" width="20" height="20">
          <p>${escapePlanHtml(item)}</p>
        </div>`
      )
      .join('');
  }

  const tableEl = root.querySelector('[data-plan-coverage-table]');
  if (tableEl) {
    const tierLabels = (product.tiers || []).map((tier) => tier.label);
    const head = tierLabels.map((label) => `<th>${escapePlanHtml(label)}</th>`).join('');
    const body = (product.coverageRows || [])
      .map(
        (row) => `
        <tr>
          <th scope="row">${escapePlanHtml(row.label)}</th>
          ${(row.values || []).map((value) => `<td>${escapePlanHtml(value)}</td>`).join('')}
        </tr>`
      )
      .join('');

    tableEl.innerHTML = `
      <table class="fwd-plan-table">
        <thead><tr><th>รายการความคุ้มครอง</th>${head}</tr></thead>
        <tbody>${body}</tbody>
      </table>`;
  }

  const promoDetailEl = root.querySelector('[data-plan-promo-detail]');
  if (promoDetailEl) {
    const items = (product.promoDetail || []).length
      ? product.promoDetail
      : product.promo
        ? [product.promo.text]
        : [];
    promoDetailEl.innerHTML = `
      <ul class="fwd-plan-list">${renderPlanList(items)}</ul>
      ${
        product.promo
          ? `<p class="fwd-plan-promo-detail__meta">รหัสโปรโมชัน <strong>${escapePlanHtml(product.promo.code)}</strong> ถึงวันที่ ${escapePlanHtml(product.promo.until)}</p>`
          : ''
      }`;
  }

  const conditionsEl = root.querySelector('[data-plan-conditions]');
  if (conditionsEl) {
    conditionsEl.innerHTML = (product.conditions || [])
      .map((item) => `<li>${escapePlanHtml(item)}</li>`)
      .join('');
  }

  const renewalEl = root.querySelector('[data-plan-renewal]');
  if (renewalEl) {
    renewalEl.innerHTML = renderPlanList(product.renewal);
  }

  const whyTitleEl = root.querySelector('[data-plan-why-title]');
  const whySection = (product.sections || []).find((section) => section.id === 'why');
  if (whyTitleEl && whySection) {
    whyTitleEl.textContent = whySection.label;
  }

  const whyEl = root.querySelector('[data-plan-why]');
  if (whyEl) {
    whyEl.innerHTML = renderPlanList(product.why);
  }

  const faqEl = root.querySelector('[data-plan-faqs]');
  if (faqEl) {
    faqEl.innerHTML = (product.faqs || [])
      .map(
        (item, index) => `
        <div class="fwd-plan-faq-item${index === 0 ? ' is-active' : ''}">
          <button type="button" class="fwd-plan-faq-item__question" aria-expanded="${index === 0}">
            <span>${escapePlanHtml(item.q)}</span>
            <i data-lucide="${index === 0 ? 'minus' : 'plus'}" aria-hidden="true"></i>
          </button>
          <div class="fwd-plan-faq-item__answer">
            <p>${escapePlanHtml(item.a)}</p>
          </div>
        </div>`
      )
      .join('');

    faqEl.querySelectorAll('.fwd-plan-faq-item__question').forEach((btn) => {
      btn.addEventListener('click', () => {
        const item = btn.closest('.fwd-plan-faq-item');
        const wasActive = item.classList.contains('is-active');

        faqEl.querySelectorAll('.fwd-plan-faq-item').forEach((el) => {
          el.classList.remove('is-active');
          el.querySelector('.fwd-plan-faq-item__question')?.setAttribute('aria-expanded', 'false');
          const icon = el.querySelector('.fwd-plan-faq-item__question [data-lucide]');
          if (icon) icon.setAttribute('data-lucide', 'plus');
        });

        if (!wasActive) {
          item.classList.add('is-active');
          btn.setAttribute('aria-expanded', 'true');
          const icon = btn.querySelector('[data-lucide]');
          if (icon) icon.setAttribute('data-lucide', 'minus');
        }

        initLucideIcons();
      });
    });
  }

  const sections = product.sections || PLAN_SECTIONS;
  const promoSection = sections.find((section) => section.id === 'promo');
  const promoTitleEl = root.querySelector('[data-plan-promo-title]');
  if (promoTitleEl && promoSection) {
    promoTitleEl.textContent = promoSection.label;
  }

  const navEl = root.querySelector('[data-plan-section-nav]');
  if (navEl) {
    navEl.innerHTML = sections
      .map(
        (section) => `
        <a href="#section-${escapePlanHtml(section.id)}" class="fwd-plan-content__nav-link" data-section-id="${escapePlanHtml(section.id)}">
          ${escapePlanHtml(section.label)}
        </a>`
      )
      .join('');

    navEl.querySelectorAll('.fwd-plan-content__nav-link').forEach((link) => {
      link.addEventListener('click', (event) => {
        event.preventDefault();
        const target = root.querySelector(`#section-${link.dataset.sectionId}`);
        if (target) {
          const offset = 100;
          const top = target.getBoundingClientRect().top + window.scrollY - offset;
          window.scrollTo({ top, behavior: 'smooth' });
        }
      });
    });
  }

  const sectionEls = root.querySelectorAll('[data-plan-section]');
  const navLinks = root.querySelectorAll('.fwd-plan-content__nav-link');

  const updateActiveNav = () => {
    let currentId = sections[0]?.id;
    const scrollPos = window.scrollY + 140;

    sectionEls.forEach((section) => {
      if (section.offsetTop <= scrollPos) {
        currentId = section.dataset.planSection;
      }
    });

    navLinks.forEach((link) => {
      link.classList.toggle('is-active', link.dataset.sectionId === currentId);
    });
  };

  window.addEventListener('scroll', updateActiveNav, { passive: true });
  updateActiveNav();

  const relatedEl = document.getElementById('planRelatedGrid');
  if (relatedEl) {
    const related =
      typeof getRelatedPlanProducts === 'function'
        ? getRelatedPlanProducts(product, 4)
        : getAllPlanProducts().filter((item) => item.id !== product.id).slice(0, 4);

    if (typeof renderPlanCards === 'function') {
      renderPlanCards(relatedEl, related);
    }
  }

  initLucideIcons();
}
