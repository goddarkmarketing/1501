const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');
const files = [
  'index.html',
  'plan.html',
  'plan-details.html',
  'plan-category.html',
  'promotion.html',
  'faq.html',
  'blog.html',
  'blog-details.html',
  'hospitals.html',
  'about-us.html',
  'login.html',
  'register.html',
  'assets/partials/header.html',
  'assets/partials/footer.html',
];

function updateFile(filePath) {
  if (!fs.existsSync(filePath)) return false;
  let content = fs.readFileSync(filePath, 'utf8');
  const original = content;

  content = content.replaceAll(
    'btn btn--primary btn--header-cta">ติดต่อตัวแทน',
    'btn btn--primary btn--header-cta">ติดต่อเรา'
  );

  content = content.replaceAll(
    '<li><a href="contact.html">ติดต่อตัวแทน</a></li>',
    '<li><a href="contact.html">ติดต่อเรา</a></li>'
  );

  content = content.replaceAll(
    /<li><a href="contact-details\.html">ศูนย์บริการลูกค้า<\/a><\/li>\s*/g,
    ''
  );

  content = content.replaceAll(
    '<li><a href="contact-details.html">แจ้งเคลม / สอบถามกรมธรรม์</a></li>',
    '<li><a href="contact.html">แจ้งเคลม / สอบถามกรมธรรม์</a></li>'
  );

  content = content.replaceAll('href="contact-details.html"', 'href="contact.html"');

  if (content !== original) {
    fs.writeFileSync(filePath, content, 'utf8');
    return true;
  }
  return false;
}

for (const file of files) {
  const fullPath = path.join(root, file);
  if (updateFile(fullPath)) {
    console.log('updated', file);
  }
}
