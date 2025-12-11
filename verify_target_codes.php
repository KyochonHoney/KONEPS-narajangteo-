<?php

require_once __DIR__ . '/public_html/vendor/autoload.php';

$app = require_once __DIR__ . '/public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\NaraApiService;

echo "=== 대상 업종코드 존재 여부 확인 ===".PHP_EOL;

$targetCodes = [
    '8111200201', // 데이터처리서비스
    '8111200202', // 빅데이터분석서비스
    '8111229901', // 소프트웨어유지및지원서비스
    '8111181101', // 운영위탁서비스
    '8111189901', // 정보시스템유지관리서비스
    '8111219901', // 인터넷지원개발서비스
    '8111159801', // 패키지소프트웨어개발및도입서비스
    '8111159901', // 정보시스템개발서비스
    '8115169901'  // 공간정보DB구축서비스
];

$naraApi = new NaraApiService();

try {
    // 더 넓은 기간으로 검색 (최근 90일)
    $endDate = date('Ymd');
    $startDate = date('Ymd', strtotime('-90 days'));
    
    echo "API 검색 기간: {$startDate} ~ {$endDate}".PHP_EOL;
    echo "대상 업종코드: ".implode(', ', $targetCodes).PHP_EOL.PHP_EOL;
    
    $response = $naraApi->getTendersByDateRange($startDate, $endDate, 1, 1000); // 최대 1000건
    
    if (isset($response['body']['items']['item']) && !empty($response['body']['items']['item'])) {
        $items = $response['body']['items']['item'];
        $totalItems = count($items);
        $foundCodes = [];
        $matchingItems = [];
        
        echo "총 {$totalItems}개 공고 검사 중...".PHP_EOL;
        
        foreach ($items as $item) {
            $classification = isset($item['pubPrcrmntClsfcNo']) ? 
                (is_array($item['pubPrcrmntClsfcNo']) ? 
                    (empty($item['pubPrcrmntClsfcNo']) ? '' : reset($item['pubPrcrmntClsfcNo'])) : 
                    (string)$item['pubPrcrmntClsfcNo']) : '';
            
            // 통계 수집
            if (!empty($classification) && !in_array($classification, $foundCodes)) {
                $foundCodes[] = $classification;
            }
            
            // 대상 코드 확인
            if (in_array($classification, $targetCodes)) {
                $matchingItems[] = [
                    'code' => $classification,
                    'bidNtceNo' => $item['bidNtceNo'] ?? '',
                    'title' => $item['bidNtceNm'] ?? '',
                    'date' => $item['bidNtceDt'] ?? ''
                ];
            }
        }
        
        sort($foundCodes);
        
        echo PHP_EOL."=== 검색 결과 ===".PHP_EOL;
        echo "발견된 대상 업종코드: ".count($matchingItems)."건".PHP_EOL;
        
        if (!empty($matchingItems)) {
            echo PHP_EOL."=== 매칭된 공고 ===".PHP_EOL;
            foreach ($matchingItems as $match) {
                echo "- 업종코드: {$match['code']}".PHP_EOL;
                echo "  공고번호: {$match['bidNtceNo']}".PHP_EOL;
                echo "  제목: ".substr($match['title'], 0, 50)."...".PHP_EOL;
                echo "  공고일: {$match['date']}".PHP_EOL.PHP_EOL;
            }
        } else {
            echo PHP_EOL."❌ 최근 90일 내에 대상 업종코드를 가진 공고가 없습니다.".PHP_EOL;
        }
        
        echo PHP_EOL."=== 발견된 모든 업종코드 샘플 (처음 30개) ===".PHP_EOL;
        $displayCodes = array_slice($foundCodes, 0, 30);
        foreach ($displayCodes as $code) {
            $isTarget = in_array($code, $targetCodes) ? ' ⭐ 대상코드' : '';
            echo "- {$code}{$isTarget}".PHP_EOL;
        }
        
        if (count($foundCodes) > 30) {
            echo "... 외 ".(count($foundCodes)-30)."개 더".PHP_EOL;
        }
        
    } else {
        echo "❌ API 응답에 데이터가 없습니다.".PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "❌ 오류 발생: ".$e->getMessage().PHP_EOL;
}

echo PHP_EOL."=== 확인 완료 ===".PHP_EOL;