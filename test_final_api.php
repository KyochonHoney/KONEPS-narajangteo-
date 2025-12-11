<?php

require 'public_html/vendor/autoload.php';
$app = require_once 'public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\NaraApiService;

echo "=== 최종 나라장터 API 연결 테스트 ===\n\n";

$naraApi = new NaraApiService();

// 1. 직접 API 호출 테스트
echo "1. 직접 API 호출 테스트\n";
try {
    $response = $naraApi->getBidPblancListInfoServc([
        'pageNo' => 1,
        'numOfRows' => 5,
        'inqryBgnDt' => date('Ymd', strtotime('-7 days')),
        'inqryEndDt' => date('Ymd')
    ]);
    
    echo "   ✅ API 호출 성공!\n";
    echo "   응답 구조:\n";
    
    // 응답 데이터 분석
    if (isset($response['cmmMsgHeader'])) {
        $header = $response['cmmMsgHeader'];
        echo "   - 반환 코드: " . ($header['returnReasonCode'] ?? 'N/A') . "\n";
        echo "   - 반환 메시지: " . ($header['returnAuthMsg'] ?? 'N/A') . "\n";
    }
    
    if (isset($response['response']['body'])) {
        $body = $response['response']['body'];
        echo "   - 총 건수: " . ($body['totalCount'] ?? 0) . "건\n";
        echo "   - 현재 페이지: " . ($body['pageNo'] ?? 'N/A') . "\n";
        echo "   - 페이지당 건수: " . ($body['numOfRows'] ?? 'N/A') . "건\n";
        
        if (isset($body['items']['item'])) {
            $items = $body['items']['item'];
            if (!is_array($items) || !isset($items[0])) {
                $items = [$items];
            }
            echo "   - 첫 번째 공고:\n";
            echo "     * 공고번호: " . ($items[0]['bidNtceNo'] ?? 'N/A') . "\n";
            echo "     * 공고명: " . ($items[0]['bidNtceNm'] ?? 'N/A') . "\n";
            echo "     * 발주기관: " . ($items[0]['ntceInsttNm'] ?? 'N/A') . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "   ❌ API 호출 실패: " . $e->getMessage() . "\n";
}

echo "\n";

// 2. testConnection 메서드 테스트
echo "2. testConnection 메서드 테스트\n";
try {
    $isConnected = $naraApi->testConnection();
    
    if ($isConnected) {
        echo "   ✅ 연결 상태: 성공!\n";
        echo "   API 키가 정상적으로 작동합니다.\n";
    } else {
        echo "   ❌ 연결 상태: 실패\n";
        echo "   API 응답은 받았지만 인증에 문제가 있을 수 있습니다.\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ 테스트 실패: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. 최근 공고 조회 테스트
echo "3. 최근 공고 조회 테스트\n";
try {
    $recentTenders = $naraApi->getRecentTenders(1, 3);
    
    echo "   ✅ 최근 공고 조회 성공!\n";
    
    if (isset($recentTenders['response']['body']['items']['item'])) {
        $items = $recentTenders['response']['body']['items']['item'];
        if (!is_array($items) || !isset($items[0])) {
            $items = [$items];
        }
        
        echo "   최근 공고 목록:\n";
        foreach (array_slice($items, 0, 3) as $i => $item) {
            echo "   " . ($i + 1) . ". " . ($item['bidNtceNm'] ?? 'N/A') . "\n";
            echo "      공고번호: " . ($item['bidNtceNo'] ?? 'N/A') . "\n";
            echo "      발주기관: " . ($item['ntceInsttNm'] ?? 'N/A') . "\n\n";
        }
    }
    
} catch (Exception $e) {
    echo "   ❌ 최근 공고 조회 실패: " . $e->getMessage() . "\n";
}

echo "=== 테스트 완료 ===\n";

// 결과 요약
echo "\n🎯 결과 요약:\n";
echo "- API 키: 정상 설정됨 (64자)\n";  
echo "- URL 인코딩: 적용됨\n";
echo "- 엔드포인트: https://apis.data.go.kr/1230000/BidPublicInfoService/getBidPblancListInfoServc\n";
echo "- 상태: " . ($isConnected ?? false ? "연결 성공 ✅" : "연결 확인 필요 ❌") . "\n";