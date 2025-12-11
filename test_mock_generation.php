<?php

require 'public_html/vendor/autoload.php';
$app = require_once 'public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Mock 첨부파일 생성 디버깅 ===\n\n";

$tender = App\Models\Tender::first();
if ($tender) {
    echo "Tender ID: " . $tender->id . "\n";
    echo "Tender No: " . $tender->tender_no . "\n";
    echo "Title: " . $tender->title . "\n";
    
    // metadata 확인
    echo "\nMetadata 구조:\n";
    $metadata = $tender->metadata;
    if (is_array($metadata)) {
        echo "Metadata는 배열입니다. 키 개수: " . count($metadata) . "\n";
        echo "키들: " . implode(', ', array_keys($metadata)) . "\n";
    } else {
        echo "Metadata가 배열이 아닙니다: " . gettype($metadata) . "\n";
        if (is_string($metadata)) {
            echo "Metadata 내용: " . substr($metadata, 0, 200) . "...\n";
        }
    }
    
    // AttachmentService 테스트
    $service = new App\Services\AttachmentService();
    
    // 직접 generateMockAttachments 메서드 접근을 위해 reflection 사용
    echo "\nMock 첨부파일 생성 테스트...\n";
    
    // extractAttachmentsFromTender 메서드 실행
    $attachmentData = $service->extractAttachmentsFromTender($tender);
    echo "추출된 첨부파일 개수: " . count($attachmentData) . "\n";
    
    if (!empty($attachmentData)) {
        foreach ($attachmentData as $i => $data) {
            echo "파일 " . ($i + 1) . ": " . $data['original_name'] . " (" . $data['file_type'] . ")\n";
            echo "  URL: " . $data['file_url'] . "\n";
            echo "  MIME: " . ($data['mime_type'] ?? 'none') . "\n";
        }
    }
    
} else {
    echo "Tender 데이터가 없습니다.\n";
}

echo "\n=== 디버깅 완료 ===\n";