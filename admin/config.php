<?php
/**
 * Base config. Hosting overrides: config.local.php (from install.php).
 */
$__cfg = array(
    'DB_HOST' => '127.0.0.1',
    'DB_NAME' => 'agent1501',
    'DB_USER' => 'root',
    'DB_PASS' => '',
    'SITE_URL' => '/1501',
    'ADMIN_URL' => '/1501/admin',
);

$__localFile = __DIR__ . '/config.local.php';
if (is_file($__localFile)) {
    $__loaded = include $__localFile;
    if (is_array($__loaded)) {
        foreach ($__loaded as $__k => $__v) {
            $__cfg[$__k] = $__v;
        }
    }
} else {
    // Auto-detect public path (works for domain root and /1501 subdirectory)
    $__script = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
    if ($__script !== '' && preg_match('#^(.*?)/admin(?:/|$)#', $__script, $__m)) {
        $__base = $__m[1];
        $__cfg['SITE_URL'] = $__base;
        $__cfg['ADMIN_URL'] = ($__base === '' ? '' : $__base) . '/admin';
    }
}

if (!defined('DB_HOST')) {
    define('DB_HOST', $__cfg['DB_HOST']);
}
if (!defined('DB_NAME')) {
    define('DB_NAME', $__cfg['DB_NAME']);
}
if (!defined('DB_USER')) {
    define('DB_USER', $__cfg['DB_USER']);
}
if (!defined('DB_PASS')) {
    define('DB_PASS', $__cfg['DB_PASS']);
}
if (!defined('SITE_ROOT')) {
    define('SITE_ROOT', dirname(__DIR__));
}
if (!defined('ADMIN_ROOT')) {
    define('ADMIN_ROOT', __DIR__);
}
if (!defined('SITE_URL')) {
    define('SITE_URL', $__cfg['SITE_URL']);
}
if (!defined('ADMIN_URL')) {
    define('ADMIN_URL', $__cfg['ADMIN_URL']);
}

date_default_timezone_set('Asia/Bangkok');

if (function_exists('session_status')) {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
} else {
    @session_start();
}
