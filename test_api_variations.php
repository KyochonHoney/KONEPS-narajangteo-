<?php

require 'public_html/vendor/autoload.php';
$app = require_once 'public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== 나라장터 API 다양한 엔드포인트 테스트 ===\n\n";

$apiKey = config('services.nara.api_key');
echo "사용할 API 키: " . $apiKey . " (길이: " . strlen($apiKey) . "자)\n\n";

// 테스트할 API 엔드포인트들
$endpoints = [
    [
        'name' => '기본 BidPublicInfoService',
        'base' => 'https://apis.data.go.kr/1230000/BidPublicInfoService',
        'method' => 'getBidPblancListInfoServc'
    ],
    [
        'name' => 'BidPublicInfoService01',
        'base' => 'https://apis.data.go.kr/1230000/BidPublicInfoService01', 
        'method' => 'getBidPblancListInfoServc'
    ],
    [
        'name' => 'PubDataOpnStdService',
        'base' => 'https://apis.data.go.kr/1230000/PubDataOpnStdService',
        'method' => 'getBidPblancListInfoServc'
    ]
];

$testParams = [
    'serviceKey' => $apiKey,
    'pageNo' => 1,
    'numOfRows' => 5,
    'inqryBgnDt' => date('Ymd', strtotime('-3 days')),
    'inqryEndDt' => date('Ymd')
];

foreach ($endpoints as $i => $endpoint) {
    echo ($i + 1) . ". " . $endpoint['name'] . " 테스트\n";
    echo "   URL: " . $endpoint['base'] . "/" . $endpoint['method'] . "\n";
    
    try {
        $fullUrl = $endpoint['base'] . '/' . $endpoint['method'];
        $response = \Illuminate\Support\Facades\Http::timeout(30)
            ->get($fullUrl, $testParams);
        
        echo "   HTTP 상태: " . $response->status() . "\n";
        
        if ($response->successful()) {
            $body = $response->body();
            echo "   응답 길이: " . strlen($body) . " bytes\n";
            
            // XML 파싱 시도
            try {
                $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);
                if ($xml !== false) {
                    $data = json_decode(json_encode($xml), true);
                    
                    // OpenAPI 응답 구조 확인
                    if (isset($data['cmmMsgHeader'])) {
                        $header = $data['cmmMsgHeader'];
                        echo "   반환 코드: " . ($header['returnReasonCode'] ?? 'N/A') . "\n";
                        echo "   반환 메시지: " . ($header['returnAuthMsg'] ?? 'N/A') . "\n";
                        
                        if (isset($header['returnReasonCode']) && $header['returnReasonCode'] === '00') {
                            echo "   ✅ 성공! 이 엔드포인트가 정상 작동합니다!\n";
                            
                            // 데이터 확인
                            if (isset($data['response']['body'])) {
                                echo "   데이터 개수: " . ($data['response']['body']['totalCount'] ?? 0) . "건\n";
                                if (isset($data['response']['body']['items']['item'])) {
                                    $items = $data['response']['body']['items']['item'];
                                    if (!is_array($items) || !isset($items[0])) {
                                        $items = [$items];
                                    }
                                    echo "   첫 번째 공고: " . ($items[0]['bidNtceNm'] ?? 'N/A') . "\n";
                                }
                            }
                            
                            break; // 성공한 엔드포인트 발견 시 중단
                        } else {
                            echo "   ❌ 오류 코드: " . ($header['returnReasonCode'] ?? 'N/A') . "\n";
                            if (isset($header['errMsg'])) {
                                echo "   오류 메시지: " . $header['errMsg'] . "\n";
                            }
                        }
                    } else {
                        echo "   ❌ 예상하지 못한 응답 구조\n";
                    }
                }
            } catch (Exception $e) {
                echo "   XML 파싱 오류: " . $e->getMessage() . "\n";
            }
        } else {
            echo "   ❌ HTTP 오류: " . $response->status() . "\n";
            $errorBody = $response->body();
            if (strlen($errorBody) < 500) {
                echo "   응답 내용: " . $errorBody . "\n";
            } else {
                echo "   응답 내용 (일부): " . substr($errorBody, 0, 200) . "...\n";
            }
        }
        
    } catch (Exception $e) {
        echo "   ❌ 요청 실패: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// URL 인코딩 방식도 테스트
echo "4. URL 인코딩된 서비스키로 테스트\n";
$encodedKey = urlencode($apiKey);
echo "   인코딩된 키: " . substr($encodedKey, 0, 30) . "...\n";

$testParamsEncoded = [
    'serviceKey' => $encodedKey,
    'pageNo' => 1,
    'numOfRows' => 5,
    'inqryBgnDt' => date('Ymd', strtotime('-3 days')),
    'inqryEndDt' => date('Ymd')
];

try {
    $response = \Illuminate\Support\Facades\Http::timeout(30)
        ->get('https://apis.data.go.kr/1230000/BidPublicInfoService/getBidPblancListInfoServc', $testParamsEncoded);
    
    echo "   HTTP 상태: " . $response->status() . "\n";
    
    if ($response->successful()) {
        echo "   ✅ URL 인코딩으로 성공!\n";
    } else {
        echo "   ❌ URL 인코딩도 실패\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ 인코딩 테스트 실패: " . $e->getMessage() . "\n";
}

echo "\n=== 테스트 완료 ===\n";