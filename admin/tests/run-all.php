<?php
/**
 * Full admin backend integration suite.
 *
 * Run (from any cwd):
 *   C:\xampp\php\php.exe c:\xampp\htdocs\1501\admin\tests\run-all.php
 *
 * Optional:
 *   set ATEST_BASE=http://localhost/1501
 *
 * Tests hit live Apache + MySQL. Test rows use marker prefix ATEST_* and are cleaned up.
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$started = microtime(true);
echo "Admin backend integration tests\n";
echo "Base: " . ATEST_BASE . "\n";
echo "Marker: " . ATEST_MARKER . "\n";

try {
    ATestDb::pdo()->query('SELECT 1');
} catch (Throwable $e) {
    fwrite(STDERR, "Cannot connect to MySQL agent1501: " . $e->getMessage() . "\n");
    exit(2);
}

try {
    $probe = ATestHttp::get('login.php');
    if ($probe['status'] < 200 || $probe['status'] >= 500) {
        throw new RuntimeException('HTTP status ' . $probe['status']);
    }
} catch (Throwable $e) {
    fwrite(STDERR, "Cannot reach admin over HTTP: " . $e->getMessage() . "\n");
    fwrite(STDERR, "Start Apache in XAMPP and retry.\n");
    exit(2);
}

$M = ATEST_MARKER;
$catId = strtolower('atest-cat-' . substr(md5($M), 0, 8));
$planId = 'atest-plan-' . substr(md5($M), 0, 8);
$blogId = 'atest-blog-' . substr(md5($M), 0, 8);
$promoId = 'atest-promo-' . substr(md5($M), 0, 8);
$testUser = 'atest_u_' . substr(md5($M), 0, 6);
$faqQuestion = "{$M} FAQ question?";
$settingsBackup = null;
$pageSectionBackup = null;
$footerBackup = null;
$filtersBackup = [];
$visualBackup = null;
$uploadedFile = null;
$contactId = null;
$faqId = null;
$userId = null;

register_shutdown_function(static function () use (
    &$catId, &$planId, &$blogId, &$promoId, &$testUser, &$faqId, &$contactId,
    &$settingsBackup, &$pageSectionBackup, &$footerBackup, &$filtersBackup, &$visualBackup, &$uploadedFile
): void {
    try {
        if ($planId) {
            ATestDb::exec('DELETE FROM plan_products WHERE id = ?', [$planId]);
        }
        if ($catId) {
            ATestDb::exec('DELETE FROM plan_category_features WHERE category_id = ?', [$catId]);
            ATestDb::exec('DELETE FROM plan_categories WHERE id = ?', [$catId]);
            // Remove test group from nav if present
            $navRow = ATestDb::one("SELECT content_json FROM cms_blocks WHERE block_key = 'plan_nav_menu'");
            if ($navRow && $navRow['content_json']) {
                $nav = json_decode($navRow['content_json'], true);
                if (is_array($nav)) {
                    foreach ($nav as &$col) {
                        if (!isset($col['groups']) || !is_array($col['groups'])) continue;
                        $col['groups'] = array_values(array_filter(
                            $col['groups'],
                            static fn($g) => ($g['category'] ?? '') !== $catId
                        ));
                    }
                    unset($col);
                    ATestDb::exec(
                        "UPDATE cms_blocks SET content_json = ? WHERE block_key = 'plan_nav_menu'",
                        [json_encode($nav, JSON_UNESCAPED_UNICODE)]
                    );
                }
            }
        }
        if ($blogId) {
            ATestDb::exec('DELETE FROM blog_articles WHERE id = ?', [$blogId]);
        }
        if ($promoId) {
            ATestDb::exec('DELETE FROM promotions WHERE id = ?', [$promoId]);
            ATestDb::exec("DELETE FROM promotion_filter_items WHERE id LIKE 'atest-%'");
        }
        if ($faqId) {
            ATestDb::exec('DELETE FROM faq_items WHERE id = ?', [$faqId]);
        }
        ATestDb::exec("DELETE FROM faq_items WHERE question LIKE 'ATEST_%'");
        if ($contactId) {
            ATestDb::exec('DELETE FROM contact_submissions WHERE id = ?', [$contactId]);
        }
        ATestDb::exec("DELETE FROM contact_submissions WHERE name LIKE 'ATEST_%' OR message LIKE 'ATEST_%'");
        if ($testUser) {
            ATestDb::exec('DELETE FROM admin_users WHERE username = ?', [$testUser]);
        }
        if (is_array($settingsBackup)) {
            foreach ($settingsBackup as $key => $val) {
                ATestDb::exec('UPDATE site_settings SET setting_value = ? WHERE setting_key = ?', [$val, $key]);
            }
        }
        if (is_array($pageSectionBackup)) {
            foreach ($pageSectionBackup as $key => $val) {
                ATestDb::exec(
                    'UPDATE page_sections SET content_value = ? WHERE page_slug = ? AND section_key = ?',
                    [$val, 'home', $key]
                );
            }
        }
        if ($footerBackup !== null) {
            ATestDb::exec(
                "UPDATE cms_blocks SET content_json = ? WHERE block_key = 'footer'",
                [is_string($footerBackup) ? $footerBackup : json_encode($footerBackup, JSON_UNESCAPED_UNICODE)]
            );
        }
        if ($filtersBackup) {
            ATestDb::exec('DELETE FROM promotion_filter_items');
            foreach ($filtersBackup as $i => $f) {
                ATestDb::exec(
                    'INSERT INTO promotion_filter_items (id, label, sort_order) VALUES (?,?,?)',
                    [$f['id'], $f['label'], (int) ($f['sort_order'] ?? $i)]
                );
            }
        }
        if ($visualBackup !== null) {
            ATestDb::exec(
                "UPDATE cms_blocks SET content_json = ? WHERE block_key = 'visual_overrides'",
                [is_string($visualBackup) ? $visualBackup : json_encode($visualBackup, JSON_UNESCAPED_UNICODE)]
            );
        }
        if ($uploadedFile && is_file($uploadedFile)) {
            @unlink($uploadedFile);
        }
    } catch (Throwable $e) {
        fwrite(STDERR, "Cleanup warning: " . $e->getMessage() . "\n");
    }
});

// ---------------------------------------------------------------------------
// 1. Auth
// ---------------------------------------------------------------------------
ATest::section('1. Authentication');

ATestHttp::resetCookies();
$bad = ATestHttp::postForm('login.php', ['username' => 'admin', 'password' => 'wrong-password'], ['follow' => true]);
ATest::assertContains('ไม่ถูกต้อง', $bad['body'], 'reject bad password');

try {
    atest_require_login();
    ATest::ok('login admin/admin123');
} catch (Throwable $e) {
    ATest::fail('login admin/admin123', $e->getMessage());
    echo "\nAborting — cannot continue without login.\n";
    exit(1);
}

$dash = ATestHttp::get('index.php');
ATest::assertTrue($dash['status'] === 200 && str_contains($dash['body'], 'ทางลัด'), 'dashboard loads when logged in');

$pages = [
    'pages.php', 'faq.php', 'plans.php', 'categories.php', 'blogs.php', 'promotions.php',
    'blocks.php', 'hospitals.php', 'promo-filters.php', 'contacts.php', 'users.php', 'settings.php',
];
foreach ($pages as $p) {
    $r = ATestHttp::get($p);
    ATest::assertTrue(
        $r['status'] === 200 && !str_contains($r['body'], 'เข้าสู่ระบบ — Admin'),
        "page loads: {$p}"
    );
}

// ---------------------------------------------------------------------------
// 2. Settings (update persists)
// ---------------------------------------------------------------------------
ATest::section('2. Site settings');

$taglineRow = ATestDb::one("SELECT setting_value FROM site_settings WHERE setting_key = 'site_tagline'");
if (!$taglineRow) {
    ATest::skip('settings update', 'site_tagline missing — run seed/install');
} else {
    $settingsBackup = ['site_tagline' => $taglineRow['setting_value']];
    $newTagline = "{$M} tagline";
    // Preserve other keys by only posting site_tagline (settings update is per posted key)
    $r = ATestHttp::postForm('settings.php', [
        'settings' => ['site_tagline' => $newTagline],
    ]);
    $dbVal = ATestDb::value("SELECT setting_value FROM site_settings WHERE setting_key = 'site_tagline'");
    ATest::assertEquals($newTagline, $dbVal, 'settings.php updates site_tagline in DB');
    ATest::assertContains('บันทึก', $r['body'], 'settings.php shows success message');
}

// ---------------------------------------------------------------------------
// 3. FAQ CRUD
// ---------------------------------------------------------------------------
ATest::section('3. FAQ CRUD');

$r = ATestHttp::postForm('faq-edit.php', [
    'question' => $faqQuestion,
    'answer' => "{$M} answer body",
    'sort_order' => '99',
    'is_active' => '1',
], ['follow' => true]);
$row = ATestDb::one('SELECT * FROM faq_items WHERE question = ?', [$faqQuestion]);
ATest::assertTrue($row !== null, 'FAQ create inserts row');
if ($row) {
    $faqId = (int) $row['id'];
    ATest::assertEquals("{$M} answer body", $row['answer'], 'FAQ create stores answer');

    $updatedQ = $faqQuestion . ' UPDATED';
    $r = ATestHttp::postForm('faq-edit.php?id=' . $faqId, [
        'question' => $updatedQ,
        'answer' => "{$M} answer UPDATED",
        'sort_order' => '98',
        'is_active' => '1',
    ], ['follow' => true]);
    $row2 = ATestDb::one('SELECT * FROM faq_items WHERE id = ?', [$faqId]);
    ATest::assertEquals($updatedQ, $row2['question'] ?? null, 'FAQ update changes question in DB');
    ATest::assertEquals("{$M} answer UPDATED", $row2['answer'] ?? null, 'FAQ update changes answer in DB');

    $r = ATestHttp::postForm('faq.php', ['delete_id' => $faqId], ['follow' => true]);
    $gone = ATestDb::one('SELECT id FROM faq_items WHERE id = ?', [$faqId]);
    ATest::assertTrue($gone === null, 'FAQ delete removes row');
    $faqId = null;

    // Recreate for publish / API tests
    ATestHttp::postForm('faq-edit.php', [
        'question' => $faqQuestion,
        'answer' => "{$M} answer for publish",
        'sort_order' => '99',
        'is_active' => '1',
    ], ['follow' => true]);
    $row = ATestDb::one('SELECT * FROM faq_items WHERE question = ?', [$faqQuestion]);
    $faqId = $row ? (int) $row['id'] : null;
}

// ---------------------------------------------------------------------------
// 4. Page sections
// ---------------------------------------------------------------------------
ATest::section('4. Page sections (home)');

$homeKeys = [
    'hero_eyebrow', 'hero_title', 'hero_cta', 'hero_cta_link', 'hero_bg_image',
    'intro_title', 'intro_text', 'rec_section_label', 'rec_section_title', 'consult_title',
];
$current = [];
foreach ($homeKeys as $k) {
    $v = ATestDb::value(
        'SELECT content_value FROM page_sections WHERE page_slug = ? AND section_key = ?',
        ['home', $k]
    );
    $current[$k] = $v ?? '';
}
$pageSectionBackup = $current;
$markerTitle = "{$M} Home Hero";
$form = ['sections' => $current];
$form['sections']['hero_title'] = $markerTitle;
// Keep image fields via sections_keep if empty upload
foreach (['hero_bg_image'] as $imgKey) {
    $form['sections_keep'][$imgKey] = $current[$imgKey];
}
$r = ATestHttp::postForm('page-edit.php?page=home', $form);
$dbTitle = ATestDb::value(
    "SELECT content_value FROM page_sections WHERE page_slug='home' AND section_key='hero_title'"
);
ATest::assertEquals($markerTitle, $dbTitle, 'page-edit saves home hero_title to DB');
ATest::assertContains('บันทึก', $r['body'], 'page-edit shows success');

// ---------------------------------------------------------------------------
// 5. Categories + Plans
// ---------------------------------------------------------------------------
ATest::section('5. Categories & Plans');

$r = ATestHttp::postForm('category-edit.php?new=1', [
    'id' => $catId,
    'label' => "{$M} Category",
    'headline' => "{$M} headline",
    'intro_title' => 'Intro',
    'intro_text' => 'Intro text',
    'promo_section' => 'Promo',
    'why_section' => 'Why',
    'listing_goals' => 'Goals',
    'icon' => 'shield-check',
    'sort_order' => '90',
    'feature_title' => ["{$M} feature"],
    'feature_desc' => ['Feature desc'],
], ['follow' => true]);
$cat = ATestDb::one('SELECT * FROM plan_categories WHERE id = ?', [$catId]);
ATest::assertTrue($cat !== null, 'category create inserts row');
ATest::assertEquals("{$M} Category", $cat['label'] ?? null, 'category label stored');
$feat = ATestDb::one('SELECT * FROM plan_category_features WHERE category_id = ?', [$catId]);
ATest::assertTrue($feat !== null, 'category feature inserted');

$r = ATestHttp::postForm('category-edit.php?id=' . urlencode($catId), [
    'id' => $catId,
    'label' => "{$M} Category UPDATED",
    'headline' => "{$M} headline UPDATED",
    'intro_title' => 'Intro',
    'intro_text' => 'Intro text',
    'promo_section' => 'Promo',
    'why_section' => 'Why',
    'listing_goals' => 'Goals',
    'icon' => 'shield-check',
    'sort_order' => '91',
    'feature_title' => ["{$M} feature 2"],
    'feature_desc' => ['Feature desc 2'],
], ['follow' => true]);
$cat2 = ATestDb::one('SELECT * FROM plan_categories WHERE id = ?', [$catId]);
ATest::assertEquals("{$M} Category UPDATED", $cat2['label'] ?? null, 'category update persists label');

$r = ATestHttp::postForm('plan-edit.php', [
    'id' => $planId,
    'name' => "{$M} Plan Name",
    'category_id' => $catId,
    'tagline' => 'Tagline',
    'headline' => 'Headline',
    'price_from' => '999',
    'price_note' => 'บาท/เดือน',
    'hero_image' => '',
    'is_active' => '1',
    'sort_order' => '90',
    'promo_text' => '',
    'promo_code' => '',
    'promo_until' => '',
    'benefits' => "Benefit A\nBenefit B",
    'highlights' => "Highlight 1",
    'conditions' => "Condition 1",
    'renewal' => "Renewal 1",
    'why' => "Why 1",
    'tier_id' => ['t1'],
    'tier_label' => ['Basic'],
    'tier_amount' => ['500'],
    'tier_unit' => ['บาท'],
    'tier_popular' => [],
    'cs_label' => ['Coverage'],
    'cs_value' => ['100000'],
    'cs_unit' => ['บาท'],
    'faq_question' => ["Plan FAQ?"],
    'faq_answer' => ["Plan FAQ answer"],
], ['follow' => true]);
$plan = ATestDb::one('SELECT * FROM plan_products WHERE id = ?', [$planId]);
ATest::assertTrue($plan !== null, 'plan create inserts product');
ATest::assertEquals("{$M} Plan Name", $plan['name'] ?? null, 'plan name stored');
ATest::assertEquals(999.0, (float) ($plan['price_from'] ?? 0), 'plan price_from stored');
$benCount = (int) ATestDb::value('SELECT COUNT(*) FROM plan_benefits WHERE plan_id = ?', [$planId]);
ATest::assertEquals(2, $benCount, 'plan benefits inserted');
$tierCount = (int) ATestDb::value('SELECT COUNT(*) FROM plan_tiers WHERE plan_id = ?', [$planId]);
ATest::assertEquals(1, $tierCount, 'plan tier inserted');

$r = ATestHttp::postForm('plan-edit.php?id=' . urlencode($planId), [
    'id' => $planId,
    'name' => "{$M} Plan UPDATED",
    'category_id' => $catId,
    'tagline' => 'Tagline2',
    'headline' => 'Headline2',
    'price_from' => '1500',
    'price_note' => 'บาท/เดือน',
    'hero_image' => '',
    'is_active' => '1',
    'sort_order' => '90',
    'promo_text' => '',
    'promo_code' => '',
    'promo_until' => '',
    'benefits' => "Only one benefit",
    'highlights' => '',
    'conditions' => '',
    'renewal' => '',
    'why' => '',
], ['follow' => true]);
$plan2 = ATestDb::one('SELECT * FROM plan_products WHERE id = ?', [$planId]);
ATest::assertEquals("{$M} Plan UPDATED", $plan2['name'] ?? null, 'plan update persists name');
ATest::assertEquals(1500.0, (float) ($plan2['price_from'] ?? 0), 'plan update persists price');
$benCount2 = (int) ATestDb::value('SELECT COUNT(*) FROM plan_benefits WHERE plan_id = ?', [$planId]);
ATest::assertEquals(1, $benCount2, 'plan benefits replaced on update');

// ---------------------------------------------------------------------------
// 6. Blogs
// ---------------------------------------------------------------------------
ATest::section('6. Blogs');

$r = ATestHttp::postForm('blog-edit.php', [
    'id' => $blogId,
    'title' => "{$M} Blog Title",
    'excerpt' => 'Excerpt',
    'pub_date' => date('Y-m-d'),
    'category' => 'health',
    'is_active' => '1',
    'tags' => 'atest, insurance',
    'block_type' => ['paragraph'],
    'block_content' => ["{$M} blog paragraph"],
], ['follow' => true]);
$blog = ATestDb::one('SELECT * FROM blog_articles WHERE id = ?', [$blogId]);
ATest::assertTrue($blog !== null, 'blog create inserts article');
ATest::assertEquals("{$M} Blog Title", $blog['title'] ?? null, 'blog title stored');
$tagCount = (int) ATestDb::value('SELECT COUNT(*) FROM blog_tags WHERE article_id = ?', [$blogId]);
ATest::assertTrue($tagCount >= 1, 'blog tags inserted');
$content = ATestDb::one('SELECT * FROM blog_content WHERE article_id = ?', [$blogId]);
ATest::assertContains($M, (string) ($content['content'] ?? ''), 'blog content block stored');

$r = ATestHttp::postForm('blog-edit.php?id=' . urlencode($blogId), [
    'id' => $blogId,
    'title' => "{$M} Blog UPDATED",
    'excerpt' => 'Excerpt2',
    'pub_date' => date('Y-m-d'),
    'category' => 'health',
    'is_active' => '1',
    'tags' => 'atest',
    'block_type' => ['paragraph'],
    'block_content' => ["{$M} blog UPDATED"],
], ['follow' => true]);
$blog2 = ATestDb::one('SELECT * FROM blog_articles WHERE id = ?', [$blogId]);
ATest::assertEquals("{$M} Blog UPDATED", $blog2['title'] ?? null, 'blog update persists title');

// ---------------------------------------------------------------------------
// 7. Promotions + filters
// ---------------------------------------------------------------------------
ATest::section('7. Promotions & filters');

$r = ATestHttp::postForm('promo-edit.php', [
    'id' => $promoId,
    'title' => "{$M} Promo",
    'highlight' => 'Save big',
    'description_html' => '<p>Desc</p>',
    'category' => 'สุขภาพ',
    'category_key' => 'health',
    'badge' => 'HOT',
    'badge_type' => 'hot',
    'image' => '',
    'cta' => 'สมัคร',
    'cta_href' => 'contact.html',
    'promo_code' => 'ATEST',
    'valid_until' => '31 ธ.ค. 2026',
    'installment' => '',
    'sort_order' => '90',
    'is_active' => '1',
    'bullets' => "Bullet 1\nBullet 2",
], ['follow' => true]);
$promo = ATestDb::one('SELECT * FROM promotions WHERE id = ?', [$promoId]);
ATest::assertTrue($promo !== null, 'promo create inserts row');
ATest::assertEquals("{$M} Promo", $promo['title'] ?? null, 'promo title stored');
$bullets = (int) ATestDb::value('SELECT COUNT(*) FROM promotion_bullets WHERE promotion_id = ?', [$promoId]);
ATest::assertEquals(2, $bullets, 'promo bullets inserted');

$r = ATestHttp::postForm('promo-edit.php?id=' . urlencode($promoId), [
    'id' => $promoId,
    'title' => "{$M} Promo UPDATED",
    'highlight' => 'Save bigger',
    'description_html' => '<p>Desc2</p>',
    'category' => 'สุขภาพ',
    'category_key' => 'health',
    'badge' => 'HOT',
    'badge_type' => 'hot',
    'image' => '',
    'cta' => 'สมัคร',
    'cta_href' => 'contact.html',
    'promo_code' => 'ATEST2',
    'valid_until' => '31 ธ.ค. 2026',
    'installment' => '',
    'sort_order' => '90',
    'is_active' => '1',
    'bullets' => 'Only bullet',
], ['follow' => true]);
$promo2 = ATestDb::one('SELECT * FROM promotions WHERE id = ?', [$promoId]);
ATest::assertEquals("{$M} Promo UPDATED", $promo2['title'] ?? null, 'promo update persists title');
ATest::assertEquals('ATEST2', $promo2['promo_code'] ?? null, 'promo update persists code');

$filtersBackup = ATestDb::all('SELECT * FROM promotion_filter_items ORDER BY sort_order, id');
$filterForm = [
    'action' => 'save',
    'filter_id' => [],
    'filter_label' => [],
    'filter_order' => [],
];
foreach ($filtersBackup as $i => $f) {
    $filterForm['filter_id'][] = $f['id'];
    $filterForm['filter_label'][] = $f['label'];
    $filterForm['filter_order'][] = (string) $f['sort_order'];
}
$filterForm['filter_id'][] = 'atest-filter';
$filterForm['filter_label'][] = "{$M} Filter";
$filterForm['filter_order'][] = '99';
$r = ATestHttp::postForm('promo-filters.php', $filterForm, ['follow' => true]);
$ff = ATestDb::one("SELECT * FROM promotion_filter_items WHERE id = 'atest-filter'");
ATest::assertTrue($ff !== null, 'promo filter create persists');
ATest::assertEquals("{$M} Filter", $ff['label'] ?? null, 'promo filter label stored');

// ---------------------------------------------------------------------------
// 8. CMS blocks
// ---------------------------------------------------------------------------
ATest::section('8. CMS blocks');

$footerRow = ATestDb::one("SELECT content_json FROM cms_blocks WHERE block_key = 'footer'");
if (!$footerRow) {
    ATest::skip('footer block update', 'footer block missing');
} else {
    $footerBackup = $footerRow['content_json'];
    $footerData = json_decode($footerRow['content_json'] ?: '{}', true) ?: [];
    $newTag = "{$M} footer tagline";
    $r = ATestHttp::postForm('block-edit.php?key=footer', [
        'tagline' => $newTag,
        'copyright' => $footerData['copyright'] ?? '',
        'cta_text' => $footerData['cta_text'] ?? '',
        'cta_href' => $footerData['cta_href'] ?? '',
        'columns_json' => json_encode($footerData['columns'] ?? [], JSON_UNESCAPED_UNICODE),
        'legal_json' => json_encode($footerData['legal'] ?? [], JSON_UNESCAPED_UNICODE),
    ]);
    $after = ATestDb::one("SELECT content_json FROM cms_blocks WHERE block_key = 'footer'");
    $decoded = json_decode($after['content_json'] ?? '{}', true);
    ATest::assertEquals($newTag, $decoded['tagline'] ?? null, 'block-edit updates footer tagline in DB');
}

// ---------------------------------------------------------------------------
// 9. Contacts (public API + admin status/delete)
// ---------------------------------------------------------------------------
ATest::section('9. Contacts');

$r = ATestHttp::postJson(ATEST_BASE . '/admin/api/contact.php', [
    'name' => "{$M} Contact",
    'phone' => '0812345678',
    'email' => 'atest@example.com',
    'insurance_type' => 'สุขภาพ',
    'province' => 'กรุงเทพ',
    'age' => 30,
    'callback_time' => 'เช้า',
    'message' => "{$M} please call",
]);
ATest::assertTrue(($r['json']['ok'] ?? false) === true || $r['status'] === 200, 'public contact API accepts submission');
$contact = ATestDb::one('SELECT * FROM contact_submissions WHERE name = ? ORDER BY id DESC LIMIT 1', ["{$M} Contact"]);
ATest::assertTrue($contact !== null, 'contact API inserts DB row');
if ($contact) {
    $contactId = (int) $contact['id'];
    ATest::assertEquals('new', $contact['status'], 'new contact has status=new');

    $r = ATestHttp::postForm('contacts.php', [
        'update_status' => 'contacted',
        'contact_id' => $contactId,
    ], ['follow' => true]);
    $st = ATestDb::value('SELECT status FROM contact_submissions WHERE id = ?', [$contactId]);
    ATest::assertEquals('contacted', $st, 'contacts.php updates status to contacted');

    $r = ATestHttp::postForm('contacts.php', [
        'update_status' => 'closed',
        'contact_id' => $contactId,
    ], ['follow' => true]);
    $st = ATestDb::value('SELECT status FROM contact_submissions WHERE id = ?', [$contactId]);
    ATest::assertEquals('closed', $st, 'contacts.php updates status to closed');

    $r = ATestHttp::postForm('contacts.php', [
        'delete_id' => $contactId,
    ], ['follow' => true]);
    $gone = ATestDb::one('SELECT id FROM contact_submissions WHERE id = ?', [$contactId]);
    ATest::assertTrue($gone === null, 'contacts.php deletes submission');
    $contactId = null;
}

// ---------------------------------------------------------------------------
// 10. Users
// ---------------------------------------------------------------------------
ATest::section('10. Admin users');

$r = ATestHttp::postForm('users.php', [
    'action' => 'add',
    'username' => $testUser,
    'password' => 'testpass123',
    'display_name' => "{$M} User",
], ['follow' => true]);
$user = ATestDb::one('SELECT * FROM admin_users WHERE username = ?', [$testUser]);
ATest::assertTrue($user !== null, 'users.php creates admin user');
$userId = $user ? (int) $user['id'] : null;

if ($userId) {
    $r = ATestHttp::postForm('users.php', [
        'action' => 'password',
        'user_id' => $userId,
        'password' => 'newpass123',
    ], ['follow' => true]);
    $hash = ATestDb::value('SELECT password_hash FROM admin_users WHERE id = ?', [$userId]);
    ATest::assertTrue(is_string($hash) && password_verify('newpass123', $hash), 'users.php password change works');

    // Login as test user
    $loginTest = atest_login($testUser, 'newpass123');
    $dash2 = ATestHttp::get('index.php', ['follow' => false]);
    ATest::assertTrue(
        $dash2['status'] === 200 || ($dash2['status'] === 302 && !str_contains($dash2['headers'], 'login.php')),
        'new user can log in'
    );

    // Back to admin for cleanup
    atest_require_login();
    $r = ATestHttp::postForm('users.php', [
        'action' => 'delete',
        'user_id' => $userId,
    ], ['follow' => true]);
    $gone = ATestDb::one('SELECT id FROM admin_users WHERE id = ?', [$userId]);
    ATest::assertTrue($gone === null, 'users.php deletes test user');
    $userId = null;
    $testUser = null;
}

// ---------------------------------------------------------------------------
// 11. APIs: cms-block-save, cms-bundle, upload
// ---------------------------------------------------------------------------
ATest::section('11. Admin APIs');

atest_require_login();

$visRow = ATestDb::one("SELECT content_json FROM cms_blocks WHERE block_key = 'visual_overrides'");
$visualBackup = $visRow['content_json'] ?? '{}';
$r = ATestHttp::postJson('api/cms-block-save.php', [
    'kind' => 'dom',
    'page' => 'home',
    'selector' => '.atest-selector',
    'text' => "{$M} override",
]);
ATest::assertTrue(($r['json']['ok'] ?? false) === true, 'cms-block-save DOM override ok');
$vis = ATestDb::one("SELECT content_json FROM cms_blocks WHERE block_key = 'visual_overrides'");
ATest::assertContains($M, (string) ($vis['content_json'] ?? ''), 'visual_overrides stored in cms_blocks');

if ($faqId) {
    // Find index among active FAQs
    $active = ATestDb::all('SELECT id FROM faq_items WHERE is_active = 1 ORDER BY sort_order, id');
    $idx = null;
    foreach ($active as $i => $a) {
        if ((int) $a['id'] === (int) $faqId) {
            $idx = $i;
            break;
        }
    }
    if ($idx !== null) {
        $r = ATestHttp::postJson('api/cms-block-save.php', [
            'kind' => 'faq',
            'itemIndex' => $idx,
            'item' => ['q' => $faqQuestion . ' VIA API', 'a' => "{$M} api answer"],
        ]);
        ATest::assertTrue(($r['json']['ok'] ?? false) === true, 'cms-block-save FAQ ok');
        $fq = ATestDb::one('SELECT * FROM faq_items WHERE id = ?', [$faqId]);
        ATest::assertContains('VIA API', (string) ($fq['question'] ?? ''), 'cms-block-save updates FAQ in DB');
    } else {
        ATest::skip('cms-block-save FAQ', 'test FAQ not in active list');
    }
}

$r = ATestHttp::get('api/cms-bundle.php');
ATest::assertTrue(($r['json']['ok'] ?? false) === true, 'cms-bundle GET returns ok');
ATest::assertTrue(isset($r['json']['bundle']['pages']), 'cms-bundle includes pages');
ATest::assertTrue(isset($r['json']['bundle']['settings']), 'cms-bundle includes settings');
ATest::assertTrue(isset($r['json']['bundle']['faqs']), 'cms-bundle includes faqs');

$tmpPng = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'atest_' . md5($M) . '.png';
file_put_contents($tmpPng, atest_png_bytes());
$r = ATestHttp::request('POST', ATestHttp::url('api/upload.php'), [
    'multipart' => [
        'folder' => 'uploads',
        'image' => new CURLFile($tmpPng, 'image/png', 'atest.png'),
    ],
]);
@unlink($tmpPng);
ATest::assertTrue(($r['json']['ok'] ?? false) === true, 'upload API accepts PNG');
if (!empty($r['json']['path'])) {
    $rel = ltrim(str_replace(['\\', ATEST_SITE_ROOT], ['/', ''], $r['json']['path']), '/');
    // path may be like assets/img/uploads/...
    $path = $r['json']['path'];
    if (!str_starts_with($path, '/') && !preg_match('#^[A-Za-z]:#', $path)) {
        $full = ATEST_SITE_ROOT . '/' . ltrim($path, '/');
    } else {
        $full = $path;
    }
    // Prefer SITE_ROOT relative
    $candidates = [
        ATEST_SITE_ROOT . '/' . ltrim((string) $path, '/'),
        ATEST_SITE_ROOT . '/assets/img/' . basename((string) $path),
    ];
    $found = false;
    foreach ($candidates as $c) {
        if (is_file($c)) {
            $uploadedFile = $c;
            $found = true;
            break;
        }
    }
    // Also try path as returned if under assets
    if (!$found && is_string($path)) {
        $guess = ATEST_SITE_ROOT . '/' . preg_replace('#^\.?/?#', '', str_replace('\\', '/', $path));
        if (is_file($guess)) {
            $uploadedFile = $guess;
            $found = true;
        }
    }
    ATest::assertTrue($found || !empty($r['json']['url']), 'upload API returns usable path/url');
}

// ---------------------------------------------------------------------------
// 12. Publish — static JS updated
// ---------------------------------------------------------------------------
ATest::section('12. Publish');

$r = ATestHttp::get('api/publish.php', ['follow' => true]);
ATest::assertTrue($r['status'] >= 200 && $r['status'] < 400, 'publish.php responds OK');

$siteContent = @file_get_contents(ATEST_SITE_ROOT . '/assets/js/site-content.js') ?: '';
$planData = @file_get_contents(ATEST_SITE_ROOT . '/assets/js/plan-data.js') ?: '';
$blogData = @file_get_contents(ATEST_SITE_ROOT . '/assets/js/blog-data.js') ?: '';
$promoData = @file_get_contents(ATEST_SITE_ROOT . '/assets/js/promotion-data.js') ?: '';

ATest::assertTrue($siteContent !== '', 'publish wrote site-content.js');
ATest::assertContains($markerTitle, $siteContent, 'published site-content contains updated home hero_title');
if ($faqId) {
    ATest::assertContains($M, $siteContent, 'published site-content contains FAQ marker');
}
ATest::assertContains($planId, $planData, 'published plan-data.js contains test plan id');
ATest::assertContains($blogId, $blogData, 'published blog-data.js contains test blog id');
ATest::assertContains($promoId, $promoData, 'published promotion-data.js contains test promo id');
if (is_array($decoded = json_decode(
    (ATestDb::one("SELECT content_json FROM cms_blocks WHERE block_key='footer'")['content_json'] ?? '{}'),
    true
))) {
    // footer may still have marker until cleanup
    if (str_contains($siteContent, $M)) {
        ATest::ok('published site-content includes CMS marker text');
    } else {
        ATest::fail('published site-content includes CMS marker text', 'marker not found');
    }
}

// ---------------------------------------------------------------------------
// 13. Deletes (plans/blogs/promos/categories)
// ---------------------------------------------------------------------------
ATest::section('13. Delete flows');

$r = ATestHttp::postForm('plans.php', ['delete_id' => $planId], ['follow' => true]);
ATest::assertTrue(ATestDb::one('SELECT id FROM plan_products WHERE id = ?', [$planId]) === null, 'plans.php deletes plan');
$planId = null;

$r = ATestHttp::postForm('blogs.php', ['delete_id' => $blogId], ['follow' => true]);
ATest::assertTrue(ATestDb::one('SELECT id FROM blog_articles WHERE id = ?', [$blogId]) === null, 'blogs.php deletes article');
$blogId = null;

$r = ATestHttp::postForm('promotions.php', ['delete_id' => $promoId], ['follow' => true]);
ATest::assertTrue(ATestDb::one('SELECT id FROM promotions WHERE id = ?', [$promoId]) === null, 'promotions.php deletes promo');
$promoId = null;

$r = ATestHttp::postForm('categories.php', [
    'action' => 'delete',
    'id' => $catId,
], ['follow' => true]);
ATest::assertTrue(ATestDb::one('SELECT id FROM plan_categories WHERE id = ?', [$catId]) === null, 'categories.php deletes unused category');
$catId = null;

// Guard: cannot delete last admin — just verify admin still exists
$adminCount = (int) ATestDb::value('SELECT COUNT(*) FROM admin_users');
ATest::assertTrue($adminCount >= 1, 'at least one admin user remains');

// ---------------------------------------------------------------------------
// 14. Logout
// ---------------------------------------------------------------------------
ATest::section('14. Logout');

$r = ATestHttp::get('logout.php', ['follow' => true]);
$dash = ATestHttp::get('index.php', ['follow' => false]);
$blocked = $dash['status'] === 302
    || str_contains($dash['headers'], 'login.php')
    || str_contains($dash['body'], 'เข้าสู่ระบบ');
ATest::assertTrue($blocked, 'logout blocks dashboard access');

// ---------------------------------------------------------------------------
// 15. Guards / edge cases
// ---------------------------------------------------------------------------
ATest::section('15. Guards & edge cases');

// API without login
ATestHttp::resetCookies();
$unauth = ATestHttp::get('api/cms-bundle.php', ['follow' => false]);
ATest::assertTrue(
    $unauth['status'] === 401 || $unauth['status'] === 302 || ($unauth['json']['ok'] ?? true) === false,
    'cms-bundle rejects unauthenticated GET'
);

$unauthUp = ATestHttp::request('POST', ATestHttp::url('api/upload.php'), [
    'follow' => false,
    'multipart' => ['folder' => 'uploads'],
]);
ATest::assertTrue(
    $unauthUp['status'] === 302 || $unauthUp['status'] === 401 || $unauthUp['status'] === 403
    || str_contains($unauthUp['headers'], 'login.php'),
    'upload API rejects unauthenticated POST'
);

atest_require_login();

// Category delete blocked when plan references it
$guardCat = 'atest-guard-' . substr(md5($M), 0, 6);
$guardPlan = 'atest-gplan-' . substr(md5($M), 0, 6);
ATestHttp::postForm('category-edit.php?new=1', [
    'id' => $guardCat,
    'label' => "{$M} Guard Cat",
    'headline' => '',
    'intro_title' => '',
    'intro_text' => '',
    'promo_section' => '',
    'why_section' => '',
    'listing_goals' => '',
    'icon' => '',
    'sort_order' => '99',
], ['follow' => true]);
ATestHttp::postForm('plan-edit.php', [
    'id' => $guardPlan,
    'name' => "{$M} Guard Plan",
    'category_id' => $guardCat,
    'tagline' => '',
    'headline' => '',
    'price_from' => '1',
    'price_note' => '',
    'hero_image' => '',
    'is_active' => '1',
    'sort_order' => '99',
], ['follow' => true]);
ATestHttp::postForm('categories.php', ['action' => 'delete', 'id' => $guardCat], ['follow' => true]);
$stillThere = ATestDb::one('SELECT id FROM plan_categories WHERE id = ?', [$guardCat]);
ATest::assertTrue($stillThere !== null, 'category delete blocked when plans still use it');
ATestDb::exec('DELETE FROM plan_products WHERE id = ?', [$guardPlan]);
ATestDb::exec('DELETE FROM plan_categories WHERE id = ?', [$guardCat]);
// clean nav remnants
$navRow = ATestDb::one("SELECT content_json FROM cms_blocks WHERE block_key = 'plan_nav_menu'");
if ($navRow && $navRow['content_json']) {
    $nav = json_decode($navRow['content_json'], true);
    if (is_array($nav)) {
        foreach ($nav as &$col) {
            if (!isset($col['groups']) || !is_array($col['groups'])) continue;
            $col['groups'] = array_values(array_filter(
                $col['groups'],
                static fn($g) => ($g['category'] ?? '') !== $guardCat
            ));
        }
        unset($col);
        ATestDb::exec(
            "UPDATE cms_blocks SET content_json = ? WHERE block_key = 'plan_nav_menu'",
            [json_encode($nav, JSON_UNESCAPED_UNICODE)]
        );
    }
}

// cms-bundle POST settings round-trip
$bundlePost = ATestHttp::postJson('api/cms-bundle.php', [
    'settings' => ['site_tagline' => "{$M} via bundle"],
    'publish' => false,
]);
ATest::assertTrue(($bundlePost['json']['ok'] ?? false) === true, 'cms-bundle POST ok');
$viaBundle = ATestDb::value("SELECT setting_value FROM site_settings WHERE setting_key='site_tagline'");
ATest::assertEquals("{$M} via bundle", $viaBundle, 'cms-bundle POST updates settings in DB');
if (is_array($settingsBackup) && isset($settingsBackup['site_tagline'])) {
    $settingsBackup['site_tagline'] = $settingsBackup['site_tagline']; // still restore original
}

// Hospitals page loads and JSON exists
$hosp = ATestHttp::get('hospitals.php');
ATest::assertTrue($hosp['status'] === 200, 'hospitals.php loads');
$hospJson = ATEST_SITE_ROOT . '/assets/data/hospital-locator.json';
ATest::assertTrue(is_file($hospJson), 'hospital-locator.json exists');

// ---------------------------------------------------------------------------
// Summary
// ---------------------------------------------------------------------------
$elapsed = round(microtime(true) - $started, 2);
echo "\n----------------------------------------\n";
echo 'Passed:  ' . ATest::$passed . "\n";
echo 'Failed:  ' . ATest::$failed . "\n";
echo 'Skipped: ' . ATest::$skipped . "\n";
echo "Time:    {$elapsed}s\n";

if (ATest::$failures) {
    echo "\nFailures:\n";
    foreach (ATest::$failures as $f) {
        echo "  - {$f}\n";
    }
}

// Re-publish after DB restore (shutdown runs after exit — so publish here after manual restore)
try {
    if (is_array($settingsBackup)) {
        foreach ($settingsBackup as $key => $val) {
            ATestDb::exec('UPDATE site_settings SET setting_value = ? WHERE setting_key = ?', [$val, $key]);
        }
        $settingsBackup = null;
    }
    if (is_array($pageSectionBackup)) {
        foreach ($pageSectionBackup as $key => $val) {
            ATestDb::exec(
                'UPDATE page_sections SET content_value = ? WHERE page_slug = ? AND section_key = ?',
                [$val, 'home', $key]
            );
        }
        $pageSectionBackup = null;
    }
    if ($footerBackup !== null) {
        ATestDb::exec(
            "UPDATE cms_blocks SET content_json = ? WHERE block_key = 'footer'",
            [is_string($footerBackup) ? $footerBackup : json_encode($footerBackup, JSON_UNESCAPED_UNICODE)]
        );
        $footerBackup = null;
    }
    if ($visualBackup !== null) {
        ATestDb::exec(
            "UPDATE cms_blocks SET content_json = ? WHERE block_key = 'visual_overrides'",
            [is_string($visualBackup) ? $visualBackup : json_encode($visualBackup, JSON_UNESCAPED_UNICODE)]
        );
        $visualBackup = null;
    }
    if ($filtersBackup) {
        ATestDb::exec('DELETE FROM promotion_filter_items');
        foreach ($filtersBackup as $i => $f) {
            ATestDb::exec(
                'INSERT INTO promotion_filter_items (id, label, sort_order) VALUES (?,?,?)',
                [$f['id'], $f['label'], (int) ($f['sort_order'] ?? $i)]
            );
        }
        $filtersBackup = [];
    }
    if ($faqId) {
        ATestDb::exec('DELETE FROM faq_items WHERE id = ?', [$faqId]);
        $faqId = null;
    }
    ATestDb::exec("DELETE FROM faq_items WHERE question LIKE 'ATEST_%'");
    // CLI publish to refresh JS without session
    $php = 'C:\\xampp\\php\\php.exe';
    if (is_file($php)) {
        $cmd = escapeshellarg($php) . ' ' . escapeshellarg(ATEST_SITE_ROOT . '/admin/cli-publish.php');
        @exec($cmd);
        echo "Re-published site JS after restoring DB fixtures.\n";
    }
} catch (Throwable $e) {
    echo "Restore/republish warning: " . $e->getMessage() . "\n";
}

exit(ATest::$failed > 0 ? 1 : 0);
