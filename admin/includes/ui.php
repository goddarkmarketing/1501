<?php

function adminAlert(string $message, string $type = 'success'): string {
    $class = $type === 'success' ? 'admin-alert-success' : 'admin-alert-error';
    return '<div class="' . $class . ' mb-6">' . htmlspecialchars($message) . '</div>';
}

function getPageDefinitions(): array {
    return [
        'home' => [
            'label' => 'หน้าแรก',
            'icon' => 'home',
            'description' => 'Hero, Intro และหัวข้อส่วนต่างๆ',
            'url' => SITE_URL . '/index.html',
            'sections' => [
                ['key' => 'hero_eyebrow', 'label' => 'ข้อความเล็กบน Hero', 'type' => 'text'],
                ['key' => 'hero_title', 'label' => 'หัวข้อ Hero (ใช้ &lt;br&gt; ขึ้นบรรทัดใหม่)', 'type' => 'html'],
                ['key' => 'hero_cta', 'label' => 'ปุ่ม Hero', 'type' => 'text'],
                ['key' => 'hero_cta_link', 'label' => 'ลิงก์ปุ่ม Hero', 'type' => 'text'],
                ['key' => 'hero_bg_image', 'label' => 'รูปพื้นหลัง Hero', 'type' => 'image'],
                ['key' => 'intro_title', 'label' => 'หัวข้อ Intro', 'type' => 'text'],
                ['key' => 'intro_text', 'label' => 'ข้อความ Intro', 'type' => 'textarea'],
                ['key' => 'rec_section_label', 'label' => 'ป้ายกำกับแผนแนะนำ', 'type' => 'text'],
                ['key' => 'rec_section_title', 'label' => 'หัวข้อแผนแนะนำ', 'type' => 'text'],
                ['key' => 'consult_title', 'label' => 'หัวข้อฟอร์มปรึกษา', 'type' => 'text'],
            ],
        ],
        'about' => [
            'label' => 'เกี่ยวกับเรา',
            'icon' => 'info',
            'description' => 'Hero, Intro และรูปพื้นหลัง',
            'url' => SITE_URL . '/about-us.html',
            'sections' => [
                ['key' => 'hero_title', 'label' => 'หัวข้อ Hero', 'type' => 'text'],
                ['key' => 'hero_headline', 'label' => 'คำอธิบาย Hero', 'type' => 'textarea'],
                ['key' => 'hero_image', 'label' => 'รูป Hero', 'type' => 'image'],
                ['key' => 'intro_title', 'label' => 'หัวข้อ Intro', 'type' => 'text'],
                ['key' => 'intro_text', 'label' => 'ข้อความ Intro', 'type' => 'textarea'],
                ['key' => 'intro_text2', 'label' => 'ข้อความ Intro ย่อหน้า 2', 'type' => 'textarea'],
            ],
        ],
        'contact' => [
            'label' => 'ติดต่อเรา',
            'icon' => 'mail',
            'description' => 'หัวข้อและรูป Hero',
            'url' => SITE_URL . '/contact.html',
            'sections' => [
                ['key' => 'hero_title', 'label' => 'หัวข้อ Hero', 'type' => 'text'],
                ['key' => 'hero_headline', 'label' => 'คำอธิบาย Hero', 'type' => 'textarea'],
                ['key' => 'hero_image', 'label' => 'รูป Hero', 'type' => 'image'],
                ['key' => 'consult_title', 'label' => 'หัวข้อฟอร์มปรึกษา', 'type' => 'text'],
            ],
        ],
        'faq' => [
            'label' => 'คำถามที่พบบ่อย',
            'icon' => 'help',
            'description' => 'หัวข้อ Hero ของหน้า FAQ',
            'url' => SITE_URL . '/faq.html',
            'sections' => [
                ['key' => 'hero_title', 'label' => 'หัวข้อ Hero', 'type' => 'text'],
                ['key' => 'hero_headline', 'label' => 'คำอธิบาย Hero', 'type' => 'textarea'],
                ['key' => 'hero_image', 'label' => 'รูป Hero', 'type' => 'image'],
            ],
        ],
        'plan' => [
            'label' => 'ผลิตภัณฑ์ทั้งหมด',
            'icon' => 'grid',
            'description' => 'Hero และหัวข้อรายการแผน',
            'url' => SITE_URL . '/plan.html',
            'sections' => [
                ['key' => 'hero_title', 'label' => 'หัวข้อ Hero ด้านบน', 'type' => 'text'],
                ['key' => 'hero_headline', 'label' => 'คำอธิบาย Hero ด้านบน', 'type' => 'textarea'],
                ['key' => 'hero_image', 'label' => 'รูป Hero', 'type' => 'image'],
                ['key' => 'listing_title', 'label' => 'หัวข้อรายการแผน', 'type' => 'text'],
                ['key' => 'listing_headline', 'label' => 'คำอธิบายรายการแผน', 'type' => 'textarea'],
            ],
        ],
        'blog' => [
            'label' => 'บทความ',
            'icon' => 'grid',
            'description' => 'Hero หน้ารายการบทความ',
            'url' => SITE_URL . '/blog.html',
            'sections' => [
                ['key' => 'hero_title', 'label' => 'หัวข้อ Hero', 'type' => 'text'],
                ['key' => 'hero_headline', 'label' => 'คำอธิบาย Hero', 'type' => 'textarea'],
                ['key' => 'hero_image', 'label' => 'รูป Hero', 'type' => 'image'],
            ],
        ],
        'promotion' => [
            'label' => 'โปรโมชัน',
            'icon' => 'grid',
            'description' => 'Hero หน้าโปรโมชัน',
            'url' => SITE_URL . '/promotion.html',
            'sections' => [
                ['key' => 'hero_title', 'label' => 'หัวข้อ Hero', 'type' => 'text'],
                ['key' => 'hero_headline', 'label' => 'คำอธิบาย Hero', 'type' => 'textarea'],
                ['key' => 'hero_image', 'label' => 'รูป Hero', 'type' => 'image'],
            ],
        ],
        'hospitals' => [
            'label' => 'โรงพยาบาล',
            'icon' => 'grid',
            'description' => 'Hero หน้าค้นหาโรงพยาบาล',
            'url' => SITE_URL . '/hospitals.html',
            'sections' => [
                ['key' => 'hero_title', 'label' => 'หัวข้อ Hero', 'type' => 'text'],
                ['key' => 'hero_headline', 'label' => 'คำอธิบาย Hero', 'type' => 'textarea'],
                ['key' => 'hero_image', 'label' => 'รูป Hero', 'type' => 'image'],
            ],
        ],
        'register' => [
            'label' => 'สมัครตัวแทน',
            'icon' => 'info',
            'description' => 'หัวข้อ Hero (เนื้อหาอื่นแก้ที่บล็อกเนื้อหา)',
            'url' => SITE_URL . '/register.html',
            'sections' => [
                ['key' => 'hero_title', 'label' => 'หัวข้อ Hero', 'type' => 'text'],
                ['key' => 'hero_headline', 'label' => 'คำอธิบาย Hero', 'type' => 'textarea'],
                ['key' => 'hero_image', 'label' => 'รูป Hero', 'type' => 'image'],
            ],
        ],
        'login' => [
            'label' => 'เข้าสู่ระบบ',
            'icon' => 'info',
            'description' => 'หัวข้อหน้า Login',
            'url' => SITE_URL . '/login.html',
            'sections' => [
                ['key' => 'hero_title', 'label' => 'หัวข้อ', 'type' => 'text'],
                ['key' => 'hero_headline', 'label' => 'คำอธิบาย', 'type' => 'textarea'],
            ],
        ],
    ];
}

function getSectionValue(string $page, string $key, string $default = ''): string {
    try {
        $row = fetchOne(
            'SELECT content_value FROM page_sections WHERE page_slug = ? AND section_key = ?',
            [$page, $key]
        );
        if ($row && $row['content_value'] !== '') {
            return $row['content_value'];
        }
    } catch (Throwable $e) {
        // table may not exist yet
    }
    return $default;
}

function saveSectionValue(string $page, string $key, string $value, string $label = '', string $type = 'text'): void {
    query(
        'INSERT INTO page_sections (page_slug, section_key, label, content_type, content_value)
         VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE content_value = VALUES(content_value), label = VALUES(label), content_type = VALUES(content_type)',
        [$page, $key, $label, $type, $value]
    );
}

function getPageSectionStats(string $slug, int $total): array {
    try {
        $row = fetchOne(
            'SELECT COUNT(*) AS c FROM page_sections WHERE page_slug = ? AND content_value IS NOT NULL AND content_value != ""',
            [$slug]
        );
        $saved = (int) ($row['c'] ?? 0);
    } catch (Throwable $e) {
        $saved = 0;
    }
    return ['saved' => $saved, 'total' => $total];
}

function adminPageIcon(string $icon): string {
    $map = [
        'home' => 'home',
        'info' => 'info',
        'mail' => 'mail',
        'help' => 'circle-help',
        'grid' => 'layout-grid',
    ];
    $name = $map[$icon] ?? 'file-text';
    return '<i data-lucide="' . htmlspecialchars($name) . '" class="admin-page-icon" aria-hidden="true"></i>';
}

function getDefaultSectionValues(string $page): array {
    $defaults = [
        'home' => [
            'hero_eyebrow' => 'INNOVATIVE FINANCIAL',
            'hero_title' => 'รีบซื้อ ชัวร์กว่า<br>Easy e-Health<br>ลดดอกเบี้ย<br>ปีแรก 12 %',
            'hero_cta' => 'ซื้อประกันสุขภาพ',
            'hero_cta_link' => 'plan-details.html?id=easy-e-health',
            'hero_bg_image' => 'assets/img/hero-bg.png',
            'intro_title' => 'ประกันชีวิต',
            'intro_text' => 'ประกันชีวิตคือการวางแผนทางการเงินที่สำคัญที่สุดสำหรับคุณและครอบครัว เรามุ่งมั่นให้บริการคำปรึกษาด้านประกันชีวิตที่ครบวงจร ช่วยคุณเลือกแผนที่เหมาะกับไลฟ์สไตล์และงบประมาณ เพื่อให้คุณมั่นใจในทุกช่วงชีวิต',
            'rec_section_label' => 'ประกัน',
            'rec_section_title' => 'แผนประกันแนะนำ',
            'consult_title' => 'ปรึกษาผู้เชี่ยวชาญประกันชีวิต',
        ],
        'about' => [
            'hero_title' => 'เกี่ยวกับเรา',
            'hero_headline' => 'ตัวแทน FWD ประกันชีวิตที่มุ่งมั่นเปลี่ยนมุมมองของผู้คนที่มีต่อการประกันชีวิต',
            'hero_image' => 'assets/img/hero/เกี่ยวกับเรา.png',
            'intro_title' => 'ที่ปรึกษาด้านประกันชีวิตที่เข้าใจคุณ',
            'intro_text' => 'Agent Thailand คือเครือข่ายตัวแทนประกันชีวิต FWD ที่มุ่งเน้นการให้คำปรึกษาแบบเข้าใจง่าย โปร่งใส และตรงความต้องการของแต่ละครอบครัว',
            'intro_text2' => 'ด้วยทีมตัวแทนมืออาชีพและระบบบริการที่ครบวงจร เราพร้อมดูแลตั้งแต่การเลือกแผน การสมัคร ไปจนถึงการเคลมและดูแลหลังการขาย',
        ],
        'contact' => [
            'hero_title' => 'ติดต่อเรา',
            'hero_headline' => 'ปรึกษาผู้เชี่ยวชาญประกันชีวิต แจ้งเคลม หรือสอบถามกรมธรรม์',
            'hero_image' => 'assets/img/hero/ติดต่อเรา.png',
            'consult_title' => 'ปรึกษาผู้เชี่ยวชาญประกันชีวิต',
        ],
        'faq' => [
            'hero_title' => 'คำถามที่พบบ่อย (FAQ)',
            'hero_headline' => 'คำถามที่พบบ่อยเกี่ยวกับประกันชีวิต ประกันสุขภาพ การสมัคร และการเคลม',
            'hero_image' => 'assets/img/hero/คำถามที่พบบ่อย.png',
        ],
        'plan' => [
            'hero_title' => 'ผลิตภัณฑ์ทั้งหมด',
            'hero_headline' => 'เลือกแผนประกันที่เหมาะกับคุณ',
            'hero_image' => 'assets/img/hero-bg.png',
            'listing_title' => 'ผลิตภัณฑ์ทั้งหมด',
            'listing_headline' => 'เลือกแผนประกันที่เหมาะกับคุณ',
        ],
        'blog' => [
            'hero_title' => 'บทความและความรู้',
            'hero_headline' => 'เคล็ดลับการวางแผนประกันชีวิตและสุขภาพ',
            'hero_image' => 'assets/img/hero/บทความ.png',
        ],
        'promotion' => [
            'hero_title' => 'โปรโมชั่นและสิทธิพิเศษ',
            'hero_headline' => 'ส่วนลดและข้อเสนอพิเศษสำหรับลูกค้า',
            'hero_image' => 'assets/img/hero/โปรโมชั่น.png',
        ],
        'hospitals' => [
            'hero_title' => 'โรงพยาบาลในเครือ',
            'hero_headline' => 'ค้นหาโรงพยาบาลที่รองรับกรมธรรม์ FWD',
            'hero_image' => 'assets/img/hero/โรงพยาบาลในเครือ.png',
        ],
        'register' => [
            'hero_title' => 'สมัครเป็นตัวแทนประกันชีวิต',
            'hero_headline' => 'เริ่มต้นอาชีพที่มั่นคงกับเครือข่าย Agent Thailand',
            'hero_image' => 'assets/img/hero/register-hero.jpg',
        ],
        'login' => [
            'hero_title' => 'เข้าสู่ระบบตัวแทน',
            'hero_headline' => 'สำหรับตัวแทน Agent Thailand',
        ],
    ];
    return $defaults[$page] ?? [];
}
