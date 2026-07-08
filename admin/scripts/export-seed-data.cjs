/**
 * Export plan/blog/promotion data from JS files as JSON for PHP seed import.
 * Usage: node admin/scripts/export-seed-data.cjs [site_root]
 */
const fs = require('fs');
const path = require('path');

const root = process.argv[2] || path.join(__dirname, '../..');

function evalJs(relPath, names) {
  const file = path.join(root, relPath);
  const code = fs.readFileSync(file, 'utf8');
  const returns = names
    .map((name) => `${name}: typeof ${name} !== 'undefined' ? ${name} : undefined`)
    .join(', ');
  const fn = new Function(code + `\n;return { ${returns} };`);
  return fn() || {};
}

function pickPlan(p) {
  return {
    id: p.id,
    name: p.name,
    category: p.category,
    tagline: p.tagline || '',
    headline: p.headline || '',
    priceFrom: p.priceFrom || 0,
    priceNote: p.priceNote || '',
    heroImage: p.heroImage || '',
    promo: p.promo || null,
    benefits: p.benefits || [],
    highlights: p.highlights || [],
    conditions: p.conditions || [],
    renewal: p.renewal || [],
    why: p.why || [],
    tiers: p.tiers || [],
    coverageSummary: p.coverageSummary || [],
    coverageRows: p.coverageRows || [],
    faqs: p.faqs || [],
  };
}

try {
  const planBox = evalJs('assets/js/plan-data.js', ['PLAN_PRODUCTS']);
  const blogBox = evalJs('assets/js/blog-data.js', ['BLOG_ARTICLES']);
  const promoBox = evalJs('assets/js/promotion-data.js', ['PROMOTION_ITEMS']);

  const plans = Object.values(planBox.PLAN_PRODUCTS || {}).map(pickPlan);
  const articles = blogBox.BLOG_ARTICLES || [];
  const promotions = promoBox.PROMOTION_ITEMS || [];

  process.stdout.write(JSON.stringify({ plans, articles, promotions }));
} catch (err) {
  console.error(err.message);
  process.exit(1);
}
