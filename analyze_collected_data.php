<?php

require_once __DIR__ . '/public_html/vendor/autoload.php';

$app = require_once __DIR__ . '/public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tender;

echo "=== 수집된 데이터 상세 분석 ===" . PHP_EOL;

$tenders = Tender::all();
echo "총 수집된 공고 수: " . $tenders->count() . "개" . PHP_EOL . PHP_EOL;

// 첫 번째 공고 상세 분석
$first = $tenders->first();
if ($first) {
    echo "첫 번째 공고 상세 정보:" . PHP_EOL;
    echo "- 공고번호: " . $first->tender_no . PHP_EOL;
    echo "- 제목: " . $first->title . PHP_EOL;
    echo "- 분류코드: '" . $first->pub_prcrmnt_clsfc_no . "'" . PHP_EOL;
    echo "- 분류명: '" . $first->pub_prcrmnt_clsfc_nm . "'" . PHP_EOL;
    
    // 메타데이터 분석
    $metadata = json_decode($first->metadata, true);
    echo PHP_EOL . "원본 API 응답에서 분류 관련 필드:" . PHP_EOL;
    
    $relevantFields = [];
    foreach ($metadata as $key => $value) {
        if (stripos($key, 'clsfc') !== false || 
            stripos($key, 'class') !== false ||
            stripos($key, '1468') !== false ||
            stripos($key, 'indstryty') !== false ||
            stripos($key, 'industry') !== false) {
            $relevantFields[$key] = $value;
        }
    }
    
    if (!empty($relevantFields)) {
        foreach ($relevantFields as $key => $value) {
            echo "- {$key}: " . (is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : "'{$value}'") . PHP_EOL;
        }
    } else {
        echo "분류 관련 필드가 없습니다." . PHP_EOL;
    }
    
    // 전체 필드 목록도 확인
    echo PHP_EOL . "API 응답의 모든 필드:" . PHP_EOL;
    $fieldNames = array_keys($metadata);
    sort($fieldNames);
    foreach ($fieldNames as $fieldName) {
        echo "- " . $fieldName . PHP_EOL;
    }
}

echo PHP_EOL . "=== 분석 완료 ===" . PHP_EOL;