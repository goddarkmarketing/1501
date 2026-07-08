<?php

function importSeedDataFromJs(): array {
    $script = SITE_ROOT . '/admin/scripts/export-seed-data.cjs';
    if (!file_exists($script)) {
        throw new RuntimeException('ไม่พบสคริปต์ export-seed-data.cjs');
    }

    $node = 'node';
    $cmd = escapeshellarg($node) . ' ' . escapeshellarg($script) . ' ' . escapeshellarg(SITE_ROOT);
    $json = shell_exec($cmd);
    if (!$json) {
        throw new RuntimeException('ไม่สามารถอ่านข้อมูลจากไฟล์ JS ได้ — ตรวจสอบว่าติดตั้ง Node.js แล้ว');
    }

    $data = json_decode($json, true);
    if (!is_array($data)) {
        throw new RuntimeException('ข้อมูลจาก JS ไม่ถูกต้อง');
    }

    return $data;
}

function runFullSeedImport(array $categories): array {
    $pdo = getDB();
    $pdo->beginTransaction();

    $stats = ['plans' => 0, 'blogs' => 0, 'promos' => 0];

    foreach ($categories as $id => $cat) {
        query(
            'REPLACE INTO plan_categories (id, label, promo_section, why_section, listing_goals, icon, headline, hero_image, intro_title, intro_text, sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?)',
            [$id, $cat['label'], $cat['promo_section'], $cat['why_section'], $cat['listing_goals'], $cat['icon'], $cat['headline'], $cat['hero_image'], $cat['intro_title'], $cat['intro_text'], $cat['sort_order']]
        );
    }

    $data = importSeedDataFromJs();

    foreach ($data['plans'] ?? [] as $plan) {
        $planId = $plan['id'] ?? '';
        if ($planId === '' || empty($plan['name'])) {
            continue;
        }

        query(
            'REPLACE INTO plan_products (id, name, category_id, tagline, headline, price_from, price_note, hero_image, promo_text, promo_code, promo_until, is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?,1)',
            [
                $planId,
                $plan['name'],
                $plan['category'] ?? 'health',
                $plan['tagline'] ?? '',
                $plan['headline'] ?? '',
                $plan['priceFrom'] ?? 0,
                $plan['priceNote'] ?? '',
                $plan['heroImage'] ?? '',
                $plan['promo']['text'] ?? null,
                $plan['promo']['code'] ?? null,
                $plan['promo']['until'] ?? null,
            ]
        );

        query('DELETE FROM plan_benefits WHERE plan_id = ?', [$planId]);
        foreach ($plan['benefits'] ?? [] as $i => $text) {
            query('INSERT INTO plan_benefits (plan_id, benefit_text, sort_order) VALUES (?,?,?)', [$planId, $text, $i]);
        }
        query('DELETE FROM plan_highlights WHERE plan_id = ?', [$planId]);
        foreach ($plan['highlights'] ?? [] as $i => $text) {
            query('INSERT INTO plan_highlights (plan_id, highlight_text, sort_order) VALUES (?,?,?)', [$planId, $text, $i]);
        }
        query('DELETE FROM plan_conditions WHERE plan_id = ?', [$planId]);
        foreach ($plan['conditions'] ?? [] as $i => $text) {
            query('INSERT INTO plan_conditions (plan_id, condition_text, sort_order) VALUES (?,?,?)', [$planId, $text, $i]);
        }
        query('DELETE FROM plan_renewal WHERE plan_id = ?', [$planId]);
        foreach ($plan['renewal'] ?? [] as $i => $text) {
            query('INSERT INTO plan_renewal (plan_id, renewal_text, sort_order) VALUES (?,?,?)', [$planId, $text, $i]);
        }
        query('DELETE FROM plan_why WHERE plan_id = ?', [$planId]);
        foreach ($plan['why'] ?? [] as $i => $text) {
            query('INSERT INTO plan_why (plan_id, why_text, sort_order) VALUES (?,?,?)', [$planId, $text, $i]);
        }
        query('DELETE FROM plan_tiers WHERE plan_id = ?', [$planId]);
        foreach ($plan['tiers'] ?? [] as $i => $tier) {
            query('INSERT INTO plan_tiers (plan_id, tier_id, label, amount, unit, is_popular, sort_order) VALUES (?,?,?,?,?,?,?)', [
                $planId, $tier['id'] ?? '', $tier['label'] ?? '', $tier['amount'] ?? '', $tier['unit'] ?? '', !empty($tier['popular']) ? 1 : 0, $i,
            ]);
        }
        query('DELETE FROM plan_coverage_summary WHERE plan_id = ?', [$planId]);
        foreach ($plan['coverageSummary'] ?? [] as $i => $row) {
            query('INSERT INTO plan_coverage_summary (plan_id, label, value, unit, sort_order) VALUES (?,?,?,?,?)', [
                $planId, $row['label'] ?? '', $row['value'] ?? '', $row['unit'] ?? '', $i,
            ]);
        }
        query('DELETE FROM plan_coverage_rows WHERE plan_id = ?', [$planId]);
        foreach ($plan['coverageRows'] ?? [] as $i => $row) {
            query('INSERT INTO plan_coverage_rows (plan_id, label, values_json, sort_order) VALUES (?,?,?,?)', [
                $planId, $row['label'] ?? '', json_encode($row['values'] ?? [], JSON_UNESCAPED_UNICODE), $i,
            ]);
        }
        query('DELETE FROM plan_faqs WHERE plan_id = ?', [$planId]);
        foreach ($plan['faqs'] ?? [] as $i => $faq) {
            query('INSERT INTO plan_faqs (plan_id, question, answer, sort_order) VALUES (?,?,?,?)', [
                $planId, $faq['q'] ?? '', $faq['a'] ?? '', $i,
            ]);
        }

        $stats['plans']++;
    }

    foreach ($data['articles'] ?? [] as $a) {
        if (empty($a['id'])) {
            continue;
        }
        query(
            'REPLACE INTO blog_articles (id, title, excerpt, pub_date, date_label, category, category_label, image, views, is_active) VALUES (?,?,?,?,?,?,?,?,?,1)',
            [$a['id'], $a['title'], $a['excerpt'] ?? '', $a['date'] ?? null, $a['dateLabel'] ?? '', $a['category'] ?? '', $a['categoryLabel'] ?? '', $a['image'] ?? '', $a['views'] ?? 0]
        );
        query('DELETE FROM blog_tags WHERE article_id = ?', [$a['id']]);
        foreach ($a['tags'] ?? [] as $tag) {
            query('INSERT INTO blog_tags (article_id, tag) VALUES (?,?)', [$a['id'], $tag]);
        }
        query('DELETE FROM blog_content WHERE article_id = ?', [$a['id']]);
        foreach ($a['content'] ?? [] as $i => $block) {
            query('INSERT INTO blog_content (article_id, block_type, content, sort_order) VALUES (?,?,?,?)', [$a['id'], $block['type'] ?? 'p', $block['text'] ?? '', $i]);
        }
        $stats['blogs']++;
    }

    foreach ($data['promotions'] ?? [] as $p) {
        if (empty($p['id'])) {
            continue;
        }
        query(
            'REPLACE INTO promotions (id, title, highlight, description_html, category, category_key, badge, badge_type, image, cta, cta_href, promo_code, valid_until, installment, is_popular, is_new, sort_order, is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1)',
            [
                $p['id'], $p['title'], $p['highlight'] ?? '', $p['descriptionHtml'] ?? '',
                $p['category'] ?? '', $p['categoryKey'] ?? '', $p['badge'] ?? '', $p['badgeType'] ?? '',
                $p['image'] ?? '', $p['cta'] ?? '', $p['ctaHref'] ?? '', $p['promoCode'] ?? '',
                $p['validUntil'] ?? '', $p['installment'] ?? '', !empty($p['popular']) ? 1 : 0,
                !empty($p['isNew']) ? 1 : 0, $p['sortOrder'] ?? 0,
            ]
        );
        query('DELETE FROM promotion_bullets WHERE promotion_id = ?', [$p['id']]);
        foreach ($p['bullets'] ?? [] as $i => $bullet) {
            query('INSERT INTO promotion_bullets (promotion_id, bullet_text, sort_order) VALUES (?,?,?)', [$p['id'], $bullet, $i]);
        }
        $stats['promos']++;
    }

    $pdo->commit();
    return $stats;
}
