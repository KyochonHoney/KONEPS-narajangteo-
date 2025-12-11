<?php

// 간단한 API 호출 테스트 (Laravel 없이)
$apiKey = "8c1ad9eb-bd9f-426e-b6c2-b4066ca4e9b3";
$baseUrl = "https://apis.data.go.kr/1230000/BidPublicInfoService04/getBidPblancListInfoServc04";

$total = 0;
$missingField = 0;
$emptyString = 0;
$nullValue = 0;
$hasValue = 0;
$samples = [];

echo "=== pubPrcrmntClsfcNo 필드 상태 분석 ===\n";
echo "API 직접 호출로 원본 데이터 확인\n\n";

// 8월 31일~9월 7일 데이터 확인
for ($page = 1; $page <= 10; $page++) {
    $params = [
        'serviceKey' => $apiKey,
        'numOfRows' => 100,
        'pageNo' => $page,
        'inqryDiv' => '01',
        'inqryBgnDt' => '20250831',
        'inqryEndDt' => '20250907',
        'type' => 'json'
    ];
    
    $url = $baseUrl . '?' . http_build_query($params);
    
    echo "Page $page 처리 중...\n";
    
    $response = file_get_contents($url);
    if ($response === false) {
        echo "API 호출 실패\n";
        break;
    }
    
    $data = json_decode($response, true);
    
    if (!isset($data['response']['body']['items']) || empty($data['response']['body']['items'])) {
        echo "Page $page: 데이터 없음\n";
        break;
    }
    
    $items = $data['response']['body']['items'];
    
    foreach ($items as $item) {
        $total++;
        $title = $item['bidNtceNm'] ?? 'N/A';
        
        // 필드 존재 여부 확인
        if (!array_key_exists('pubPrcrmntClsfcNo', $item)) {
            $missingField++;
            if (count($samples) < 15) {
                $samples[] = "필드없음: " . substr($title, 0, 40) . "...";
            }
        } else {
            $code = $item['pubPrcrmntClsfcNo'];
            
            if ($code === null) {
                $nullValue++;
                if (count($samples) < 15) {
                    $samples[] = "NULL값: " . substr($title, 0, 40) . "...";
                }
            } elseif ($code === "" || trim($code) === "") {
                $emptyString++;
                if (count($samples) < 15) {
                    $samples[] = "빈문자열: " . substr($title, 0, 40) . "...";
                }
            } else {
                $hasValue++;
                // 8111 패턴이 아닌 것들도 확인
                if (!preg_match('/^8111/', $code) && count($samples) < 15) {
                    $samples[] = "다른패턴: [$code] " . substr($title, 0, 30) . "...";
                }
            }
        }
    }
    
    if ($page % 3 == 0) {
        echo "  - 현재까지: 총 $total건, 필드없음 $missingField건, 빈값 $emptyString건\n";
    }
}

echo "\n===== 최종 결과 =====\n";
echo "총 공고 수: $total 건\n";
echo "필드 자체 없음: $missingField 건 (" . round($missingField/$total*100, 1) . "%)\n";
echo "빈 문자열: $emptyString 건 (" . round($emptyString/$total*100, 1) . "%)\n";
echo "NULL 값: $nullValue 건 (" . round($nullValue/$total*100, 1) . "%)\n";
echo "정상 값 있음: $hasValue 건 (" . round($hasValue/$total*100, 1) . "%)\n";
echo "\n업종상세코드가 없거나 빈 값: " . ($missingField + $emptyString + $nullValue) . " 건\n";

if (!empty($samples)) {
    echo "\n=== 샘플 데이터 ===\n";
    foreach ($samples as $sample) {
        echo $sample . "\n";
    }
}

?>