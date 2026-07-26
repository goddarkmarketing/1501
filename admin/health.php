<?php
header('Content-Type: text/plain; charset=utf-8');
echo "OK\n";
echo 'PHP ' . PHP_VERSION . "\n";
echo 'time ' . date('c') . "\n";
echo 'dir ' . __DIR__ . "\n";
echo 'writable ' . (is_writable(__DIR__) ? 'yes' : 'no') . "\n";
echo 'pdo ' . (class_exists('PDO') ? 'yes' : 'no') . "\n";
echo 'setup.sql ' . (is_file(__DIR__ . '/setup.sql') ? 'yes' : 'no') . "\n";
echo 'config.php ' . (is_file(__DIR__ . '/config.php') ? 'yes' : 'no') . "\n";
echo 'config.local.php ' . (is_file(__DIR__ . '/config.local.php') ? 'yes' : 'no') . "\n";
