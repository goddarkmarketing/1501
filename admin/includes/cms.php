<?php

function getCmsBlockDefinitions(): array {
    return [
        'footer' => [
            'label' => 'Footer ทั้งหมด',
            'group' => 'ส่วนกลาง',
            'type' => 'footer',
            'description' => 'ข้อความ ลิงก์ และโซเชียลด้านล่างทุกหน้า',
        ],
        'header_nav' => [
            'label' => 'เมนู Header',
            'group' => 'ส่วนกลาง',
            'type' => 'header_nav',
            'description' => 'ลิงก์หลักและปุ่ม CTA บนแถบด้านบน',
        ],
        'plan_nav_menu' => [
            'label' => 'Mega Menu ผลิตภัณฑ์',
            'group' => 'ส่วนกลาง',
            'type' => 'json',
            'description' => 'เมนูผลิตภัณฑ์ทั้งหมด (JSON)',
        ],
        'service_nav_menu' => [
            'label' => 'Mega Menu บริการลูกค้า',
            'group' => 'ส่วนกลาง',
            'type' => 'json',
            'description' => 'เมนูบริการลูกค้า (JSON)',
        ],
        'hero_fab' => [
            'label' => 'ปุ่มลอยติดต่อ (หน้าแรก)',
            'group' => 'หน้าแรก',
            'type' => 'list',
            'description' => 'ช่องทางติดต่อในปุ่มลอย',
        ],
        'home_services' => [
            'label' => 'บริการของเรา (Hero)',
            'group' => 'หน้าแรก',
            'type' => 'list',
            'description' => 'การ์ดบริการ 6 ช่องใต้ Hero',
        ],
        'home_featured_plans' => [
            'label' => 'แผนแนะนำ (หน้าแรก)',
            'group' => 'หน้าแรก',
            'type' => 'plan_ids',
            'description' => 'รหัสแผนประกันที่จะแสดง (คั่นด้วยบรรทัดใหม่)',
        ],
        'home_features' => [
            'label' => 'ส่วนจุดเด่น (หน้าแรก)',
            'group' => 'หน้าแรก',
            'type' => 'feature_section',
            'description' => 'หัวข้อและการ์ดจุดเด่น',
        ],
        'home_about_cta' => [
            'label' => 'แถบ CTA (หน้าแรก)',
            'group' => 'หน้าแรก',
            'type' => 'cta_band',
            'description' => 'แถบพร้อมวางแผนด้านล่าง',
        ],
        'visual_overrides' => [
            'label' => 'ข้อความแก้จาก Visual Editor',
            'group' => 'ระบบ',
            'type' => 'json',
            'description' => 'override ข้อความตาม selector ต่อหน้า',
        ],
        'consult_aside' => [
            'label' => 'ข้อความข้างฟอร์มปรึกษา',
            'group' => 'ฟอร์ม',
            'type' => 'consult_aside',
            'description' => 'การ์ดด้านข้างฟอร์มปรึกษา',
        ],
        'consult_terms' => [
            'label' => 'เงื่อนไขฟอร์มปรึกษา',
            'group' => 'ฟอร์ม',
            'type' => 'html',
            'description' => 'ข้อความใน popup เงื่อนไข',
        ],
        'about_stats' => [
            'label' => 'ตัวเลขสถิติ (เกี่ยวกับเรา)',
            'group' => 'เกี่ยวกับเรา',
            'type' => 'stats',
            'description' => 'ตัวเลข 4 ช่อง',
        ],
        'about_pillars' => [
            'label' => 'วิสัยทัศน์/พันธกิจ',
            'group' => 'เกี่ยวกับเรา',
            'type' => 'pillars',
            'description' => 'การ์ด 3 ช่อง',
        ],
        'about_why' => [
            'label' => 'ทำไมต้องเลือกเรา',
            'group' => 'เกี่ยวกับเรา',
            'type' => 'list',
            'description' => 'การ์ดจุดเด่น 6 ช่อง',
        ],
        'about_ethics' => [
            'label' => 'จริยธรรมธุรกิจ',
            'group' => 'เกี่ยวกับเรา',
            'type' => 'text_list',
            'description' => 'รายการข้อความ',
        ],
        'about_milestones' => [
            'label' => 'ไทม์ไลน์',
            'group' => 'เกี่ยวกับเรา',
            'type' => 'milestones',
            'description' => 'เหตุการณ์สำคัญ',
        ],
        'about_quote' => [
            'label' => 'คำกล่าวผู้บริหาร',
            'group' => 'เกี่ยวกับเรา',
            'type' => 'quote',
            'description' => 'ข้อความและชื่อผู้กล่าว',
        ],
        'register_content' => [
            'label' => 'หน้าสมัครตัวแทน',
            'group' => 'อื่นๆ',
            'type' => 'register',
            'description' => 'Hero และสิทธิประโยชน์',
        ],
        'login_content' => [
            'label' => 'หน้าเข้าสู่ระบบ',
            'group' => 'อื่นๆ',
            'type' => 'login',
            'description' => 'ข้อความหน้า Login',
        ],
        'promotion_filters' => [
            'label' => 'ตัวกรองโปรโมชัน',
            'group' => 'โปรโมชัน',
            'type' => 'filters',
            'description' => 'แท็บกรองหมวดหมู่',
        ],
        'plan_requirements' => [
            'label' => 'เอกสารสมัครประกัน',
            'group' => 'แผนประกัน',
            'type' => 'requirements',
            'description' => 'รายการเอกสารในหน้ารายละเอียดแผน',
        ],
        'plan_section_labels' => [
            'label' => 'หัวข้อ Section แผนประกัน',
            'group' => 'แผนประกัน',
            'type' => 'section_labels',
            'description' => 'ชื่อแท็บในหน้ารายละเอียดแผน',
        ],
    ];
}

function getDefaultCmsBlocks(): array {
    $planNavFile = SITE_ROOT . '/assets/js/hero-form-data.js';
    $planNav = [];
    $serviceNav = [];
    if (file_exists($planNavFile)) {
        $src = file_get_contents($planNavFile);
        if (preg_match('/const PLAN_NAV_MEGA_MENU = (\[[\s\S]*\])\s*;/m', $src, $m)) {
            $planNav = json_decode($m[1], true) ?: [];
        }
        if (preg_match('/const SERVICE_NAV_MEGA_MENU = (\[[\s\S]*\])\s*;/m', $src, $m)) {
            $serviceNav = json_decode($m[1], true) ?: [];
        }
    }

    return [
        'footer' => [
            'tagline' => 'ที่ปรึกษาด้านประกันชีวิตและสุขภาพ ช่วยวางแผนคุ้มครองที่เหมาะกับทุกช่วงวัย ด้วยผลิตภัณฑ์ FWD ที่ได้รับความไว้วางใจจากครอบครัวไทย',
            'copyright' => '© 2025 Agent Thailand — ตัวแทน FWD ประกันชีวิต สงวนลิขสิทธิ์',
            'cta_text' => 'นัดปรึกษาฟรี',
            'cta_href' => 'contact.html',
            'legal' => [
                ['label' => 'นโยบายความเป็นส่วนตัว', 'href' => '#'],
                ['label' => 'ข้อกำหนดการใช้งาน', 'href' => '#'],
            ],
            'columns' => [
                [
                    'title' => 'แบบประกัน',
                    'links' => [
                        ['label' => 'แบบประกันทั้งหมด', 'href' => 'plan.html'],
                        ['label' => 'ประกันชีวิตและอุบัติเหตุ', 'href' => 'plan.html?category=life-accident'],
                        ['label' => 'ประกันสุขภาพ', 'href' => 'plan.html?category=health'],
                        ['label' => 'ประกันโรคร้ายแรง', 'href' => 'plan.html?category=critical'],
                        ['label' => 'ประกันสะสมทรัพย์', 'href' => 'plan.html?category=savings'],
                    ],
                ],
                [
                    'title' => 'บริการลูกค้า',
                    'links' => [
                        ['label' => 'ติดต่อเรา', 'href' => 'contact.html'],
                        ['label' => 'โรงพยาบาลในเครือ', 'href' => 'hospitals.html'],
                        ['label' => 'แจ้งเคลม / สอบถามกรมธรรม์', 'href' => 'contact.html'],
                        ['label' => 'คำถามที่พบบ่อย', 'href' => 'faq.html'],
                        ['label' => 'โปรโมชั่นและสิทธิพิเศษ', 'href' => 'promotion.html'],
                    ],
                ],
                [
                    'title' => 'Agent Thailand',
                    'links' => [
                        ['label' => 'เกี่ยวกับเรา', 'href' => 'about-us.html'],
                        ['label' => 'บทความและความรู้', 'href' => 'blog.html'],
                        ['label' => 'สมัครเป็นตัวแทน', 'href' => 'register.html'],
                        ['label' => 'เข้าสู่ระบบตัวแทน', 'href' => 'login.html'],
                    ],
                ],
            ],
        ],
        'header_nav' => [
            'links' => [
                ['label' => 'หน้าแรก', 'href' => 'index.html', 'type' => 'link'],
                ['label' => 'ผลิตภัณฑ์ทั้งหมด', 'href' => 'plan.html', 'type' => 'dropdown', 'menu' => 'products'],
                ['label' => 'โปรโมชั่น', 'href' => 'promotion.html', 'type' => 'link'],
                ['label' => 'บทความ', 'href' => 'blog.html', 'type' => 'link'],
                ['label' => 'บริการลูกค้า', 'href' => '#', 'type' => 'dropdown', 'menu' => 'services'],
                ['label' => 'เกี่ยวกับเรา', 'href' => 'about-us.html', 'type' => 'link'],
            ],
            'agent_link' => ['label' => 'สมัครตัวแทน', 'href' => 'register.html'],
            'cta' => ['label' => 'ติดต่อเรา', 'href' => 'contact.html'],
        ],
        'plan_nav_menu' => $planNav,
        'service_nav_menu' => $serviceNav,
        'hero_fab' => [
            'title' => 'ติดต่อเรา',
            'items' => [
                ['channel' => 'buy', 'label' => 'ซื้อประกัน', 'value' => 'กรอกแบบฟอร์มปรึกษา', 'href' => 'contact.html'],
                ['channel' => 'agent', 'label' => 'สมัครตัวแทน', 'value' => 'สมัครเป็นตัวแทน FWD', 'href' => 'register.html'],
                ['channel' => 'facebook', 'label' => 'Facebook', 'value' => 'Agent Thailand', 'href' => 'https://www.facebook.com/AgentThailandFWD'],
                ['channel' => 'line', 'label' => 'LINE', 'value' => '@agentthailand', 'href' => 'https://line.me/ti/p/~@agentthailand'],
                ['channel' => 'tiktok', 'label' => 'TikTok', 'value' => '@agentthailand', 'href' => 'https://www.tiktok.com/@agentthailand'],
                ['channel' => 'email', 'label' => 'อีเมล', 'value' => 'me.agentthailand@gmail.com', 'href' => 'mailto:me.agentthailand@gmail.com'],
                ['channel' => 'phone', 'label' => 'โทรศัพท์', 'value' => '065-651-5328', 'href' => 'tel:+66656515328'],
            ],
        ],
        'home_services' => [
            'label' => 'บริการของเรา',
            'items' => [
                ['icon' => 'mouse-pointer-click', 'text' => 'ซื้อประกันออนไลน์', 'href' => 'plan.html'],
                ['icon' => 'file-text', 'text' => 'ยื่นเคลมออนไลน์', 'href' => 'contact.html'],
                ['icon' => 'laptop', 'text' => 'ยื่นกู้กรมธรรม์ออนไลน์', 'href' => 'contact.html'],
                ['icon' => 'shield-check', 'text' => 'ซื้อประกันออนไลน์', 'href' => 'plan.html'],
                ['icon' => 'crown', 'text' => 'ดูสิทธิพิเศษ', 'href' => 'promotion.html'],
                ['icon' => 'percent', 'text' => 'จ่ายเบี้ยฯ ง่ายๆ ผ่าน Omne', 'href' => 'contact.html'],
            ],
        ],
        'home_featured_plans' => [
            'plan_ids' => ['easy-e-health', 'fwd-precious-care', 'easy-e-life', 'easy-e-save-10-5'],
        ],
        'home_features' => [
            'eyebrow' => 'ทำไมต้อง Agent Thailand',
            'title' => 'ให้การประกันเป็นเรื่องง่ายด้วย',
            'highlight' => 'ที่ปรึกษามืออาชีพ',
            'image' => 'assets/img/feature-advisor.png',
            'items' => [
                ['icon' => 'users', 'title' => 'ทีมตัวแทนมืออาชีพ', 'text' => 'ผู้เชี่ยวชาญพร้อมให้คำปรึกษาแบบเข้าใจง่าย'],
                ['icon' => 'shield-check', 'title' => 'ผลิตภัณฑ์ FWD', 'text' => 'แผนประกันครบทุกความต้องการ'],
                ['icon' => 'clock', 'title' => 'บริการรวดเร็ว', 'text' => 'ติดต่อกลับตามเวลาที่คุณสะดวก'],
                ['icon' => 'heart-handshake', 'title' => 'ดูแลหลังการขาย', 'text' => 'ช่วยเหลือตั้งแต่สมัครจนถึงเคลม'],
            ],
            'cta' => ['label' => 'ปรึกษาฟรี', 'href' => 'contact.html'],
        ],
        'home_about_cta' => [
            'title' => 'พร้อมวางแผนความมั่นคงให้ครอบครัวแล้วหรือยัง?',
            'text' => 'ปรึกษาผู้เชี่ยวชาญฟรี ไม่มีค่าใช้จ่าย',
            'primary' => ['label' => 'นัดปรึกษา', 'href' => 'contact.html'],
            'secondary' => ['label' => 'ดูแผนประกัน', 'href' => 'plan.html'],
        ],
        'visual_overrides' => [],
        'consult_aside' => [
            'image' => 'assets/img/consult-advisor.jpg',
            'title' => 'เลือกปรึกษาผู้เชี่ยวชาญที่เหมาะกับคุณ',
            'text' => 'บริการให้คำปรึกษาด้านประกันชีวิตครบวงจร ช่วยคุณเลือกแผนที่เหมาะกับไลฟ์สไตล์และงบประมาณ',
            'bullets' => ['ปรึกษาฟรี ไม่มีค่าใช้จ่าย', 'ติดต่อกลับตามเวลาที่สะดวก', 'ทีมตัวแทนมืออาชีพ'],
            'link_label' => 'รายละเอียดและเงื่อนไข',
            'link_href' => '#',
        ],
        'consult_terms' => '<p>เมื่อส่งข้อมูลแบบฟอร์มนี้ ถือว่าท่านยินยอมให้ Agent Thailand ติดต่อกลับเพื่อให้คำปรึกษาด้านประกันชีวิต ข้อมูลของท่านจะถูกเก็บรักษาอย่างปลอดภัยตามนโยบายความเป็นส่วนตัว</p>',
        'about_stats' => [
            'items' => [
                ['value' => '10+', 'label' => 'ปีแห่งประสบการณ์', 'desc' => 'ให้บริการคำปรึกษาด้านประกันชีวิตอย่างต่อเนื่อง'],
                ['value' => '500+', 'label' => 'ตัวแทนมืออาชีพ', 'desc' => 'ครอบคลุมพื้นที่ให้บริการทั่วประเทศไทย'],
                ['value' => '5', 'label' => 'หมวดผลิตภัณฑ์ FWD', 'desc' => 'สุขภาพ ชีวิต โรคร้าย ออมทรัพย์ และลงทุน'],
                ['value' => '24/7', 'label' => 'ช่องทางบริการ', 'desc' => 'ติดต่อ แจ้งเคลม และสอบถามกรมธรรม์ได้สะดวก'],
            ],
        ],
        'about_pillars' => [
            'label' => 'วิสัยทัศน์และพันธกิจ',
            'title' => 'สิ่งที่เรายึดมั่นในการทำงาน',
            'items' => [
                ['icon' => 'target', 'title' => 'พันธกิจ', 'text' => 'ทำให้การประกันชีวิตเข้าถึงได้และเข้าใจง่ายสำหรับทุกครอบครัวไทย'],
                ['icon' => 'eye', 'title' => 'วิสัยทัศน์', 'text' => 'เป็นเครือข่ายตัวแทนประกันชีวิตที่ลูกค้าไว้วางใจมากที่สุด'],
                ['icon' => 'heart-handshake', 'title' => 'ค่านิยม', 'text' => 'บริการด้วยความจริงใจ โปร่งใส และเข้าใจลูกค้า'],
            ],
        ],
        'about_why' => [
            'label' => 'จุดเด่น',
            'title' => 'ทำไมต้องเลือก Agent Thailand',
            'items' => [
                ['icon' => 'award', 'title' => 'ตัวแทน FWD อย่างเป็นทางการ', 'text' => 'ผลิตภัณฑ์คุณภาพจากบริษัทประกันชั้นนำ'],
                ['icon' => 'users', 'title' => 'ทีมผู้เชี่ยวชาญ', 'text' => 'ตัวแทนมืออาชีพพร้อมให้คำปรึกษา'],
                ['icon' => 'smartphone', 'title' => 'สะดวกทุกช่องทาง', 'text' => 'ปรึกษาได้ทั้งออนไลน์และที่สำนักงาน'],
                ['icon' => 'shield-check', 'title' => 'ครบวงจร', 'text' => 'ดูแลตั้งแต่เลือกแผนจนถึงเคลม'],
                ['icon' => 'clock', 'title' => 'ติดต่อกลับรวดเร็ว', 'text' => 'ตอบกลับตามเวลาที่คุณสะดวก'],
                ['icon' => 'heart', 'title' => 'ใส่ใจทุกครอบครัว', 'text' => 'วางแผนที่เหมาะกับแต่ละบ้าน'],
            ],
        ],
        'about_ethics' => [
            'label' => 'จริยธรรมธุรกิจ',
            'title' => 'หลักการที่เรายึดถือ',
            'items' => [
                'ให้ข้อมูลที่ถูกต้อง ครบถ้วน และเข้าใจง่าย',
                'ปฏิบัติตามกฎหมายและจรรยาบรรณวิชาชีพ',
                'รักษาความลับของลูกค้า',
                'ให้บริการด้วยความซื่อสัตย์และเป็นธรรม',
            ],
        ],
        'about_milestones' => [
            'label' => 'เส้นทางของเรา',
            'title' => 'เหตุการณ์สำคัญ',
            'items' => [
                ['year' => '2015', 'title' => 'ก่อตั้ง Agent Thailand', 'text' => 'เริ่มให้บริการเป็นตัวแทน FWD'],
                ['year' => '2018', 'title' => 'ขยายเครือข่าย', 'text' => 'ตัวแทนครอบคลุมทั่วประเทศ'],
                ['year' => '2022', 'title' => 'บริการออนไลน์', 'text' => 'ปรึกษาและสมัครผ่านช่องทางดิจิทัล'],
                ['year' => '2025', 'title' => 'พันธมิตรที่ไว้วางใจ', 'text' => 'ดูแลครอบครัวไทยหลายพันครัวเรือน'],
            ],
        ],
        'about_quote' => [
            'text' => 'เราเชื่อว่าการประกันชีวิตไม่ใช่แค่กรมธรรม์ แต่คือความมั่นใจของครอบครัว — Agent Thailand พร้อมเดินเคียงข้างคุณในทุกช่วงชีวิต',
            'name' => 'ทีมผู้บริหาร Agent Thailand',
            'role' => 'ตัวแทน FWD ประกันชีวิต',
        ],
        'register_content' => [
            'hero_title' => 'สมัครเป็นตัวแทนประกันชีวิต',
            'hero_text' => 'เริ่มต้นอาชีพที่มั่นคงกับเครือข่าย Agent Thailand',
            'hero_image' => 'assets/img/hero/register-hero.jpg',
            'perks' => [
                ['icon' => 'trending-up', 'title' => 'รายได้มั่นคง', 'text' => 'โครงสร้างค่าตอบแทนที่ชัดเจน'],
                ['icon' => 'graduation-cap', 'title' => 'อบรมฟรี', 'text' => 'หลักสูตรพัฒนาตัวแทนมืออาชีพ'],
                ['icon' => 'users', 'title' => 'ทีมสนับสนุน', 'text' => 'มี Mentor ดูแลตลอดเส้นทาง'],
                ['icon' => 'laptop', 'title' => 'เครื่องมือดิจิทัล', 'text' => 'ระบบออนไลน์ช่วยทำงาน'],
            ],
            'steps_title' => 'ขั้นตอนการสมัคร',
            'steps' => ['กรอกใบสมัครออนไลน์', 'สัมภาษณ์กับทีมงาน', 'อบรมและเริ่มปฏิบัติงาน'],
        ],
        'login_content' => [
            'title' => 'เข้าสู่ระบบตัวแทน',
            'subtitle' => 'สำหรับตัวแทน Agent Thailand',
            'footer_text' => 'ยังไม่มีบัญชี?',
            'footer_link' => ['label' => 'สมัครตัวแทน', 'href' => 'register.html'],
        ],
        'promotion_filters' => [
            'filters' => [
                ['id' => 'all', 'label' => 'ทั้งหมด'],
                ['id' => 'savings', 'label' => 'ประกันสะสมทรัพย์'],
                ['id' => 'health', 'label' => 'ประกันสุขภาพ'],
                ['id' => 'life-accident', 'label' => 'ประกันชีวิตและอุบัติเหตุ'],
                ['id' => 'critical', 'label' => 'ประกันโรคร้ายแรง'],
            ],
        ],
        'plan_requirements' => [
            'items' => [
                ['icon' => 'assets/img/plan-detail/identity.svg', 'title' => 'บัตรประชาชน', 'desc' => 'เตรียมบัตรประชาชนตัวจริง'],
                ['icon' => 'assets/img/plan-detail/camera.svg', 'title' => 'ยืนยันตัวตน', 'desc' => 'ถ่ายรูปเซลฟี่ถือบัตรประชาชน'],
                ['icon' => 'assets/img/plan-detail/visa.svg', 'title' => 'ชำระเงิน', 'desc' => 'บัตรเครดิต หรือแอปธนาคาร'],
            ],
        ],
        'plan_section_labels' => [
            'sections' => [
                ['id' => 'highlights', 'label' => 'จุดเด่นของแผนประกันนี้'],
                ['id' => 'compare', 'label' => 'เปรียบเทียบแผนความคุ้มครอง'],
                ['id' => 'promo', 'label' => 'โปรโมชันประกันสุขภาพ'],
                ['id' => 'conditions', 'label' => 'รายละเอียดและเงื่อนไขกรมธรรม์'],
                ['id' => 'renewal', 'label' => 'การต่ออายุ'],
                ['id' => 'why', 'label' => 'ทำไมต้องมีประกันนี้?'],
                ['id' => 'faq', 'label' => 'คำถามที่พบบ่อย (FAQ)'],
            ],
        ],
    ];
}

function getCmsBlock(string $key): array|string {
    $defs = getCmsBlockDefinitions();
    $defaults = getDefaultCmsBlocks();
    $default = $defaults[$key] ?? [];

    try {
        $row = fetchOne('SELECT content_json FROM cms_blocks WHERE block_key = ?', [$key]);
        if ($row && $row['content_json'] !== '') {
            $decoded = json_decode($row['content_json'], true);
            if (is_string($decoded)) {
                return $decoded;
            }
            if (is_array($decoded)) {
                return is_array($default) ? array_replace_recursive($default, $decoded) : $decoded;
            }
        }
    } catch (Throwable $e) {
        // table may not exist
    }
    return $default;
}

function saveCmsBlock(string $key, array|string $data): void {
    $defs = getCmsBlockDefinitions();
    $label = $defs[$key]['label'] ?? $key;
    $group = $defs[$key]['group'] ?? 'general';
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    query(
        'INSERT INTO cms_blocks (block_key, label, block_group, content_json) VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE content_json = VALUES(content_json), label = VALUES(label), block_group = VALUES(block_group)',
        [$key, $label, $group, $json]
    );
}

function seedCmsBlocksIfEmpty(): void {
    try {
        $count = fetchOne('SELECT COUNT(*) AS c FROM cms_blocks');
        if ((int) ($count['c'] ?? 0) > 0) {
            return;
        }
    } catch (Throwable $e) {
        return;
    }
    foreach (getDefaultCmsBlocks() as $key => $data) {
        saveCmsBlock($key, $data);
    }
}
