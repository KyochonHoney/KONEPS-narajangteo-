<?php

require 'public_html/vendor/autoload.php';
$app = require_once 'public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tender;

echo "=== 나라장터 URL 테스트 ===\n\n";

$tenders = Tender::take(3)->get();

foreach ($tenders as $tender) {
    echo "공고: {$tender->title}\n";
    echo "공고번호: {$tender->tender_no}\n";
    echo "source_url: {$tender->source_url}\n";
    echo "detail_url: {$tender->detail_url}\n";
    echo "실제 나라장터 URL: https://www.g2b.go.kr/ep/invitation/publish/bidInfoDtl.do?bidno={$tender->tender_no}\n";
    echo "URL 매치 여부: " . ($tender->source_url === "https://www.g2b.go.kr/ep/invitation/publish/bidInfoDtl.do?bidno={$tender->tender_no}" ? "✅" : "❌") . "\n";
    echo "---\n";
}

echo "\n웹페이지에서 '원본 보기' 버튼 클릭 시:\n";
echo "- source_url이나 detail_url로 연결됨\n";
echo "- 실제 나라장터 공고 페이지가 새 탭에서 열림\n";
echo "- 단, Mock 공고번호이므로 실제 페이지는 존재하지 않을 수 있음\n";
echo "- 실제 API 연동 시에는 정확한 공고 페이지로 연결됨\n\n";

echo "관리자 페이지에서 확인: https://nara.tideflo.work/admin/tenders\n";