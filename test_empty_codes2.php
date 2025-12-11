<?php

// Laravel 환경에서 실행
require '/home/tideflo/nara/public_html/vendor/autoload.php';

// Laravel 앱 부트스트랩
$app = require '/home/tideflo/nara/public_html/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// NaraApiService 사용
$apiService = new App\Services\NaraApiService();

$total = 0;
$missingField = 0;
$emptyString = 0;
$nullValue = 0;
$hasValue = 0;
$samples = [];

echo "=== pubPrcrmntClsfcNo 필드 상태 분석 ===\n";
echo "NaraApiService를 통한 원본 데이터 확인\n\n";

try {
    // 최근 7일간 데이터 확인 (페이지 수 줄여서 빠르게)
    for ($page = 1; $page <= 5; $page++) {
        echo "Page $page 처리 중...\n";
        
        $result = $apiService->getTendersByDateRange('20250831', '20250907', $page, 100);
        
        if (!isset($result['response']['body']['items']) || empty($result['response']['body']['items'])) {
            echo "Page $page: 데이터 없음\n";
            break;
        }
        
        $items = $result['response']['body']['items'];
        
        foreach ($items as $item) {
            $total++;
            $title = $item['bidNtceNm'] ?? 'N/A';
            
            // 필드 존재 여부 확인
            if (!array_key_exists('pubPrcrmntClsfcNo', $item)) {
                $missingField++;
                if (count($samples) < 10) {
                    $samples[] = "[필드없음] " . substr($title, 0, 35) . "...";
                }
            } else {
                $code = $item['pubPrcrmntClsfcNo'];
                
                if ($code === null) {
                    $nullValue++;
                    if (count($samples) < 10) {
                        $samples[] = "[NULL값] " . substr($title, 0, 35) . "...";
                    }
                } elseif ($code === "" || trim($code) === "") {
                    $emptyString++;
                    if (count($samples) < 10) {
                        $samples[] = "[빈문자열] " . substr($title, 0, 35) . "...";
                    }
                } else {
                    $hasValue++;
                    // 처음 몇 개의 정상 값도 확인
                    if ($hasValue <= 3) {
                        echo "  정상값 샘플: [$code] " . substr($title, 0, 30) . "...\n";
                    }
                }
            }
        }
        
        echo "  현재까지: 총 $total건, 필드없음 $missingField건, 빈값 $emptyString건, NULL $nullValue건, 정상값 $hasValue건\n";
    }

} catch (Exception $e) {
    echo "오류 발생: " . $e->getMessage() . "\n";
}

if ($total > 0) {
    echo "\n===== 최종 결과 =====\n";
    echo "총 공고 수: $total 건\n";
    echo "필드 자체 없음: $missingField 건 (" . round($missingField/$total*100, 1) . "%)\n";
    echo "빈 문자열: $emptyString 건 (" . round($emptyString/$total*100, 1) . "%)\n";
    echo "NULL 값: $nullValue 건 (" . round($nullValue/$total*100, 1) . "%)\n";
    echo "정상 값 있음: $hasValue 건 (" . round($hasValue/$total*100, 1) . "%)\n";
    echo "\n▶ 업종상세코드가 없거나 빈 값: " . ($missingField + $emptyString + $nullValue) . " 건 (" . round(($missingField + $emptyString + $nullValue)/$total*100, 1) . "%)\n";
    
    if (!empty($samples)) {
        echo "\n=== 문제 샘플 ===\n";
        foreach ($samples as $sample) {
            echo $sample . "\n";
        }
    }
} else {
    echo "데이터를 가져올 수 없었습니다.\n";
}

?>