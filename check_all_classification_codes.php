<?php

require_once __DIR__ . '/public_html/vendor/autoload.php';

$app = require_once __DIR__ . '/public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tender;

echo "=== 전체 312건 데이터 분류코드 분석 ===" . PHP_EOL;

$tenders = Tender::all();
echo "총 공고 수: " . $tenders->count() . "개" . PHP_EOL . PHP_EOL;

$codeStats = [
    'empty_array' => 0,
    'empty_string' => 0,
    'null' => 0,
    'with_value' => 0
];

$foundCodes = [];

foreach ($tenders as $tender) {
    $code = $tender->pub_prcrmnt_clsfc_no;
    $metadata = json_decode($tender->metadata, true);
    $apiCode = $metadata['pubPrcrmntClsfcNo'] ?? null;
    
    // 데이터베이스 코드 분석
    if (is_null($code)) {
        $codeStats['null']++;
    } elseif ($code === '') {
        $codeStats['empty_string']++;
    } else {
        $codeStats['with_value']++;
        if (!isset($foundCodes[$code])) {
            $foundCodes[$code] = 0;
        }
        $foundCodes[$code]++;
    }
    
    // API 원본 데이터 분석
    if (is_array($apiCode)) {
        if (empty($apiCode)) {
            $codeStats['empty_array']++;
        } else {
            foreach ($apiCode as $codeValue) {
                if (!empty($codeValue)) {
                    echo "실제 분류코드 발견! 공고번호: {$tender->tender_no}, 코드: {$codeValue}" . PHP_EOL;
                }
            }
        }
    } elseif (!empty($apiCode)) {
        echo "실제 분류코드 발견! 공고번호: {$tender->tender_no}, 코드: {$apiCode}" . PHP_EOL;
    }
    
    // 업종코드 1468 특별 확인
    if (stripos($tender->title, '1468') !== false || 
        stripos(json_encode($metadata), '1468') !== false) {
        echo "업종코드 1468 관련 공고 발견! 공고번호: {$tender->tender_no}" . PHP_EOL;
        echo "제목: " . $tender->title . PHP_EOL;
    }
}

echo PHP_EOL . "분류코드 상태 분포:" . PHP_EOL;
echo "- 빈 배열 (API): {$codeStats['empty_array']}개" . PHP_EOL;
echo "- 빈 문자열 (DB): {$codeStats['empty_string']}개" . PHP_EOL; 
echo "- NULL (DB): {$codeStats['null']}개" . PHP_EOL;
echo "- 값 있음 (DB): {$codeStats['with_value']}개" . PHP_EOL;

if (!empty($foundCodes)) {
    echo PHP_EOL . "발견된 분류코드:" . PHP_EOL;
    foreach ($foundCodes as $code => $count) {
        echo "- {$code}: {$count}개" . PHP_EOL;
    }
} else {
    echo PHP_EOL . "실제 분류코드가 있는 공고가 없습니다." . PHP_EOL;
    echo "현재 수집된 데이터는 모두 분류코드가 비어있는 상태입니다." . PHP_EOL;
}

echo PHP_EOL . "=== 분석 완료 ===" . PHP_EOL;