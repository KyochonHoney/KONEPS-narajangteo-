<?php

echo "=== 실제 나라장터 URL 형식 테스트 ===\n\n";

// 가능한 나라장터 URL 형식들 테스트
$testBidNo = "20250001234-00"; // 일반적인 공고번호 형식

$urlFormats = [
    "https://www.g2b.go.kr/ep/invitation/publish/bidInfoDtl.do?bidno={$testBidNo}",
    "https://www.g2b.go.kr:8082/ep/invitation/publish/bidInfoDtl.do?bidno={$testBidNo}", 
    "https://www.g2b.go.kr/ep/invitation/publish/bidInfoDtl.do?bidNtceNo={$testBidNo}",
    "https://www.g2b.go.kr:8082/ep/invitation/publish/bidInfoDtl.do?bidNtceNo={$testBidNo}",
    "https://www.g2b.go.kr/pt/menu/selectSubFrame.do?framesrc=/pt/menu/frameTgong.do?url=https://www.g2b.go.kr:8082/ep/invitation/publish/bidInfoDtl.do?bidno={$testBidNo}",
];

echo "테스트할 URL 형식들:\n";
foreach ($urlFormats as $i => $url) {
    echo ($i + 1) . ". {$url}\n";
}

echo "\n각 URL을 브라우저에서 직접 테스트해보시기 바랍니다.\n";
echo "실제 존재하는 공고번호로 테스트하려면 나라장터에서 현재 공고를 확인하세요.\n\n";

// 현재 Mock 데이터도 다시 확인
require 'public_html/vendor/autoload.php';
$app = require_once 'public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tender;

echo "현재 Mock 데이터의 URL들:\n";
$tenders = Tender::take(2)->get();
foreach ($tenders as $tender) {
    echo "공고: {$tender->title}\n";
    echo "URL: {$tender->source_url}\n";
    echo "공고번호 형식: {$tender->tender_no}\n\n";
}

echo "=== 해결 방법 ===\n";
echo "1. 실제 나라장터에서 현재 진행 중인 공고 찾기\n";
echo "2. 해당 공고의 URL 형식 확인\n";
echo "3. Mock 데이터를 실제 공고번호로 업데이트\n";
echo "4. 또는 실제 API 연동하여 정확한 URL 받기\n";