<?php
$js = file_get_contents(dirname(__DIR__, 2) . '/assets/js/site-content.js');
preg_match('/const SITE_FAQ_ITEMS = (\[.*?\]);/s', $js, $m);
$arr = json_decode($m[1] ?? '[]', true);
echo 'count=' . count($arr) . PHP_EOL;
foreach ($arr as $i => $x) {
    echo ($i + 1) . '. ' . $x['q'] . PHP_EOL;
}
