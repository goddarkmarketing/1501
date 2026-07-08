<?php
require_once dirname(__DIR__) . '/includes/db.php';

$isCli = (PHP_SAPI === 'cli');
if (!$isCli) {
    require_once dirname(__DIR__) . '/includes/auth.php';
    requireLogin();
}

function jsExport($value): string {
    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function extractBetween(string $file, string $start, string $end, bool $includeEnd = true): string {
    $content = file_get_contents($file);
    $s = strpos($content, $start);
    if ($s === false) {
        throw new RuntimeException("Marker not found: {$start}");
    }
    if ($end === '') {
        return substr($content, $s);
    }
    $e = strpos($content, $end, $s);
    if ($e === false) {
        throw new RuntimeException("Marker not found: {$end}");
    }
    $len = $includeEnd ? $e - $s + strlen($end) : $e - $s;
    return substr($content, $s, $len);
}

function planDataHelperTail(): string {
    return <<<'JS'

function getAllPlanProducts() {
  return Object.values(PLAN_PRODUCTS);
}

function getPlanProduct(id) {
  return PLAN_PRODUCTS[id] || null;
}

JS;
}

function buildPlanDataJs(): string {
    $src = SITE_ROOT . '/assets/js/plan-data.js';
    $planCount = (int) (fetchOne('SELECT COUNT(*) AS c FROM plan_products WHERE is_active = 1')['c'] ?? 0);
    if ($planCount === 0) {
        return file_get_contents($src);
    }

    $labels = [];
    foreach (fetchAll('SELECT id, label FROM plan_categories ORDER BY sort_order, id') as $row) {
        $labels[$row['id']] = $row['label'];
    }
    if (!$labels) {
        $labels = [
            'health' => 'ประกันสุขภาพ',
            'critical' => 'ประกันโรคร้ายแรง',
            'life-accident' => 'ประกันชีวิตและอุบัติเหตุ',
            'savings' => 'ประกันสะสมทรัพย์',
            'investment-linked' => 'ประกันชีวิตควบการลงทุน',
        ];
    }

    $requirements = [];
    try {
        require_once dirname(__DIR__) . '/includes/cms.php';
        $reqBlock = getCmsBlock('plan_requirements');
        $requirements = $reqBlock['items'] ?? [];
    } catch (Throwable $e) {
        $requirements = [];
    }
    if (!$requirements) {
        $requirements = [
            ['icon' => 'assets/img/plan-detail/identity.svg', 'title' => 'บัตรประชาชน', 'desc' => 'เตรียมบัตรประชาชนตัวจริง'],
            ['icon' => 'assets/img/plan-detail/camera.svg', 'title' => 'ยืนยันตัวตน', 'desc' => 'ถ่ายรูปเซลฟี่ถือบัตรประชาชนเพื่อยืนยันตัวตน'],
            ['icon' => 'assets/img/plan-detail/visa.svg', 'title' => 'ชำระเงิน', 'desc' => 'บัตรเครดิต หรือแอปธนาคารสำหรับชำระเบี้ย'],
        ];
    }

    $sections = [];
    try {
        if (!isset($reqBlock)) {
            require_once dirname(__DIR__) . '/includes/cms.php';
        }
        $secBlock = getCmsBlock('plan_section_labels');
        $sections = $secBlock['sections'] ?? [];
    } catch (Throwable $e) {
        $sections = [];
    }
    if (!$sections) {
        $sections = [
            ['id' => 'highlights', 'label' => 'จุดเด่นของแผนประกันนี้'],
            ['id' => 'compare', 'label' => 'เปรียบเทียบแผนความคุ้มครอง'],
            ['id' => 'promo', 'label' => 'โปรโมชันประกันสุขภาพ'],
            ['id' => 'conditions', 'label' => 'รายละเอียดและเงื่อนไขกรมธรรม์'],
            ['id' => 'renewal', 'label' => 'การต่ออายุ'],
            ['id' => 'why', 'label' => 'ทำไมต้องมีประกันนี้?'],
            ['id' => 'faq', 'label' => 'คำถามที่พบบ่อย (FAQ)'],
        ];
    }

    $meta = [];
    foreach (fetchAll('SELECT * FROM plan_categories ORDER BY sort_order, id') as $cat) {
        $features = fetchAll(
            'SELECT title, description AS feature_desc FROM plan_category_features WHERE category_id = ? ORDER BY sort_order, id',
            [$cat['id']]
        );
        $meta[$cat['id']] = [
            'promoSection' => $cat['promo_section'],
            'whySection' => $cat['why_section'],
            'listingGoals' => $cat['listing_goals'],
            'icon' => $cat['icon'],
            'headline' => $cat['headline'],
            'heroImage' => $cat['hero_image'],
            'introTitle' => $cat['intro_title'],
            'introText' => $cat['intro_text'],
            'features' => array_map(fn($f) => ['title' => $f['title'], 'desc' => $f['feature_desc']], $features),
        ];
    }

    $middle = extractBetween($src, 'function getPlanCategoryPage(category)', 'const PLAN_PRODUCTS = {', false);
    $tail = planDataHelperTail();

    $productsJs = "const PLAN_PRODUCTS = {\n";
    $plans = fetchAll('SELECT * FROM plan_products WHERE is_active = 1 ORDER BY sort_order, name');
    if ($plans) {
        foreach ($plans as $plan) {
        $id = $plan['id'];
        $benefits = array_column(fetchAll('SELECT benefit_text FROM plan_benefits WHERE plan_id = ? ORDER BY sort_order, id', [$id]), 'benefit_text');
        $highlights = array_column(fetchAll('SELECT highlight_text FROM plan_highlights WHERE plan_id = ? ORDER BY sort_order, id', [$id]), 'highlight_text');
        $conditions = array_column(fetchAll('SELECT condition_text FROM plan_conditions WHERE plan_id = ? ORDER BY sort_order, id', [$id]), 'condition_text');
        $renewal = array_column(fetchAll('SELECT renewal_text FROM plan_renewal WHERE plan_id = ? ORDER BY sort_order, id', [$id]), 'renewal_text');
        $why = array_column(fetchAll('SELECT why_text FROM plan_why WHERE plan_id = ? ORDER BY sort_order, id', [$id]), 'why_text');

        $tiers = [];
        foreach (fetchAll('SELECT tier_id AS id, label, amount, unit, is_popular AS popular FROM plan_tiers WHERE plan_id = ? ORDER BY sort_order, id', [$id]) as $t) {
            $tiers[] = [
                'id' => $t['id'],
                'label' => $t['label'],
                'amount' => $t['amount'],
                'unit' => $t['unit'],
                'popular' => (bool) $t['popular'],
            ];
        }

        $coverageSummary = [];
        foreach (fetchAll('SELECT label, value, unit FROM plan_coverage_summary WHERE plan_id = ? ORDER BY sort_order, id', [$id]) as $c) {
            $coverageSummary[] = ['label' => $c['label'], 'value' => $c['value'], 'unit' => $c['unit']];
        }

        $coverageRows = [];
        foreach (fetchAll('SELECT label, values_json FROM plan_coverage_rows WHERE plan_id = ? ORDER BY sort_order, id', [$id]) as $r) {
            $coverageRows[] = ['label' => $r['label'], 'values' => json_decode($r['values_json'], true) ?: []];
        }

        $faqs = [];
        foreach (fetchAll('SELECT question, answer FROM plan_faqs WHERE plan_id = ? ORDER BY sort_order, id', [$id]) as $f) {
            $faqs[] = ['q' => $f['question'], 'a' => $f['answer']];
        }

        $payload = [
            'id' => $id,
            'name' => $plan['name'],
            'category' => $plan['category_id'],
            'tagline' => $plan['tagline'],
            'headline' => $plan['headline'],
            'priceFrom' => (float) $plan['price_from'],
            'priceNote' => $plan['price_note'],
            'heroImage' => $plan['hero_image'],
            'benefits' => $benefits,
            'highlights' => $highlights,
            'conditions' => $conditions,
            'renewal' => $renewal,
            'why' => $why,
        ];
        if ($plan['promo_text']) {
            $payload['promo'] = [
                'text' => $plan['promo_text'],
                'code' => $plan['promo_code'],
                'until' => $plan['promo_until'],
            ];
        }
        if ($tiers) {
            $payload['tiers'] = $tiers;
        }
        if ($coverageSummary) {
            $payload['coverageSummary'] = $coverageSummary;
        }
        if ($coverageRows) {
            $payload['coverageRows'] = $coverageRows;
        }
        if ($faqs) {
            $payload['faqs'] = $faqs;
        }

        $productsJs .= "  " . jsExport($id) . ": createPlanProduct(" . jsExport($payload) . "),\n";
        }
        $productsJs .= "};\n\n";
    } else {
        if (preg_match('/const PLAN_PRODUCTS = (\{[\s\S]*?\n\});/', $src, $m)) {
            $productsJs = 'const PLAN_PRODUCTS = ' . $m[1] . ";\n\n";
        } else {
            $productsJs .= "};\n\n";
        }
    }

    $out = "const PLAN_CATEGORY_LABELS = " . jsExport($labels) . ";\n\n";
    $out .= "const PLAN_REQUIREMENTS = " . jsExport($requirements) . ";\n\n";
    $out .= "const PLAN_SECTIONS = " . jsExport($sections) . ";\n\n";
    $out .= "const PLAN_CATEGORY_META = " . jsExport($meta) . ";\n\n";
    $out .= $middle;
    $out .= $productsJs;
    $out .= $tail;

    return $out;
}

function buildBlogDataJs(): string {
    $src = SITE_ROOT . '/assets/js/blog-data.js';
    $blogCount = (int) (fetchOne('SELECT COUNT(*) AS c FROM blog_articles WHERE is_active = 1')['c'] ?? 0);
    if ($blogCount === 0) {
        return file_get_contents($src);
    }

    try {
        $tail = extractBetween($src, 'function getBlogArticle(id)', '');
    } catch (Throwable $e) {
        $tail = "\nfunction getBlogArticle(id) {\n  return BLOG_ARTICLES.find((a) => a.id === id) || null;\n}\n";
    }

    $categories = [['value' => 'all', 'label' => 'ทั้งหมด']];
    $seen = [];
    foreach (fetchAll('SELECT DISTINCT category, category_label FROM blog_articles WHERE is_active = 1 ORDER BY category') as $row) {
        if ($row['category'] && !isset($seen[$row['category']])) {
            $categories[] = ['value' => $row['category'], 'label' => $row['category_label']];
            $seen[$row['category']] = true;
        }
    }

    $articles = [];
    foreach (fetchAll('SELECT * FROM blog_articles WHERE is_active = 1 ORDER BY pub_date DESC') as $a) {
        $tags = array_column(fetchAll('SELECT tag FROM blog_tags WHERE article_id = ?', [$a['id']]), 'tag');
        $content = [];
        foreach (fetchAll('SELECT block_type AS type, content AS text FROM blog_content WHERE article_id = ? ORDER BY sort_order, id', [$a['id']]) as $block) {
            $content[] = ['type' => $block['type'], 'text' => $block['text']];
        }
        $articles[] = [
            'id' => $a['id'],
            'title' => $a['title'],
            'excerpt' => $a['excerpt'],
            'date' => $a['pub_date'],
            'dateLabel' => $a['date_label'],
            'category' => $a['category'],
            'categoryLabel' => $a['category_label'],
            'tags' => $tags,
            'image' => $a['image'],
            'views' => (int) $a['views'],
            'content' => $content,
        ];
    }

    if (!$articles && preg_match('/const BLOG_ARTICLES = (\[[\s\S]*?\]);/', $src, $m)) {
        return file_get_contents($src);
    }

    return "const BLOG_CATEGORIES = " . jsExport($categories) . ";\n\n"
        . "const BLOG_ARTICLES = " . jsExport($articles) . ";\n\n"
        . $tail;
}

function buildPromotionDataJs(): string {
    $src = SITE_ROOT . '/assets/js/promotion-data.js';
    $promoCount = (int) (fetchOne('SELECT COUNT(*) AS c FROM promotions WHERE is_active = 1')['c'] ?? 0);
    if ($promoCount === 0) {
        return file_get_contents($src);
    }

    try {
        $tail = extractBetween($src, 'function getPromotionItems(category', '');
    } catch (Throwable $e) {
        $tail = "\nfunction getPromotionItems(category = 'all') {\n  if (category === 'all') return PROMOTION_ITEMS;\n  return PROMOTION_ITEMS.filter((p) => p.categoryKey === category);\n}\n";
    }

    $filters = [];
    try {
        foreach (fetchAll('SELECT id, label FROM promotion_filter_items ORDER BY sort_order, id') as $row) {
            $filters[] = ['id' => $row['id'], 'label' => $row['label']];
        }
    } catch (Throwable $e) {
        $filters = [];
    }
    if (!$filters) {
        $filters = [
            ['id' => 'all', 'label' => 'ทั้งหมด'],
            ['id' => 'savings', 'label' => 'ประกันสะสมทรัพย์'],
            ['id' => 'health', 'label' => 'ประกันสุขภาพ'],
            ['id' => 'life-accident', 'label' => 'ประกันชีวิตและอุบัติเหตุ'],
            ['id' => 'critical', 'label' => 'ประกันโรคร้ายแรง'],
        ];
    }

    $items = [];
    foreach (fetchAll('SELECT * FROM promotions WHERE is_active = 1 ORDER BY sort_order, title') as $p) {
        $bullets = array_column(fetchAll('SELECT bullet_text FROM promotion_bullets WHERE promotion_id = ? ORDER BY sort_order, id', [$p['id']]), 'bullet_text');
        $items[] = [
            'id' => $p['id'],
            'title' => $p['title'],
            'highlight' => $p['highlight'],
            'descriptionHtml' => $p['description_html'],
            'bullets' => $bullets,
            'category' => $p['category'],
            'categoryKey' => $p['category_key'],
            'badge' => $p['badge'] ?? '',
            'badgeType' => $p['badge_type'] ?? '',
            'image' => $p['image'],
            'cta' => $p['cta'],
            'ctaHref' => $p['cta_href'],
            'promoCode' => $p['promo_code'],
            'validUntil' => $p['valid_until'],
            'installment' => $p['installment'],
            'popular' => (bool) $p['is_popular'],
            'isNew' => (bool) $p['is_new'],
            'sortOrder' => (int) $p['sort_order'],
        ];
    }

    if (!$items && preg_match('/const PROMOTION_ITEMS = (\[[\s\S]*?\]);/', $src, $m)) {
        $itemsJson = $m[1];
        return "const PROMOTION_FILTERS = " . jsExport($filters) . ";\n\n"
            . "const PROMOTION_ITEMS = " . $itemsJson . ";\n\n"
            . $tail;
    }

    return "const PROMOTION_FILTERS = " . jsExport($filters) . ";\n\n"
        . "const PROMOTION_ITEMS = " . jsExport($items) . ";\n\n"
        . $tail;
}

function buildSiteContentJs(): string {
    require_once dirname(__DIR__) . '/includes/ui.php';
    require_once dirname(__DIR__) . '/includes/cms.php';

    $pages = [];
    foreach (getPageDefinitions() as $slug => $def) {
        $defaults = getDefaultSectionValues($slug);
        $sections = [];
        foreach ($def['sections'] as $section) {
            $sections[$section['key']] = getSectionValue(
                $slug,
                $section['key'],
                $defaults[$section['key']] ?? ''
            );
        }
        $pages[$slug] = $sections;
    }

    $settings = [];
    foreach (fetchAll('SELECT setting_key, setting_value FROM site_settings') as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    $faqItems = [];
    foreach (fetchAll('SELECT question, answer FROM faq_items WHERE is_active = 1 ORDER BY sort_order, id') as $row) {
        $faqItems[] = ['q' => $row['question'], 'a' => $row['answer']];
    }

    $blocks = [];
    foreach (getCmsBlockDefinitions() as $key => $def) {
        $blocks[$key] = getCmsBlock($key);
    }

    $planNav = $blocks['plan_nav_menu'] ?? [];
    $serviceNav = $blocks['service_nav_menu'] ?? [];
    $navMenus = [
        'products' => $planNav,
        'services' => $serviceNav,
    ];

    $out = "const SITE_PAGES = " . jsExport($pages) . ";\n\n";
    $out .= "const SITE_SETTINGS = " . jsExport($settings) . ";\n\n";
    $out .= "const SITE_FAQ_ITEMS = " . jsExport($faqItems) . ";\n\n";
    $out .= "const SITE_BLOCKS = " . jsExport($blocks) . ";\n\n";
    $out .= "const SITE_NAV_MENUS = " . jsExport($navMenus) . ";\n\n";
    $out .= "function getSitePage(slug) {\n  return SITE_PAGES[slug] || {};\n}\n";
    return $out;
}

try {
    file_put_contents(SITE_ROOT . '/assets/js/plan-data.js', buildPlanDataJs());
    file_put_contents(SITE_ROOT . '/assets/js/blog-data.js', buildBlogDataJs());
    file_put_contents(SITE_ROOT . '/assets/js/promotion-data.js', buildPromotionDataJs());
    file_put_contents(SITE_ROOT . '/assets/js/site-content.js', buildSiteContentJs());
    if ($isCli) {
        echo "Published successfully.\n";
        exit(0);
    }
    $_SESSION['flash'] = 'เผยแพร่เว็บไซต์สำเร็จ — อัปเดตข้อมูลทั้งหมดแล้ว';
} catch (Throwable $e) {
    if ($isCli) {
        fwrite(STDERR, 'Publish failed: ' . $e->getMessage() . "\n");
        exit(1);
    }
    $_SESSION['flash_error'] = 'เผยแพร่ไม่สำเร็จ: ' . $e->getMessage();
}

if (!$isCli) {
    header('Location: ' . ADMIN_URL . '/');
    exit;
}
