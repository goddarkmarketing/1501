const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');

function walk(dir) {
  return fs.readdirSync(dir).flatMap((name) => {
    const fp = path.join(dir, name);
    if (fs.statSync(fp).isDirectory()) return walk(fp);
    return fp.endsWith('.html') ? [fp] : [];
  });
}

const old =
  '<li><a href="contact.html">ติดต่อตัวแทน</a></li>\r\n            <li><a href="contact-details.html">ศูนย์บริการลูกค้า</a></li>';
const neu =
  '<li><a href="contact.html">ติดต่อตัวแทน</a></li>\r\n            <li><a href="hospitals.html">โรงพยาบาลในเครือ</a></li>\r\n            <li><a href="contact-details.html">ศูนย์บริการลูกค้า</a></li>';

for (const fp of walk(root)) {
  let html = fs.readFileSync(fp, 'utf8');
  if (html.includes(old) && !html.includes('hospitals.html')) {
    fs.writeFileSync(fp, html.replace(old, neu), 'utf8');
    console.log('footer', path.relative(root, fp));
  }
}
