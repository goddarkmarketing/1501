<?php
/**
 * Quick probe — delete after install works.
 * https://prakandd-thailand.com/admin/ping.php
 */
header('Content-Type: text/plain; charset=utf-8');
echo "OK\n";
echo 'PHP ' . PHP_VERSION . "\n";
echo 'PDO: ' . (class_exists('PDO') ? 'yes' : 'no') . "\n";
echo 'pdo_mysql: ' . (in_array('mysql', PDO::getAvailableDrivers(), true) ? 'yes' : 'no') . "\n";
echo 'admin writable: ' . (is_writable(__DIR__) ? 'yes' : 'no') . "\n";
echo 'setup.sql: ' . (is_file(__DIR__ . '/setup.sql') ? 'yes' : 'no') . "\n";
echo 'config.php: ' . (is_file(__DIR__ . '/config.php') ? 'yes' : 'no') . "\n";
