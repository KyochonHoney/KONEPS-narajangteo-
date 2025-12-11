<?php

require_once __DIR__ . '/public_html/vendor/autoload.php';

$app = require_once __DIR__ . '/public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "=== Tenders 테이블 컬럼 목록 ===" . PHP_EOL;
$columns = Schema::getColumnListing('tenders');
foreach ($columns as $column) {
    echo "- " . $column . PHP_EOL;
}

echo PHP_EOL . "=== 상세제품분류번호 관련 컬럼 검색 ===" . PHP_EOL;
$detailColumns = array_filter($columns, function($col) {
    return strpos(strtolower($col), 'prdct') !== false || 
           strpos(strtolower($col), 'clsfc') !== false ||
           strpos(strtolower($col), 'dtil') !== false ||
           strpos(strtolower($col), 'detail') !== false ||
           strpos(strtolower($col), 'industry') !== false ||
           strpos(strtolower($col), 'classification') !== false;
});

foreach ($detailColumns as $col) {
    echo "✅ " . $col . PHP_EOL;
}

// 샘플 데이터에서 실제 컬럼 값 확인
echo PHP_EOL . "=== 샘플 데이터 확인 ===" . PHP_EOL;
$sample = App\Models\Tender::first();
if ($sample) {
    echo "샘플 공고: " . $sample->title . PHP_EOL;
    foreach ($columns as $column) {
        if (strpos(strtolower($column), 'prdct') !== false || 
            strpos(strtolower($column), 'clsfc') !== false ||
            strpos(strtolower($column), 'industry') !== false) {
            echo "- {$column}: " . $sample->$column . PHP_EOL;
        }
    }
}