CREATE DATABASE IF NOT EXISTS agent1501 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE agent1501;

CREATE TABLE admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  display_name VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE plan_categories (
  id VARCHAR(50) PRIMARY KEY,
  label VARCHAR(100) NOT NULL,
  promo_section VARCHAR(150),
  why_section VARCHAR(150),
  listing_goals VARCHAR(100),
  icon VARCHAR(50),
  headline TEXT,
  hero_image VARCHAR(500),
  intro_title VARCHAR(255),
  intro_text TEXT,
  sort_order INT DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE plan_category_features (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id VARCHAR(50) NOT NULL,
  title VARCHAR(255),
  description TEXT,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (category_id) REFERENCES plan_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE plan_products (
  id VARCHAR(100) PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  category_id VARCHAR(50) NOT NULL,
  tagline VARCHAR(255),
  headline TEXT,
  price_from DECIMAL(10,2) DEFAULT 0,
  price_note TEXT,
  hero_image VARCHAR(500),
  promo_text TEXT,
  promo_code VARCHAR(50),
  promo_until VARCHAR(30),
  is_active TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES plan_categories(id)
) ENGINE=InnoDB;

CREATE TABLE plan_benefits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  plan_id VARCHAR(100) NOT NULL,
  benefit_text TEXT,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (plan_id) REFERENCES plan_products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE plan_tiers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  plan_id VARCHAR(100) NOT NULL,
  tier_id VARCHAR(50),
  label VARCHAR(100),
  amount VARCHAR(50),
  unit VARCHAR(50),
  is_popular TINYINT(1) DEFAULT 0,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (plan_id) REFERENCES plan_products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE plan_highlights (
  id INT AUTO_INCREMENT PRIMARY KEY,
  plan_id VARCHAR(100) NOT NULL,
  highlight_text TEXT,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (plan_id) REFERENCES plan_products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE plan_coverage_summary (
  id INT AUTO_INCREMENT PRIMARY KEY,
  plan_id VARCHAR(100) NOT NULL,
  label VARCHAR(255),
  value VARCHAR(100),
  unit VARCHAR(100),
  sort_order INT DEFAULT 0,
  FOREIGN KEY (plan_id) REFERENCES plan_products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE plan_coverage_rows (
  id INT AUTO_INCREMENT PRIMARY KEY,
  plan_id VARCHAR(100) NOT NULL,
  label VARCHAR(255),
  values_json TEXT,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (plan_id) REFERENCES plan_products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE plan_conditions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  plan_id VARCHAR(100) NOT NULL,
  condition_text TEXT,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (plan_id) REFERENCES plan_products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE plan_faqs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  plan_id VARCHAR(100) NOT NULL,
  question TEXT,
  answer TEXT,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (plan_id) REFERENCES plan_products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE plan_renewal (
  id INT AUTO_INCREMENT PRIMARY KEY,
  plan_id VARCHAR(100) NOT NULL,
  renewal_text TEXT,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (plan_id) REFERENCES plan_products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE plan_why (
  id INT AUTO_INCREMENT PRIMARY KEY,
  plan_id VARCHAR(100) NOT NULL,
  why_text TEXT,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (plan_id) REFERENCES plan_products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE blog_articles (
  id VARCHAR(100) PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  excerpt TEXT,
  pub_date DATE,
  date_label VARCHAR(50),
  category VARCHAR(50),
  category_label VARCHAR(100),
  image VARCHAR(500),
  views INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE blog_tags (
  id INT AUTO_INCREMENT PRIMARY KEY,
  article_id VARCHAR(100) NOT NULL,
  tag VARCHAR(100),
  FOREIGN KEY (article_id) REFERENCES blog_articles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE blog_content (
  id INT AUTO_INCREMENT PRIMARY KEY,
  article_id VARCHAR(100) NOT NULL,
  block_type VARCHAR(20) DEFAULT 'p',
  content TEXT,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (article_id) REFERENCES blog_articles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE promotions (
  id VARCHAR(100) PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  highlight VARCHAR(255),
  description_html TEXT,
  category VARCHAR(50),
  category_key VARCHAR(50),
  badge VARCHAR(100),
  badge_type VARCHAR(50),
  image VARCHAR(500),
  cta VARCHAR(100),
  cta_href VARCHAR(500),
  promo_code VARCHAR(50),
  valid_until VARCHAR(50),
  installment VARCHAR(100),
  is_popular TINYINT(1) DEFAULT 0,
  is_new TINYINT(1) DEFAULT 0,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE promotion_bullets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  promotion_id VARCHAR(100) NOT NULL,
  bullet_text TEXT,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (promotion_id) REFERENCES promotions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE site_settings (
  setting_key VARCHAR(100) PRIMARY KEY,
  setting_value TEXT,
  setting_group VARCHAR(50) DEFAULT 'general',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE page_sections (
  id INT AUTO_INCREMENT PRIMARY KEY,
  page_slug VARCHAR(50) NOT NULL,
  section_key VARCHAR(100) NOT NULL,
  label VARCHAR(255),
  content_type VARCHAR(20) DEFAULT 'text',
  content_value TEXT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_page_section (page_slug, section_key)
) ENGINE=InnoDB;

CREATE TABLE faq_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  question TEXT NOT NULL,
  answer TEXT NOT NULL,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE contact_submissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  phone VARCHAR(20),
  email VARCHAR(150),
  insurance_type VARCHAR(100),
  province VARCHAR(100),
  age INT,
  callback_time VARCHAR(100),
  message TEXT,
  status ENUM('new','contacted','closed') DEFAULT 'new',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin: username=admin, password=admin123
INSERT INTO admin_users (username, password_hash, display_name) VALUES
('admin', '$2y$10$YWVyOGZkNTg0ZDFkNmI3YeC9HVfBqJ1xqKj6vW5n8Rp2sL4mQ3hOe', 'Administrator');

-- Default site settings
INSERT INTO site_settings (setting_key, setting_value, setting_group) VALUES
('site_name', 'Agent Thailand', 'general'),
('site_tagline', 'ตัวแทนประกันชีวิต FWD', 'general'),
('phone', '065-651-5328', 'contact'),
('email', 'agentthailand.fwd@gmail.com', 'contact'),
('line_id', '@agentthailand', 'contact'),
('facebook', 'AgentThailandFWD', 'social'),
('tiktok', '@agentthailand', 'social'),
('address', '130 ถนนสุขุมวิท แขวงบางจาก เขตพระโขนง กรุงเทพมหานคร 10260', 'contact'),
('primary_color', '#150f96', 'theme'),
('phone2', '02-116-4915', 'contact'),
('business_hours', 'จันทร์–ศุกร์ 09:00–18:00 น. · เสาร์ 09:00–12:00 น.', 'contact'),
('copyright', '© 2025 Agent Thailand — ตัวแทน FWD ประกันชีวิต สงวนลิขสิทธิ์', 'general'),
('facebook_url', 'https://www.facebook.com/AgentThailandFWD', 'social'),
('line_url', 'https://line.me/ti/p/~@agentthailand', 'social'),
('tiktok_url', 'https://www.tiktok.com/@agentthailand', 'social'),
('youtube_url', '#', 'social'),
('instagram_url', '#', 'social'),
('privacy_url', '#', 'legal'),
('terms_url', '#', 'legal'),
('logo_url', 'assets/img/logo.png', 'general');

CREATE TABLE cms_blocks (
  block_key VARCHAR(100) PRIMARY KEY,
  label VARCHAR(255),
  block_group VARCHAR(50) DEFAULT 'general',
  content_json LONGTEXT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE promotion_filter_items (
  id VARCHAR(50) PRIMARY KEY,
  label VARCHAR(100) NOT NULL,
  sort_order INT DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO promotion_filter_items (id, label, sort_order) VALUES
('all', 'ทั้งหมด', 0),
('savings', 'ประกันสะสมทรัพย์', 1),
('health', 'ประกันสุขภาพ', 2),
('life-accident', 'ประกันชีวิตและอุบัติเหตุ', 3),
('critical', 'ประกันโรคร้ายแรง', 4);

INSERT INTO faq_items (question, answer, sort_order) VALUES
('ประกันชีวิตคืออะไร และทำไมถึงสำคัญ?', 'ประกันชีวิตเป็นเครื่องมือทางการเงินที่ช่วยคุ้มครองครอบครัวของคุณในกรณีที่เกิดเหตุไม่คาดฝัน เป็นการวางแผนทางการเงินที่สำคัญเพื่อให้คนที่คุณรักมีความมั่นคงในอนาคต', 1),
('ต้องใช้เอกสารอะไรบ้างในการสมัครประกัน?', 'เอกสารที่ต้องใช้ ได้แก่ บัตรประชาชน สำเนาทะเบียนบ้าน และเอกสารทางการแพทย์ (ถ้ามี)', 2),
('สามารถเปลี่ยนแผนประกันได้หรือไม่?', 'สามารถเปลี่ยนแผนประกันได้ตามเงื่อนไขของกรมธรรม์ โดยติดต่อตัวแทนหรือฝ่ายบริการลูกค้า', 3),
('เคลมประกันใช้เวลานานเท่าไหร่?', 'ระยะเวลาในการพิจารณาเคลมโดยทั่วไปใช้เวลา 7-15 วันทำการ', 4),
('มีบริการปรึกษาฟรีหรือไม่?', 'มีบริการปรึกษาฟรีจากทีมผู้เชี่ยวชาญของเรา ติดต่อได้ผ่านแบบฟอร์ม โทรศัพท์ หรือ Line Official', 5),
('สามารถจ่ายเบี้ยประกันรายเดือนได้หรือไม่?', 'สามารถเลือกชำระเบี้ยได้หลายรูปแบบ ทั้งรายเดือน ราย 3 เดือน ราย 6 เดือน หรือรายปี ตามแผนที่คุณสมัคร', 6),
('ประกันคุ้มครองตั้งแต่เมื่อไหร่?', 'ความคุ้มครองเริ่มต้นตามเงื่อนไขในกรมธรรม์ โดยทั่วไปมีผลหลังอนุมัติและชำระเบี้ยงวดแรกเรียบร้อยแล้ว', 7),
('ยกเลิกกรมธรรม์ได้หรือไม่?', 'สามารถยกเลิกกรมธรรม์ได้ตามเงื่อนไขที่ระบุในกรมธรรม์ โดยแนะนำให้ปรึกษาตัวแทนก่อนตัดสินใจ', 8);
