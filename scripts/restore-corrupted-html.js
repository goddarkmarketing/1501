const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');

function readUtf8(file) {
  return fs.readFileSync(path.join(root, file), 'utf8');
}

function writeUtf8(file, content) {
  fs.writeFileSync(path.join(root, file), content, 'utf8');
}

function extractFooter(source) {
  const start = source.indexOf('<footer class="footer">');
  const end = source.indexOf('</footer>', start);
  return source.slice(start, end + '</footer>'.length);
}

function makeHeader({ productsActive = false, blogActive = false } = {}) {
  const productsClasses = productsActive
    ? 'header__nav-item header__nav-item--dropdown is-active'
    : 'header__nav-item header__nav-item--dropdown';
  const productsBtnClasses = productsActive
    ? 'header__nav-link header__nav-link--dropdown active'
    : 'header__nav-link header__nav-link--dropdown';
  const blogLinkClass = blogActive ? 'header__nav-link active' : 'header__nav-link';

  return `<header class="header">
  <div class="container header__inner">
    <a href="index.html" class="logo logo--header">
      <img src="assets/img/logo.png" alt="Agent Thailand" class="logo__img" width="183" height="78">
    </a>
    <nav class="header__nav">
      <a href="index.html" class="header__nav-link">หน้าแรก</a>
      <div class="${productsClasses}" data-nav-menu="products">
        <button type="button" class="${productsBtnClasses}" aria-expanded="false" aria-haspopup="true">
          ผลิตภัณฑ์ทั้งหมด
          <i data-lucide="chevron-down" class="header__nav-chevron" aria-hidden="true"></i>
        </button>
        <div class="header__nav-submenu" role="menu"></div>
      </div>
      <a href="promotion.html" class="header__nav-link">โปรโมชั่น</a>
      <a href="blog.html" class="${blogLinkClass}">บทความ</a>
      <div class="header__nav-item header__nav-item--dropdown" data-nav-menu="services">
        <button type="button" class="header__nav-link header__nav-link--dropdown" aria-expanded="false" aria-haspopup="true">
          บริการลูกค้า
          <i data-lucide="chevron-down" class="header__nav-chevron" aria-hidden="true"></i>
        </button>
        <div class="header__nav-submenu" role="menu"></div>
      </div>
      <a href="about-us.html" class="header__nav-link">เกี่ยวกับเรา</a>
    </nav>
    <div class="header__actions">
      <a href="register.html" class="header__agent-link">สมัครตัวแทน</a>
      <a href="contact.html" class="btn btn--primary btn--header-cta">ติดต่อตัวแทน</a>
      <button class="header__hamburger" aria-label="เมนู" type="button"><i data-lucide="menu"></i></button>
    </div>
  </div>
</header>`;
}

const footer = extractFooter(readUtf8('faq.html'));
const consultModal = readUtf8('index.html').slice(
  readUtf8('index.html').indexOf('<div class="consult-terms-modal"'),
  readUtf8('index.html').indexOf('</div>\n\n<!-- ARTICLES SPOTLIGHT -->')
);

const planBody = `
<section class="hero hero--form">
  <div class="hero__bg" style="background-image:url('https://images.unsplash.com/photo-1511895426328-dc8714191300?w=1440&q=80')"></div>
  <div class="hero__overlay"></div>
  <div class="container hero__content">
    <div class="hero__inner">
      <div class="hero__copy">
        <span class="hero__eyebrow">INNOVATIVE FINANCIAL</span>
        <h1 class="hero__title">รีบซื้อ ชัวร์กว่า<br>Easy e-Health<br>ลดดอกเบี้ย<br>ปีแรก 12 %</h1>
        <a href="plan.html" class="hero__cta">ซื้อประกันสุขภาพ</a>
      </div>
      <div class="hero__form-card">
        <h3 class="hero__form-title">สนใจซื้อประกันออนไลน์</h3>
        <form class="hero-form" action="#" method="post" novalidate>
          <div class="hero-form__fields">
            <div class="form-group">
              <label class="sr-only" for="hero-insurance">ประกันที่สนใจ</label>
              <select id="hero-insurance" class="form-control hero-form__select hero-form__select--product" name="insurance" required data-selected="health-20">
                <option value="health-20">สุขภาพระยะยาว 20 ปี</option>
              </select>
            </div>
            <div class="hero-form__row">
              <div class="form-group">
                <label class="sr-only" for="hero-age">อายุ</label>
                <input id="hero-age" type="number" class="form-control" name="age" placeholder="อายุ" min="1" max="99" inputmode="numeric" autocomplete="off">
              </div>
              <div class="form-group">
                <label class="sr-only" for="hero-province">จังหวัด</label>
                <select id="hero-province" class="form-control hero-form__select hero-form__select--muted" name="province" required>
                  <option value="">เลือกจังหวัด</option>
                </select>
              </div>
            </div>
            <div class="hero-form__row">
              <div class="form-group">
                <label class="sr-only" for="hero-firstname">ชื่อ</label>
                <input id="hero-firstname" type="text" class="form-control" name="firstname" placeholder="ชื่อ" autocomplete="given-name" required>
              </div>
              <div class="form-group">
                <label class="sr-only" for="hero-lastname">นามสกุล</label>
                <input id="hero-lastname" type="text" class="form-control" name="lastname" placeholder="นามสกุล" autocomplete="family-name" required>
              </div>
            </div>
            <div class="hero-form__row">
              <div class="form-group">
                <label class="sr-only" for="hero-phone">เบอร์โทรศัพท์</label>
                <input id="hero-phone" type="tel" class="form-control" name="phone" placeholder="เบอร์โทรศัพท์" autocomplete="tel" inputmode="tel" required>
              </div>
              <div class="form-group">
                <label class="sr-only" for="hero-email">อีเมล</label>
                <input id="hero-email" type="email" class="form-control" name="email" placeholder="อีเมล" autocomplete="email">
              </div>
            </div>
            <div class="form-group hero-form__message">
              <label class="hero-form__label" for="hero-message">ข้อความเพิ่มเติม</label>
              <textarea id="hero-message" class="form-control" name="message" rows="2" placeholder="ระบุความต้องการหรือคำถามเพิ่มเติม (ถ้ามี)"></textarea>
            </div>
          </div>
          <div class="hero-form__footer">
            <div class="hero-form__consent">
              <label class="hero-form__consent-label">
                <input type="checkbox" name="consent" required>
                <span>ยอมรับในการเก็บข้อมูล เพื่อส่งข้อความ</span>
              </label>
              <a href="#" class="hero-form__terms" role="button">รายละเอียดและเงื่อนไข</a>
            </div>
            <button type="submit" class="btn hero-form__submit">ส่งข้อมูล</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>

<section class="section section--products">
  <div class="container container--inset-products">
    <h2 class="section-title section-title--center">ผลิตภัณฑ์ทั้งหมด</h2>
    <div class="product-categories">
      <div class="product-category active">
        <div class="product-category__icon"><i data-lucide="heart-handshake"></i></div>
        <div class="product-category__name">ประกันชีวิตและอุบัติเหตุ</div>
        <a href="plan.html" class="btn btn--primary btn--icon" aria-label="ดูรายละเอียด"><i data-lucide="arrow-right"></i></a>
      </div>
      <div class="product-category">
        <div class="product-category__icon"><i data-lucide="shield-plus"></i></div>
        <div class="product-category__name">ประกันสุขภาพ</div>
        <a href="plan.html" class="btn btn--primary btn--icon" aria-label="ดูรายละเอียด"><i data-lucide="arrow-right"></i></a>
      </div>
      <div class="product-category">
        <div class="product-category__icon"><i data-lucide="activity"></i></div>
        <div class="product-category__name">ประกันโรคร้ายแรง</div>
        <a href="plan.html" class="btn btn--primary btn--icon" aria-label="ดูรายละเอียด"><i data-lucide="arrow-right"></i></a>
      </div>
      <div class="product-category">
        <div class="product-category__icon"><i data-lucide="chart-no-axes-combined"></i></div>
        <div class="product-category__name">ประกันชีวิตควบการลงทุน</div>
        <a href="plan.html" class="btn btn--primary btn--icon" aria-label="ดูรายละเอียด"><i data-lucide="arrow-right"></i></a>
      </div>
      <div class="product-category">
        <div class="product-category__icon"><i data-lucide="wallet"></i></div>
        <div class="product-category__name">ประกันสะสมทรัพย์</div>
        <a href="plan.html" class="btn btn--primary btn--icon" aria-label="ดูรายละเอียด"><i data-lucide="arrow-right"></i></a>
      </div>
    </div>
  </div>
</section>

<section class="section section--plan-listing">
  <div class="container">
    <div class="plan-listing">
      <aside class="plan-listing__sidebar" aria-label="ตัวกรองแผนประกัน">
        <div class="plan-filter">
          <h3 class="plan-filter__title">ประเภทประกัน</h3>
          <div class="plan-filter__chips" data-filter-group="category">
            <button type="button" class="plan-filter__chip is-active" data-filter-value="all">ทั้งหมด</button>
            <button type="button" class="plan-filter__chip" data-filter-value="life-accident">ประกันชีวิตและอุบัติเหตุ</button>
            <button type="button" class="plan-filter__chip" data-filter-value="health">ประกันสุขภาพ</button>
            <button type="button" class="plan-filter__chip" data-filter-value="health-20">สุขภาพระยะยาว 20 ปี</button>
            <button type="button" class="plan-filter__chip" data-filter-value="critical">ประกันโรคร้ายแรง</button>
            <button type="button" class="plan-filter__chip" data-filter-value="investment-linked">ประกันชีวิตควบการลงทุน</button>
            <button type="button" class="plan-filter__chip" data-filter-value="savings">ประกันสะสมทรัพย์</button>
          </div>
        </div>
        <div class="plan-filter">
          <h3 class="plan-filter__title">ช่วงเบี้ยประกัน</h3>
          <div class="plan-filter__chips" data-filter-group="price">
            <button type="button" class="plan-filter__chip is-active" data-filter-value="all">ทั้งหมด</button>
            <button type="button" class="plan-filter__chip" data-filter-value="under-500">ต่ำกว่า 500 บาท</button>
            <button type="button" class="plan-filter__chip" data-filter-value="500-1000">500 – 1,000 บาท</button>
            <button type="button" class="plan-filter__chip" data-filter-value="1000-2000">1,000 – 2,000 บาท</button>
            <button type="button" class="plan-filter__chip" data-filter-value="over-2000">มากกว่า 2,000 บาท</button>
          </div>
        </div>
        <div class="plan-filter">
          <h3 class="plan-filter__title">เป้าหมายการเงิน</h3>
          <div class="plan-filter__chips" data-filter-group="goal">
            <button type="button" class="plan-filter__chip" data-filter-value="family">คุ้มครองครอบครัว</button>
            <button type="button" class="plan-filter__chip" data-filter-value="health">ดูแลสุขภาพ</button>
            <button type="button" class="plan-filter__chip" data-filter-value="retire">วางแผนเกษียณ</button>
            <button type="button" class="plan-filter__chip" data-filter-value="tax">ลดหย่อนภาษี</button>
            <button type="button" class="plan-filter__chip" data-filter-value="invest">ลงทุนระยะยาว</button>
          </div>
        </div>
        <div class="plan-filter">
          <h3 class="plan-filter__title">โปรโมชั่น</h3>
          <div class="plan-filter__chips" data-filter-group="promo">
            <button type="button" class="plan-filter__chip" data-filter-value="discount">ส่วนลด</button>
            <button type="button" class="plan-filter__chip" data-filter-value="new">ใหม่</button>
          </div>
        </div>
      </aside>
      <div class="plan-listing__main">
        <div class="plan-listing__toolbar">
          <p class="plan-listing__count">พบทั้งหมด <strong id="planListingCount">9</strong> แผน</p>
          <div class="plan-listing__actions">
            <button type="button" class="plan-listing__clear" id="planListingClear" disabled>
              <i data-lucide="rotate-ccw" aria-hidden="true"></i>
              ล้างตัวกรอง
            </button>
            <label class="plan-listing__sort-wrap">
              <span class="sr-only">เรียงลำดับ</span>
              <select class="plan-listing__sort" id="planListingSort" aria-label="เรียงลำดับ">
                <option value="recommended">แนะนำ</option>
                <option value="price-asc">เบี้ยต่ำ – สูง</option>
                <option value="price-desc">เบี้ยสูง – ต่ำ</option>
                <option value="name-asc">ชื่อ ก – ฮ</option>
              </select>
            </label>
          </div>
        </div>
        <div class="card-grid card-grid--listing plan-listing__grid" id="planListingGrid">
          <p class="plan-listing__empty" id="planListingEmpty" role="status">ไม่พบแผนประกันที่ตรงกับตัวกรอง ลองปรับตัวกรองหรือล้างตัวกรองทั้งหมด</p>
      <article class="plan-card" data-category="health" data-price="665" data-goals="health,family" data-promo="discount">
        <div class="plan-card__image">
          <img src="assets/img/plans/plan-rec-1.jpg" alt="ครอบครัวมีความสุขในสวนสาธารณะ" width="313" height="174" loading="lazy">
          <span class="plan-card__badge plan-card__badge--discount">รับส่วนลด 4 %</span>
        </div>
        <div class="plan-card__body">
          <h3 class="plan-card__title">Easy e-Health ลดดอกเบี้ย ปีแรก 12%</h3>
          <p class="plan-card__desc">ประกันสุขภาพออนไลน์ สมัครง่าย คุ้มครองครอบคลุมทั้ง IPD และ OPD ไม่ต้องตรวจสุขภาพก่อนรับประกัน จ่ายเบี้ยเริ่มต้นเพียง 665 บาท/เดือน รับส่วนลดพิเศษปีแรก 12% เมื่อสมัครภายในเดือนนี้</p>
          <a href="#" class="btn plan-card__btn">ดูรายละเอียด</a>
        </div>
      </article>
      <article class="plan-card" data-category="life-accident" data-price="1200" data-goals="family,health" data-promo="discount">
        <div class="plan-card__image">
          <img src="assets/img/plans/plan-rec-2.jpg" alt="ครอบครัวนั่งพักผ่อนร่วมกันกลางแจ้ง" width="313" height="174" loading="lazy">
          <span class="plan-card__badge plan-card__badge--discount">รับส่วนลด 4 %</span>
        </div>
        <div class="plan-card__body">
          <h3 class="plan-card__title">FWD Life Protection Plus</h3>
          <p class="plan-card__desc">คุ้มครองชีวิตและสุขภาพครบวงจร ด้วยเบี้ยประกันที่เหมาะสมกับทุกวัย ครอบคลุมค่ารักษา ค่าห้อง ICU และผลประโยชน์กรณีเสียชีวิต ปรับแผนความคุ้มครองได้ตามช่วงชีวิตของคุณ</p>
          <a href="#" class="btn plan-card__btn">ดูรายละเอียด</a>
        </div>
      </article>
      <article class="plan-card" data-category="savings" data-price="1500" data-goals="retire,invest">
        <div class="plan-card__image">
          <img src="assets/img/plans/plan-rec-3.jpg" alt="คู่รักวางแผนการเงินและอนาคตร่วมกัน" width="313" height="174" loading="lazy">
        </div>
        <div class="plan-card__body">
          <h3 class="plan-card__title">Smart Saving Plan</h3>
          <p class="plan-card__desc">ออมทรัพย์พร้อมความคุ้มครอง สร้างอนาคตที่มั่นคงให้ครอบครัวของคุณ รับเงินคืนตามระยะเวลาที่กำหนด พร้อมโบนัสพิเศษเมื่อครบสัญญา วางแผนเกษียณได้อย่างมั่นใจ</p>
          <a href="#" class="btn plan-card__btn">ดูรายละเอียด</a>
        </div>
      </article>
      <article class="plan-card" data-category="critical" data-price="800" data-goals="health,family">
        <div class="plan-card__image">
          <img src="assets/img/plans/plan-rec-4.jpg" alt="แพทย์ให้คำปรึกษาด้านสุขภาพ" width="313" height="174" loading="lazy">
        </div>
        <div class="plan-card__body">
          <h3 class="plan-card__title">Critical Illness Cover</h3>
          <p class="plan-card__desc">คุ้มครองโรคร้ายแรง 40 โรค จ่ายเงินทันทีเมื่อวินิจฉัย ไม่ต้องส่งใบเสร็จ ใช้เงินก้อนนี้ดูแลครอบครัวได้อย่างอิสระ ลดภาระค่าใช้จ่ายในช่วงเวลาที่สำคัญ</p>
          <a href="#" class="btn plan-card__btn">ดูรายละเอียด</a>
        </div>
      </article>
      <article class="plan-card" data-category="health-20" data-price="1800" data-goals="health,retire">
        <div class="plan-card__image">
          <img src="assets/img/plans/plan-rec-1.jpg" alt="ครอบครัวมีความสุขในสวนสาธารณะ" width="313" height="174" loading="lazy">
        </div>
        <div class="plan-card__body">
          <h3 class="plan-card__title">Health Plus Premium</h3>
          <p class="plan-card__desc">ประกันสุขภาพระดับพรีเมียม คุ้มครองค่ารักษาสูง ครอบคลุมทั้ง IPD และ OPD พร้อมบริการตรวจสุขภาพประจำปี เหมาะสำหรับผู้ที่ต้องการความคุ้มครองครบวงจร</p>
          <a href="#" class="btn plan-card__btn">ดูรายละเอียด</a>
        </div>
      </article>
      <article class="plan-card" data-category="life-accident" data-price="900" data-goals="family">
        <div class="plan-card__image">
          <img src="assets/img/plans/plan-rec-2.jpg" alt="ครอบครัวนั่งพักผ่อนร่วมกันกลางแจ้ง" width="313" height="174" loading="lazy">
        </div>
        <div class="plan-card__body">
          <h3 class="plan-card__title">Family Protection Plan</h3>
          <p class="plan-card__desc">แผนคุ้มครองครอบครัว ครอบคลุมสมาชิกหลักและผู้ติดตาม ช่วยวางแผนความมั่นคงทางการเงินเมื่อเกิดเหตุไม่คาดฝัน ปรับความคุ้มครองได้ตามขนาดครอบครัว</p>
          <a href="#" class="btn plan-card__btn">ดูรายละเอียด</a>
        </div>
      </article>
      <article class="plan-card" data-category="investment-linked" data-price="2500" data-goals="invest" data-promo="new">
        <div class="plan-card__image">
          <img src="assets/img/plans/plan-rec-3.jpg" alt="คู่รักวางแผนการเงินและอนาคตร่วมกัน" width="313" height="174" loading="lazy">
        </div>
        <div class="plan-card__body">
          <h3 class="plan-card__title">Investment Linked Plan</h3>
          <p class="plan-card__desc">ประกันชีวิตควบการลงทุน สร้างโอกาสผลตอบแทนระยะยาว พร้อมความคุ้มครองชีวิต ปรับพอร์ตการลงทุนได้ตามความเสี่ยงที่ยอมรับได้</p>
          <a href="#" class="btn plan-card__btn">ดูรายละเอียด</a>
        </div>
      </article>
      <article class="plan-card" data-category="savings" data-price="1100" data-goals="retire,tax">
        <div class="plan-card__image">
          <img src="assets/img/plans/plan-rec-4.jpg" alt="แพทย์ให้คำปรึกษาด้านสุขภาพ" width="313" height="174" loading="lazy">
        </div>
        <div class="plan-card__body">
          <h3 class="plan-card__title">Retirement Plan</h3>
          <p class="plan-card__desc">วางแผนเกษียณอย่างมั่นใจ รับเงินคืนตามระยะเวลา พร้อมสิทธิลดหย่อนภาษี สร้างกองทุนเกษียณที่มั่นคงสำหรับชีวิตหลังทำงาน</p>
          <a href="#" class="btn plan-card__btn">ดูรายละเอียด</a>
        </div>
      </article>
      <article class="plan-card" data-category="life-accident" data-price="450" data-goals="family" data-promo="new">
        <div class="plan-card__image">
          <img src="assets/img/consult-advisor.jpg" alt="ที่ปรึกษาประกันชีวิต" width="313" height="174" loading="lazy">
        </div>
        <div class="plan-card__body">
          <h3 class="plan-card__title">Accident Protection</h3>
          <p class="plan-card__desc">คุ้มครองอุบัติเหตุ 24 ชั่วโมง ทั้งในและนอกประเทศ จ่ายผลประโยชน์เมื่อเสียชีวิตหรือทุพพลภาพจากอุบัติเหตุ เบี้ยประกันเริ่มต้นเข้าถึงได้</p>
          <a href="#" class="btn plan-card__btn">ดูรายละเอียด</a>
        </div>
      </article>
    </div>
        <div class="pagination">
      <span class="pagination__item active">1</span>
      <span class="pagination__item">2</span>
      <span class="pagination__item">3</span>
      <span class="pagination__item">4</span>
      <span class="pagination__item">5</span>
      <span class="pagination__item"><i data-lucide="chevrons-right"></i></span>
      <select class="pagination__select"><option>5..</option></select>
        </div>
      </div>
    </div>
  </div>
</section>

${consultModal}`;

const blogBody = `
<section class="section section--page-intro section--blog-intro">
  <div class="container">
    <h1 class="page-intro__title">บทความและความรู้</h1>
    <p class="page-intro__desc">รวมบทความเกี่ยวกับประกันชีวิต สุขภาพ การวางแผนการเงิน การเคลม และเคล็ดลับที่ช่วยให้คุณตัดสินใจเลือกแผนประกันที่เหมาะกับชีวิตได้อย่างมั่นใจ</p>
  </div>
</section>

<section class="section section--blog-listing">
  <div class="container">
    <div class="plan-listing blog-listing">
      <aside class="plan-listing__sidebar blog-listing__sidebar" aria-label="ตัวกรองบทความ">
        <div class="blog-filter">
          <h3 class="plan-filter__title">ค้นหาบทความ</h3>
          <label class="blog-filter__search">
            <i data-lucide="search" aria-hidden="true"></i>
            <input type="search" id="blogListingSearch" class="blog-filter__search-input" placeholder="พิมพ์คำค้นหา..." autocomplete="off">
          </label>
        </div>
        <div class="plan-filter blog-filter">
          <h3 class="plan-filter__title">หมวดหมู่</h3>
          <div class="plan-filter__chips blog-filter__chips">
            <button type="button" class="plan-filter__chip blog-filter__chip is-active" data-filter-value="all">ทั้งหมด</button>
            <button type="button" class="plan-filter__chip blog-filter__chip" data-filter-value="life">ประกันชีวิต</button>
            <button type="button" class="plan-filter__chip blog-filter__chip" data-filter-value="health">ประกันสุขภาพ</button>
            <button type="button" class="plan-filter__chip blog-filter__chip" data-filter-value="planning">การวางแผนการเงิน</button>
            <button type="button" class="plan-filter__chip blog-filter__chip" data-filter-value="claim">การเคลมและบริการ</button>
          </div>
        </div>
      </aside>
      <div class="plan-listing__main blog-listing__main">
        <div class="plan-listing__toolbar">
          <p class="plan-listing__count">พบทั้งหมด <strong id="blogListingCount">6</strong> บทความ</p>
          <div class="plan-listing__actions">
            <button type="button" class="plan-listing__clear" id="blogListingClear" disabled>
              <i data-lucide="rotate-ccw" aria-hidden="true"></i>
              ล้างตัวกรอง
            </button>
            <label class="plan-listing__sort-wrap">
              <span class="sr-only">เรียงลำดับ</span>
              <select class="plan-listing__sort" id="blogListingSort" aria-label="เรียงลำดับ">
                <option value="newest">ใหม่ล่าสุด</option>
                <option value="oldest">เก่าสุด</option>
                <option value="title">ชื่อ ก – ฮ</option>
              </select>
            </label>
          </div>
        </div>
        <div class="card-grid card-grid--listing card-grid--articles blog-listing__grid" id="blogListingGrid">
          <p class="plan-listing__empty" id="blogListingEmpty" role="status">ไม่พบบทความที่ตรงกับตัวกรอง ลองเปลี่ยนคำค้นหาหรือหมวดหมู่</p>
        </div>
      </div>
    </div>
  </div>
</section>`;

function wrapPage({ title, header, body, scripts }) {
  return `<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>${title}</title>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/site-chrome.css">
</head>
<body>

${header}

${body}

${footer}
${scripts}
</body>
</html>
`;
}

writeUtf8(
  'plan.html',
  wrapPage({
    title: 'แผนประกัน - Agent Thailand',
    header: makeHeader({ productsActive: true }),
    body: planBody,
    scripts: `<script src="https://unpkg.com/lucide@0.477.0"></script>
<script src="assets/js/hero-form-data.js"></script>
<script src="assets/js/main.js"></script>`,
  })
);

writeUtf8(
  'blog.html',
  wrapPage({
    title: 'บทความ - Agent Thailand',
    header: makeHeader({ blogActive: true }),
    body: blogBody,
    scripts: `<script src="https://unpkg.com/lucide@0.477.0"></script>
<script src="assets/js/hero-form-data.js"></script>
<script src="assets/js/blog-data.js"></script>
<script src="assets/js/main.js"></script>`,
  })
);

console.log('Restored plan.html and blog.html with UTF-8 encoding.');
