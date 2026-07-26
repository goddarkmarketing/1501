<?php
/**
 * Default config (XAMPP local).
 * On hosting, run /admin/install.php — it writes config.local.php which overrides these.
 */
$configLocal = __DIR__ . '/config.local.php';

$defaults = [
    'DB_HOST' => '127.0.0.1',
    'DB_NAME' => 'agent1501',
    'DB_USER' => 'root',
    'DB_PASS' => '',
    'SITE_URL' => '/1501',
    'ADMIN_URL' => '/1501/admin',
];

$local = [];
if (is_file($configLocal)) {
    $loaded = include $configLocal;
    if (is_array($loaded)) {
        $local = $loaded;
    }
}

$cfg = array_merge($defaults, $local);

define('DB_HOST', (string) $cfg['DB_HOST']);
define('DB_NAME', (string) $cfg['DB_NAME']);
define('DB_USER', (string) $cfg['DB_USER']);
define('DB_PASS', (string) $cfg['DB_PASS']);
define('SITE_ROOT', dirname(__DIR__));
define('ADMIN_ROOT', __DIR__);
define('SITE_URL', (string) $cfg['SITE_URL']);
define('ADMIN_URL', (string) $cfg['ADMIN_URL']);

date_default_timezone_set('Asia/Bangkok');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
