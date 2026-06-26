const fs = require('fs');
const html = fs.readFileSync('c:/xampp/htdocs/1501/assets/ref/fwd-promotion/index.html', 'utf8');
const m = html.match(/<script id="__NEXT_DATA__"[^>]*>([\s\S]*?)<\/script>/);
const data = JSON.parse(m[1]);
const page = data.props.pageProps.data.data;

const byId = new Map();

const walk = (obj) => {
  if (!obj || typeof obj !== 'object') return;
  if (Array.isArray(obj)) {
    obj.forEach(walk);
    return;
  }
  if (obj._content_type_uid === 'promotion_card') {
    const cat = obj.insurance_category?.[0];
    const image =
      obj.promotion_image?.url ||
      obj.promotion_image?.file?.url ||
      obj.image?.url ||
      obj.card_image?.url ||
      obj.thumbnail?.url ||
      '';
    const tag = obj.promotion_tag?.[0] || obj.tag?.[0] || obj.label?.[0];
    byId.set(obj.uid, {
      id: obj.uid,
      title: stripHtml(obj.title || '').replace(/\s+qa$/i, ''),
      highlight: stripHighlight(obj.highlight_text),
      descriptionHtml: obj.description || '',
      bullets: extractBullets(obj.description || ''),
      category: cat?.title || '',
      categoryKey: mapCategory(cat?.filter_value || ''),
      badge: tag?.title || '',
      badgeType: tag?.type || tag?.tag_type || '',
      image,
      cta: obj.button?.[0]?.title || 'คำนวณเบี้ยฯ คลิกเลย',
      ctaHref: mapCta(obj.button?.[0]?.url || '#'),
      promoCode: extractCode(obj.description || ''),
      validUntil: extractDate(obj.description || ''),
      installment: extractInstallment(obj.description || ''),
      popular: (tag?.title || '').includes('นิยม'),
      isNew: (tag?.title || '').includes('ใหม่'),
      sortOrder: obj.order || 0,
    });
  }
  Object.values(obj).forEach(walk);
};

function stripHtml(s) {
  return s.replace(/<[^>]+>/g, ' ').replace(/&nbsp;/g, ' ').replace(/\s+/g, ' ').trim();
}

function stripHighlight(s) {
  if (!s) return '';
  return s.replace(/[<>]/g, '').trim();
}

function extractBullets(html) {
  const bullets = [];
  const re = /✅\s*([^<]+)/g;
  let match;
  while ((match = re.exec(html))) {
    bullets.push(match[1].replace(/&nbsp;/g, ' ').trim());
  }
  if (!bullets.length) {
    const plain = stripHtml(html);
    const parts = plain.split(/(?=✅)/).filter(Boolean).slice(0, 4);
    parts.forEach((p) => bullets.push(p.replace(/^✅\s*/, '').trim()));
  }
  return bullets.filter(Boolean).slice(0, 4);
}

function extractCode(html) {
  const m = html.match(/โค้ด\s+([A-Z0-9]+)/i);
  return m ? m[1] : '';
}

function extractDate(html) {
  const m = html.match(/ระหว่างวันที่\s*([^<]+)/);
  return m ? m[1].replace(/&nbsp;/g, ' ').trim() : '';
}

function extractInstallment(html) {
  const m = html.match(/ผ่อน\s*0%\s*นานสูงสุด\s*(\d+)\s*เดือน/);
  return m ? `ผ่อน 0% นาน ${m[1]} เดือน` : '';
}

function mapCategory(filter) {
  const map = {
    '/savings-insurance/': 'savings',
    '/health-insurance/': 'health',
    '/life-and-accident-insurance/': 'life-accident',
    '/critical-illness-insurance/': 'critical',
  };
  return map[filter] || 'all';
}

function mapCta(url) {
  const planMap = {
    'easy-e-health': 'easy-e-health',
    'easy-e-save': 'easy-e-save-10-5',
    'fwd-precious-care': 'fwd-precious-care',
    'opd-plus': 'opd-plus',
    'big-3': 'big-3-critical',
    'easy-e-life': 'easy-e-life',
  };
  for (const [key, id] of Object.entries(planMap)) {
    if (url.includes(key)) return `plan-details.html?id=${id}`;
  }
  if (url.includes('savings')) return 'plan-category.html?category=savings';
  if (url.includes('health')) return 'plan-category.html?category=health';
  if (url.includes('life-and-accident')) return 'plan-category.html?category=life-accident';
  if (url.includes('critical')) return 'plan-category.html?category=critical';
  return 'plan.html';
}

walk(page);

const PROMO_IMAGES = {
  savings: 'https://images.unsplash.com/photo-1554224311-beee415c201f?w=640&q=80',
  health: 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=640&q=80',
  'life-accident': 'https://images.unsplash.com/photo-1511895426328-dc8714191300?w=640&q=80',
  critical: 'https://images.unsplash.com/photo-1631217868264-e5b1bb5c27e0?w=640&q=80',
  default: 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=640&q=80',
};

const cards = [...byId.values()]
  .filter((c) => c.title && !c.title.startsWith('/online-promotions'))
  .map((c) => ({
    ...c,
    image: c.image || PROMO_IMAGES[c.categoryKey] || PROMO_IMAGES.default,
    popular: c.id === 'bltf48db74bfd0f602e' || c.popular,
    isNew: c.id === 'blt7b1fb931eaef89f1' || c.isNew,
  }))
  .sort((a, b) => a.title.localeCompare(b.title, 'th'));

const PROMOTION_FILTERS = [
  { id: 'all', label: 'ทั้งหมด' },
  { id: 'savings', label: 'ประกันสะสมทรัพย์' },
  { id: 'health', label: 'ประกันสุขภาพ' },
  { id: 'life-accident', label: 'ประกันชีวิตและอุบัติเหตุ' },
  { id: 'critical', label: 'ประกันโรคร้ายแรง' },
];

const out = `const PROMOTION_FILTERS = ${JSON.stringify(PROMOTION_FILTERS, null, 2)};

const PROMOTION_ITEMS = ${JSON.stringify(cards, null, 2)};

function getPromotionItems(category = 'all') {
  if (!category || category === 'all') return PROMOTION_ITEMS;
  return PROMOTION_ITEMS.filter((item) => item.categoryKey === category);
}
`;

fs.writeFileSync('c:/xampp/htdocs/1501/assets/js/promotion-data.js', out, 'utf8');
console.log('wrote', cards.length, 'cards');
console.log(cards.map((c) => ({ title: c.title, cat: c.categoryKey, badge: c.badge, img: !!c.image })));
