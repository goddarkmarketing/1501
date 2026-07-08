<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/seed-import.php';

$categories = require __DIR__ . '/seed-categories.php';

try {
    $stats = runFullSeedImport($categories);
    echo "Seed OK: plans={$stats['plans']} blogs={$stats['blogs']} promos={$stats['promos']}\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Seed failed: ' . $e->getMessage() . "\n");
    exit(1);
}
