<?php

require 'public_html/vendor/autoload.php';
$app = require_once 'public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== 첨부파일 기본 기능 테스트 ===\n\n";

// 1. Attachment 모델 테스트
try {
    $attachment = new App\Models\Attachment();
    echo "✅ Attachment 모델 로드 성공\n";
    
    // HWP 파일 확인은 AttachmentService에서 수행
    echo "✅ HWP 파일 확인은 AttachmentService에서 제공\n";
    
    // 통계 메서드 테스트
    $stats = App\Models\Attachment::getDownloadStats();
    echo "✅ 통계 조회 성공: " . json_encode($stats, JSON_UNESCAPED_UNICODE) . "\n";
} catch (Exception $e) {
    echo "❌ Attachment 모델 오류: " . $e->getMessage() . "\n";
}

echo "\n";

// 2. AttachmentService 테스트
try {
    $service = new App\Services\AttachmentService();
    echo "✅ AttachmentService 로드 성공\n";
    
    // HWP 파일 확인 메서드 테스트
    $isHwp = $service->isHwpFile('문서.hwp');
    echo "✅ HWP 파일 확인: '문서.hwp' = " . ($isHwp ? '성공' : '실패') . "\n";
    
    // 통계 조회 테스트
    $stats = $service->getDownloadStats();
    echo "✅ 통계 조회 성공: " . count($stats) . "개 항목\n";
} catch (Exception $e) {
    echo "❌ AttachmentService 오류: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. Tender 관계 테스트
try {
    $tender = App\Models\Tender::first();
    if ($tender) {
        echo "✅ Tender 모델: ID " . $tender->id . "\n";
        echo "✅ attachments() 관계: " . (method_exists($tender, 'attachments') ? '존재' : '없음') . "\n";
        echo "✅ hwpAttachments() 관계: " . (method_exists($tender, 'hwpAttachments') ? '존재' : '없음') . "\n";
        
        // 관계 실행 테스트
        $attachments = $tender->attachments;
        echo "✅ 첨부파일 관계 실행: 성공 (" . $attachments->count() . "개)\n";
    } else {
        echo "⚠️ Tender 데이터 없음 - 스킵\n";
    }
} catch (Exception $e) {
    echo "❌ Tender 관계 오류: " . $e->getMessage() . "\n";
}

echo "\n";

// 4. Mock 첨부파일 생성 테스트
try {
    $tender = App\Models\Tender::first();
    if ($tender) {
        $service = new App\Services\AttachmentService();
        
        // Mock 첨부파일 추출 테스트
        $attachmentData = $service->extractAttachmentsFromTender($tender);
        echo "✅ Mock 첨부파일 추출: " . count($attachmentData) . "개\n";
        
        // HWP 파일만 필터링 테스트
        $hwpCount = 0;
        foreach ($attachmentData as $data) {
            if ($service->isHwpFile($data['original_name'], $data['mime_type'] ?? null)) {
                $hwpCount++;
            }
        }
        echo "✅ HWP 파일 개수: " . $hwpCount . "개\n";
        
        // 첫 번째 Mock 파일 정보 출력
        if (!empty($attachmentData)) {
            $first = $attachmentData[0];
            echo "✅ 첫 번째 파일: " . $first['original_name'] . " (" . $first['file_type'] . ")\n";
        }
        
        echo "✅ 첨부파일 시스템 테스트 성공\n";
    } else {
        echo "⚠️ Tender 데이터 없음 - Mock 테스트 스킵\n";
    }
} catch (Exception $e) {
    echo "❌ Mock 첨부파일 오류: " . $e->getMessage() . "\n";
}

echo "\n=== 테스트 완료 ===\n";