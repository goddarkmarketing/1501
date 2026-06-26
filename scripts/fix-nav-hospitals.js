const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');
const files = fs
  .readdirSync(root)
  .filter((f) => f.endsWith('.html'))
  .map((f) => path.join(root, f))
  .concat([
    path.join(root, 'assets/partials/header.html'),
    path.join(root, 'assets/partials/footer.html'),
  ]);

const footerNeedle =
  '<li><a href="contact.html">ติดต่อตัวแทน</a></li>\n            <li><a href="contact-details.html">ศูนย์บริการลูกค้า</a></li>';
const footerReplacement =
  '<li><a href="contact.html">ติดต่อตัวแทน</a></li>\n            <li><a href="hospitals.html">โรงพยาบาลในเครือ</a></li>\n            <li><a href="contact-details.html">ศูนย์บริการลูกค้า</a></li>';

for (const fp of files) {
  if (!fs.existsSync(fp)) continue;
  let html = fs.readFileSync(fp, 'utf8');
  const original = html;

  html = html.replace(/data-nav-menu="products""/g, 'data-nav-menu="products"');
  html = html.replace(
    /class="header__nav-item header__nav-item--dropdown" data-nav-menu="products" is-active"/g,
    'class="header__nav-item header__nav-item--dropdown is-active" data-nav-menu="products"'
  );

  if (!html.includes('hospitals.html') && html.includes(footerNeedle)) {
    html = html.replace(footerNeedle, footerReplacement);
  }

  if (html !== original) {
    fs.writeFileSync(fp, html, 'utf8');
    console.log('fixed', path.relative(root, fp));
  }
}
