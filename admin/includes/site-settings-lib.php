<?php
declare(strict_types=1);

function defaultSiteSettingsRows(): array {
    return [
        ['site_name', 'Agent Thailand', 'general'],
        ['site_tagline', 'ตัวแทนประกันชีวิต FWD', 'general'],
        ['phone', '065-651-5328', 'contact'],
        ['email', 'agentthailand.fwd@gmail.com', 'contact'],
        ['line_id', '@agentthailand', 'contact'],
        ['facebook', 'AgentThailandFWD', 'social'],
        ['tiktok', '@agentthailand', 'social'],
        ['address', '130 ถนนสุขุมวิท แขวงบางจาก เขตพระโขนง กรุงเทพมหานคร 10260', 'contact'],
        ['primary_color', '#150f96', 'theme'],
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
}

function ensureSiteSettingsTable(): void {
    query("CREATE TABLE IF NOT EXISTS site_settings (
        setting_key VARCHAR(100) PRIMARY KEY,
        setting_value TEXT,
        setting_group VARCHAR(50) DEFAULT 'general',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
}

function countVisibleSiteSettings(): int {
    $row = fetchOne("SELECT COUNT(*) AS c FROM site_settings WHERE setting_group != 'feedback'");
    return (int) ($row['c'] ?? 0);
}

function seedDefaultSiteSettings(): int {
    ensureSiteSettingsTable();
    $added = 0;
    foreach (defaultSiteSettingsRows() as $row) {
        $exists = fetchOne('SELECT setting_key FROM site_settings WHERE setting_key = ?', [$row[0]]);
        if ($exists) {
            continue;
        }
        query(
            'INSERT INTO site_settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?)',
            [$row[0], $row[1], $row[2]]
        );
        $added++;
    }
    return $added;
}
