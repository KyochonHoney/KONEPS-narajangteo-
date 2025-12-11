<?php

require 'public_html/vendor/autoload.php';
$app = require_once 'public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tender = App\Models\Tender::first();
echo 'Source URL: ' . $tender->source_url . PHP_EOL;
echo 'Contains mock: ' . (strpos($tender->source_url, 'mock') !== false ? 'YES' : 'NO') . PHP_EOL;
echo 'URL condition: ' . (($tender->source_url && $tender->source_url !== '#') ? 'PASS' : 'FAIL') . PHP_EOL;
echo 'URL is empty: ' . (empty($tender->source_url) ? 'YES' : 'NO') . PHP_EOL;