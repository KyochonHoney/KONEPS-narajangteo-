<?php

require 'public_html/vendor/autoload.php';
$app = require_once 'public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== 파일 HWP 변환 기능 테스트 ===\n\n";

$tender = App\Models\Tender::first();
if ($tender) {
    echo "테스트 대상 공고: " . $tender->tender_no . " - " . $tender->title . "\n\n";
    
    // 기존 첨부파일 삭제 (테스트용)
    App\Models\Attachment::where('tender_id', $tender->id)->delete();
    
    $service = new App\Services\AttachmentService();
    
    // 1. 다양한 형식의 첨부파일 수집
    echo "1. 다양한 형식의 첨부파일 정보 수집...\n";
    $count = $service->collectAttachmentsForTender($tender);
    echo "   수집된 첨부파일: " . $count . "개\n\n";
    
    // 2. 수집된 파일들 확인
    echo "2. 수집된 파일 목록:\n";
    $attachments = App\Models\Attachment::where('tender_id', $tender->id)->get();
    foreach ($attachments as $attachment) {
        echo "   - " . $attachment->original_name . " (" . $attachment->file_type . ") - " . $attachment->mime_type . "\n";
    }
    
    echo "\n3. FileConverterService 기능 테스트:\n";
    $converter = new App\Services\FileConverterService();
    
    // 지원되는 형식 확인
    $supportedFormats = $converter->getSupportedFormats();
    echo "   지원되는 변환 형식: " . count($supportedFormats) . "개\n";
    
    // 각 파일별 변환 가능성 확인
    foreach ($attachments as $attachment) {
        $isConvertible = $converter->isConvertible($attachment->original_name);
        echo "   " . $attachment->original_name . " → 변환 " . ($isConvertible ? "가능" : "불가") . "\n";
    }
    
    echo "\n4. Mock 변환 테스트 (실제 다운로드 없이):\n";
    
    // PDF 파일 Mock 변환 테스트
    $pdfFile = $attachments->where('file_type', 'pdf')->first();
    if ($pdfFile) {
        try {
            // Mock 텍스트 파일로 변환 테스트
            $mockContent = "Mock PDF 내용: " . $pdfFile->original_name . "\n\n";
            $mockContent .= "PDF에서 추출된 텍스트가 여기에 표시됩니다.\n";
            $mockContent .= "이 파일은 한글(.hwp) 형식으로 변환되었습니다.\n";
            $mockContent .= "변환 일시: " . now()->format('Y-m-d H:i:s');
            
            $mockPath = 'test_conversions/' . $pdfFile->id . '_converted.hwp';
            Storage::put($mockPath, $mockContent);
            
            echo "   ✅ " . $pdfFile->original_name . " → Mock HWP 변환 성공\n";
            echo "   파일 경로: " . $mockPath . "\n";
            echo "   파일 크기: " . Storage::size($mockPath) . " bytes\n";
            
        } catch (Exception $e) {
            echo "   ❌ 변환 실패: " . $e->getMessage() . "\n";
        }
    }
    
    // Word 파일 Mock 변환 테스트
    $docxFile = $attachments->where('file_type', 'docx')->first();
    if ($docxFile) {
        try {
            $mockContent = "Mock Word 문서: " . $docxFile->original_name . "\n\n";
            $mockContent .= "Microsoft Word 문서가 한글 형식으로 변환되었습니다.\n";
            $mockContent .= "원본 문서의 내용이 여기에 표시됩니다.\n";
            $mockContent .= "변환 일시: " . now()->format('Y-m-d H:i:s');
            
            $mockPath = 'test_conversions/' . $docxFile->id . '_converted.hwp';
            Storage::put($mockPath, $mockContent);
            
            echo "   ✅ " . $docxFile->original_name . " → Mock HWP 변환 성공\n";
            echo "   파일 경로: " . $mockPath . "\n";
            echo "   파일 크기: " . Storage::size($mockPath) . " bytes\n";
            
        } catch (Exception $e) {
            echo "   ❌ 변환 실패: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n5. 통계 정보:\n";
    $stats = $service->getDownloadStats();
    foreach ($stats as $key => $value) {
        echo "   " . $key . ": " . $value . "\n";
    }
    
    $conversionStats = $converter->getConversionStats();
    echo "\n6. 변환 통계:\n";
    foreach ($conversionStats as $key => $value) {
        echo "   " . $key . ": " . $value . "\n";
    }
    
} else {
    echo "Tender 데이터가 없습니다.\n";
}

echo "\n=== 테스트 완료 ===\n";