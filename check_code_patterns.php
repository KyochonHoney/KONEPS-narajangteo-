<?php

require_once __DIR__ . '/public_html/vendor/autoload.php';

$app = require_once __DIR__ . '/public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\NaraApiService;

echo "=== 업종코드 패턴 분석 ===".PHP_EOL;

// 사용자가 제공한 9개 코드의 기본 패턴 (마지막 2자리 제거)
$targetPatterns = [
    '81112002', // 8111200201, 8111200202 -> 데이터처리서비스, 빅데이터분석서비스
    '81112299', // 8111229901 -> 소프트웨어유지및지원서비스
    '81111811', // 8111181101 -> 운영위탁서비스
    '81111899', // 8111189901 -> 정보시스템유지관리서비스
    '81112199', // 8111219901 -> 인터넷지원개발서비스
    '81111598', // 8111159801, 8111159901 -> 패키지소프트웨어개발및도입서비스, 정보시스템개발서비스
    '81151699'  // 8115169901 -> 공간정보DB구축서비스
];

$naraApi = new NaraApiService();

try {
    // 최근 30일 데이터 검색
    $endDate = date('Ymd');
    $startDate = date('Ymd', strtotime('-30 days'));
    
    echo "검색 기간: {$startDate} ~ {$endDate}".PHP_EOL;
    echo "대상 패턴: ".implode(', ', $targetPatterns).PHP_EOL.PHP_EOL;
    
    $response = $naraApi->getTendersByDateRange($startDate, $endDate, 1, 500);
    
    if (isset($response['body']['items']['item']) && !empty($response['body']['items']['item'])) {
        $items = $response['body']['items']['item'];
        $totalItems = count($items);
        $foundCodes = [];
        $patternMatches = [];
        
        echo "총 {$totalItems}개 공고 검사 중...".PHP_EOL;
        
        foreach ($items as $item) {
            $classification = isset($item['pubPrcrmntClsfcNo']) ? 
                (is_array($item['pubPrcrmntClsfcNo']) ? 
                    (empty($item['pubPrcrmntClsfcNo']) ? '' : reset($item['pubPrcrmntClsfcNo'])) : 
                    (string)$item['pubPrcrmntClsfcNo']) : '';
            
            if (!empty($classification)) {
                // 모든 코드 수집
                if (!in_array($classification, $foundCodes)) {
                    $foundCodes[] = $classification;
                }
                
                // 패턴 매칭 확인
                foreach ($targetPatterns as $pattern) {
                    if (strpos($classification, $pattern) === 0) {
                        $patternMatches[] = [
                            'pattern' => $pattern,
                            'full_code' => $classification,
                            'bidNtceNo' => $item['bidNtceNo'] ?? '',
                            'title' => $item['bidNtceNm'] ?? ''
                        ];
                    }
                }
            }
        }
        
        sort($foundCodes);
        
        echo PHP_EOL."=== 패턴 매칭 결과 ===".PHP_EOL;
        echo "매칭된 공고: ".count($patternMatches)."건".PHP_EOL;
        
        if (!empty($patternMatches)) {
            echo PHP_EOL."=== 매칭된 공고 목록 ===".PHP_EOL;
            foreach ($patternMatches as $match) {
                echo "- 패턴: {$match['pattern']} → 전체코드: {$match['full_code']}".PHP_EOL;
                echo "  공고번호: {$match['bidNtceNo']}".PHP_EOL;
                echo "  제목: ".substr($match['title'], 0, 50)."...".PHP_EOL.PHP_EOL;
            }
        }
        
        echo PHP_EOL."=== 8111로 시작하는 모든 코드 ===".PHP_EOL;
        $itCodes = array_filter($foundCodes, function($code) {
            return strpos($code, '8111') === 0;
        });
        
        if (!empty($itCodes)) {
            foreach ($itCodes as $code) {
                // 어떤 패턴과 매칭되는지 확인
                $matchedPattern = '';
                foreach ($targetPatterns as $pattern) {
                    if (strpos($code, $pattern) === 0) {
                        $matchedPattern = " ⭐ 매칭: {$pattern}";
                        break;
                    }
                }
                echo "- {$code}{$matchedPattern}".PHP_EOL;
            }
        } else {
            echo "8111로 시작하는 코드가 없습니다.".PHP_EOL;
        }
        
        echo PHP_EOL."=== 전체 코드 샘플 (처음 20개) ===".PHP_EOL;
        $displayCodes = array_slice($foundCodes, 0, 20);
        foreach ($displayCodes as $code) {
            echo "- {$code}".PHP_EOL;
        }
        
    } else {
        echo "❌ API 응답에 데이터가 없습니다.".PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "❌ 오류 발생: ".$e->getMessage().PHP_EOL;
}

echo PHP_EOL."=== 분석 완료 ===".PHP_EOL;