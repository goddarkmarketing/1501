const fs = require('fs');
const path = require('path');

const data = JSON.parse(
  fs.readFileSync(path.join(__dirname, '../assets/data/hospital-locator.json'), 'utf8')
);

function formatHospitalAddress(hospital) {
  return [hospital.streetNumber, hospital.road, hospital.county, hospital.district, hospital.province, hospital.postalCode]
    .filter(Boolean)
    .join(' ');
}

function renderHospitalCard(hospital) {
  const address = formatHospitalAddress(hospital);
  const facilities = hospital.facilities || [];
  const phone = hospital.telephone || hospital.mobile || '';
  const mapQuery =
    hospital.latitude && hospital.longitude
      ? `${hospital.latitude},${hospital.longitude}`
      : encodeURIComponent(`${hospital.name} ${address}`);
  const mapUrl = `https://www.google.com/maps/search/?api=1&query=${mapQuery}`;

  return `
    <article class="hospital-card" data-hospital-id="${hospital.id}">
      <div class="hospital-card__head">
        <h3 class="hospital-card__title">${hospital.name}</h3>
      </div>
    </article>`;
}

console.log('results', data.results.length);
console.log('provinces', data.facetResult?.locationprovincefield_s?.length);

let errors = 0;
for (const h of data.results) {
  try {
    renderHospitalCard(h);
  } catch (e) {
    errors++;
    console.log('error', h.name, e.message);
  }
}
console.log('render errors', errors);

const slice = data.results.slice(0, 50);
const html = slice.map(renderHospitalCard).join('');
console.log('html length', html.length);
