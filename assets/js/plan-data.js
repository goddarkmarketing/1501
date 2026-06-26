const PLAN_CATEGORY_LABELS = {
  health: 'ประกันสุขภาพ',
  critical: 'ประกันโรคร้ายแรง',
  'life-accident': 'ประกันชีวิตและอุบัติเหตุ',
  savings: 'ประกันสะสมทรัพย์',
  'investment-linked': 'ประกันชีวิตควบการลงทุน',
};

const PLAN_REQUIREMENTS = [
  {
    icon: 'assets/img/plan-detail/identity.svg',
    title: 'บัตรประชาชน',
    desc: 'เตรียมบัตรประชาชนตัวจริง',
  },
  {
    icon: 'assets/img/plan-detail/camera.svg',
    title: 'ยืนยันตัวตน',
    desc: 'ถ่ายรูปเซลฟี่ถือบัตรประชาชนเพื่อยืนยันตัวตน',
  },
  {
    icon: 'assets/img/plan-detail/visa.svg',
    title: 'ชำระเงิน',
    desc: 'บัตรเครดิต หรือแอปธนาคารสำหรับชำระเบี้ย',
  },
];

const PLAN_SECTIONS = [
  { id: 'highlights', label: 'จุดเด่นของแผนประกันนี้' },
  { id: 'compare', label: 'เปรียบเทียบแผนความคุ้มครอง' },
  { id: 'promo', label: 'โปรโมชันประกันสุขภาพ' },
  { id: 'conditions', label: 'รายละเอียดและเงื่อนไขกรมธรรม์' },
  { id: 'renewal', label: 'การต่ออายุ' },
  { id: 'why', label: 'ทำไมต้องมีประกันนี้?' },
  { id: 'faq', label: 'คำถามที่พบบ่อย (FAQ)' },
];

const PLAN_CATEGORY_META = {
  health: {
    promoSection: 'โปรโมชันประกันสุขภาพ',
    whySection: 'ทำไมต้องมีประกันสุขภาพ?',
    listingGoals: 'health,family',
    icon: 'shield-plus',
    headline: 'เลือกแผนประกันสุขภาพที่เหมาะกับทุกไลฟ์สไตล์',
    heroImage: 'assets/img/hero/ประกัน/สุขภาพ.png',
    introTitle: 'ทำไมเราถึงต้องการประกันสุขภาพ?',
    introText:
      'ทุกคนมีความต้องการความคุ้มครองสุขภาพแตกต่างกัน ไม่ว่าจะเพื่อรับมือค่ารักษาผู้ป่วยใน (IPD) ผู้ป่วยนอก (OPD) หรือชดเชยรายได้เมื่อนอนโรงพยาบาล เรามีแผนประกันสุขภาพหลากหลาย คุ้มค่า ครอบคลุมทุกช่วงวัย ให้คุณและคนที่คุณรักอุ่นใจในยามเจ็บป่วย',
    features: [
      {
        title: 'แบบประกันตอบโจทย์ทุกความต้องการ',
        desc: 'ไม่ว่ามีงบเท่าไหร่ก็เลือกแผนประกันสุขภาพที่เหมาะกับคุณได้ พร้อมเลือกงวดชำระเบี้ยได้ตามต้องการ',
      },
      {
        title: 'ออกแบบความคุ้มครองได้',
        desc: 'ปรับแต่งความคุ้มครองหลัก เสริม OPD และค่าชดเชยรายวันให้ตรงกับไลฟ์สไตล์ของคุณ',
      },
      {
        title: 'เข้ารับการรักษาโดยไม่ต้องสำรองจ่าย',
        desc: 'ใช้สิทธิ์ที่โรงพยาบาลและคลินิกในเครือข่ายทั่วประเทศได้อย่างสะดวก',
      },
    ],
  },
  critical: {
    promoSection: 'โปรโมชันประกันโรคร้ายแรง',
    whySection: 'ทำไมต้องมีประกันโรคร้ายแรง?',
    listingGoals: 'health,family',
    icon: 'activity',
    headline: 'คุ้มครองโรคร้ายแรง มั่นใจเมื่อวินิจฉัย',
    heroImage: 'assets/img/hero/ประกัน/โรคร้ายแรง.png',
    introTitle: 'ทำไมเราถึงต้องการประกันโรคร้ายแรง?',
    introText:
      'โรคร้ายแรงอาจเกิดขึ้นได้โดยไม่คาดคิด และส่งผลกระทบต่อการเงินของครอบครัวอย่างมาก ประกันโรคร้ายแรงจ่ายเงินก้อนเมื่อวินิจฉัย ช่วยให้คุณโฟกัสกับการรักษาได้เต็มที่',
    features: [
      {
        title: 'จ่ายเงินก้อนเมื่อวินิจฉัย',
        desc: 'รับผลประโยชน์ทันทีเมื่อวินิจฉัยโรคร้ายแรงตามเงื่อนไข ไม่ต้องส่งใบเสร็จ',
      },
      {
        title: 'คุ้มครองหลายกลุ่มโรค',
        desc: 'เลือกแผนที่ครอบคลุมกลุ่มโรคที่สำคัญ เช่น มะเร็ง โรคหัวใจ และโรคไต',
      },
      {
        title: 'วางแผนการเงินระยะยาว',
        desc: 'ใช้เงินก้อนนี้ดูแลครอบครัวและค่าใช้จ่ายระหว่างการรักษาได้อย่างอิสระ',
      },
    ],
  },
  'life-accident': {
    promoSection: 'โปรโมชันประกันชีวิตและอุบัติเหตุ',
    whySection: 'ทำไมต้องมีประกันชีวิตและอุบัติเหตุ?',
    listingGoals: 'family,health',
    icon: 'heart-handshake',
    headline: 'คุ้มครองชีวิตและอุบัติเหตุให้ครอบครัวอุ่นใจ',
    heroImage: 'assets/img/hero/ประกัน/อุบัติเหตุ.png',
    introTitle: 'ทำไมเราถึงต้องการประกันชีวิตและอุบัติเหตุ?',
    introText:
      'ประกันชีวิตและอุบัติเหตุช่วยสร้างความมั่นคงให้ครอบครัวเมื่อเกิดเหตุไม่คาดฝัน ไม่ว่าจะเป็นรายได้ทดแทน ค่ารักษาพยาบาล หรือเงินก้อนเพื่อวางแผนอนาคตของคนที่คุณรัก',
    features: [
      {
        title: 'คุ้มครองครอบครัวเมื่อเกิดเหตุไม่คาดฝัน',
        desc: 'ผลประโยชน์กรณีเสียชีวิตและทุพพลภาพช่วยลดภาระทางการเงินของครอบครัว',
      },
      {
        title: 'คุ้มครองอุบัติเหตุ 24 ชั่วโมง',
        desc: 'อุ่นใจได้ทุกวัน ไม่ว่าจะทำงาน เดินทาง หรือใช้ชีวิตในช่วงเวลาใด',
      },
      {
        title: 'เบี้ยประกันเข้าถึงได้',
        desc: 'เลือกทุนประกันและแผนที่เหมาะกับงบประมาณของคุณได้',
      },
    ],
  },
  savings: {
    promoSection: 'โปรโมชันประกันสะสมทรัพย์',
    whySection: 'ทำไมต้องวางแผนออมทรัพย์?',
    listingGoals: 'retire,invest,tax',
    icon: 'wallet',
    headline: 'ออมทรัพย์พร้อมความคุ้มครอง วางแผนอนาคตอย่างมั่นใจ',
    heroImage: 'assets/img/hero/ประกัน/สะสมทรัพย์.png',
    introTitle: 'ทำไมเราถึงต้องการประกันสะสมทรัพย์?',
    introText:
      'ประกันสะสมทรัพย์ช่วยสร้างวินัยการออมระยะยาว พร้อมความคุ้มครองชีวิตและผลประโยชน์ตามระยะเวลา เหมาะสำหรับการวางแผนเกษียณ การศึกษาบุตร หรือสร้างเงินก้อนในอนาคต',
    features: [
      {
        title: 'สร้างวินัยการออม',
        desc: 'จ่ายเบี้ยตามแผนที่เลือกและรับเงินคืนตามระยะเวลาที่กำหนด',
      },
      {
        title: 'คุ้มครองชีวิตควบคู่การออม',
        desc: 'ได้รับความคุ้มครองระหว่างสัญญา พร้อมโอกาสรับผลประโยชน์เมื่อครบกำหนด',
      },
      {
        title: 'วางแผนเป้าหมายระยะยาว',
        desc: 'เลือกระยะเวลาจ่ายเบี้ยและความคุ้มครองให้สอดคล้องกับเป้าหมายการเงิน',
      },
    ],
  },
  'investment-linked': {
    promoSection: 'โปรโมชันประกันควบการลงทุน',
    whySection: 'ทำไมต้องลงทุนควบประกันชีวิต?',
    listingGoals: 'invest,retire',
    icon: 'chart-no-axes-combined',
    headline: 'ลงทุนอย่างยืดหยุ่น พร้อมความคุ้มครองชีวิต',
    heroImage: 'assets/img/hero/ประกัน/การลงทุน.png',
    introTitle: 'ทำไมเราถึงต้องการประกันชีวิตควบการลงทุน?',
    introText:
      'ประกันชีวิตควบการลงทุนผสมผสานการสร้างความมั่นคงและโอกาสรับผลตอบแทน คุณเลือกกองทุนและปรับพอร์ตได้ตามความเสี่ยงที่ยอมรับ พร้อมความคุ้มครองชีวิตตลอดสัญญา',
    features: [
      {
        title: 'เลือกกองทุนลงทุนได้',
        desc: 'ปรับสัดส่วนการลงทุนตามเป้าหมายและระดับความเสี่ยงของคุณ',
      },
      {
        title: 'ความคุ้มครองชีวิตตลอดสัญญา',
        desc: 'มั่นใจว่าครอบครัวได้รับการดูแลแม้ในช่วงเวลาที่คุณลงทุนระยะยาว',
      },
      {
        title: 'ยืดหยุ่นสูง',
        desc: 'ปรับทุนประกันและกองทุนได้ตามช่วงชีวิตและเป้าหมายทางการเงิน',
      },
    ],
  },
};

function getPlanCategoryPage(category) {
  const label = PLAN_CATEGORY_LABELS[category];
  const meta = PLAN_CATEGORY_META[category];
  if (!label || !meta) return null;
  return {
    id: category,
    label,
    ...meta,
    products: getPlanProductsByCategory(category),
  };
}

const QUOTE_SLIDER_PRESETS = {
  health: [
    { id: 'main', label: 'ความคุ้มครองหลัก', values: [500000, 750000, 1000000, 1250000, 1500000] },
    {
      id: 'opd',
      label: 'ค่ารักษาพยาบาลแบบผู้ป่วยนอก OPD+',
      hint: 'โอพีดี พลัส+ (OPD+) 1 ครั้ง ต่อวัน สูงสุด 30 ครั้งต่อปี',
      values: [0, 500, 1000, 1500, 2000, 2500, 3000],
    },
    {
      id: 'daily',
      label: 'ค่าชดเชยรายวัน',
      hint: 'หากเจ็บป่วยด้วย 7 โรคร้ายแรง สูงสุดถึง 700 วัน',
      values: [0, 500, 1000, 1500, 2000, 2500, 3000],
    },
  ],
  critical: [
    { id: 'main', label: 'วงเงินคุ้มครองเมื่อวินิจฉัย', values: [300000, 500000, 1000000, 1500000, 2000000] },
    {
      id: 'opd',
      label: 'จำนวนกลุ่มโรคที่คุ้มครอง',
      hint: 'ขยายความคุ้มครองครอบคลุมหลายกลุ่มโรคร้ายแรง',
      values: [1, 2, 3, 4, 5],
      format: 'plain',
    },
    {
      id: 'daily',
      label: 'ผลประโยชน์เมื่อเคลมซ้ำ',
      hint: 'จ่ายเพิ่มเมื่อโรคกลับมาหรือเคลมในระยะที่กำหนด',
      values: [0, 100000, 200000, 300000, 500000],
    },
  ],
  'life-accident': [
    { id: 'main', label: 'ทุนประกันชีวิต', values: [500000, 1000000, 1500000, 2000000, 3000000] },
    {
      id: 'opd',
      label: 'ผลประโยชน์อุบัติเหตุ',
      hint: 'คุ้มครองอุบัติเหตุ 24 ชั่วโมงทั่วโลก',
      values: [100000, 200000, 300000, 500000, 1000000],
    },
    {
      id: 'daily',
      label: 'ผลประโยชน์ทุพพลภาพ',
      hint: 'ชดเชยรายได้เมื่อสูญเสียความสามารถในการทำงาน',
      values: [0, 100000, 200000, 300000, 500000],
    },
  ],
  savings: [
    { id: 'main', label: 'เงินคืนสะสมต่อปี', values: [10000, 20000, 30000, 50000, 100000] },
    {
      id: 'opd',
      label: 'ระยะเวลาจ่ายเบี้ย',
      hint: 'เลือกจำนวนปีที่ต้องการจ่ายเบี้ยประกัน',
      values: [5, 10, 15, 20, 25],
      format: 'plain',
    },
    {
      id: 'daily',
      label: 'ระยะเวลาคุ้มครอง',
      hint: 'ระยะเวลาที่ได้รับความคุ้มครองตามแผน',
      values: [5, 10, 15, 20, 25],
      format: 'plain',
    },
  ],
  'investment-linked': [
    { id: 'main', label: 'ทุนประกันชีวิต', values: [500000, 1000000, 2000000, 3000000, 5000000] },
    {
      id: 'opd',
      label: 'เงินลงทุนเริ่มต้นต่อปี',
      hint: 'กำหนดงบลงทุนต่อปีในกองทุนที่เลือก',
      values: [12000, 24000, 36000, 60000, 120000],
    },
    {
      id: 'daily',
      label: 'ความคุ้มครองชีวิตขั้นต่ำ',
      hint: 'ทุนประกันขั้นต่ำที่รับประกันตลอดสัญญา',
      values: [100000, 200000, 500000, 1000000, 2000000],
    },
  ],
};

const QUOTE_LABEL_PRESETS = {
  health: {
    mainLimit: 'วงเงินค่ารักษาเหมาจ่ายสูงสุด',
    mainUnit: 'บาท/ปี',
    stat1: 'ค่ารักษาพยาบาลแบบผู้ป่วยนอก OPD',
    stat1Unit: 'บาท/ครั้ง',
    stat2: 'ค่าชดเชยรายวัน',
    stat2Unit: 'บาท/วัน',
  },
  critical: {
    mainLimit: 'วงเงินคุ้มครองโรคร้ายแรง',
    mainUnit: 'บาท',
    stat1: 'จำนวนกลุ่มโรคที่คุ้มครอง',
    stat1Unit: 'กลุ่ม',
    stat2: 'ผลประโยชน์เคลมซ้ำ',
    stat2Unit: 'บาท',
  },
  'life-accident': {
    mainLimit: 'ทุนประกันชีวิต',
    mainUnit: 'บาท',
    stat1: 'ผลประโยชน์อุบัติเหตุ',
    stat1Unit: 'บาท',
    stat2: 'ผลประโยชน์ทุพพลภาพ',
    stat2Unit: 'บาท',
  },
  savings: {
    mainLimit: 'เงินคืนสะสมต่อปี',
    mainUnit: 'บาท/ปี',
    stat1: 'ระยะเวลาจ่ายเบี้ย',
    stat1Unit: 'ปี',
    stat2: 'ระยะเวลาคุ้มครอง',
    stat2Unit: 'ปี',
  },
  'investment-linked': {
    mainLimit: 'ทุนประกันชีวิต',
    mainUnit: 'บาท',
    stat1: 'เงินลงทุนเริ่มต้นต่อปี',
    stat1Unit: 'บาท/ปี',
    stat2: 'ความคุ้มครองชีวิตขั้นต่ำ',
    stat2Unit: 'บาท',
  },
};

function getSectionsForCategory(category) {
  const meta = PLAN_CATEGORY_META[category] || {};
  return PLAN_SECTIONS.map((section) => {
    if (section.id === 'promo' && meta.promoSection) {
      return { ...section, label: meta.promoSection };
    }
    if (section.id === 'why' && meta.whySection) {
      return { ...section, label: meta.whySection };
    }
    return { ...section };
  });
}

function buildTierPresets(sliders) {
  const mainValues = sliders.find((s) => s.id === 'main')?.values || [500000, 1000000, 1500000];
  const opdValues = sliders.find((s) => s.id === 'opd')?.values || [0, 1500, 3000];
  const dailyValues = sliders.find((s) => s.id === 'daily')?.values || [0, 1000, 3000];
  const pick = (arr, index) => arr[Math.min(index, arr.length - 1)];

  return {
    basic: { main: pick(mainValues, 0), opd: pick(opdValues, 0), daily: pick(dailyValues, 1) },
    standard: {
      main: pick(mainValues, Math.floor(mainValues.length / 2)),
      opd: pick(opdValues, Math.floor(opdValues.length / 2)),
      daily: pick(dailyValues, 0),
    },
    advance: {
      main: pick(mainValues, mainValues.length - 1),
      opd: pick(opdValues, opdValues.length - 1),
      daily: pick(dailyValues, dailyValues.length - 1),
    },
  };
}

function buildQuoteConfig(product) {
  if (product.quoteConfig) return product.quoteConfig;

  const category = product.category || 'health';
  const sliders = QUOTE_SLIDER_PRESETS[category] || QUOTE_SLIDER_PRESETS.health;
  const labels = QUOTE_LABEL_PRESETS[category] || QUOTE_LABEL_PRESETS.health;
  const tierPresets = product.quoteTierPresets || buildTierPresets(sliders);
  const mainBase = sliders[0]?.values?.[0] || 500000;
  const priceFrom = product.priceFrom || 665;
  const baseRate = product.quoteBaseRate || Math.round(priceFrom * 14);

  return {
    discountPercent: product.quoteDiscount ?? 10,
    defaultTier: 'basic',
    baseRate,
    mainBase,
    labels,
    tierPresets,
    tierMainValues: {
      basic: tierPresets.basic.main,
      standard: tierPresets.standard.main,
      advance: tierPresets.advance.main,
    },
    sliders,
  };
}

function getPlanProductsByCategory(category) {
  return getAllPlanProducts().filter((product) => product.category === category);
}

function getRelatedPlanProducts(product, limit = 4) {
  if (!product) return [];

  const sameCategory = getPlanProductsByCategory(product.category).filter((item) => item.id !== product.id);
  const others = getAllPlanProducts().filter(
    (item) => item.id !== product.id && item.category !== product.category
  );

  return [...sameCategory, ...others].slice(0, limit);
}

function createPlanProduct(base) {
  const product = {
    requirements: PLAN_REQUIREMENTS,
    promo: {
      text: 'รับส่วนลดเบี้ยปีแรก และสิทธิพิเศษเมื่อสมัครภายในเดือนนี้',
      code: 'AGENTTH',
      until: '30/06/2569',
    },
    tiers: [
      { id: 'basic', label: 'Basic', amount: '500,000', unit: 'บาท/ปี', popular: false },
      { id: 'standard', label: 'Standard', amount: '1,000,000', unit: 'บาท/ปี', popular: true },
      { id: 'advance', label: 'Advance', amount: '1,500,000', unit: 'บาท/ปี', popular: false },
    ],
    coverageSummary: [
      { label: 'วงเงินค่ารักษาเหมาจ่ายสูงสุด', value: '1,000,000', unit: 'บาท/ปี' },
      { label: 'ค่ารักษาผู้ป่วยนอก OPD', value: '1,500', unit: 'บาท/ครั้ง' },
      { label: 'ค่าชดเชยรายวัน', value: '3,000', unit: 'บาท/วัน' },
    ],
    highlights: [
      'สมัครออนไลน์ได้รวดเร็ว ไม่ยุ่งยาก',
      'ทีมตัวแทนพร้อมให้คำปรึกษาแบบตัวต่อตัว',
      'เครือข่ายโรงพยาบาลทั่วประเทศ',
      'ปรับแผนความคุ้มครองได้ตามงบประมาณ',
    ],
    coverageRows: [
      { label: 'วงเงินคุ้มครองหลักต่อปี', values: ['500,000', '1,000,000', '1,500,000'] },
      { label: 'ค่ารักษาผู้ป่วยใน (IPD)', values: ['เหมาจ่ายตามจริง', 'เหมาจ่ายตามจริง', 'เหมาจ่ายตามจริง'] },
      { label: 'ผู้ป่วยนอก OPD (เสริม)', values: ['สูงสุด 1,500', 'สูงสุด 1,500', 'สูงสุด 3,000'] },
      { label: 'ค่าชดเชยรายวัน', values: ['ไม่รวม', '3,000', '3,000'] },
    ],
    conditions: [
      'ผู้ขอเอาประกันภัยควรทำความเข้าใจรายละเอียดความคุ้มครองและเงื่อนไขก่อนตัดสินใจทุกครั้ง',
      'อายุรับประกันและเงื่อนไขการต่ออายุเป็นไปตามที่ระบุในกรมธรรม์',
      'การพิจารณารับประกันขึ้นอยู่กับหลักเกณฑ์ของบริษัทประกันชีวิต',
    ],
    sections: PLAN_SECTIONS,
    promoDetail: [],
    renewal: [
      'สามารถต่ออายุกรมธรรม์ได้ตามเงื่อนไขที่บริษัทกำหนด',
      'เบี้ยประกันอาจปรับตามอายุและเงื่อนไขการต่ออายุ',
    ],
    why: [
      'ค่ารักษาพยาบาลสูงขึ้นเรื่อยๆ ประกันช่วยลดภาระค่าใช้จ่าย',
      'เข้าถึงการรักษาได้ทันทีโดยไม่ต้องกังวลเรื่องเงิน',
      'วางแผนความมั่นคงทางการเงินให้ครอบครัว',
    ],
    customizeOptions: [],
    faqs: [
      {
        q: 'สามารถซื้อแผนนี้ได้กี่กรมธรรม์ต่อคน?',
        a: 'โดยทั่วไปสามารถถือกรมธรรม์ได้ตามเงื่อนไขของบริษัทประกัน แนะนำให้ปรึกษาตัวแทนก่อนสมัคร',
      },
      {
        q: 'ใช้สิทธิ์ที่โรงพยาบาลในเครือได้อย่างไร?',
        a: 'ยื่นบัตรประชาชนก่อนเข้ารับบริการที่โรงพยาบาลในเครือข่าย โดยไม่ต้องสำรองจ่าย (ยกเว้นมีค่าใช้จ่ายเกินสิทธิ์)',
      },
    ],
    priceFrom: 665,
    priceNote: 'เบี้ยประมาณการ ขึ้นอยู่กับอายุ เพศ และแผนที่เลือก',
    heroImage: PLAN_CATEGORY_META[base.category]?.heroImage || 'assets/img/hero-bg.jpg',
    ...base,
  };

  if (!base.heroImage) {
    product.heroImage = PLAN_CATEGORY_META[product.category]?.heroImage || 'assets/img/hero-bg.jpg';
  }

  product.sections = base.sections || getSectionsForCategory(product.category);
  if (!base.faqs) {
    product.faqs = getDefaultFaqs(product.category);
  }
  product.quoteConfig = buildQuoteConfig(product);
  return product;
}

function getDefaultFaqs(category) {
  const common = [
    {
      q: 'สามารถซื้อแผนนี้ได้กี่กรมธรรม์ต่อคน?',
      a: 'โดยทั่วไปสามารถถือกรมธรรม์ได้ตามเงื่อนไขของบริษัทประกัน แนะนำให้ปรึกษาตัวแทนก่อนสมัคร',
    },
    {
      q: 'ชำระเบี้ยประกันได้ด้วยวิธีใดบ้าง?',
      a: 'รองรับบัตรเครดิต แอปธนาคาร และช่องทางชำระออนไลน์ตามที่ระบุในขั้นตอนสมัคร',
    },
  ];
  const byCategory = {
    health: [
      {
        q: 'ใช้สิทธิ์ที่โรงพยาบาลในเครือได้อย่างไร?',
        a: 'ยื่นบัตรประชาชนก่อนเข้ารับบริการที่โรงพยาบาลในเครือข่าย โดยไม่ต้องสำรองจ่าย (ยกเว้นมีค่าใช้จ่ายเกินสิทธิ์)',
      },
    ],
    critical: [
      {
        q: 'เมื่อวินิจฉัยโรคร้ายแรงจะได้รับเงินอย่างไร?',
        a: 'จ่ายผลประโยชน์เป็นเงินก้อนตามวงเงินที่เลือก เมื่อได้รับการวินิจฉัยจากแพทย์ตามเงื่อนไขกรมธรรม์',
      },
    ],
    'life-accident': [
      {
        q: 'คุ้มครองอุบัติเหตุนอกประเทศหรือไม่?',
        a: 'ขึ้นอยู่กับแผนที่เลือก โดยทั่วไปคุ้มครองอุบัติเหตุทั่วโลกตามเงื่อนไขกรมธรรม์',
      },
    ],
    savings: [
      {
        q: 'ได้รับเงินคืนเมื่อใด?',
        a: 'ได้รับเงินคืนตามระยะเวลาและเงื่อนไขที่ระบุในกรมธรรม์ของแต่ละแผน',
      },
    ],
    'investment-linked': [
      {
        q: 'สามารถเปลี่ยนกองทุนลงทุนได้หรือไม่?',
        a: 'สามารถสลับกองทุนหรือปรับสัดส่วนการลงทุนได้ตามเงื่อนไขและช่วงเวลาที่กำหนด',
      },
    ],
  };
  return [...common, ...(byCategory[category] || [])];
}

const PLAN_PRODUCTS = {
  'easy-e-health': createPlanProduct({
    id: 'easy-e-health',
    name: 'Easy E-Health',
    category: 'health',
    tagline: 'ประกันสุขภาพเหมาจ่าย',
    headline: 'ประกันสุขภาพที่ดูแลค่ารักษาพยาบาลแบบไม่มีลิมิต ไม่ต้องตรวจสุขภาพ',
    benefits: [
      'ประกันสุขภาพเหมาจ่ายผู้ป่วยใน (IPD) ไม่จำกัดค่ารักษาต่อครั้ง',
      'คุ้มครองสูงสุด 1.5 ล้านบาท/ปี ไม่แยกค่าใช้จ่ายให้จุกจิก',
      'เพิ่มแพ็ก OPD ได้ คุ้มครองเมื่อป่วยเบาๆ ไม่ต้องนอนโรงพยาบาล',
      'ไม่ต้องสำรองจ่ายเมื่อรักษาที่โรงพยาบาลเครือข่ายกว่า 1,000 แห่งทั่วไทย',
    ],
    promo: {
      text: 'รับส่วนลด 12% สำหรับเบี้ยปีแรก แผน 1 ล้าน รับ Shopee code 500 บ. แผน 1.5 ล้าน รับ Shopee code 1,000 บ.',
      code: 'MIDYEAR',
      until: '30/06/2569',
    },
    highlights: [
      'ไม่ต้องตรวจสุขภาพก่อนทำประกัน',
      'เหมาจ่ายค่ารักษาตามจริง สูงสุด 1.5 ล้านบาทต่อปี',
      'ส่วนลดเบี้ย 10% ปีถัดไป หากไม่มีเคลม',
      'รับประกันอายุ 20–60 ปี ต่ออายุได้ถึง 80 ปี',
      'ครอบคลุมการผ่าตัดเล็ก-ใหญ่ มะเร็ง เคมีบำบัด และ Targeted Therapy',
      'ค่าห้องสูงสุด 4,500 บาท/วัน นานถึง 365 วัน',
    ],
    coverageRows: [
      { label: 'วงเงินคุ้มครอง IPD ต่อปี', values: ['500,000', '1,000,000', '1,500,000'] },
      { label: 'ค่ารักษาเหมาจ่ายต่อครั้ง', values: ['ไม่จำกัด', 'ไม่จำกัด', 'ไม่จำกัด'] },
      { label: 'โอพีดี พลัส (เสริม)', values: ['0 – 1,500', '0 – 1,500', '0 – 3,000'] },
      { label: 'ค่าชดเชยรายวัน (7 โรคร้ายแรง)', values: ['0', '0 – 3,000', '0 – 3,000'] },
      { label: 'ค่าห้องสูงสุด', values: ['4,500 บาท/วัน', '4,500 บาท/วัน', '4,500 บาท/วัน'] },
      { label: 'ส่วนลดไม่มีเคลมปีถัดไป', values: ['10%', '10%', '10%'] },
    ],
    conditions: [
      'รับประกันอายุ 20–60 ปี สามารถต่ออายุความคุ้มครองได้ถึง 80 ปี',
      'ซื้อได้สูงสุด 1 กรมธรรม์ต่อ 1 คน',
      'ผู้ซื้อควรทำความเข้าใจรายละเอียดความคุ้มครองและเงื่อนไขก่อนตัดสินใจทุกครั้ง',
      'การพิจารณารับประกันขึ้นอยู่กับหลักเกณฑ์ของบริษัท เอฟดับบลิวดี ประกันชีวิต',
    ],
    faqs: [
      {
        q: 'หากต้องการความคุ้มครองผู้ป่วยในเมื่อต้องนอนโรงพยาบาล ต้องเลือกแบบใด?',
        a: 'Easy E-Health ให้ความคุ้มครอง IPD เป็นหลัก เลือกวงเงินได้ 3 แผน คือ 500,000 / 1,000,000 / 1,500,000 บาทต่อปี เหมาจ่ายค่ารักษาตามจริง',
      },
      {
        q: 'แบบประกันไหนคุ้มครองผู้ป่วยนอก (OPD)?',
        a: 'สามารถซื้อโอพีดี พลัส เพิ่มเติมได้หลังเลือกความคุ้มครองหลัก วงเงินสูงสุด 3,000 บาทต่อครั้ง (1 ครั้งต่อวัน)',
      },
      {
        q: 'โรงพยาบาลในเครือข่ายมีที่ไหนบ้าง?',
        a: 'เครือข่ายกว่า 1,000 แห่งทั่วประเทศ ค้นหาได้ที่หน้าโรงพยาบาลในเครือของเรา',
      },
    ],
    priceFrom: 665,
    priceNote: 'ผ่อน 0% 6 เดือน เริ่มต้นประมาณ 4,078 บาท/เดือน (แผน Standard)',
    promoDetail: [
      'รับส่วนลด 12% สำหรับเบี้ยปีแรก',
      'แผน 1 ล้าน รับ Shopee code 500 บาท',
      'แผน 1.5 ล้าน รับ Shopee code 1,000 บาท',
    ],
    renewal: [
      'ต่ออายุได้ถึง 80 ปี',
      'รับส่วนลด 10% เบี้ยปีถัดไป หากไม่มีเคลม',
      'ไม่ต้องตรวจสุขภาพใหม่เมื่อต่ออายุตามเงื่อนไข',
    ],
    why: [
      'ค่ารักษาพยาบาลในประเทศไทยสูงขึ้นทุกปี',
      'ประกันสุขภาพเหมาจ่ายช่วยให้รักษาได้เต็มที่โดยไม่ต้องสำรองจ่าย',
      'เครือข่ายโรงพยาบาลกว่า 1,000 แห่งทั่วประเทศ',
      'ป้องกันภาระทางการเงินเมื่อเกิดโรคร้ายหรืออุบัติเหตุ',
    ],
    customizeOptions: [
      { label: 'ความคุ้มครองหลัก', range: '500,000 – 1,500,000 บาท/ปี' },
      { label: 'โอพีดี พลัส', range: '0 – 3,000 บาท/ครั้ง' },
      { label: 'ค่าชดเชยรายวัน', range: '0 – 3,000 บาท/วัน' },
    ],
    sections: PLAN_SECTIONS.map((section) =>
      section.id === 'why'
        ? { ...section, label: 'ทำไมต้องมีประกันสุขภาพเหมาจ่าย?' }
        : section
    ),
    quoteConfig: {
      discountPercent: 12,
      defaultTier: 'basic',
      baseRate: 9310,
      mainBase: 500000,
      labels: QUOTE_LABEL_PRESETS.health,
      tierMainValues: { basic: 500000, standard: 1000000, advance: 1500000 },
      tierPresets: {
        basic: { main: 500000, opd: 0, daily: 1000 },
        standard: { main: 1000000, opd: 1500, daily: 0 },
        advance: { main: 1500000, opd: 3000, daily: 3000 },
      },
      sliders: [
        {
          id: 'main',
          label: 'ความคุ้มครองหลัก',
          values: [500000, 750000, 1000000, 1250000, 1500000],
        },
        {
          id: 'opd',
          label: 'ค่ารักษาพยาบาลแบบผู้ป่วยนอก OPD+',
          hint: 'โอพีดี พลัส+ (OPD+) 1 ครั้ง ต่อวัน สูงสุด 30 ครั้งต่อปี',
          values: [0, 500, 1000, 1500, 2000, 2500, 3000],
        },
        {
          id: 'daily',
          label: 'ค่าชดเชยรายวัน',
          hint: 'หากเจ็บป่วยด้วย 7 โรคร้ายแรง สูงสุดถึง 700 วัน',
          values: [0, 500, 1000, 1500, 2000, 2500, 3000],
        },
      ],
    },
  }),
  'fwd-precious-care': createPlanProduct({
    id: 'fwd-precious-care',
    name: 'เอฟดับบลิวดี พรีเชียส แคร์',
    category: 'health',
    tagline: 'ประกันสุขภาพระดับพรีเมียม',
    headline: 'คุ้มครองสุขภาพครบวงจร วงเงินสูง บริการพิเศษ',
    benefits: [
      'คุ้มครองค่ารักษาพยาบาลทั้งผู้ป่วยในและผู้ป่วยนอก',
      'วงเงินคุ้มครองสูง ปรับแผนได้ตามไลฟ์สไตล์',
      'บริการตรวจสุขภาพและที่ปรึกษาสุขภาพ',
      'เครือข่ายโรงพยาบาลชั้นนำทั่วประเทศ',
    ],
    priceFrom: 1800,
  }),
  'opd-plus': createPlanProduct({
    id: 'opd-plus',
    name: 'โอพีดีพลัส',
    category: 'health',
    tagline: 'ประกันผู้ป่วยนอก',
    headline: 'เสริมความคุ้มครอง OPD เมื่อป่วยเบาๆ ไม่ต้องนอนโรงพยาบาล',
    benefits: [
      'คุ้มครองค่ารักษาผู้ป่วยนอกตามแผนที่เลือก',
      'ซื้อเสริมคู่กับประกันสุขภาพหลักได้',
      'ใช้สิทธิ์สะดวกที่โรงพยาบาลในเครือข่าย',
      'เลือกวงเงินได้หลายระดับ',
    ],
    tiers: [
      { id: 'basic', label: 'Plan 1', amount: '1,000', unit: 'บาท/ครั้ง', popular: false },
      { id: 'standard', label: 'Plan 2', amount: '1,500', unit: 'บาท/ครั้ง', popular: true },
      { id: 'advance', label: 'Plan 3', amount: '3,000', unit: 'บาท/ครั้ง', popular: false },
    ],
    coverageRows: [
      { label: 'วงเงินต่อครั้ง', values: ['1,000', '1,500', '3,000'] },
      { label: 'จำนวนครั้งสูงสุด/ปี', values: ['30', '30', '30'] },
      { label: 'คุ้มครองเวชภัณฑ์', values: ['รวม', 'รวม', 'รวม'] },
    ],
    priceFrom: 450,
    quoteDiscount: 8,
    quoteBaseRate: 5400,
    quoteConfig: {
      discountPercent: 8,
      defaultTier: 'basic',
      baseRate: 5400,
      mainBase: 1000,
      labels: {
        mainLimit: 'วงเงินคุ้มครอง OPD ต่อครั้ง',
        mainUnit: 'บาท/ครั้ง',
        stat1: 'จำนวนครั้งสูงสุดต่อปี',
        stat1Unit: 'ครั้ง',
        stat2: 'ค่าเวชภัณฑ์ที่คุ้มครอง',
        stat2Unit: 'บาท/ครั้ง',
      },
      tierPresets: {
        basic: { main: 1000, opd: 30, daily: 500 },
        standard: { main: 1500, opd: 30, daily: 800 },
        advance: { main: 3000, opd: 30, daily: 1200 },
      },
      tierMainValues: { basic: 1000, standard: 1500, advance: 3000 },
      sliders: [
        { id: 'main', label: 'วงเงินคุ้มครอง OPD ต่อครั้ง', values: [1000, 1500, 2000, 2500, 3000] },
        {
          id: 'opd',
          label: 'จำนวนครั้งสูงสุดต่อปี',
          hint: 'สูงสุด 30 ครั้งต่อปีกรมธรรม์',
          values: [10, 15, 20, 25, 30],
          format: 'plain',
        },
        {
          id: 'daily',
          label: 'ค่าเวชภัณฑ์ที่คุ้มครอง',
          hint: 'คุ้มครองค่ายาและเวชภัณฑ์ตามแผน',
          values: [0, 500, 800, 1000, 1200],
        },
      ],
    },
  }),
  'big-3-critical': createPlanProduct({
    id: 'big-3-critical',
    name: 'Big 3 คุ้มครอง 3 กลุ่มโรคร้ายแรง',
    category: 'critical',
    tagline: 'ประกันโรคร้ายแรง',
    headline: 'คุ้มครอง 3 กลุ่มโรคร้ายแรงหลัก จ่ายเงินก้อนเมื่อวินิจฉัย',
    benefits: [
      'คุ้มครองกลุ่มโรคมะเร็ง โรคหัวใจ และโรคไตวายเรื้อรัง',
      'จ่ายผลประโยชน์ทันทีเมื่อวินิจฉัย ไม่ต้องส่งใบเสร็จ',
      'ใช้เงินก้อนนี้ดูแลครอบครัวได้อย่างอิสระ',
      'เบี้ยประกันเข้าถึงได้',
    ],
    priceFrom: 800,
  }),
  'ci-reclaim-recare': createPlanProduct({
    id: 'ci-reclaim-recare',
    name: 'CI Re-Claim Re-Care',
    category: 'critical',
    tagline: 'ประกันโรคร้ายแรง',
    headline: 'เคลมซ้ำได้ คุ้มครองต่อเนื่องเมื่อโรคกลับมา',
    benefits: [
      'ออกแบบสำหรับการเคลมโรคร้ายแรงซ้ำ',
      'คุ้มครองหลายระยะของการรักษา',
      'ลดภาระค่าใช้จ่ายระยะยาว',
      'วางแผนการเงินเมื่อเจ็บป่วยรุนแรง',
    ],
    priceFrom: 950,
  }),
  'whole-life-critical': createPlanProduct({
    id: 'whole-life-critical',
    name: 'ตลอดชีพคุ้มครองโรคร้ายแรง',
    category: 'critical',
    tagline: 'ประกันโรคร้ายแรง',
    headline: 'คุ้มครองโรคร้ายแรงตลอดชีพ พร้อมความมั่นคงระยะยาว',
    benefits: [
      'ความคุ้มครองยาวนานตลอดชีพ',
      'จ่ายผลประโยชน์เมื่อวินิจฉัยโรคร้ายแรง',
      'สร้างหลักประกันทางการเงินให้ครอบครัว',
      'ปรับแผนได้ตามช่วงวัย',
    ],
    priceFrom: 1200,
  }),
  'easy-e-life': createPlanProduct({
    id: 'easy-e-life',
    name: 'Easy E-Life',
    category: 'life-accident',
    tagline: 'ประกันชีวิตออนไลน์',
    headline: 'ประกันชีวิตสมัครง่าย คุ้มครองครอบครัวเมื่อเกิดเหตุไม่คาดฝัน',
    benefits: [
      'สมัครออนไลน์ได้รวดเร็ว',
      'คุ้มครองชีวิตและอุบัติเหตุในยอดที่เลือก',
      'เบี้ยประกันเริ่มต้นไม่สูง',
      'ปรับทุนประกันได้ตามความต้องการ',
    ],
    priceFrom: 450,
  }),
  'khum-chivee-extra': createPlanProduct({
    id: 'khum-chivee-extra',
    name: 'คุ้มชีวีเอ็กซ์ตร้า',
    category: 'life-accident',
    tagline: 'ประกันชีวิตและอุบัติเหตุ',
    headline: 'คุ้มครองชีวิตและอุบัติเหตุเพิ่มเติมในยอดที่มากขึ้น',
    benefits: [
      'ผลประโยชน์กรณีเสียชีวิตและทุพพลภาพ',
      'คุ้มครองอุบัติเหตุ 24 ชั่วโมง',
      'เบี้ยสม่ำเสมอตามแผนที่เลือก',
      'เหมาะกับผู้หาเสี้ยเลี้ยงครอบครัว',
    ],
    priceFrom: 900,
  }),
  'khum-krong-kha-khum-phai': createPlanProduct({
    id: 'khum-krong-kha-khum-phai',
    name: 'คุ้มครอง คุ้มค่า คุ้มภัย',
    category: 'life-accident',
    tagline: 'ประกันชีวิตและอุบัติเหตุ',
    headline: 'ครบ คุ้มค่า ในแผนเดียว สำหรับทุกครอบครัว',
    benefits: [
      'คุ้มครองชีวิตและค่ารักษาพยาบาลจากอุบัติเหตุ',
      'เบี้ยประกันคุ้มค่า จ่ายตามงบ',
      'สร้างความมั่นใจให้คนที่คุณรัก',
      'เงื่อนไขเข้าใจง่าย',
    ],
    priceFrom: 750,
  }),
  'easy-e-save-10-5': createPlanProduct({
    id: 'easy-e-save-10-5',
    name: 'Easy E-Save 10/5',
    category: 'savings',
    tagline: 'ประกันสะสมทรัพย์',
    headline: 'ออม 10 ปี คุ้มครอง 5 ปี สร้างเงินก้อนพร้อมความคุ้มครอง',
    benefits: [
      'จ่ายเบี้ย 10 ปี รับความคุ้มครองและผลประโยชน์ตามแผน',
      'สร้างวินัยการออมระยะยาว',
      'มีเงินคืนตามระยะเวลา',
      'เหมาะกับการวางแผนอนาคต',
    ],
    priceFrom: 1100,
  }),
  'fwd-for-saving-25-15': createPlanProduct({
    id: 'fwd-for-saving-25-15',
    name: 'เอฟดับบลิวดี ฟอร์ เซฟวิ่ง 25/15',
    category: 'savings',
    tagline: 'ประกันสะสมทรัพย์',
    headline: 'ออมระยะยาว 25 ปี คุ้มครอง 15 ปี วางแผนเกษียณอย่างมั่นใจ',
    benefits: [
      'วางแผนเกษียณและมรดกให้ครอบครัว',
      'รับเงินคืนตามระยะเวลาที่กำหนด',
      'โบนัสพิเศษเมื่อครบสัญญา',
      'ลดหย่อนภาษีตามเงื่อนไข',
    ],
    priceFrom: 1500,
  }),
  'fwd-for-saving-20-10': createPlanProduct({
    id: 'fwd-for-saving-20-10',
    name: 'เอฟดับบลิวดี ฟอร์ เซฟวิ่ง 20/10',
    category: 'savings',
    tagline: 'ประกันสะสมทรัพย์',
    headline: 'ออม 20 ปี คุ้มครอง 10 ปี สร้างหลักทรัพย์ระยะกลาง',
    benefits: [
      'สร้างเงินออมพร้อมความคุ้มครองชีวิต',
      'รับเงินคืนเป็นงวด',
      'ปรับแผนได้ตามเป้าหมายการเงิน',
      'มั่นใจในอนาคตระยะยาว',
    ],
    priceFrom: 1300,
  }),
  'fwd-ultra-link-99-99': createPlanProduct({
    id: 'fwd-ultra-link-99-99',
    name: 'เอฟดับบลิวดี อัลตร้า ลิงค์ 99/99',
    category: 'investment-linked',
    tagline: 'ประกันชีวิตควบการลงทุน',
    headline: 'ลงทุนควบคู่ความคุ้มครองชีวิต ยืดหยุ่นสูง',
    benefits: [
      'เลือกกองทุนลงทุนได้ตามความเสี่ยง',
      'ความคุ้มครองชีวิตตลอดสัญญา',
      'ปรับพอร์ตการลงทุนได้',
      'เหมาะกับผู้วางแผนระยะยาว',
    ],
    priceFrom: 2500,
  }),
  'fwd-future-link-99-9': createPlanProduct({
    id: 'fwd-future-link-99-9',
    name: 'เอฟดับบลิวดี ฟิวเจอร์ ลิงค์ 99/9',
    category: 'investment-linked',
    tagline: 'ประกันชีวิตควบการลงทุน',
    headline: 'วางแผนอนาคตด้วยการลงทุนที่ยืดหยุ่น',
    benefits: [
      'ผสมผสานการออมและการลงทุน',
      'คุ้มครองชีวิตตามทุนประกัน',
      'ติดตามผลตอบแทนได้สะดวก',
      'ปรับทุนและกองทุนได้',
    ],
    priceFrom: 2200,
  }),
  'fwd-freedom-link-plus-15-5': createPlanProduct({
    id: 'fwd-freedom-link-plus-15-5',
    name: 'เอฟดับบลิวดี ฟรีดอม ลิงค์ พลัส 15/5',
    category: 'investment-linked',
    tagline: 'ประกันชีวิตควบการลงทุน',
    headline: 'อิสระในการลงทุน พร้อมความคุ้มครองที่ปรับได้',
    benefits: [
      'จ่ายเบี้ย 15 ปี คุ้มครอง 5 ปีขึ้นไปตามแผน',
      'โอกาสรับผลตอบแทนจากการลงทุน',
      'ถอน/เปลี่ยนแปลงกองทุนได้ตามเงื่อนไข',
      'เหมาะกับผู้เริ่มลงทุนระยะยาว',
    ],
    priceFrom: 2000,
  }),
};

function getPlanProduct(id) {
  return PLAN_PRODUCTS[id] || null;
}

function getAllPlanProducts() {
  return Object.values(PLAN_PRODUCTS);
}

const PLAN_LISTING_IMAGES = [
  'assets/img/plans/plan-rec-1.jpg',
  'assets/img/plans/plan-rec-2.jpg',
  'assets/img/plans/plan-rec-3.jpg',
  'assets/img/plans/plan-rec-4.jpg',
];

function createPlanCardElement(product, index) {
  const meta = PLAN_CATEGORY_META[product.category] || {};
  const goals = meta.listingGoals || 'family,health';
  const hasPromo = product.promo?.text?.includes('ส่วนลด');
  const article = document.createElement('article');
  article.className = 'plan-card';
  article.dataset.category = product.category;
  article.dataset.price = String(product.priceFrom || 0);
  article.dataset.goals = goals;
  if (hasPromo) article.dataset.promo = 'discount';
  article.dataset.order = String(index);

  article.innerHTML = `
    <div class="plan-card__image">
      <img src="${PLAN_LISTING_IMAGES[index % PLAN_LISTING_IMAGES.length]}" alt="" width="313" height="174" loading="lazy">
      ${hasPromo ? '<span class="plan-card__badge plan-card__badge--discount">รับส่วนลด</span>' : ''}
    </div>
    <div class="plan-card__body">
      <h3 class="plan-card__title">${product.name}</h3>
      <p class="plan-card__desc">${product.headline}</p>
      <a href="plan-details.html?id=${encodeURIComponent(product.id)}" class="btn plan-card__btn">ดูรายละเอียด</a>
    </div>`;

  return article;
}

function renderPlanCards(container, products) {
  if (!container || !Array.isArray(products)) return;

  const allProducts = getAllPlanProducts();
  container.innerHTML = '';

  products.forEach((product, index) => {
    const globalIndex = allProducts.findIndex((item) => item.id === product.id);
    container.appendChild(createPlanCardElement(product, globalIndex >= 0 ? globalIndex : index));
  });
}

function renderPlanListingCards() {
  const grid = document.getElementById('planListingGrid');
  if (!grid || typeof getAllPlanProducts !== 'function') return false;

  const emptyEl = document.getElementById('planListingEmpty');

  grid.querySelectorAll('.plan-card').forEach((card) => card.remove());

  getAllPlanProducts().forEach((product, index) => {
    const article = createPlanCardElement(product, index);

    if (emptyEl) {
      grid.insertBefore(article, emptyEl);
    } else {
      grid.appendChild(article);
    }
  });

  return true;
}
