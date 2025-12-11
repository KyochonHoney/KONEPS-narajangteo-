<?php

require 'public_html/vendor/autoload.php';
$app = require_once 'public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\NaraApiService;

echo "=== 나라장터 API 키 및 연결 테스트 ===\n\n";

$naraApi = new NaraApiService();

// 1. 설정 확인
echo "1. 설정 확인\n";
$apiKey = config('services.nara.api_key');
$timeout = config('services.nara.timeout');

echo "   API 키: " . $apiKey . "\n";
echo "   API 키 길이: " . strlen($apiKey) . "자\n";
echo "   타임아웃: " . $timeout . "초\n\n";

// 2. 환경변수 직접 확인
echo "2. 환경변수 직접 확인\n";
$envKey = env('NARA_API_KEY');
echo "   ENV에서 읽은 키: " . $envKey . "\n";
echo "   ENV 키 길이: " . strlen($envKey) . "자\n";
echo "   키 일치 여부: " . ($apiKey === $envKey ? "일치" : "불일치") . "\n\n";

// 3. API URL 및 파라미터 테스트
echo "3. API 요청 파라미터 테스트\n";
$baseUrl = 'https://apis.data.go.kr/1230000/BidPublicInfoService01';
$endpoint = '/getBidPblancListInfoServc';
$fullUrl = $baseUrl . $endpoint;

$params = [
    'serviceKey' => $apiKey,
    'pageNo' => 1,
    'numOfRows' => 10,
    'inqryBgnDt' => date('Ymd', strtotime('-7 days')),
    'inqryEndDt' => date('Ymd')
];

echo "   기본 URL: " . $baseUrl . "\n";
echo "   엔드포인트: " . $endpoint . "\n";
echo "   전체 URL: " . $fullUrl . "\n";
echo "   요청 파라미터:\n";
foreach ($params as $key => $value) {
    if ($key === 'serviceKey') {
        echo "     {$key}: " . substr($value, 0, 20) . "...(총 " . strlen($value) . "자)\n";
    } else {
        echo "     {$key}: {$value}\n";
    }
}

// 4. URL 인코딩 테스트
echo "\n4. URL 인코딩 테스트\n";
$queryString = http_build_query($params);
echo "   인코딩된 쿼리: " . substr($queryString, 0, 100) . "...\n";
echo "   전체 요청 URL: " . $fullUrl . "?" . substr($queryString, 0, 50) . "...\n\n";

// 5. 직접 HTTP 요청 테스트
echo "5. 직접 HTTP 요청 테스트\n";
try {
    $response = \Illuminate\Support\Facades\Http::timeout(30)
        ->get($fullUrl, $params);
    
    echo "   HTTP 상태: " . $response->status() . "\n";
    echo "   응답 헤더 Content-Type: " . $response->header('Content-Type') . "\n";
    
    if ($response->successful()) {
        $body = $response->body();
        echo "   응답 길이: " . strlen($body) . " bytes\n";
        echo "   응답 첫 200자: " . substr($body, 0, 200) . "...\n";
        
        // XML 파싱 시도
        try {
            $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);
            if ($xml !== false) {
                $data = json_decode(json_encode($xml), true);
                echo "   XML 파싱: 성공\n";
                
                // 응답 구조 확인
                if (isset($data['cmmMsgHeader'])) {
                    echo "   메시지 헤더 발견\n";
                    $header = $data['cmmMsgHeader'];
                    echo "   반환 코드: " . ($header['returnReasonCode'] ?? 'N/A') . "\n";
                    echo "   반환 메시지: " . ($header['returnAuthMsg'] ?? 'N/A') . "\n";
                    
                    if (isset($header['returnReasonCode'])) {
                        if ($header['returnReasonCode'] === '00') {
                            echo "   ✅ API 키가 유효합니다!\n";
                        } else {
                            echo "   ❌ API 오류 코드: " . $header['returnReasonCode'] . "\n";
                            if (isset($header['errMsg'])) {
                                echo "   오류 메시지: " . $header['errMsg'] . "\n";
                            }
                        }
                    }
                } else {
                    echo "   ❌ 예상하지 못한 응답 구조\n";
                    echo "   응답 데이터: " . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
                }
            } else {
                echo "   ❌ XML 파싱 실패\n";
                echo "   응답 내용: " . $body . "\n";
            }
        } catch (Exception $e) {
            echo "   XML 파싱 오류: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "   ❌ HTTP 오류: " . $response->status() . "\n";
        echo "   응답 내용: " . $response->body() . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ 요청 실패: " . $e->getMessage() . "\n";
}

// 6. NaraApiService testConnection 메서드 테스트
echo "\n6. NaraApiService testConnection 메서드 테스트\n";
try {
    $isConnected = $naraApi->testConnection();
    echo "   연결 상태: " . ($isConnected ? "성공 ✅" : "실패 ❌") . "\n";
} catch (Exception $e) {
    echo "   테스트 오류: " . $e->getMessage() . "\n";
}

echo "\n=== 테스트 완료 ===\n";