<?php
/**
 * Run once to add new tables/settings to existing database.
 * Safe to re-run — uses IF NOT EXISTS / INSERT IGNORE patterns.
 */
require_once __DIR__ . '/config.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->exec("CREATE TABLE IF NOT EXISTS faq_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question TEXT NOT NULL,
        answer TEXT NOT NULL,
        sort_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS contact_submissions (
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
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS page_sections (
        id INT AUTO_INCREMENT PRIMARY KEY,
        page_slug VARCHAR(50) NOT NULL,
        section_key VARCHAR(100) NOT NULL,
        label VARCHAR(255),
        content_type VARCHAR(20) DEFAULT 'text',
        content_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_page_section (page_slug, section_key)
    ) ENGINE=InnoDB");

    $faqCount = (int) $pdo->query('SELECT COUNT(*) FROM faq_items')->fetchColumn();
    if ($faqCount === 0) {
        $pdo->exec("INSERT INTO faq_items (question, answer, sort_order, is_active) VALUES
            ('ประกันชีวิตคืออะไร และทำไมถึงสำคัญ?', 'ประกันชีวิตเป็นเครื่องมือทางการเงินที่ช่วยคุ้มครองครอบครัวของคุณในกรณีที่เกิดเหตุไม่คาดฝัน เป็นการวางแผนทางการเงินที่สำคัญเพื่อให้คนที่คุณรักมีความมั่นคงในอนาคต', 1, 1),
            ('ต้องใช้เอกสารอะไรบ้างในการสมัครประกัน?', 'เอกสารที่ต้องใช้ ได้แก่ บัตรประชาชน สำเนาทะเบียนบ้าน และเอกสารทางการแพทย์ (ถ้ามี)', 2, 1),
            ('สามารถเปลี่ยนแผนประกันได้หรือไม่?', 'สามารถเปลี่ยนแผนประกันได้ตามเงื่อนไขของกรมธรรม์ โดยติดต่อตัวแทนหรือฝ่ายบริการลูกค้า', 3, 1),
            ('เคลมประกันใช้เวลานานเท่าไหร่?', 'ระยะเวลาในการพิจารณาเคลมโดยทั่วไปใช้เวลา 7-15 วันทำการ', 4, 1),
            ('มีบริการปรึกษาฟรีหรือไม่?', 'มีบริการปรึกษาฟรีจากทีมผู้เชี่ยวชาญของเรา ติดต่อได้ผ่านแบบฟอร์ม โทรศัพท์ หรือ Line Official', 5, 1),
            ('สามารถจ่ายเบี้ยประกันรายเดือนได้หรือไม่?', 'สามารถเลือกชำระเบี้ยได้หลายรูปแบบ ทั้งรายเดือน ราย 3 เดือน ราย 6 เดือน หรือรายปี ตามแผนที่คุณสมัคร', 6, 1),
            ('ประกันคุ้มครองตั้งแต่เมื่อไหร่?', 'ความคุ้มครองเริ่มต้นตามเงื่อนไขในกรมธรรม์ โดยทั่วไปมีผลหลังอนุมัติและชำระเบี้ยงวดแรกเรียบร้อยแล้ว', 7, 1),
            ('ยกเลิกกรมธรรม์ได้หรือไม่?', 'สามารถยกเลิกกรมธรรม์ได้ตามเงื่อนไขที่ระบุในกรมธรรม์ โดยแนะนำให้ปรึกษาตัวแทนก่อนตัดสินใจ', 8, 1)");
    } elseif ($faqCount < 8) {
        // Backfill missing defaults if DB was seeded with the short 2-item migrate set
        $existing = $pdo->query('SELECT question FROM faq_items')->fetchAll(PDO::FETCH_COLUMN);
        $defaults = [
            ['สามารถเปลี่ยนแผนประกันได้หรือไม่?', 'สามารถเปลี่ยนแผนประกันได้ตามเงื่อนไขของกรมธรรม์ โดยติดต่อตัวแทนหรือฝ่ายบริการลูกค้า', 3],
            ['เคลมประกันใช้เวลานานเท่าไหร่?', 'ระยะเวลาในการพิจารณาเคลมโดยทั่วไปใช้เวลา 7-15 วันทำการ', 4],
            ['มีบริการปรึกษาฟรีหรือไม่?', 'มีบริการปรึกษาฟรีจากทีมผู้เชี่ยวชาญของเรา ติดต่อได้ผ่านแบบฟอร์ม โทรศัพท์ หรือ Line Official', 5],
            ['สามารถจ่ายเบี้ยประกันรายเดือนได้หรือไม่?', 'สามารถเลือกชำระเบี้ยได้หลายรูปแบบ ทั้งรายเดือน ราย 3 เดือน ราย 6 เดือน หรือรายปี ตามแผนที่คุณสมัคร', 6],
            ['ประกันคุ้มครองตั้งแต่เมื่อไหร่?', 'ความคุ้มครองเริ่มต้นตามเงื่อนไขในกรมธรรม์ โดยทั่วไปมีผลหลังอนุมัติและชำระเบี้ยงวดแรกเรียบร้อยแล้ว', 7],
            ['ยกเลิกกรมธรรม์ได้หรือไม่?', 'สามารถยกเลิกกรมธรรม์ได้ตามเงื่อนไขที่ระบุในกรมธรรม์ โดยแนะนำให้ปรึกษาตัวแทนก่อนตัดสินใจ', 8],
        ];
        $ins = $pdo->prepare('INSERT INTO faq_items (question, answer, sort_order, is_active) VALUES (?,?,?,1)');
        foreach ($defaults as $row) {
            if (!in_array($row[0], $existing, true)) {
                $ins->execute($row);
            }
        }
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS cms_blocks (
        block_key VARCHAR(100) PRIMARY KEY,
        label VARCHAR(255),
        block_group VARCHAR(50) DEFAULT 'general',
        content_json LONGTEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS promotion_filter_items (
        id VARCHAR(50) PRIMARY KEY,
        label VARCHAR(100) NOT NULL,
        sort_order INT DEFAULT 0
    ) ENGINE=InnoDB");

    $newSettings = [
        ['phone2', '02-116-4915', 'contact'],
        ['business_hours', 'จันทร์–ศุกร์ 09:00–18:00 น. · เสาร์ 09:00–12:00 น.', 'contact'],
        ['copyright', '© 2025 Agent Thailand — ตัวแทน FWD ประกันชีวิต สงวนลิขสิทธิ์', 'general'],
        ['facebook_url', 'https://www.facebook.com/AgentThailandFWD', 'social'],
        ['line_url', 'https://line.me/ti/p/~@agentthailand', 'social'],
        ['tiktok_url', 'https://www.tiktok.com/@agentthailand', 'social'],
        ['youtube_url', '#', 'social'],
        ['instagram_url', '#', 'social'],
        ['privacy_url', '#', 'legal'],
        ['terms_url', '#', 'legal'],
        ['logo_url', 'assets/img/logo.png', 'general'],
    ];
    $stmt = $pdo->prepare('INSERT IGNORE INTO site_settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?)');
    foreach ($newSettings as $s) {
        $stmt->execute($s);
    }

    $filterCount = (int) $pdo->query('SELECT COUNT(*) FROM promotion_filter_items')->fetchColumn();
    if ($filterCount === 0) {
        $pdo->exec("INSERT INTO promotion_filter_items (id, label, sort_order) VALUES
            ('all', 'ทั้งหมด', 0),
            ('savings', 'ประกันสะสมทรัพย์', 1),
            ('health', 'ประกันสุขภาพ', 2),
            ('life-accident', 'ประกันชีวิตและอุบัติเหตุ', 3),
            ('critical', 'ประกันโรคร้ายแรง', 4)");
    }

    require_once __DIR__ . '/includes/db.php';
    require_once __DIR__ . '/includes/cms.php';
    seedCmsBlocksIfEmpty();

    echo "Migration completed successfully.\n";
    echo "CMS blocks seeded: " . $pdo->query('SELECT COUNT(*) FROM cms_blocks')->fetchColumn() . "\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo "Migration failed: " . $e->getMessage() . "\n";
}
