function escapePromoHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function formatPromoVisualText(highlight, installment) {
  const lines = [];
  if (highlight) lines.push(highlight.replace(/\*/g, '').trim());
  if (installment) lines.push(installment);
  return lines;
}

function renderPromotionCard(item) {
  const visualLines = formatPromoVisualText(item.highlight, item.installment);
  const badge = item.popular ? 'เป็นที่นิยม' : item.isNew ? 'ใหม่' : item.badge || '';
  const bullets = item.bullets?.length
    ? item.bullets
    : [item.highlight].filter(Boolean);

  return `
    <article class="promo-card" data-category="${escapePromoHtml(item.categoryKey)}">
      <div class="promo-card__visual">
        <div class="promo-card__timer" data-promo-countdown aria-live="polite">
          <span class="promo-card__timer-label">เหลือเวลาโปรโมชัน</span>
          <div class="promo-card__timer-grid" aria-hidden="true">
            <span class="promo-card__timer-unit"><strong data-promo-unit="days">0</strong><small>วัน</small></span>
            <span class="promo-card__timer-sep">:</span>
            <span class="promo-card__timer-unit"><strong data-promo-unit="hours">00</strong><small>ชม.</small></span>
            <span class="promo-card__timer-sep">:</span>
            <span class="promo-card__timer-unit"><strong data-promo-unit="minutes">00</strong><small>นาที</small></span>
            <span class="promo-card__timer-sep">:</span>
            <span class="promo-card__timer-unit"><strong data-promo-unit="seconds">00</strong><small>วินาที</small></span>
          </div>
        </div>
        <img src="${escapePromoHtml(item.image)}" alt="" class="promo-card__visual-image" width="420" height="280" loading="lazy">
        <div class="promo-card__visual-overlay" aria-hidden="true"></div>
        <div class="promo-card__visual-copy">
          ${visualLines.map((line) => `<p>${escapePromoHtml(line)}</p>`).join('')}
        </div>
      </div>
      <div class="promo-card__body">
        <div class="promo-card__top">
          <span class="promo-card__category">${escapePromoHtml(item.category)}</span>
          <div class="promo-card__meta-actions">
            ${badge ? `<span class="promo-card__badge">${escapePromoHtml(badge)}</span>` : ''}
            <button type="button" class="promo-card__share" aria-label="แชร์โปรโมชัน">
              <i data-lucide="share-2" aria-hidden="true"></i>
            </button>
          </div>
        </div>
        <h2 class="promo-card__title">${escapePromoHtml(item.title)}</h2>
        <p class="promo-card__highlight">
          <i data-lucide="tag" aria-hidden="true"></i>
          <span>${escapePromoHtml(item.highlight)}</span>
        </p>
        <ul class="promo-card__bullets">
          ${bullets.map((bullet) => `<li>${escapePromoHtml(bullet)}</li>`).join('')}
        </ul>
        ${
          item.promoCode || item.validUntil
            ? `<p class="promo-card__note">
                ${item.promoCode ? `รหัสโปรโมชัน <strong>${escapePromoHtml(item.promoCode)}</strong>` : ''}
                ${item.validUntil ? `<span>${escapePromoHtml(item.validUntil)}</span>` : ''}
              </p>`
            : ''
        }
        <a href="${escapePromoHtml(item.ctaHref)}" class="btn btn--primary promo-card__cta">${escapePromoHtml(item.cta)}</a>
      </div>
    </article>`;
}

let promoCountdownIntervalId = null;

function initPromotionCountdowns() {
  const end = new Date();
  end.setDate(end.getDate() + 7);

  const tick = () => {
    const now = Date.now();
    const diff = Math.max(0, end.getTime() - now);
    const days = Math.floor(diff / 86400000);
    const hours = Math.floor((diff % 86400000) / 3600000);
    const minutes = Math.floor((diff % 3600000) / 60000);
    const seconds = Math.floor((diff % 60000) / 1000);

    document.querySelectorAll('[data-promo-countdown]').forEach((el) => {
      const daysEl = el.querySelector('[data-promo-unit="days"]');
      const hoursEl = el.querySelector('[data-promo-unit="hours"]');
      const minutesEl = el.querySelector('[data-promo-unit="minutes"]');
      const secondsEl = el.querySelector('[data-promo-unit="seconds"]');

      if (daysEl) daysEl.textContent = String(days);
      if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
      if (minutesEl) minutesEl.textContent = String(minutes).padStart(2, '0');
      if (secondsEl) secondsEl.textContent = String(seconds).padStart(2, '0');
    });
  };

  tick();

  if (promoCountdownIntervalId) {
    window.clearInterval(promoCountdownIntervalId);
  }
  promoCountdownIntervalId = window.setInterval(tick, 1000);
}

function initPromotionListing() {
  const root = document.getElementById('promotionPage');
  if (!root || typeof PROMOTION_ITEMS === 'undefined') return;

  const filtersEl = root.querySelector('[data-promo-filters]');
  const listEl = root.querySelector('[data-promo-list]');
  const countEl = root.querySelector('[data-promo-count]');
  const sortEl = root.querySelector('[data-promo-sort]');
  if (!filtersEl || !listEl || !countEl) return;

  let activeCategory = 'all';

  const renderFilters = () => {
    filtersEl.innerHTML = (PROMOTION_FILTERS || [])
      .filter((filter) => filter.id !== 'all')
      .map(
        (filter) => `
        <button type="button"
          class="promo-filter${activeCategory === filter.id ? ' is-active' : ''}"
          data-promo-filter="${escapePromoHtml(filter.id)}">
          ${escapePromoHtml(filter.label)}
        </button>`
      )
      .join('');
  };

  const getFilteredItems = () => {
    let items =
      typeof getPromotionItems === 'function'
        ? getPromotionItems(activeCategory === 'all' ? 'all' : activeCategory)
        : PROMOTION_ITEMS;

    if (activeCategory !== 'all') {
      items = items.filter((item) => item.categoryKey === activeCategory);
    }

    const sort = sortEl?.value || 'recommended';
    items = [...items];

    if (sort === 'name-asc') {
      items.sort((a, b) => a.title.localeCompare(b.title, 'th'));
    } else if (sort === 'category') {
      items.sort((a, b) => a.category.localeCompare(b.category, 'th'));
    } else {
      items.sort((a, b) => Number(b.popular) - Number(a.popular) || a.title.localeCompare(b.title, 'th'));
    }

    return items;
  };

  const renderList = () => {
    const items = getFilteredItems();
    countEl.textContent = `เลือกจาก ${items.length} โปรโมชัน`;
    listEl.innerHTML = items.map(renderPromotionCard).join('') || '<p class="promo-list__empty">ไม่พบโปรโมชันในหมวดนี้</p>';

    if (typeof initLucideIcons === 'function') initLucideIcons();
    initPromotionCountdowns();
  };

  renderFilters();
  renderList();

  filtersEl.addEventListener('click', (event) => {
    const button = event.target.closest('[data-promo-filter]');
    if (!button) return;
    const next = button.dataset.promoFilter || 'all';
    activeCategory = activeCategory === next ? 'all' : next;
    renderFilters();
    renderList();
  });

  sortEl?.addEventListener('change', renderList);
}
