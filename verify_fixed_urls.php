<?php

require 'public_html/vendor/autoload.php';
$app = require_once 'public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "수정된 URL 검증 시작...\n";

$tender = App\Models\Tender::first();
echo "첫 번째 공고 정보:\n";
echo "공고번호: {$tender->tender_no}\n";
echo "Source URL: {$tender->source_url}\n";
echo "Contains mock: " . (strpos($tender->source_url, 'mock') !== false ? 'YES' : 'NO') . "\n";
echo "URL condition: " . (($tender->source_url && $tender->source_url !== '#') ? 'PASS' : 'FAIL') . "\n";

$mockCount = App\Models\Tender::where('source_url', 'LIKE', '%mock%')->count();
$validCount = App\Models\Tender::where('source_url', 'LIKE', 'https://www.g2b.go.kr:8082/%')->count();

echo "\n통계:\n";
echo "Mock URL 남은 개수: {$mockCount}\n";
echo "유효한 G2B URL 개수: {$validCount}\n";
echo "\n검증 완료!\n";