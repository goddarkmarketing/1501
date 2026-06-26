const PLAN_CAT_IMAGES = [
  'assets/img/plans/plan-rec-1.jpg',
  'assets/img/plans/plan-rec-2.jpg',
  'assets/img/plans/plan-rec-3.jpg',
  'assets/img/plans/plan-rec-4.jpg',
];

function getPlanCategoryFromUrl() {
  const params = new URLSearchParams(window.location.search);
  return params.get('category') || '';
}

const PLAN_CAT_FEATURE_ICONS = ['sparkles', 'sliders-horizontal', 'shield-check'];

function renderPlanCategoryBreadcrumb(page) {
  const el = document.querySelector('[data-plan-cat-breadcrumb]');
  if (!el) return;
  el.innerHTML = `
    <a href="index.html">หน้าแรก</a>
    <span aria-hidden="true">/</span>
    <a href="plan.html">ผลิตภัณฑ์ทั้งหมด</a>
    <span aria-hidden="true">/</span>
    <span aria-current="page">${page.label}</span>`;
}

function renderPlanCategoryHero(page) {
  const heroImage = document.querySelector('[data-plan-cat-hero-image]');
  const tagline = document.querySelector('[data-plan-cat-tagline]');
  const title = document.querySelector('[data-plan-cat-title]');
  const headline = document.querySelector('[data-plan-cat-headline]');
  const iconWrap = document.querySelector('[data-plan-cat-icon-wrap]');
  const iconEl = document.querySelector('[data-plan-cat-icon]');

  if (heroImage) {
    heroImage.src = page.heroImage || '';
    heroImage.alt = page.label;
  }
  if (tagline) tagline.textContent = 'ผลิตภัณฑ์ FWD';
  if (title) title.textContent = page.label;
  if (headline) headline.textContent = page.headline || '';
  if (iconEl && page.icon) {
    iconEl.setAttribute('data-lucide', page.icon);
    if (iconWrap) iconWrap.hidden = false;
  }
  document.title = `${page.label} - Agent Thailand`;
}

function renderPlanCategoryIntro(page) {
  const introTitle = document.querySelector('[data-plan-cat-intro-title]');
  const introText = document.querySelector('[data-plan-cat-intro-text]');
  if (introTitle) introTitle.textContent = page.introTitle || '';
  if (introText) introText.textContent = page.introText || '';
}

function renderPlanCategoryFeatures(page) {
  const grid = document.querySelector('[data-plan-cat-features]');
  if (!grid || !page.features?.length) return;

  grid.innerHTML = page.features
    .map(
      (feature, index) => `
      <article class="plan-cat-feature">
        <div class="plan-cat-feature__icon" aria-hidden="true">
          <i data-lucide="${PLAN_CAT_FEATURE_ICONS[index % PLAN_CAT_FEATURE_ICONS.length]}"></i>
        </div>
        <div class="plan-cat-feature__content">
          <h3 class="plan-cat-feature__title">${feature.title}</h3>
          <p class="plan-cat-feature__desc">${feature.desc}</p>
        </div>
      </article>`
    )
    .join('');
}

function renderPlanCategoryTabs(activeId) {
  const tabs = document.querySelector('[data-plan-cat-tabs]');
  if (!tabs || typeof PLAN_CATEGORY_LABELS !== 'object') return;

  const order = ['life-accident', 'health', 'critical', 'investment-linked', 'savings'];
  tabs.innerHTML = order
    .filter((id) => PLAN_CATEGORY_LABELS[id])
    .map((id) => {
      const label = PLAN_CATEGORY_LABELS[id];
      const icon = (typeof PLAN_NAV_ICONS !== 'undefined' && PLAN_NAV_ICONS[id]) || 'shield';
      const isActive = id === activeId;
      return `
        <a href="plan-category.html?category=${encodeURIComponent(id)}"
           class="plan-cat-tab${isActive ? ' is-active' : ''}"
           ${isActive ? 'aria-current="page"' : ''}>
          <i data-lucide="${icon}" aria-hidden="true"></i>
          <span>${label}</span>
        </a>`;
    })
    .join('');
}

function renderPlanCategoryProducts(page) {
  const grid = document.querySelector('[data-plan-cat-grid]');
  const countEl = document.querySelector('[data-plan-cat-count]');
  if (!grid) return;

  const products = page.products || [];
  if (countEl) countEl.textContent = `พบ ${products.length} แผน`;

  grid.innerHTML = products
    .map((product, index) => {
      const highlights = (product.highlights || []).slice(0, 3);
      const priceFrom = product.priceFrom ? product.priceFrom.toLocaleString('th-TH') : '—';
      const hasPromo = product.promo?.text?.includes('ส่วนลด');
      const image = product.heroImage || PLAN_CAT_IMAGES[index % PLAN_CAT_IMAGES.length];

      return `
        <article class="plan-cat-card">
          <div class="plan-cat-card__media">
            <img src="${image}" alt="" width="400" height="220" loading="lazy">
            ${hasPromo ? '<span class="plan-cat-card__badge">รับส่วนลด</span>' : ''}
          </div>
          <div class="plan-cat-card__body">
            <p class="plan-cat-card__category">${page.label}</p>
            <h3 class="plan-cat-card__title">${product.name}</h3>
            <p class="plan-cat-card__headline">${product.headline}</p>
            ${
              highlights.length
                ? `<ul class="plan-cat-card__highlights">${highlights.map((item) => `<li>${item}</li>`).join('')}</ul>`
                : ''
            }
            <div class="plan-cat-card__footer">
              <div class="plan-cat-card__price">
                <span class="plan-cat-card__price-label">เบี้ยเริ่มต้น</span>
                <span class="plan-cat-card__price-value">${priceFrom} <small>บาท/เดือน</small></span>
              </div>
              <div class="plan-cat-card__actions">
                <a href="plan-details.html?id=${encodeURIComponent(product.id)}" class="btn btn--primary plan-cat-card__btn">ดูรายละเอียด</a>
                <a href="contact.html" class="btn plan-cat-card__btn plan-cat-card__btn--ghost">นัดปรึกษา</a>
              </div>
            </div>
          </div>
        </article>`;
    })
    .join('');
}

function initPlanCategory() {
  const root = document.getElementById('planCategory');
  if (!root || typeof getPlanCategoryPage !== 'function') return;

  const categoryId = getPlanCategoryFromUrl();
  const page = getPlanCategoryPage(categoryId);
  if (!page) {
    window.location.replace('plan.html');
    return;
  }

  renderPlanCategoryBreadcrumb(page);
  renderPlanCategoryHero(page);
  renderPlanCategoryIntro(page);
  renderPlanCategoryFeatures(page);
  renderPlanCategoryTabs(categoryId);
  renderPlanCategoryProducts(page);

  if (typeof initLucideIcons === 'function') initLucideIcons();
}
