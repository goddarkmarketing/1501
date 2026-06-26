const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');
const files = [
  'index.html',
  'blog.html',
  'blog-details.html',
  'plan.html',
  'promotion.html',
  'contact.html',
  'contact-details.html',
  'about-us.html',
  'register.html',
  'login.html',
  'assets/partials/header.html',
  'assets/partials/footer.html',
];

const serviceDropdown = `      <div class="header__nav-item header__nav-item--dropdown" data-nav-menu="services">
        <button type="button" class="header__nav-link header__nav-link--dropdown" aria-expanded="false" aria-haspopup="true">
          บริการลูกค้า
          <i data-lucide="chevron-down" class="header__nav-chevron" aria-hidden="true"></i>
        </button>
        <div class="header__nav-submenu" role="menu"></div>
      </div>`;

const serviceDropdownActive = serviceDropdown
  .replace('header__nav-item--dropdown"', 'header__nav-item--dropdown is-active"')
  .replace('header__nav-link--dropdown"', 'header__nav-link--dropdown active"');

for (const file of files) {
  const fp = path.join(root, file);
  if (!fs.existsSync(fp)) continue;

  let html = fs.readFileSync(fp, 'utf8');
  let changed = false;

  if (!html.includes('data-nav-menu="products"')) {
    const next = html.replace(
      /<div class="header__nav-item header__nav-item--dropdown(?![^>]*data-nav-menu)/g,
      '<div class="header__nav-item header__nav-item--dropdown" data-nav-menu="products"'
    );
    if (next !== html) {
      html = next;
      changed = true;
    }
  }

  if (html.includes('<a href="contact-details.html" class="header__nav-link active">บริการลูกค้า</a>')) {
    html = html.replace(
      '<a href="contact-details.html" class="header__nav-link active">บริการลูกค้า</a>',
      serviceDropdownActive
    );
    changed = true;
  } else if (html.includes('<a href="contact-details.html" class="header__nav-link">บริการลูกค้า</a>')) {
    html = html.replace('<a href="contact-details.html" class="header__nav-link">บริการลูกค้า</a>', serviceDropdown);
    changed = true;
  }

  if (!html.includes('hospitals.html') && html.includes('บริการลูกค้า</h4>')) {
    html = html.replace(
      '<li><a href="contact.html">ติดต่อตัวแทน</a></li>\n            <li><a href="contact-details.html">ศูนย์บริการลูกค้า</a></li>',
      '<li><a href="contact.html">ติดต่อตัวแทน</a></li>\n            <li><a href="hospitals.html">โรงพยาบาลในเครือ</a></li>\n            <li><a href="contact-details.html">ศูนย์บริการลูกค้า</a></li>'
    );
    changed = true;
  }

  if (changed) {
    fs.writeFileSync(fp, html, 'utf8');
    console.log('updated', file);
  }
}
