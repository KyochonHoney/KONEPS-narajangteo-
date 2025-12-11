<?php

require 'public_html/vendor/autoload.php';
$app = require_once 'public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tender;

echo "=== 올바른 나라장터 URL 형식으로 수정 ===\n\n";

$tenders = Tender::all();

foreach ($tenders as $tender) {
    // 실제 나라장터에서 사용하는 프레임 기반 URL 형식
    $correctUrl = "https://www.g2b.go.kr/pt/menu/selectSubFrame.do?framesrc=/pt/menu/frameTgong.do?url=https://www.g2b.go.kr:8082/ep/invitation/publish/bidInfoDtl.do?bidno={$tender->tender_no}";
    
    $tender->update([
        'source_url' => $correctUrl,
        'detail_url' => $correctUrl
    ]);
    
    echo "✅ {$tender->tender_no}: {$tender->title}\n";
    echo "   올바른 나라장터 URL: {$correctUrl}\n\n";
}

echo "=== 수정 완료 ===\n";
echo "이제 '원본 보기' 버튼이 올바른 나라장터 프레임 구조로 연결됩니다.\n";
echo "※ Mock 공고번호이므로 실제 페이지는 존재하지 않을 수 있습니다.\n";
echo "※ 실제 API 연동 시에는 bidNtceUrl 필드의 정확한 URL을 사용합니다.\n\n";

echo "URL 형식 설명:\n";
echo "- 프레임 기반: /pt/menu/selectSubFrame.do?framesrc=...\n";
echo "- 실제 내용: :8082/ep/invitation/publish/bidInfoDtl.do?bidno=공고번호\n";
echo "- 이는 나라장터의 표준 URL 구조입니다.\n";