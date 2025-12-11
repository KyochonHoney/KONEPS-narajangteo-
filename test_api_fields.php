<?php

require_once __DIR__ . '/public_html/vendor/autoload.php';

$app = require_once __DIR__ . '/public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\NaraApiService;

echo "=== API 필드 구조 테스트 ===" . PHP_EOL;

$naraApi = new NaraApiService();

try {
    echo "나라장터 API 호출 중..." . PHP_EOL;
    
    // 더 많은 데이터로 분류코드 패턴 확인
    $endDate = date('Ymd');
    $startDate = date('Ymd', strtotime('-30 days'));
    $response = $naraApi->getTendersByDateRange($startDate, $endDate, 1, 100); // 100개 확인
    
    if (isset($response['body']['items']['item']) && !empty($response['body']['items']['item'])) {
        $items = $response['body']['items']['item'];
        echo "수신된 아이템 수: " . count($items) . "개" . PHP_EOL . PHP_EOL;
        
        // 첫 번째 아이템 분석
        $firstItem = $items[0];
        echo "첫 번째 아이템의 모든 필드:" . PHP_EOL;
        
        $relevantFields = [];
        foreach ($firstItem as $key => $value) {
            // 업종코드나 품명 관련 필드 찾기
            if (stripos($key, 'indstryty') !== false || 
                stripos($key, 'industry') !== false ||
                stripos($key, 'prdct') !== false ||
                stripos($key, 'product') !== false ||
                stripos($key, 'clsfc') !== false ||
                stripos($key, 'dtil') !== false ||
                stripos($key, 'detail') !== false ||
                $key === 'bidNtceNo' || 
                $key === 'bidNtceNm') {
                $relevantFields[$key] = $value;
            }
        }
        
        // 모든 아이템의 분류코드 수집
        $allCodes = [];
        $matchingCodes = []; // 81108989로 시작하는 코드들
        
        foreach ($items as $item) {
            $code = isset($item['pubPrcrmntClsfcNo']) ? 
                (is_array($item['pubPrcrmntClsfcNo']) ? 
                    (empty($item['pubPrcrmntClsfcNo']) ? '' : reset($item['pubPrcrmntClsfcNo'])) : 
                    (string)$item['pubPrcrmntClsfcNo']) : '';
            
            if (!empty($code)) {
                $allCodes[] = $code;
                
                // 81108989로 시작하는지 확인
                if (strpos($code, '81108989') === 0) {
                    $matchingCodes[] = [
                        'code' => $code,
                        'bidNtceNo' => $item['bidNtceNo'] ?? '',
                        'title' => $item['bidNtceNm'] ?? ''
                    ];
                }
            }
        }
        
        $uniqueCodes = array_unique($allCodes);
        sort($uniqueCodes);
        
        echo "수집된 모든 분류코드들 (총 " . count($uniqueCodes) . "종류):" . PHP_EOL;
        foreach ($uniqueCodes as $code) {
            echo "- {$code}" . PHP_EOL;
        }
        
        echo PHP_EOL . "81108989로 시작하는 코드들:" . PHP_EOL;
        if (!empty($matchingCodes)) {
            foreach ($matchingCodes as $match) {
                echo "- 코드: {$match['code']}" . PHP_EOL;
                echo "  공고번호: {$match['bidNtceNo']}" . PHP_EOL;  
                echo "  제목: " . substr($match['title'], 0, 50) . "..." . PHP_EOL . PHP_EOL;
            }
        } else {
            echo "해당 패턴의 코드가 없습니다." . PHP_EOL;
        }
        
        // 8110, 8111로 시작하는 IT 관련 코드들 찾기
        echo "IT 관련 코드들 (811로 시작):" . PHP_EOL;
        foreach ($uniqueCodes as $code) {
            if (strpos($code, '811') === 0) {
                echo "- {$code}" . PHP_EOL;
            }
        }
        
    } else {
        echo "API 응답에 데이터가 없습니다." . PHP_EOL;
        echo "전체 응답: " . json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "오류 발생: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== 테스트 완료 ===" . PHP_EOL;