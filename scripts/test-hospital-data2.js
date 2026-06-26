const fs = require('fs');
const path = require('path');
const data = JSON.parse(
  fs.readFileSync(path.join(__dirname, '../assets/data/hospital-locator.json'), 'utf8')
);

const specials = data.results.filter((h) => /["'<>\\]/.test(JSON.stringify(h)));
console.log('special chars count', specials.length);
specials.slice(0, 5).forEach((h) => console.log(h.name, h.telephone));

const provinces = [...new Set(data.results.map((h) => h.province).filter(Boolean))].sort();
console.log('unique provinces from results', provinces.length);
