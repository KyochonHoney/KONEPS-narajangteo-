<?php

echo "=== 나라장터 API 서비스 ID 탐지 ===\n\n";

// 가능한 서비스 ID들
$serviceIds = [
    '1230000/PubDataOpnStdService',
    '1230000/BidPublicInfoService', 
    '1230000/BidPublicInfoService01',
    '1230000/ScsbidInfoService',
    '1230000/ad/BidPublicInfoService',
    '1230000/TenderService',
    '1230000/openbidservice',
];

$apiKey = '3d18152cba55dc1ae0d4b82c0b965225de24e5fc4c97629bbadf4f7a75de6749';

foreach ($serviceIds as $i => $serviceId) {
    echo ($i + 1) . ". 서비스 ID: {$serviceId}\n";
    $baseUrl = "https://apis.data.go.kr/{$serviceId}";
    
    // 다양한 메서드명도 테스트
    $methods = [
        'getBidPblancListInfoServc',
        'getBidPblancListInfo',
        'getOpengBidInfo',
        'getTenderInfo'
    ];
    
    foreach ($methods as $method) {
        $fullUrl = $baseUrl . '/' . $method;
        echo "   메서드: {$method}\n";
        echo "   URL: {$fullUrl}\n";
        
        try {
            $params = [
                'serviceKey' => urlencode($apiKey),
                'pageNo' => 1,
                'numOfRows' => 1,
                'inqryBgnDt' => date('Ymd'),
                'inqryEndDt' => date('Ymd')
            ];
            
            $response = \Illuminate\Support\Facades\Http::timeout(20)->get($fullUrl, $params);
            
            echo "   HTTP 상태: " . $response->status() . "\n";
            
            if ($response->successful()) {
                $body = $response->body();
                
                // XML 파싱 시도
                try {
                    $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);
                    if ($xml !== false) {
                        $data = json_decode(json_encode($xml), true);
                        
                        if (isset($data['cmmMsgHeader'])) {
                            $header = $data['cmmMsgHeader'];
                            $returnCode = $header['returnReasonCode'] ?? 'N/A';
                            $returnMsg = $header['returnAuthMsg'] ?? 'N/A';
                            
                            echo "   응답 코드: {$returnCode}\n";
                            echo "   응답 메시지: {$returnMsg}\n";
                            
                            if ($returnCode === '00') {
                                echo "   🎉 성공! 이 조합이 올바릅니다!\n";
                                echo "   ===== 정답 발견 =====\n";
                                echo "   서비스 ID: {$serviceId}\n";
                                echo "   메서드: {$method}\n";
                                echo "   전체 URL: {$fullUrl}\n";
                                echo "   =====================\n\n";
                                exit(0);
                            } else {
                                echo "   ❌ 오류: {$returnCode} - {$returnMsg}\n";
                            }
                        }
                    }
                } catch (Exception $e) {
                    echo "   XML 파싱 오류: " . $e->getMessage() . "\n";
                }
            } else {
                echo "   ❌ HTTP 오류: " . $response->status() . "\n";
            }
            
        } catch (Exception $e) {
            echo "   ❌ 요청 실패: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    echo "---\n\n";
}

echo "모든 조합을 시도했으나 성공하지 못했습니다.\n";
echo "API 키가 잘못되었거나 서비스 신청이 필요할 수 있습니다.\n";