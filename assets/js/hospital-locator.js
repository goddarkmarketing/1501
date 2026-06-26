const HOSPITAL_ITEMS_PER_PAGE = 50;
const HOSPITAL_DEFAULT_PROVINCE = 'กรุงเทพมหานคร';
const HOSPITAL_DEFAULT_MAP_CENTER = [13.7563, 100.5018];
const HOSPITAL_DEFAULT_MAP_ZOOM = 11;
function formatHospitalAddress(hospital) {
  return [hospital.streetNumber, hospital.road, hospital.county, hospital.district, hospital.province, hospital.postalCode]
    .filter(Boolean)
    .join(' ');
}

function escapeHospitalHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function getHospitalMapUrl(hospital) {
  const address = formatHospitalAddress(hospital);
  const mapQuery =
    hospital.latitude && hospital.longitude
      ? `${hospital.latitude},${hospital.longitude}`
      : encodeURIComponent(`${hospital.name} ${address}`);
  return `https://www.google.com/maps/search/?api=1&query=${mapQuery}`;
}

function createHospitalPopupContent(hospital) {
  const safeName = escapeHospitalHtml(hospital.name);
  const mapUrl = getHospitalMapUrl(hospital);

  return `
    <div class="hospital-map-popup">
      <p class="hospital-map-popup__name">${safeName}</p>
      <span class="hospital-map-popup__divider" aria-hidden="true"></span>
      <a href="${mapUrl}" class="hospital-map-popup__nav" target="_blank" rel="noopener noreferrer" aria-label="นำทางไป ${safeName}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="m3 11 19-9-9 19-2-8-8-2z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
        </svg>
      </a>
    </div>`;
}

function renderHospitalCard(hospital) {  const address = formatHospitalAddress(hospital);
  const facilities = hospital.facilities || [];
  const phone = hospital.telephone || hospital.mobile || '';
  const safeName = escapeHospitalHtml(hospital.name);
  const safeAddress = escapeHospitalHtml(address);
  const safePhone = escapeHospitalHtml(phone);
  const mapUrl = getHospitalMapUrl(hospital);
  const typesText = facilities.map((item) => escapeHospitalHtml(item)).join(' · ');

  return `
    <article class="hospital-card" role="listitem" data-hospital-id="${escapeHospitalHtml(hospital.id)}" tabindex="0">
      <a href="${mapUrl}" class="hospital-card__nav" target="_blank" rel="noopener noreferrer" aria-label="นำทางไป ${safeName}" onclick="event.stopPropagation()">
        <i data-lucide="navigation" aria-hidden="true"></i>
      </a>
      <h3 class="hospital-card__title">${safeName}</h3>
      ${typesText ? `<p class="hospital-card__types">${typesText}</p>` : ''}
      ${address ? `<p class="hospital-card__address"><i data-lucide="map-pin" aria-hidden="true"></i><span>${safeAddress}</span></p>` : ''}
      <div class="hospital-card__contact" hidden>
        ${phone ? `<p class="hospital-card__phone"><i data-lucide="phone" aria-hidden="true"></i><a href="tel:${phone.replace(/[^\d+]/g, '')}">${safePhone}</a></p>` : ''}
      </div>
      <button type="button" class="hospital-card__toggle" aria-expanded="false">
        <span class="hospital-card__toggle-show">ดูข้อมูลการติดต่อ</span>
        <span class="hospital-card__toggle-hide" hidden>ซ่อนข้อมูลการติดต่อ</span>
        <i data-lucide="chevron-down" aria-hidden="true"></i>
      </button>
    </article>`;
}

function createCheckInIcon(isActive) {
  const width = isActive ? 32 : 28;
  const height = isActive ? 40 : 36;
  const fill = isActive ? '#e87722' : '#f58020';

  return L.divIcon({
    className: 'hospital-map-marker-wrap leaflet-div-icon',
    html: `
      <div class="hospital-map-marker ${isActive ? 'is-active' : ''}">
        <svg width="${width}" height="${height}" viewBox="0 0 28 36" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <path d="M14 36C14 36 24 24.2 24 16.5 24 9.06 19.94 5 14 5 8.06 5 4 9.06 4 16.5 4 24.2 14 36 14 36Z" fill="${fill}" stroke="#ffffff" stroke-width="1.5"/>
          <circle cx="14" cy="15" r="5.5" fill="#ffffff"/>
          <path d="M11.5 15.2 13.3 17 16.7 13.4" stroke="${fill}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
    `,
    iconSize: [width, height],
    iconAnchor: [width / 2, height],
    popupAnchor: [0, -height + 4],
  });
}

function scrollHospitalCardIntoView(listEl, card) {
  if (!listEl || !card) return;

  const listRect = listEl.getBoundingClientRect();
  const cardRect = card.getBoundingClientRect();
  const delta = cardRect.top - listRect.top - (listRect.height / 2 - cardRect.height / 2);
  listEl.scrollBy({ top: delta, behavior: 'smooth' });
}

async function initHospitalLocator() {
  const root = document.getElementById('hospitalLocator');
  const grid = document.getElementById('hospitalListingGrid');  const countEl = document.getElementById('hospitalListingCount');
  const emptyEl = document.getElementById('hospitalListingEmpty');
  const searchEl = document.getElementById('hospitalListingSearch');
  const searchClearEl = document.getElementById('hospitalListingSearchClear');
  const loadMoreEl = document.getElementById('hospitalListingLoadMore');
  const mapEl = document.getElementById('hospitalMap');
  if (!root || !grid || !countEl) return;

  root.classList.add('is-loading');

  let hospitals = [];
  let map = null;
  let markersLayer = null;
  let markerById = new Map();
  let selectedId = null;

  try {
    const dataUrl = new URL('assets/data/hospital-locator.json', window.location.href);
    const response = await fetch(dataUrl);
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }
    const data = await response.json();
    hospitals = data.results || [];
  } catch {
    if (emptyEl) {
      emptyEl.textContent = 'ไม่สามารถโหลดข้อมูลโรงพยาบาลได้ กรุณาลองใหม่อีกครั้ง';
      emptyEl.hidden = false;
    }
    root.classList.remove('is-loading');
    return;
  }

  let planType = 'individual';
  let facility = 'all';
  let province = HOSPITAL_DEFAULT_PROVINCE;
  let query = '';
  let visibleCount = HOSPITAL_ITEMS_PER_PAGE;
  const matchesPlanType = (hospital) => {
    if (planType === 'group') {
      return (hospital.groupFacilities || []).length > 0;
    }
    return (hospital.individualFacilities || []).length > 0 || (hospital.commonFacilities || []).length > 0;
  };

  const getFiltered = () => {
    const normalizedQuery = query.trim().toLowerCase();

    return hospitals.filter((hospital) => {
      if (!matchesPlanType(hospital)) return false;
      if (facility !== 'all' && !(hospital.facilities || []).includes(facility)) return false;
      if (province && province !== 'all' && hospital.province !== province) return false;

      if (!normalizedQuery) return true;

      const haystack = [
        hospital.name,        hospital.province,
        hospital.district,
        hospital.county,
        hospital.road,
        hospital.streetNumber,
        ...(hospital.facilities || []),
      ]
        .join(' ')
        .toLowerCase();

      return haystack.includes(normalizedQuery);
    });
  };

  const initMap = () => {
    if (map || !mapEl || typeof L === 'undefined') return;

    map = L.map(mapEl, { zoomControl: false }).setView(HOSPITAL_DEFAULT_MAP_CENTER, HOSPITAL_DEFAULT_MAP_ZOOM);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {      maxZoom: 19,
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(map);
    L.control.zoom({ position: 'bottomright' }).addTo(map);
    markersLayer = L.layerGroup().addTo(map);
  };

  const HOSPITAL_SELECT_ZOOM = 15;

  const openHospitalPopup = (id) => {
    if (!id || !markerById.has(id)) return;
    markerById.get(id).openPopup();
  };

  const selectHospital = (id, panMap = true) => {
    const previousId = selectedId;
    selectedId = id;

    grid.querySelectorAll('.hospital-card').forEach((card) => {
      card.classList.toggle('is-active', card.dataset.hospitalId === id);
    });

    if (previousId && markerById.has(previousId)) {
      markerById.get(previousId).closePopup();
      markerById.get(previousId).setIcon(createCheckInIcon(false));
    }
    if (id && markerById.has(id)) {
      markerById.get(id).setIcon(createCheckInIcon(true));
      markerById.get(id).openPopup();
    }

    const card = grid.querySelector(`[data-hospital-id="${id}"]`);
    if (card) {
      scrollHospitalCardIntoView(grid, card);
    }

    const hospital = hospitals.find((item) => item.id === id);
    if (!hospital || !map || !panMap) return;

    const lat = parseFloat(hospital.latitude);
    const lng = parseFloat(hospital.longitude);
    if (Number.isFinite(lat) && Number.isFinite(lng)) {
      map.flyTo([lat, lng], HOSPITAL_SELECT_ZOOM, { duration: 0.65 });
    }
  };

  const updateMapMarkers = (filtered, refitBounds = true) => {
    if (!map || !markersLayer) return;

    markersLayer.clearLayers();
    markerById = new Map();
    const bounds = [];

    filtered.forEach((hospital) => {
      const lat = parseFloat(hospital.latitude);
      const lng = parseFloat(hospital.longitude);
      if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

      const isActive = selectedId === hospital.id;
      const marker = L.marker([lat, lng], {
        icon: createCheckInIcon(isActive),
        riseOnHover: true,
        zIndexOffset: isActive ? 1000 : 0,
      });

      marker.bindPopup(createHospitalPopupContent(hospital), {
        className: 'hospital-map-popup-wrap',
        closeButton: false,
        autoPan: true,
        maxWidth: 320,
        minWidth: 180,
        offset: [0, -4],
      });

      marker.on('click', () => selectHospital(hospital.id));
      marker.addTo(markersLayer);
      markerById.set(hospital.id, marker);
      bounds.push([lat, lng]);
    });

    if (selectedId && markerById.has(selectedId)) {
      openHospitalPopup(selectedId);
    }

    if (refitBounds && bounds.length && !selectedId) {
      if (bounds.length === 1) {
        map.setView(bounds[0], 14);
      } else {
        map.fitBounds(bounds, { padding: [48, 48], maxZoom: 12 });
      }
    }
  };

  const bindCardInteractions = () => {
    grid.querySelectorAll('.hospital-card').forEach((card) => {
      const id = card.dataset.hospitalId;
      if (!id || card.dataset.bound === '1') return;
      card.dataset.bound = '1';

      card.addEventListener('click', () => selectHospital(id));

      card.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          selectHospital(id);
        }
      });

      const btn = card.querySelector('.hospital-card__toggle');
      btn?.addEventListener('click', (e) => {
        e.stopPropagation();
        const contact = card.querySelector('.hospital-card__contact');
        const showLabel = btn.querySelector('.hospital-card__toggle-show');
        const hideLabel = btn.querySelector('.hospital-card__toggle-hide');
        const icon = btn.querySelector('[data-lucide]');
        const expanded = btn.getAttribute('aria-expanded') === 'true';

        btn.setAttribute('aria-expanded', String(!expanded));
        if (contact) contact.hidden = expanded;
        if (showLabel) showLabel.hidden = !expanded;
        if (hideLabel) hideLabel.hidden = expanded;
        if (icon) {
          icon.setAttribute('data-lucide', expanded ? 'chevron-down' : 'chevron-up');
          if (typeof lucide !== 'undefined') lucide.createIcons();
        }
      });
    });
  };

  const render = () => {
    const filtered = getFiltered();
    const slice = filtered.slice(0, visibleCount);

    grid.querySelectorAll('.hospital-card').forEach((card) => card.remove());
    if (slice.length) {
      grid.insertAdjacentHTML('beforeend', slice.map(renderHospitalCard).join(''));
    }

    countEl.textContent = String(filtered.length);
    if (emptyEl) emptyEl.hidden = filtered.length > 0;
    if (loadMoreEl) {
      loadMoreEl.hidden = visibleCount >= filtered.length;
      const remaining = filtered.length - visibleCount;
      loadMoreEl.textContent = remaining > 0 ? `แสดงเพิ่ม (${Math.min(HOSPITAL_ITEMS_PER_PAGE, remaining)} รายการ)` : 'แสดงเพิ่ม';
    }

    if (selectedId && !filtered.some((item) => item.id === selectedId)) {
      selectedId = null;
    }

    bindCardInteractions();
    updateMapMarkers(filtered, !selectedId);
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }

    if (map) {
      requestAnimationFrame(() => map.invalidateSize());
    }
  };

  initMap();

  document.querySelectorAll('.hospital-locator__chip').forEach((chip) => {
    chip.addEventListener('click', () => {
      document.querySelectorAll('.hospital-locator__chip').forEach((item) => item.classList.remove('is-active'));
      chip.classList.add('is-active');
      facility = chip.dataset.filterValue || 'all';
      visibleCount = HOSPITAL_ITEMS_PER_PAGE;
      render();
    });
  });

  const chipsScrollEl = document.getElementById('hospitalChipsScroll');
  document.getElementById('hospitalChipsScrollNext')?.addEventListener('click', () => {
    if (chipsScrollEl) {
      chipsScrollEl.scrollBy({ left: 160, behavior: 'smooth' });
    }
  });

  document.querySelectorAll('.hospital-locator__plan-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.hospital-locator__plan-btn').forEach((item) => item.classList.remove('is-active'));
      btn.classList.add('is-active');
      planType = btn.dataset.planType || 'individual';
      visibleCount = HOSPITAL_ITEMS_PER_PAGE;
      render();
    });
  });

  const syncSearchClear = () => {
    if (searchClearEl) {
      searchClearEl.hidden = !query.trim();
    }
  };

  searchEl?.addEventListener('input', () => {
    query = searchEl.value || '';
    province = 'all';
    visibleCount = HOSPITAL_ITEMS_PER_PAGE;
    syncSearchClear();
    render();
  });

  searchClearEl?.addEventListener('click', () => {
    query = '';
    province = 'all';
    if (searchEl) searchEl.value = '';
    visibleCount = HOSPITAL_ITEMS_PER_PAGE;
    syncSearchClear();
    render();
    searchEl?.focus();
  });
  loadMoreEl?.addEventListener('click', () => {
    visibleCount += HOSPITAL_ITEMS_PER_PAGE;
    render();
  });

  if (searchEl) {
    searchEl.value = HOSPITAL_DEFAULT_PROVINCE;
  }
  syncSearchClear();

  root.classList.remove('is-loading');
  render();
}