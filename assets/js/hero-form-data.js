const HERO_INSURANCE_OPTIONS = [
  { value: 'life-accident', label: 'ประกันชีวิตและอุบัติเหตุ' },
  { value: 'health', label: 'ประกันสุขภาพ' },
  { value: 'health-20', label: 'สุขภาพระยะยาว 20 ปี' },
  { value: 'critical', label: 'ประกันโรคร้ายแรง' },
  { value: 'investment-linked', label: 'ประกันชีวิตควบการลงทุน' },
  { value: 'savings', label: 'ประกันสะสมทรัพย์' },
];

const PLAN_NAV_ICONS = {
  'life-accident': 'heart-handshake',
  health: 'shield-plus',
  'health-20': 'heart-pulse',
  critical: 'activity',
  'investment-linked': 'chart-no-axes-combined',
  savings: 'wallet',
};

const PLAN_NAV_MEGA_MENU = [
  {
    groups: [
      {
        category: 'health',
        title: 'ประกันสุขภาพ',
        viewAllHref: 'plan-category.html?category=health',
        products: [
          { id: 'easy-e-health', label: 'Easy E-Health', href: 'plan-details.html?id=easy-e-health' },
          { id: 'fwd-precious-care', label: 'เอฟดับบลิวดี พรีเชียส แคร์', href: 'plan-details.html?id=fwd-precious-care' },
          { id: 'opd-plus', label: 'โอพีดีพลัส', href: 'plan-details.html?id=opd-plus' },
        ],
      },
      {
        category: 'critical',
        title: 'ประกันโรคร้ายแรง',
        viewAllHref: 'plan-category.html?category=critical',
        products: [
          { id: 'big-3-critical', label: 'Big 3 คุ้มครอง 3 กลุ่มโรคร้ายแรง', href: 'plan-details.html?id=big-3-critical' },
          { id: 'ci-reclaim-recare', label: 'CI Re-Claim Re-Care', href: 'plan-details.html?id=ci-reclaim-recare' },
          { id: 'whole-life-critical', label: 'ตลอดชีพคุ้มครองโรคร้ายแรง', href: 'plan-details.html?id=whole-life-critical' },
        ],
      },
    ],
  },
  {
    groups: [
      {
        category: 'life-accident',
        title: 'ประกันชีวิตและอุบัติเหตุ',
        viewAllHref: 'plan-category.html?category=life-accident',
        products: [
          { id: 'easy-e-life', label: 'Easy E-Life', href: 'plan-details.html?id=easy-e-life' },
          { id: 'khum-chivee-extra', label: 'คุ้มชีวีเอ็กซ์ตร้า', href: 'plan-details.html?id=khum-chivee-extra' },
          { id: 'khum-krong-kha-khum-phai', label: 'คุ้มครอง คุ้มค่า คุ้มภัย', href: 'plan-details.html?id=khum-krong-kha-khum-phai' },
        ],
      },
      {
        category: 'savings',
        title: 'ประกันสะสมทรัพย์',
        viewAllHref: 'plan-category.html?category=savings',
        products: [
          { id: 'easy-e-save-10-5', label: 'Easy E-Save 10/5', href: 'plan-details.html?id=easy-e-save-10-5' },
          { id: 'fwd-for-saving-25-15', label: 'เอฟดับบลิวดี ฟอร์ เซฟวิ่ง 25/15', href: 'plan-details.html?id=fwd-for-saving-25-15' },
          { id: 'fwd-for-saving-20-10', label: 'เอฟดับบลิวดี ฟอร์ เซฟวิ่ง 20/10', href: 'plan-details.html?id=fwd-for-saving-20-10' },
        ],
      },
    ],
  },
  {
    groups: [
      {
        category: 'investment-linked',
        title: 'ประกันชีวิตควบการลงทุน',
        viewAllHref: 'plan-category.html?category=investment-linked',
        products: [
          { id: 'fwd-ultra-link-99-99', label: 'เอฟดับบลิวดี อัลตร้า ลิงค์ 99/99', href: 'plan-details.html?id=fwd-ultra-link-99-99' },
          { id: 'fwd-future-link-99-9', label: 'เอฟดับบลิวดี ฟิวเจอร์ ลิงค์ 99/9', href: 'plan-details.html?id=fwd-future-link-99-9' },
          { id: 'fwd-freedom-link-plus-15-5', label: 'เอฟดับบลิวดี ฟรีดอม ลิงค์ พลัส 15/5', href: 'plan-details.html?id=fwd-freedom-link-plus-15-5' },
        ],
      },
    ],
  },
];

const SERVICE_NAV_MEGA_MENU = [
  {
    groups: [
      {
        title: 'บริการลูกค้า',
        products: [
          { label: 'โรงพยาบาลในเครือ', href: 'hospitals.html' },
          { label: 'แจ้งเคลม / สอบถามกรมธรรม์', href: 'contact.html' },
          { label: 'คำถามที่พบบ่อย', href: 'faq.html' },
        ],
      },
    ],
  },
  {
    groups: [
      {
        title: 'ติดต่อและสิทธิพิเศษ',
        products: [
          { label: 'ติดต่อเรา', href: 'contact.html' },
          { label: 'โปรโมชั่นและสิทธิพิเศษ', href: 'promotion.html' },
        ],
      },
    ],
  },
];

const NAV_MEGA_MENUS = {
  products: PLAN_NAV_MEGA_MENU,
  services: SERVICE_NAV_MEGA_MENU,
};

const THAI_PROVINCES = [
  'กระบี่',
  'กรุงเทพมหานคร',
  'กาญจนบุรี',
  'กาฬสินธุ์',
  'กำแพงเพชร',
  'ขอนแก่น',
  'จันทบุรี',
  'ฉะเชิงเทรา',
  'ชลบุรี',
  'ชัยนาท',
  'ชัยภูมิ',
  'ชุมพร',
  'เชียงราย',
  'เชียงใหม่',
  'ตรัง',
  'ตราด',
  'ตาก',
  'นครนายก',
  'นครปฐม',
  'นครพนม',
  'นครราชสีมา',
  'นครศรีธรรมราช',
  'นครสวรรค์',
  'นนทบุรี',
  'นราธิวาส',
  'น่าน',
  'บึงกาฬ',
  'บุรีรัมย์',
  'ปทุมธานี',
  'ประจวบคีรีขันธ์',
  'ปราจีนบุรี',
  'ปัตตานี',
  'พระนครศรีอยุธยา',
  'พะเยา',
  'พังงา',
  'พัทลุง',
  'พิจิตร',
  'พิษณุโลก',
  'เพชรบุรี',
  'เพชรบูรณ์',
  'แพร่',
  'ภูเก็ต',
  'มหาสารคาม',
  'มุกดาหาร',
  'แม่ฮ่องสอน',
  'ยโสธร',
  'ยะลา',
  'ร้อยเอ็ด',
  'ระนอง',
  'ระยอง',
  'ราชบุรี',
  'ลพบุรี',
  'ลำปาง',
  'ลำพูน',
  'เลย',
  'ศรีสะเกษ',
  'สกลนคร',
  'สงขลา',
  'สตูล',
  'สมุทรปราการ',
  'สมุทรสงคราม',
  'สมุทรสาคร',
  'สระแก้ว',
  'สระบุรี',
  'สิงห์บุรี',
  'สุโขทัย',
  'สุพรรณบุรี',
  'สุราษฎร์ธานี',
  'สุรินทร์',
  'หนองคาย',
  'หนองบัวลำภู',
  'อ่างทอง',
  'อำนาจเจริญ',
  'อุดรธานี',
  'อุตรดิตถ์',
  'อุทัยธานี',
  'อุบลราชธานี',
];

function initHeroFormSelects() {
  const insuranceSelect = document.getElementById('hero-insurance');
  const provinceSelect = document.getElementById('hero-province');

  if (insuranceSelect) {
    const selected = insuranceSelect.dataset.selected || insuranceSelect.value || 'health-20';
    insuranceSelect.innerHTML = '';

    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = 'เลือกประเภทประกัน';
    insuranceSelect.appendChild(placeholder);

    HERO_INSURANCE_OPTIONS.forEach(({ value, label }) => {
      const option = document.createElement('option');
      option.value = value;
      option.textContent = label;
      insuranceSelect.appendChild(option);
    });

    insuranceSelect.value = HERO_INSURANCE_OPTIONS.some((item) => item.value === selected)
      ? selected
      : 'health-20';
  }

  if (provinceSelect) {
    const selected = provinceSelect.value;
    provinceSelect.innerHTML = '';

    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = 'เลือกจังหวัด';
    provinceSelect.appendChild(placeholder);

    THAI_PROVINCES.forEach((name) => {
      const option = document.createElement('option');
      option.value = name;
      option.textContent = name;
      provinceSelect.appendChild(option);
    });

    if (selected && THAI_PROVINCES.includes(selected)) {
      provinceSelect.value = selected;
    }
  }
}

function initPlanNavDropdown() {
  initNavDropdowns();
}

function initNavDropdowns() {
  document.querySelectorAll('.header__nav-item--dropdown').forEach((item) => {
    const menuKey = item.dataset.navMenu || 'products';
    const menuData = NAV_MEGA_MENUS[menuKey];
    const submenu = item.querySelector('.header__nav-submenu');
    const trigger = item.querySelector('.header__nav-link--dropdown');
    if (!menuData || !submenu || !trigger) return;

    const buildKey = `${menuKey}-${JSON.stringify(menuData.map((col) => col.groups.map((g) => [g.title, g.viewAllHref || ''])))}`;
    if (submenu.dataset.built !== buildKey) {
      const renderGroup = (group) => `
        <div class="header__nav-mega-group">
          <div class="header__nav-mega-head">
            <span class="header__nav-mega-title">${group.title}</span>
            ${group.viewAllHref ? `<a href="${group.viewAllHref}" class="header__nav-mega-all" role="menuitem">ดูทั้งหมด</a>` : ''}
          </div>
          <ul class="header__nav-mega-list">
            ${group.products
              .map(
                (product) =>
                  `<li><a href="${product.href}" class="header__nav-mega-link" role="menuitem"><i data-lucide="arrow-right" aria-hidden="true"></i><span>${product.label}</span></a></li>`
              )
              .join('')}
          </ul>
        </div>`;

      const renderColumn = (column) =>
        `<div class="header__nav-mega-col">${column.groups.map(renderGroup).join('')}</div>`;

      const columns = menuData.map(renderColumn).join(
        '<div class="header__nav-mega-divider" aria-hidden="true"></div>'
      );

      submenu.classList.add('header__nav-submenu--mega');
      if (menuData.length === 2) {
        submenu.classList.add('header__nav-submenu--mega-2col');
      }
      submenu.innerHTML = `<div class="header__nav-mega">${columns}</div>`;
      submenu.dataset.built = buildKey;

      if (typeof lucide !== 'undefined') {
        lucide.createIcons();
      }
    }

    const positionMegaMenuArrow = () => {
      if (!submenu.classList.contains('header__nav-submenu--mega') || !item.classList.contains('is-open')) {
        return;
      }

      if (window.matchMedia('(max-width: 768px)').matches) {
        return;
      }

      const submenuRect = submenu.getBoundingClientRect();
      const triggerRect = trigger.getBoundingClientRect();
      const centerX = triggerRect.left + triggerRect.width / 2 - submenuRect.left;
      const clamped = Math.max(24, Math.min(submenuRect.width - 24, centerX));
      submenu.style.setProperty('--mega-arrow-left', `${clamped}px`);
    };

    trigger.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      const open = !item.classList.contains('is-open');
      document.querySelectorAll('.header__nav-item--dropdown.is-open').forEach((el) => {
        if (el !== item) {
          el.classList.remove('is-open');
          el.querySelector('.header__nav-link--dropdown')?.setAttribute('aria-expanded', 'false');
        }
      });
      item.classList.toggle('is-open', open);
      trigger.setAttribute('aria-expanded', String(open));

      if (open) {
        requestAnimationFrame(positionMegaMenuArrow);
      }
    });

    window.addEventListener('resize', positionMegaMenuArrow);
    window.addEventListener('scroll', positionMegaMenuArrow, { passive: true });

    document.addEventListener('click', (e) => {
      if (!item.contains(e.target)) {
        item.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        item.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
      }
    });
  });
}

function initPlanListing() {
  const grid = document.getElementById('planListingGrid');
  const countEl = document.getElementById('planListingCount');
  const emptyEl = document.getElementById('planListingEmpty');
  const sortEl = document.getElementById('planListingSort');
  const clearEl = document.getElementById('planListingClear');
  if (!grid || !countEl) return;

  if (typeof renderPlanListingCards === 'function') {
    renderPlanListingCards();
  }

  const cards = [...grid.querySelectorAll('.plan-card')];
  cards.forEach((card, index) => {
    card.dataset.order = String(index);
  });

  const chipGroups = document.querySelectorAll('.plan-filter__chips');
  const state = {
    category: 'all',
    price: 'all',
    goals: [],
    promos: [],
  };

  const priceRanges = {
    'under-500': (price) => price < 500,
    '500-1000': (price) => price >= 500 && price < 1000,
    '1000-2000': (price) => price >= 1000 && price <= 2000,
    'over-2000': (price) => price > 2000,
  };

  const getActiveValues = (groupName) => {
    const group = document.querySelector(`.plan-filter__chips[data-filter-group="${groupName}"]`);
    if (!group) return [];
    return [...group.querySelectorAll('.plan-filter__chip.is-active')].map((chip) => chip.dataset.filterValue);
  };

  const syncState = () => {
    const categoryValues = getActiveValues('category');
    state.category = categoryValues.includes('all') || !categoryValues.length ? 'all' : categoryValues[0];

    const priceValues = getActiveValues('price');
    state.price = priceValues.includes('all') || !priceValues.length ? 'all' : priceValues[0];

    state.goals = getActiveValues('goal').filter((value) => value !== 'all');
    state.promos = getActiveValues('promo').filter((value) => value !== 'all');
  };

  const cardMatches = (card) => {
    const category = card.dataset.category || '';
    const price = Number(card.dataset.price || 0);
    const goals = (card.dataset.goals || '').split(',').filter(Boolean);
    const promo = card.dataset.promo || '';

    if (state.category !== 'all' && category !== state.category) return false;
    if (state.price !== 'all' && !priceRanges[state.price]?.(price)) return false;
    if (state.goals.length && !state.goals.some((goal) => goals.includes(goal))) return false;
    if (state.promos.length && !state.promos.some((item) => promo.includes(item))) return false;

    return true;
  };

  const sortCards = () => {
    const visible = cards.filter((card) => !card.classList.contains('is-hidden'));
    const hidden = cards.filter((card) => card.classList.contains('is-hidden'));
    const sortValue = sortEl?.value || 'recommended';
    const sorted = [...visible];

    if (sortValue === 'price-asc') {
      sorted.sort((a, b) => Number(a.dataset.price || 0) - Number(b.dataset.price || 0));
    } else if (sortValue === 'price-desc') {
      sorted.sort((a, b) => Number(b.dataset.price || 0) - Number(a.dataset.price || 0));
    } else if (sortValue === 'name-asc') {
      sorted.sort((a, b) =>
        (a.querySelector('.plan-card__title')?.textContent || '').localeCompare(
          b.querySelector('.plan-card__title')?.textContent || '',
          'th'
        )
      );
    } else {
      sorted.sort((a, b) => Number(a.dataset.order || 0) - Number(b.dataset.order || 0));
    }

    [...sorted, ...hidden].forEach((card) => grid.appendChild(card));
    if (emptyEl) {
      grid.insertBefore(emptyEl, grid.firstChild);
    }
  };

  const applyFilters = () => {
    syncState();
    const visible = [];

    cards.forEach((card) => {
      const show = cardMatches(card);
      card.classList.toggle('is-hidden', !show);
      if (show) visible.push(card);
    });

    sortCards();
    countEl.textContent = String(visible.length);
    emptyEl?.classList.toggle('is-visible', visible.length === 0);

    if (clearEl) {
      const isDefault =
        state.category === 'all' &&
        state.price === 'all' &&
        state.goals.length === 0 &&
        state.promos.length === 0 &&
        (sortEl?.value || 'recommended') === 'recommended';
      clearEl.disabled = isDefault;
    }
  };

  const resetFilters = () => {
    chipGroups.forEach((group) => {
      const groupName = group.dataset.filterGroup;
      group.querySelectorAll('.plan-filter__chip').forEach((chip) => {
        chip.classList.remove('is-active');
      });
      if (groupName === 'category' || groupName === 'price') {
        group.querySelector('[data-filter-value="all"]')?.classList.add('is-active');
      }
    });

    if (sortEl) sortEl.value = 'recommended';

    const url = new URL(window.location.href);
    if (url.searchParams.has('category')) {
      url.searchParams.delete('category');
      window.history.replaceState({}, '', url);
    }

    applyFilters();
  };

  chipGroups.forEach((group) => {
    const groupName = group.dataset.filterGroup;
    const singleSelect = groupName === 'category' || groupName === 'price';

    group.addEventListener('click', (e) => {
      const chip = e.target.closest('.plan-filter__chip');
      if (!chip) return;

      if (singleSelect) {
        group.querySelectorAll('.plan-filter__chip').forEach((el) => el.classList.remove('is-active'));
        chip.classList.add('is-active');
      } else {
        chip.classList.toggle('is-active');
      }

      applyFilters();
    });
  });

  sortEl?.addEventListener('change', applyFilters);
  clearEl?.addEventListener('click', resetFilters);

  const params = new URLSearchParams(window.location.search);
  const categoryParam = params.get('category');
  if (categoryParam) {
    const categoryChip = document.querySelector(
      `.plan-filter__chips[data-filter-group="category"] .plan-filter__chip[data-filter-value="${categoryParam}"]`
    );
    if (categoryChip) {
      document
        .querySelectorAll('.plan-filter__chips[data-filter-group="category"] .plan-filter__chip')
        .forEach((el) => el.classList.remove('is-active'));
      categoryChip.classList.add('is-active');
    }
  }

  applyFilters();
}
