<?php
/**
 * JSON API for the React CMS (cms/).
 * GET  → bundle of pages, settings, blocks, faqs, plans, categories
 * POST → save bundle (+ optional publish)
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Credentials: true');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?$#', $origin)) {
    header("Access-Control-Allow-Origin: {$origin}");
}
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/ui.php';
require_once dirname(__DIR__) . '/includes/cms.php';

function jsonOut(array $payload, int $code = 200): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function buildBundle(): array {
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

    $faqs = [];
    foreach (fetchAll('SELECT question, answer FROM faq_items WHERE is_active = 1 ORDER BY sort_order, id') as $row) {
        $faqs[] = ['q' => $row['question'], 'a' => $row['answer']];
    }

    $blocks = [];
    foreach (getCmsBlockDefinitions() as $key => $def) {
        $blocks[$key] = getCmsBlock($key);
    }

    $categories = [];
    foreach (fetchAll('SELECT * FROM plan_categories ORDER BY sort_order, id') as $cat) {
        $features = fetchAll(
            'SELECT title, description AS feature_desc FROM plan_category_features WHERE category_id = ? ORDER BY sort_order, id',
            [$cat['id']]
        );
        $categories[$cat['id']] = [
            'id' => $cat['id'],
            'label' => $cat['label'],
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

    $plans = [];
    foreach (fetchAll('SELECT * FROM plan_products ORDER BY sort_order, name') as $plan) {
        $id = $plan['id'];
        $payload = [
            'id' => $id,
            'name' => $plan['name'],
            'category' => $plan['category_id'],
            'tagline' => $plan['tagline'],
            'headline' => $plan['headline'],
            'priceFrom' => (float) $plan['price_from'],
            'priceNote' => $plan['price_note'],
            'heroImage' => $plan['hero_image'],
            'isActive' => (bool) $plan['is_active'],
            'sortOrder' => (int) $plan['sort_order'],
            'benefits' => array_column(fetchAll('SELECT benefit_text FROM plan_benefits WHERE plan_id = ? ORDER BY sort_order, id', [$id]), 'benefit_text'),
            'highlights' => array_column(fetchAll('SELECT highlight_text FROM plan_highlights WHERE plan_id = ? ORDER BY sort_order, id', [$id]), 'highlight_text'),
            'conditions' => array_column(fetchAll('SELECT condition_text FROM plan_conditions WHERE plan_id = ? ORDER BY sort_order, id', [$id]), 'condition_text'),
            'renewal' => array_column(fetchAll('SELECT renewal_text FROM plan_renewal WHERE plan_id = ? ORDER BY sort_order, id', [$id]), 'renewal_text'),
            'why' => array_column(fetchAll('SELECT why_text FROM plan_why WHERE plan_id = ? ORDER BY sort_order, id', [$id]), 'why_text'),
            'promo' => [
                'text' => $plan['promo_text'] ?? '',
                'code' => $plan['promo_code'] ?? '',
                'until' => $plan['promo_until'] ?? '',
            ],
            'tiers' => [],
            'coverageSummary' => [],
            'coverageRows' => [],
            'faqs' => [],
        ];
        foreach (fetchAll('SELECT tier_id AS id, label, amount, unit, is_popular AS popular FROM plan_tiers WHERE plan_id = ? ORDER BY sort_order, id', [$id]) as $t) {
            $payload['tiers'][] = [
                'id' => $t['id'],
                'label' => $t['label'],
                'amount' => $t['amount'],
                'unit' => $t['unit'],
                'popular' => (bool) $t['popular'],
            ];
        }
        foreach (fetchAll('SELECT label, value, unit FROM plan_coverage_summary WHERE plan_id = ? ORDER BY sort_order, id', [$id]) as $c) {
            $payload['coverageSummary'][] = ['label' => $c['label'], 'value' => $c['value'], 'unit' => $c['unit']];
        }
        foreach (fetchAll('SELECT label, values_json FROM plan_coverage_rows WHERE plan_id = ? ORDER BY sort_order, id', [$id]) as $r) {
            $payload['coverageRows'][] = ['label' => $r['label'], 'values' => json_decode($r['values_json'], true) ?: []];
        }
        foreach (fetchAll('SELECT question, answer FROM plan_faqs WHERE plan_id = ? ORDER BY sort_order, id', [$id]) as $f) {
            $payload['faqs'][] = ['q' => $f['question'], 'a' => $f['answer']];
        }
        $plans[$id] = $payload;
    }

    return compact('pages', 'settings', 'blocks', 'faqs', 'plans', 'categories');
}

function linesToList($value): array {
    if (is_array($value)) {
        return array_values(array_filter(array_map(fn($v) => is_string($v) ? trim($v) : '', $value), fn($v) => $v !== ''));
    }
    if (!is_string($value)) return [];
    return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $value) ?: []), fn($v) => $v !== ''));
}

function savePlanFromPayload(array $plan): void {
    $id = trim((string) ($plan['id'] ?? ''));
    $name = trim((string) ($plan['name'] ?? ''));
    if ($id === '' || $name === '') return;

    $categoryId = (string) ($plan['category'] ?? '');
    $tagline = (string) ($plan['tagline'] ?? '');
    $headline = (string) ($plan['headline'] ?? '');
    $priceFrom = (float) ($plan['priceFrom'] ?? 0);
    $priceNote = (string) ($plan['priceNote'] ?? '');
    $heroImage = (string) ($plan['heroImage'] ?? '');
    $isActive = !empty($plan['isActive']) || !isset($plan['isActive']) ? 1 : 0;
    $sortOrder = (int) ($plan['sortOrder'] ?? 0);
    $promo = is_array($plan['promo'] ?? null) ? $plan['promo'] : [];
    $promoText = (string) ($promo['text'] ?? '');
    $promoCode = (string) ($promo['code'] ?? '');
    $promoUntil = (string) ($promo['until'] ?? '');

    $existing = fetchOne('SELECT id FROM plan_products WHERE id = ?', [$id]);
    if ($existing) {
        query(
            'UPDATE plan_products SET name=?, category_id=?, tagline=?, headline=?, price_from=?, price_note=?, hero_image=?, promo_text=?, promo_code=?, promo_until=?, is_active=?, sort_order=? WHERE id=?',
            [$name, $categoryId, $tagline, $headline, $priceFrom, $priceNote, $heroImage, $promoText, $promoCode, $promoUntil, $isActive, $sortOrder, $id]
        );
    } else {
        query(
            'INSERT INTO plan_products (id, name, category_id, tagline, headline, price_from, price_note, hero_image, promo_text, promo_code, promo_until, is_active, sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [$id, $name, $categoryId, $tagline, $headline, $priceFrom, $priceNote, $heroImage, $promoText, $promoCode, $promoUntil, $isActive, $sortOrder]
        );
    }

    $listMaps = [
        'plan_benefits' => ['benefits', 'benefit_text'],
        'plan_highlights' => ['highlights', 'highlight_text'],
        'plan_conditions' => ['conditions', 'condition_text'],
        'plan_renewal' => ['renewal', 'renewal_text'],
        'plan_why' => ['why', 'why_text'],
    ];
    foreach ($listMaps as $table => [$field, $col]) {
        query("DELETE FROM {$table} WHERE plan_id = ?", [$id]);
        foreach (linesToList($plan[$field] ?? []) as $i => $line) {
            query("INSERT INTO {$table} (plan_id, {$col}, sort_order) VALUES (?,?,?)", [$id, $line, $i]);
        }
    }

    query('DELETE FROM plan_tiers WHERE plan_id = ?', [$id]);
    foreach (($plan['tiers'] ?? []) as $i => $t) {
        if (!is_array($t)) continue;
        $tid = trim((string) ($t['id'] ?? ''));
        $label = trim((string) ($t['label'] ?? ''));
        if ($tid === '' && $label === '') continue;
        query(
            'INSERT INTO plan_tiers (plan_id, tier_id, label, amount, unit, is_popular, sort_order) VALUES (?,?,?,?,?,?,?)',
            [$id, $tid ?: ('tier-' . $i), $label, (string) ($t['amount'] ?? ''), (string) ($t['unit'] ?? ''), !empty($t['popular']) ? 1 : 0, $i]
        );
    }

    query('DELETE FROM plan_coverage_summary WHERE plan_id = ?', [$id]);
    foreach (($plan['coverageSummary'] ?? []) as $i => $c) {
        if (!is_array($c) || trim((string) ($c['label'] ?? '')) === '') continue;
        query(
            'INSERT INTO plan_coverage_summary (plan_id, label, value, unit, sort_order) VALUES (?,?,?,?,?)',
            [$id, trim((string) $c['label']), (string) ($c['value'] ?? ''), (string) ($c['unit'] ?? ''), $i]
        );
    }

    query('DELETE FROM plan_coverage_rows WHERE plan_id = ?', [$id]);
    foreach (($plan['coverageRows'] ?? []) as $i => $r) {
        if (!is_array($r) || trim((string) ($r['label'] ?? '')) === '') continue;
        $values = $r['values'] ?? [];
        if (!is_array($values)) $values = [];
        query(
            'INSERT INTO plan_coverage_rows (plan_id, label, values_json, sort_order) VALUES (?,?,?,?)',
            [$id, trim((string) $r['label']), json_encode(array_values($values), JSON_UNESCAPED_UNICODE), $i]
        );
    }

    query('DELETE FROM plan_faqs WHERE plan_id = ?', [$id]);
    foreach (($plan['faqs'] ?? []) as $i => $f) {
        if (!is_array($f)) continue;
        $q = trim((string) ($f['q'] ?? ''));
        $a = trim((string) ($f['a'] ?? ''));
        if ($q === '' || $a === '') continue;
        query('INSERT INTO plan_faqs (plan_id, question, answer, sort_order) VALUES (?,?,?,?)', [$id, $q, $a, $i]);
    }
}

function saveCategoryFromPayload(string $id, array $cat): void {
    $label = (string) ($cat['label'] ?? $id);
    $existing = fetchOne('SELECT id FROM plan_categories WHERE id = ?', [$id]);
    $fields = [
        $label,
        (string) ($cat['promoSection'] ?? ''),
        (string) ($cat['whySection'] ?? ''),
        (string) ($cat['listingGoals'] ?? ''),
        (string) ($cat['icon'] ?? ''),
        (string) ($cat['headline'] ?? ''),
        (string) ($cat['heroImage'] ?? ''),
        (string) ($cat['introTitle'] ?? ''),
        (string) ($cat['introText'] ?? ''),
    ];
    if ($existing) {
        query(
            'UPDATE plan_categories SET label=?, promo_section=?, why_section=?, listing_goals=?, icon=?, headline=?, hero_image=?, intro_title=?, intro_text=? WHERE id=?',
            [...$fields, $id]
        );
    } else {
        query(
            'INSERT INTO plan_categories (id, label, promo_section, why_section, listing_goals, icon, headline, hero_image, intro_title, intro_text, sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?)',
            [$id, ...$fields, 0]
        );
    }

    query('DELETE FROM plan_category_features WHERE category_id = ?', [$id]);
    foreach (($cat['features'] ?? []) as $i => $f) {
        if (!is_array($f)) continue;
        $title = trim((string) ($f['title'] ?? ''));
        if ($title === '') continue;
        query(
            'INSERT INTO plan_category_features (category_id, title, description, sort_order) VALUES (?,?,?,?)',
            [$id, $title, (string) ($f['desc'] ?? $f['description'] ?? ''), $i]
        );
    }
}

function saveBundle(array $payload): void {
    $db = getDB();
    $db->beginTransaction();
    try {
        if (!empty($payload['pages']) && is_array($payload['pages'])) {
            $defs = getPageDefinitions();
            foreach ($payload['pages'] as $slug => $sections) {
                if (!isset($defs[$slug]) || !is_array($sections)) continue;
                $sectionMeta = [];
                foreach ($defs[$slug]['sections'] as $s) {
                    $sectionMeta[$s['key']] = $s;
                }
                foreach ($sections as $key => $value) {
                    $meta = $sectionMeta[$key] ?? ['label' => $key, 'type' => 'text'];
                    saveSectionValue($slug, $key, (string) $value, $meta['label'] ?? $key, $meta['type'] ?? 'text');
                }
            }
        }

        if (!empty($payload['settings']) && is_array($payload['settings'])) {
            foreach ($payload['settings'] as $key => $value) {
                query('UPDATE site_settings SET setting_value = ? WHERE setting_key = ?', [(string) $value, $key]);
            }
        }

        if (!empty($payload['faqs']) && is_array($payload['faqs'])) {
            query('DELETE FROM faq_items');
            foreach ($payload['faqs'] as $i => $item) {
                if (!is_array($item)) continue;
                $q = trim((string) ($item['q'] ?? ''));
                $a = trim((string) ($item['a'] ?? ''));
                if ($q === '' || $a === '') continue;
                query('INSERT INTO faq_items (question, answer, sort_order, is_active) VALUES (?,?,?,1)', [$q, $a, $i]);
            }
        }

        if (!empty($payload['blocks']) && is_array($payload['blocks'])) {
            foreach ($payload['blocks'] as $key => $data) {
                if (is_array($data) || is_string($data)) {
                    saveCmsBlock($key, $data);
                }
            }
        }

        if (isset($payload['categories']) && is_array($payload['categories'])) {
            $keepIds = [];
            foreach ($payload['categories'] as $id => $cat) {
                if (!is_array($cat)) continue;
                $cid = (string) $id;
                saveCategoryFromPayload($cid, $cat);
                $keepIds[] = $cid;
            }
            // ลบหมวดที่ไม่มีใน payload (ถ้าไม่มีแผนผูกอยู่)
            foreach (fetchAll('SELECT id FROM plan_categories') as $row) {
                if (in_array($row['id'], $keepIds, true)) continue;
                $used = fetchOne('SELECT COUNT(*) AS c FROM plan_products WHERE category_id = ?', [$row['id']]);
                if ((int) ($used['c'] ?? 0) > 0) continue;
                query('DELETE FROM plan_categories WHERE id = ?', [$row['id']]);
            }
        }

        if (!empty($payload['plans']) && is_array($payload['plans'])) {
            foreach ($payload['plans'] as $plan) {
                if (is_array($plan)) savePlanFromPayload($plan);
            }
        }

        $db->commit();
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (!isLoggedIn()) {
            jsonOut(['ok' => false, 'error' => 'unauthorized'], 401);
        }
        jsonOut(['ok' => true, 'bundle' => buildBundle()]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isLoggedIn()) {
            jsonOut(['ok' => false, 'error' => 'unauthorized', 'message' => 'กรุณาเข้าสู่ระบบที่ /admin/login.php ก่อน'], 401);
        }
        $raw = file_get_contents('php://input') ?: '';
        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            jsonOut(['ok' => false, 'message' => 'JSON ไม่ถูกต้อง'], 400);
        }
        saveBundle($payload);
        $message = 'บันทึกข้อมูลเรียบร้อย';
        if (!empty($payload['publish'])) {
            define('CMS_INTERNAL_PUBLISH', true);
            require __DIR__ . '/publish.php';
            $message = 'บันทึกและเผยแพร่เรียบร้อย';
        }
        jsonOut(['ok' => true, 'message' => $message, 'bundle' => buildBundle()]);
    }

    jsonOut(['ok' => false, 'message' => 'Method not allowed'], 405);
} catch (Throwable $e) {
    jsonOut(['ok' => false, 'message' => $e->getMessage()], 500);
}
