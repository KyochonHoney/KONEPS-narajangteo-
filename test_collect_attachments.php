<?php

require 'public_html/vendor/autoload.php';
$app = require_once 'public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== 첨부파일 수집 테스트 ===\n\n";

$tender = App\Models\Tender::first();
if ($tender) {
    echo "테스트 대상 공고: " . $tender->tender_no . " - " . $tender->title . "\n\n";
    
    $service = new App\Services\AttachmentService();
    
    // 첨부파일 수집 실행
    echo "1. 첨부파일 정보 수집 중...\n";
    $count = $service->collectAttachmentsForTender($tender);
    echo "   수집된 첨부파일: " . $count . "개\n\n";
    
    // 데이터베이스에서 수집된 첨부파일 확인
    echo "2. 데이터베이스 확인:\n";
    $attachments = App\Models\Attachment::where('tender_id', $tender->id)->get();
    echo "   저장된 첨부파일: " . $attachments->count() . "개\n";
    
    foreach ($attachments as $attachment) {
        echo "   - " . $attachment->original_name . " (" . $attachment->file_type . ") - " . $attachment->download_status . "\n";
    }
    
    echo "\n3. 한글파일만 필터링:\n";
    $hwpFiles = App\Models\Attachment::where('tender_id', $tender->id)->hwpFiles()->get();
    echo "   한글파일: " . $hwpFiles->count() . "개\n";
    
    foreach ($hwpFiles as $hwp) {
        echo "   - " . $hwp->original_name . " (" . $hwp->file_extension . ")\n";
    }
    
    echo "\n4. 통계 업데이트 확인:\n";
    $stats = $service->getDownloadStats();
    foreach ($stats as $key => $value) {
        echo "   " . $key . ": " . $value . "\n";
    }
    
} else {
    echo "Tender 데이터가 없습니다.\n";
}

echo "\n=== 테스트 완료 ===\n";