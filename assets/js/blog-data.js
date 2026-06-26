const BLOG_CATEGORIES = [
  { value: 'all', label: 'ทั้งหมด' },
  { value: 'life', label: 'ประกันชีวิต' },
  { value: 'health', label: 'ประกันสุขภาพ' },
  { value: 'planning', label: 'การวางแผนการเงิน' },
  { value: 'claim', label: 'การเคลมและบริการ' },
];

const BLOG_ARTICLES = [
  {
    id: 'life-at-30',
    title: '5 เหตุผลที่ควรมีประกันชีวิตตั้งแต่อายุ 30',
    excerpt:
      'ทำไมการวางแผนประกันชีวิตตั้งแต่เนิ่นๆ ถึงเป็นสิ่งสำคัญ ช่วยสร้างหลักประกันทางการเงินให้ครอบครัวเมื่อเกิดเหตุไม่คาดฝัน',
    date: '2025-05-12',
    dateLabel: '12 พ.ค. 2568',
    category: 'life',
    categoryLabel: 'ประกันชีวิต',
    tags: ['ประกันชีวิต', 'วางแผนการเงิน', 'ครอบครัว'],
    image: 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=800&q=80',
    views: 2840,
    content: [
      {
        type: 'p',
        text: 'อายุ 30 ปี เป็นช่วงเวลาที่หลายคนเริ่มมีครอบครัว มีภาระสินเชื่อ และต้องวางแผนอนาคตไปพร้อมกัน การมีประกันชีวิตตั้งแต่เนิ่นๆ จึงไม่ใช่เรื่องไกลตัว แต่เป็นรากฐานสำคัญของการเงินที่ช่วยให้คุณและคนที่คุณรักมีความมั่นใจเมื่อเกิดเหตุไม่คาดฝัน',
      },
      {
        type: 'h2',
        text: '1. เบี้ยประกันถูกกว่าเมื่อซื้อตั้งแต่อายุน้อย',
      },
      {
        type: 'p',
        text: 'ยิ่งซื้อประกันชีวิตเร็วเท่าไร โอกาสที่เบี้ยจะถูกกว่าก็ยิ่งสูง เพราะบริษัทประกันมักประเมินความเสี่ยงจากอายุและสุขภาพ การเริ่มวางแผนตั้งแต่อายุ 30 ปี ช่วยให้คุณได้ความคุ้มครองสูงในราคาที่จัดการได้ง่ายกว่าเมื่ออายุมากขึ้น',
      },
      {
        type: 'h2',
        text: '2. สร้างหลักประกันให้ครอบครัว',
      },
      {
        type: 'p',
        text: 'หากเกิดเหตุสูญเสียรายได้หลักของครอบครัว ประกันชีวิตช่วยให้คนที่คุณรักยังสามารถใช้ชีวิตต่อไปได้โดยไม่ต้องแบกภาระหนี้สินหรือค่าใช้จ่ายที่ท่วมท้น เงินก้อนนี้สามารถนำไปใช้ชำระสินเชื่อบ้าน ค่าเลี้ยงดูบุตร หรือค่าใช้จ่ายในชีวิตประจำวัน',
      },
      {
        type: 'h2',
        text: '3. วางแผนการเงินได้ยืดหยุ่น',
      },
      {
        type: 'p',
        text: 'ประกันชีวิตสามารถออกแบบให้สอดคล้องกับเป้าหมายของคุณ ไม่ว่าจะเน้นคุ้มครองอย่างเดียว หรือผสมการออมและลดหย่อนภาษี การปรึกษากับตัวแทนที่เข้าใจผลิตภัณฑ์ FWD จะช่วยให้คุณเลือกแผนที่เหมาะกับไลฟ์สไตล์และงบประมาณจริง',
      },
    ],
  },
  {
    id: 'choose-health-insurance',
    title: 'วิธีเลือกประกันสุขภาพที่เหมาะกับคุณ',
    excerpt:
      'เคล็ดลับการเลือกแผนประกันสุขภาพให้คุ้มค่าที่สุด เปรียบเทียบความคุ้มครอง วงเงิน และข้อยกเว้นก่อนตัดสินใจ',
    date: '2025-04-28',
    dateLabel: '28 เม.ย. 2568',
    category: 'health',
    categoryLabel: 'ประกันสุขภาพ',
    tags: ['ประกันสุขภาพ', 'Easy E-Health', 'OPD'],
    image: 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=800&q=80',
    views: 1965,
    content: [
      {
        type: 'p',
        text: 'ค่ารักษาพยาบาลในปัจจุบันเพิ่มสูงขึ้นอย่างต่อเนื่อง การมีประกันสุขภาพจึงเป็นเกราะป้องกันที่ช่วยลดภาระค่าใช้จ่ายเมื่อป่วย แต่การเลือกแผนที่เหมาะสมต้องดูมากกว่าแค่เบี้ยต่อเดือน',
      },
      {
        type: 'h2',
        text: 'เปรียบเทียบความคุ้มครอง IPD และ OPD',
      },
      {
        type: 'p',
        text: 'แผนที่ดีควรระบุวงเงินคุ้มครองค่าห้อง ค่าผ่าตัด และค่ารักษาผู้ป่วยนอก (OPD) อย่างชัดเจน หากคุณต้องพบแพทย์บ่อย ควรเลือกแผนที่มี OPD หรือ Easy E-Health ที่ออกแบบมาสำหรับการใช้งานจริงในชีวิตประจำวัน',
      },
      {
        type: 'h2',
        text: 'อ่านข้อยกเว้นให้ครบ',
      },
      {
        type: 'p',
        text: 'ข้อยกเว้นสำคัญเช่น โรคเรื้อรังที่มีอยู่ก่อนทำประกัน ระยะเวลารอคอย หรือการคุ้มครองโรคเฉพาะ ควรอ่านและสอบถามตัวแทนให้เข้าใจก่อนลงนาม จะช่วยลดความผิดหวังเมื่อต้องเคลมจริง',
      },
    ],
  },
  {
    id: 'family-financial-plan',
    title: 'การวางแผนการเงินสำหรับครอบครัว',
    excerpt:
      'แนวทางการวางแผนการเงินที่มั่นคงสำหรับทุกครอบครัว ตั้งเป้าหมายออมทรัพย์และจัดสรรงบประมาณอย่างสมดุล',
    date: '2025-03-15',
    dateLabel: '15 มี.ค. 2568',
    category: 'planning',
    categoryLabel: 'การวางแผนการเงิน',
    tags: ['การวางแผนการเงิน', 'ครอบครัว', 'ออมทรัพย์'],
    image: 'https://images.unsplash.com/photo-1554224311-beee415c201f?w=800&q=80',
    views: 1520,
    content: [
      {
        type: 'p',
        text: 'ครอบครัวที่มั่นคงเริ่มจากการวางแผนการเงินที่ชัดเจน ไม่ว่าจะเป็นการสำรองฉุกเฉิน การวางแผนการศึกษาบุตร หรือการเตรียมเกษียณ ประกันชีวิตและสุขภาพเป็นส่วนหนึ่งของแผนนี้ที่ช่วยป้องกันความเสี่ยงใหญ่',
      },
      {
        type: 'h2',
        text: 'แบ่งงบเป็น 3 ส่วนหลัก',
      },
      {
        type: 'p',
        text: 'แนวทางที่นิยมคือจัดสรรรายได้เป็น 50% ค่าใช้จ่ายจำเป็น 30% ความต้องการส่วนตัว และ 20% ออมและลงทุน ส่วนของการออมควรมีทั้งเงินสดฉุกเฉิน ประกันคุ้มครอง และการลงทุนระยะยาว',
      },
      {
        type: 'h2',
        text: 'ทบทวนแผนทุกปี',
      },
      {
        type: 'p',
        text: 'เมื่อมีเหตุการณ์สำคัญ เช่น มีบุตร ซื้อบ้าน หรือเปลี่ยนงาน ควรทบทวนความคุ้มครองและวงเงินออมใหม่ ตัวแทน FWD สามารถช่วยประเมินว่าแผนปัจจุบันยังเหมาะสมหรือควรปรับเพิ่ม',
      },
    ],
  },
  {
    id: 'savings-vs-deposit',
    title: 'ประกันออมทรัพย์ vs เงินฝากธนาคาร',
    excerpt:
      'เปรียบเทียบข้อดีข้อเสียของแต่ละตัวเลือก ทั้งผลตอบแทน ความยืดหยุ่น และความคุ้มครองระยะยาว',
    date: '2025-02-08',
    dateLabel: '8 ก.พ. 2568',
    category: 'planning',
    categoryLabel: 'การวางแผนการเงิน',
    tags: ['ประกันออมทรัพย์', 'การลงทุน', 'ลดหย่อนภาษี'],
    image: 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=800&q=80',
    views: 1288,
    content: [
      {
        type: 'p',
        text: 'หลายคนเคยสงสัยว่าควรเก็บเงินในธนาคารหรือทำประกันออมทรัพย์ดี คำตอบขึ้นอยู่กับเป้าหมาย ระยะเวลา และความต้องการความคุ้มครองควบคู่ไปด้วยหรือไม่',
      },
      {
        type: 'h2',
        text: 'เงินฝากธนาคาร: สภาพคล่องสูง',
      },
      {
        type: 'p',
        text: 'เงินฝากเหมาะกับเป้าหมายระยะสั้นและเงินฉุกเฉิน เพราะถอนได้ทันทีและมีความเสี่ยงต่ำ แต่ผลตอบแทนอาจต่ำกว่าเงินเฟ้อในระยะยาว',
      },
      {
        type: 'h2',
        text: 'ประกันออมทรัพย์: วางแผนระยะยาวพร้อมคุ้มครอง',
      },
      {
        type: 'p',
        text: 'แผนอย่าง Easy E-Save หรือ FWD For Saving ช่วยบังคับออมในระยะยาว พร้อมความคุ้มครองชีวิต และอาจได้สิทธิลดหย่อนภาษี จึงเหมาะกับเป้าหมายเกษียณหรือการศึกษาบุตรมากกว่าเงินฝากทั่วไป',
      },
    ],
  },
  {
    id: 'life-insurance-gen-z',
    title: 'ประกันชีวิตสำหรับคนรุ่นใหม่',
    excerpt:
      'ทำไม Gen Z และ Millennial ถึงควรเริ่มวางแผนประกันชีวิตตั้งแต่เนิ่นๆ เพื่อลดภาระและสร้างความมั่นคงในอนาคต',
    date: '2025-01-20',
    dateLabel: '20 ม.ค. 2568',
    category: 'life',
    categoryLabel: 'ประกันชีวิต',
    tags: ['ประกันชีวิต', 'Gen Z', 'Easy E-Life'],
    image: 'https://images.unsplash.com/photo-1511895426328-dc8714191300?w=800&q=80',
    views: 2145,
    content: [
      {
        type: 'p',
        text: 'คนรุ่นใหม่มักคิดว่าประกันชีวิตเป็นเรื่องของวัยทำงานที่อายุมากแล้ว แต่ความจริงการเริ่มเร็วช่วยให้ได้เบี้ยถูก สุขภาพดี และมีเวลาสะสมความคุ้มครองนานขึ้น',
      },
      {
        type: 'h2',
        text: 'เริ่มจากความคุ้มครองพื้นฐาน',
      },
      {
        type: 'p',
        text: 'แผนอย่าง Easy E-Life หรือคุ้มครอง คุ้มค่า คุ้มภัย เหมาะกับผู้เริ่มต้นทำงานที่ต้องการเบี้ยไม่สูง แต่ได้ความคุ้มครองชีวิตและอุบัติเหตุที่จำเป็น สามารถปรับเพิ่มเมื่อรายได้เติบโต',
      },
      {
        type: 'h2',
        text: 'ผสมประกันสุขภาพตั้งแต่เนิ่นๆ',
      },
      {
        type: 'p',
        text: 'การมีประกันสุขภาพควบคู่กับประกันชีวิตช่วยลดความเสี่ยงด้านค่ารักษา ซึ่งเป็นหนึ่งในสาเหตุหลักที่ทำให้แผนการเงินล้มเหลว ปรึกษาตัวแทนเพื่อจัดชุดความคุ้มครองที่สมดุลกับงบประมาณ',
      },
    ],
  },
  {
    id: 'claim-insurance-fast',
    title: 'เคลมประกันอย่างไรให้ได้เงินเร็ว',
    excerpt:
      'ขั้นตอนและเอกสารที่ต้องเตรียมสำหรับการเคลมประกันให้รวดเร็ว ลดความล่าช้าและได้รับเงินตามสิทธิ์ครบถ้วน',
    date: '2024-12-05',
    dateLabel: '5 ธ.ค. 2567',
    category: 'claim',
    categoryLabel: 'การเคลมและบริการ',
    tags: ['การเคลม', 'บริการลูกค้า', 'เอกสาร'],
    image: 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=800&q=80',
    views: 1732,
    content: [
      {
        type: 'p',
        text: 'การเคลมประกันไม่ยากอย่างที่คิด หากเตรียมเอกสารครบและเข้าใจขั้นตอน การติดต่อตัวแทนหรือศูนย์บริการลูกค้าตั้งแต่เนิ่นๆ จะช่วยให้กระบวนการราบรื่นขึ้น',
      },
      {
        type: 'h2',
        text: 'เอกสารที่มักต้องใช้',
      },
      {
        type: 'p',
        text: 'โดยทั่วไปต้องใช้ใบเคลมที่กรอกครบ สำเนาบัตรประชาชน ใบรับรองแพทย์หรือใบเสร็จค่ารักษา และสำเนากรมธรรม์ กรณีเสียชีวิตอาจต้องใช้ใบมรณบัตรและหลักฐานความสัมพันธ์ผู้รับผลประโยชน์',
      },
      {
        type: 'h2',
        text: 'ติดตามสถานะอย่างสม่ำเสมอ',
      },
      {
        type: 'p',
        text: 'หลังยื่นเคลมแล้ว ควรเก็บเลขที่อ้างอิงและติดตามผลผ่านตัวแทนหรือช่องทางออนไลน์ของ FWD หากเอกสารไม่ครบ บริษัทจะแจ้งให้เพิ่มเติม การตอบกลับรวดเร็วจะช่วยให้ได้รับเงินเร็วขึ้น',
      },
    ],
  },
];

function getBlogArticle(id) {
  return BLOG_ARTICLES.find((article) => article.id === id) || null;
}

function formatBlogViews(count) {
  return Number(count || 0).toLocaleString('th-TH');
}

function renderBlogCard(article) {
  const searchText = [article.title, article.excerpt, article.categoryLabel, ...(article.tags || [])]
    .join(' ')
    .toLowerCase();
  const articleUrl = `blog-details.html?id=${article.id}`;
  const safeTitle = article.title.replace(/"/g, '&quot;');

  return `
    <article class="plan-card blog-card" data-category="${article.category}" data-date="${article.date}" data-search="${searchText.replace(/"/g, '&quot;')}" data-order="${BLOG_ARTICLES.indexOf(article)}">
      <a href="${articleUrl}" class="blog-card__image-link plan-card__image">
        <img src="${article.image}" alt="" width="313" height="174" loading="lazy">
      </a>
      <div class="plan-card__body">
        <span class="blog-card__category">${article.categoryLabel}</span>
        <time class="plan-card__date" datetime="${article.date}">${article.dateLabel}</time>
        <h3 class="plan-card__title"><a href="${articleUrl}" class="blog-card__title-link">${article.title}</a></h3>
        <p class="plan-card__desc">${article.excerpt}</p>
        <div class="blog-card__meta">
          <span class="blog-card__stat blog-card__stat--views">
            <i data-lucide="eye" aria-hidden="true"></i>
            <span>${formatBlogViews(article.views)}</span>
          </span>
          <button type="button" class="blog-card__stat blog-card__share" data-share-url="${articleUrl}" data-share-title="${safeTitle}" aria-label="แชร์ ${safeTitle}">
            <i data-lucide="share-2" aria-hidden="true"></i>
            <span>แชร์</span>
          </button>
        </div>
        <a href="${articleUrl}" class="plan-card__btn">อ่านบทความ <i data-lucide="arrow-right"></i></a>
      </div>
    </article>`;
}

function initBlogCardShare(container) {
  if (!container) return;

  container.querySelectorAll('.blog-card__share').forEach((btn) => {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      e.stopPropagation();

      const url = new URL(btn.dataset.shareUrl || '', window.location.href).href;
      const title = btn.dataset.shareTitle || document.title;
      const label = btn.querySelector('span');

      if (navigator.share) {
        try {
          await navigator.share({ title, url });
          return;
        } catch (err) {
          if (err?.name === 'AbortError') return;
        }
      }

      try {
        await navigator.clipboard.writeText(url);
        if (label) {
          const original = label.textContent;
          label.textContent = 'คัดลอกแล้ว';
          setTimeout(() => {
            label.textContent = original;
          }, 2000);
        }
      } catch {
        window.prompt('คัดลอกลิงก์บทความ:', url);
      }
    });
  });
}

function initBlogListing() {
  const grid = document.getElementById('blogListingGrid');
  const countEl = document.getElementById('blogListingCount');
  const emptyEl = document.getElementById('blogListingEmpty');
  const searchEl = document.getElementById('blogListingSearch');
  const clearEl = document.getElementById('blogListingClear');
  const sortEl = document.getElementById('blogListingSort');
  if (!grid || !countEl) return;

  if (!grid.querySelector('.blog-card')) {
    grid.insertAdjacentHTML('beforeend', BLOG_ARTICLES.map(renderBlogCard).join(''));
    initBlogCardShare(grid);
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }
  }

  const cards = [...grid.querySelectorAll('.blog-card')];
  let category = 'all';
  let query = '';

  const params = new URLSearchParams(window.location.search);
  const urlCategory = params.get('category');
  if (urlCategory && BLOG_CATEGORIES.some((item) => item.value === urlCategory)) {
    category = urlCategory;
    document.querySelectorAll('.blog-filter__chip[data-filter-value]').forEach((chip) => {
      chip.classList.toggle('is-active', chip.dataset.filterValue === category);
    });
  }

  const cardMatches = (card) => {
    const cardCategory = card.dataset.category || '';
    const searchBlob = card.dataset.search || '';
    if (category !== 'all' && cardCategory !== category) return false;
    if (query && !searchBlob.includes(query)) return false;
    return true;
  };

  const sortCards = (visible) => {
    const sortValue = sortEl?.value || 'newest';
    const sorted = [...visible];
    if (sortValue === 'oldest') {
      sorted.sort((a, b) => (a.dataset.date || '').localeCompare(b.dataset.date || ''));
    } else if (sortValue === 'title') {
      sorted.sort((a, b) =>
        (a.querySelector('.plan-card__title')?.textContent || '').localeCompare(
          b.querySelector('.plan-card__title')?.textContent || '',
          'th'
        )
      );
    } else {
      sorted.sort((a, b) => (b.dataset.date || '').localeCompare(a.dataset.date || ''));
    }
    const hidden = cards.filter((card) => card.classList.contains('is-hidden'));
    [...sorted, ...hidden].forEach((card) => grid.appendChild(card));
    if (emptyEl) {
      grid.insertBefore(emptyEl, grid.firstChild);
    }
  };

  const applyFilters = () => {
    query = (searchEl?.value || '').trim().toLowerCase();
    const visible = [];

    cards.forEach((card) => {
      const show = cardMatches(card);
      card.classList.toggle('is-hidden', !show);
      if (show) visible.push(card);
    });

    sortCards(visible);
    countEl.textContent = String(visible.length);
    emptyEl?.classList.toggle('is-visible', visible.length === 0);

    if (clearEl) {
      clearEl.disabled = category === 'all' && !query && (sortEl?.value || 'newest') === 'newest';
    }
  };

  document.querySelectorAll('.blog-filter__chip').forEach((chip) => {
    chip.addEventListener('click', () => {
      document.querySelectorAll('.blog-filter__chip').forEach((item) => item.classList.remove('is-active'));
      chip.classList.add('is-active');
      category = chip.dataset.filterValue || 'all';
      applyFilters();
    });
  });

  searchEl?.addEventListener('input', applyFilters);
  sortEl?.addEventListener('change', applyFilters);

  clearEl?.addEventListener('click', () => {
    category = 'all';
    if (searchEl) searchEl.value = '';
    if (sortEl) sortEl.value = 'newest';
    document.querySelectorAll('.blog-filter__chip').forEach((chip) => {
      chip.classList.toggle('is-active', chip.dataset.filterValue === 'all');
    });
    applyFilters();
  });

  applyFilters();
}

function initBlogDetail() {
  const root = document.getElementById('blogDetail');
  if (!root) return;

  const params = new URLSearchParams(window.location.search);
  const id = params.get('id');
  const article = getBlogArticle(id) || BLOG_ARTICLES[0];

  document.title = `${article.title} - Agent Thailand`;

  const setText = (selector, text) => {
    const el = root.querySelector(selector);
    if (el) el.textContent = text;
  };

  setText('[data-blog-date]', article.dateLabel);
  root.querySelector('[data-blog-date]')?.setAttribute('datetime', article.date);

  document.querySelectorAll('[data-blog-title]').forEach((el) => {
    el.textContent = article.title;
  });

  const categoryLink = root.querySelector('[data-blog-category-link]');
  if (categoryLink) {
    categoryLink.href = `blog.html?category=${article.category}`;
    categoryLink.textContent = article.categoryLabel;
  }

  const tagsEl = root.querySelector('[data-blog-tags]');
  if (tagsEl) {
    tagsEl.innerHTML = (article.tags || [])
      .map((tag) => `<a href="blog.html" class="blog-detail__tag">${tag}</a>`)
      .join('');
  }

  const heroImg = root.querySelector('[data-blog-image]');
  if (heroImg) {
    heroImg.src = article.image;
    heroImg.alt = article.title;
  }

  const bodyEl = root.querySelector('[data-blog-body]');
  if (bodyEl) {
    bodyEl.innerHTML = (article.content || [])
      .map((block) => {
        if (block.type === 'h2') return `<h2>${block.text}</h2>`;
        return `<p>${block.text}</p>`;
      })
      .join('');
  }

  const relatedGrid = document.getElementById('blogRelatedGrid');
  if (relatedGrid) {
    const related = BLOG_ARTICLES.filter((item) => item.id !== article.id)
      .sort((a, b) => {
        const aMatch = a.category === article.category ? 1 : 0;
        const bMatch = b.category === article.category ? 1 : 0;
        if (aMatch !== bMatch) return bMatch - aMatch;
        return (b.date || '').localeCompare(a.date || '');
      })
      .slice(0, 3);

    relatedGrid.innerHTML = related
      .map(
        (item) => `
        <article class="blog-related-card">
          <a href="blog-details.html?id=${item.id}" class="blog-related-card__link">
            <div class="blog-related-card__image">
              <img src="${item.image}" alt="" width="280" height="160" loading="lazy">
            </div>
            <div class="blog-related-card__body">
              <time datetime="${item.date}">${item.dateLabel}</time>
              <h3>${item.title}</h3>
              <p>${item.excerpt}</p>
            </div>
          </a>
        </article>`
      )
      .join('');
  }

  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }
}
