<?php

require 'public_html/vendor/autoload.php';
$app = require_once 'public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tender;

echo "=== 나라장터 URL 수정 (실제 API 형식에 맞게) ===\n\n";

// 기존 데이터의 URL을 실제 나라장터 URL 형식으로 수정
$tenders = Tender::all();

echo "수정할 공고 수: " . $tenders->count() . "건\n\n";

foreach ($tenders as $tender) {
    // 실제 나라장터 공고 상세 페이지 URL 형식
    $realDetailUrl = "https://www.g2b.go.kr/ep/invitation/publish/bidInfoDtl.do?bidno=" . $tender->tender_no;
    
    // source_url과 detail_url을 모두 실제 URL로 설정
    $tender->update([
        'source_url' => $realDetailUrl,
        'detail_url' => $realDetailUrl
    ]);
    
    echo "✅ {$tender->tender_no}: {$tender->title}\n";
    echo "   원본 URL: {$realDetailUrl}\n\n";
}

echo "=== URL 수정 완료 ===\n";
echo "이제 '원본 보기' 버튼이 실제 나라장터 공고 페이지로 연결됩니다.\n";