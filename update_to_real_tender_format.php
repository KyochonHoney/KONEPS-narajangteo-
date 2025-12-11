<?php

require 'public_html/vendor/autoload.php';
$app = require_once 'public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tender;

echo "=== 실제 나라장터 공고번호 형식으로 업데이트 ===\n\n";

// 실제 나라장터 공고번호 형식 (YYYYMMDD-NNNN 또는 유사 형식)
$realTenderNumbers = [
    '20250828-001',
    '20250828-002', 
    '20250828-003',
    '20250828-004',
    '20250828-005',
    '20250828-006',
    '20250828-007',
    '20250828-008'
];

$tenders = Tender::all();

foreach ($tenders as $index => $tender) {
    if (isset($realTenderNumbers[$index])) {
        $newTenderNo = $realTenderNumbers[$index];
        
        // 다양한 URL 형식 테스트를 위해 여러 형식 준비
        $urlFormats = [
            "https://www.g2b.go.kr/ep/invitation/publish/bidInfoDtl.do?bidno={$newTenderNo}",
            "https://www.g2b.go.kr:8082/ep/invitation/publish/bidInfoDtl.do?bidno={$newTenderNo}",
            "https://www.g2b.go.kr/ep/invitation/publish/bidInfoDtl.do?bidNtceNo={$newTenderNo}",
        ];
        
        // 가장 표준적인 형식 사용
        $standardUrl = $urlFormats[0];
        
        $tender->update([
            'tender_no' => $newTenderNo,
            'source_url' => $standardUrl,
            'detail_url' => $standardUrl
        ]);
        
        echo "✅ 업데이트: {$tender->title}\n";
        echo "   공고번호: {$newTenderNo}\n";
        echo "   URL: {$standardUrl}\n\n";
    }
}

echo "=== 추가 URL 형식 정보 ===\n";
echo "나라장터에서 사용하는 가능한 URL 형식들:\n";
echo "1. bidno 파라미터 사용: ...bidInfoDtl.do?bidno=공고번호\n";
echo "2. bidNtceNo 파라미터 사용: ...bidInfoDtl.do?bidNtceNo=공고번호\n";
echo "3. 포트 8082 사용: :8082/ep/invitation/...\n";
echo "4. 프레임 형식: /pt/menu/selectSubFrame.do?framesrc=...\n\n";

echo "실제 API에서는 'bidNtceUrl' 필드에 정확한 URL이 제공됩니다.\n";
echo "현재는 Mock 데이터이므로 표준 형식으로 생성했습니다.\n";