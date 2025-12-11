<?php

require 'public_html/vendor/autoload.php';
$app = require_once 'public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "기존 Mock URL 데이터 수정 시작...\n";

$tenders = App\Models\Tender::all();
$updated = 0;

foreach ($tenders as $tender) {
    if (strpos($tender->source_url, 'mock') !== false && !empty($tender->tender_no)) {
        $newUrl = "https://www.g2b.go.kr:8082/ep/invitation/publish/bidInfoDtl.do?bidno={$tender->tender_no}";
        $tender->update(['source_url' => $newUrl]);
        $updated++;
        echo "공고번호 {$tender->tender_no}: URL 업데이트\n";
    }
}

echo "총 {$updated}개의 URL을 실제 나라장터 링크로 업데이트했습니다.\n";
echo "업데이트 완료!\n";