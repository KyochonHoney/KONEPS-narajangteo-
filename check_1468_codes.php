<?php

require_once __DIR__ . '/public_html/vendor/autoload.php';

$app = require_once __DIR__ . '/public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tender;

echo "업종코드 1468 분석\n";
echo "================\n\n";

// 1468 업종코드가 포함된 공고 검색
$count1468 = Tender::where('pub_prcrmnt_clsfc_no', '1468')->count();
echo "현재 1468 업종코드 공고: {$count1468}개\n";

// 1468이 포함된 다른 형태도 확인
$like1468 = Tender::where('pub_prcrmnt_clsfc_no', 'LIKE', '%1468%')->count();
echo "1468이 포함된 공고: {$like1468}개\n\n";

// 현재 분류코드 모두 조회
$allCodes = Tender::whereNotNull('pub_prcrmnt_clsfc_no')
    ->where('pub_prcrmnt_clsfc_no', '!=', '')
    ->groupBy('pub_prcrmnt_clsfc_no')
    ->selectRaw('pub_prcrmnt_clsfc_no, count(*) as count')
    ->orderBy('count', 'desc')
    ->get();

echo "현재 수집된 모든 분류코드:\n";
foreach ($allCodes as $codeInfo) {
    echo "- {$codeInfo->pub_prcrmnt_clsfc_no}: {$codeInfo->count}개\n";
}

echo "\n1468 또는 146으로 시작하는 코드:\n";
foreach ($allCodes as $codeInfo) {
    $code = $codeInfo->pub_prcrmnt_clsfc_no;
    if (strpos($code, '1468') !== false || strpos($code, '146') === 0) {
        echo "- {$code}: {$codeInfo->count}개\n";
    }
}

echo "\n=== 분석 결과 ===\n";
echo "현재 데이터셋에는 업종코드 1468이 없습니다.\n";
echo "새로운 데이터 수집이 필요합니다.\n";