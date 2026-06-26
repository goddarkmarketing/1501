const fs = require('fs');
const html = fs.readFileSync('c:/xampp/htdocs/1501/assets/ref/fwd-promotion/index.html', 'utf8');
const m = html.match(/<script id="__NEXT_DATA__"[^>]*>([\s\S]*?)<\/script>/);
const data = JSON.parse(m[1]);
const page = data.props.pageProps.data.data;

const cards = [];
const categories = new Map();

const walk = (obj) => {
  if (!obj || typeof obj !== 'object') return;
  if (Array.isArray(obj)) {
    obj.forEach(walk);
    return;
  }
  if (obj._content_type_uid === 'promotion_card') {
    const cat = obj.insurance_category?.[0];
    if (cat?.title) categories.set(cat.filter_value || cat.title, cat.title);
    cards.push({
      id: obj.uid,
      title: stripHtml(obj.title || ''),
      highlight: stripHighlight(obj.highlight_text),
      description: stripHtml(obj.description || ''),
      category: cat?.title || '',
      categoryFilter: cat?.filter_value || '',
      badge: obj.tag?.[0]?.title || obj.promotion_tag?.[0]?.title || '',
      image: obj.image?.url || obj.promotion_image?.url || obj.card_image?.url || '',
      cta: obj.button?.[0]?.title || 'คำนวณเบี้ยฯ คลิกเลย',
      ctaHref: obj.button?.[0]?.url || '#',
      countdown: !!obj.countdown_timer,
    });
  }
  Object.values(obj).forEach(walk);
};

function stripHtml(s) {
  return s.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
}

function stripHighlight(s) {
  if (!s) return '';
  return s.replace(/[<>]/g, '').trim();
}

walk(page);

console.log('categories', [...categories.entries()]);
console.log('card count', cards.length);
cards.slice(0, 5).forEach((c, i) => console.log(i + 1, JSON.stringify(c, null, 2)));

fs.writeFileSync(
  'c:/xampp/htdocs/1501/assets/ref/fwd-promotion/promotions-extracted.json',
  JSON.stringify({ categories: [...categories.entries()], cards }, null, 2),
  'utf8'
);
