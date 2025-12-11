<?php

require 'public_html/vendor/autoload.php';
$app = require_once 'public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== 전체 파일 HWP 변환 통합 테스트 ===\n\n";

$tender = App\Models\Tender::first();
if ($tender) {
    echo "테스트 대상 공고: " . $tender->tender_no . " - " . $tender->title . "\n\n";
    
    // 기존 첨부파일 삭제 (테스트용)
    App\Models\Attachment::where('tender_id', $tender->id)->delete();
    
    $service = new App\Services\AttachmentService();
    
    // 1. 첨부파일 정보 수집
    echo "1. 첨부파일 정보 수집...\n";
    $count = $service->collectAttachmentsForTender($tender);
    echo "   수집된 첨부파일: " . $count . "개\n\n";
    
    // 2. 수집된 파일들 확인
    echo "2. 수집된 파일 목록:\n";
    $attachments = App\Models\Attachment::where('tender_id', $tender->id)->get();
    foreach ($attachments as $attachment) {
        echo "   - " . $attachment->original_name . " (" . $attachment->file_type . ") [" . $attachment->download_status . "]\n";
    }
    
    echo "\n3. 모든 파일을 HWP 형식으로 변환 다운로드 실행...\n";
    
    try {
        // Mock 다운로드 데이터 생성 (실제 URL에서 다운로드하는 대신)
        foreach ($attachments as $attachment) {
            if ($attachment->download_status === 'pending') {
                // Mock 원본 파일 내용 생성
                $mockFileContent = generateMockFileContent($attachment);
                
                // 원본 파일 저장
                $directory = 'attachments/' . date('Y/m/d') . '/' . $attachment->tender->tender_no;
                $originalFileName = $attachment->tender->tender_no . '_' . $attachment->original_name;
                $originalFilePath = $directory . '/' . $originalFileName;
                Storage::put($originalFilePath, $mockFileContent);
                
                echo "   📁 Mock 원본 파일 생성: " . $attachment->original_name . " (" . strlen($mockFileContent) . " bytes)\n";
            }
        }
        
        // HWP 변환 다운로드 실행
        $results = $service->downloadAllFilesAsHwp($tender);
        
        echo "\n4. 변환 결과:\n";
        echo "   전체 파일: " . $results['total'] . "개\n";
        echo "   다운로드 성공: " . $results['downloaded'] . "개\n";
        echo "   변환된 파일: " . $results['converted'] . "개\n";
        echo "   실패: " . $results['failed'] . "개\n";
        
        if (!empty($results['errors'])) {
            echo "\n   오류 목록:\n";
            foreach ($results['errors'] as $error) {
                echo "   ❌ " . $error['file'] . ": " . $error['error'] . "\n";
            }
        }
        
    } catch (Exception $e) {
        echo "   ❌ 변환 실행 실패: " . $e->getMessage() . "\n";
    }
    
    echo "\n5. 변환 후 파일 상태 확인:\n";
    $updatedAttachments = App\Models\Attachment::where('tender_id', $tender->id)->get();
    foreach ($updatedAttachments as $attachment) {
        echo "   - " . $attachment->original_name . " → " . $attachment->file_name . " [" . $attachment->download_status . "]\n";
        if ($attachment->is_downloaded && $attachment->local_path) {
            if (Storage::exists($attachment->local_path)) {
                $fileSize = Storage::size($attachment->local_path);
                echo "     파일 크기: " . $fileSize . " bytes\n";
                
                // HWP 변환된 파일 내용 미리보기
                if ($attachment->file_type === 'hwp' && $fileSize < 1000) {
                    $content = Storage::get($attachment->local_path);
                    $preview = substr($content, 0, 100);
                    echo "     내용 미리보기: " . $preview . "...\n";
                }
            } else {
                echo "     ⚠️ 파일이 존재하지 않음: " . $attachment->local_path . "\n";
            }
        }
    }
    
    echo "\n6. 최종 통계:\n";
    $finalStats = $service->getDownloadStats();
    foreach ($finalStats as $key => $value) {
        echo "   " . $key . ": " . $value . "\n";
    }
    
} else {
    echo "Tender 데이터가 없습니다.\n";
}

echo "\n=== 통합 테스트 완료 ===\n";

// Mock 파일 내용 생성 함수
function generateMockFileContent($attachment) {
    $extension = strtolower(pathinfo($attachment->original_name, PATHINFO_EXTENSION));
    
    $baseContent = "Mock 파일 내용: " . $attachment->original_name . "\n";
    $baseContent .= "파일 형식: " . $extension . "\n";
    $baseContent .= "생성 시간: " . date('Y-m-d H:i:s') . "\n\n";
    
    switch ($extension) {
        case 'pdf':
            return $baseContent . "이것은 PDF 문서의 Mock 내용입니다.\n페이지 1: 첫 번째 페이지\n페이지 2: 두 번째 페이지\n";
        case 'docx':
            return $baseContent . "Microsoft Word 문서의 Mock 내용입니다.\n제목: 사업 계획서\n내용: 상세한 사업 계획...\n";
        case 'xlsx':
            return $baseContent . "Excel 스프레드시트의 Mock 내용입니다.\nA1: 항목, B1: 값\nA2: 예산, B2: 1000만원\n";
        case 'pptx':
            return $baseContent . "PowerPoint 프레젠테이션의 Mock 내용입니다.\n슬라이드 1: 제목\n슬라이드 2: 내용\n슬라이드 3: 결론\n";
        case 'txt':
            return $baseContent . "텍스트 파일의 내용입니다.\n요구사항:\n1. 기능 A 구현\n2. 기능 B 테스트\n3. 기능 C 배포\n";
        case 'html':
            return $baseContent . "<html><head><title>회사소개</title></head><body><h1>우리 회사</h1><p>회사 소개 내용</p></body></html>\n";
        case 'hwp':
            return $baseContent . "한글 문서의 Mock 내용입니다.\n이미 HWP 형식이므로 변환이 필요하지 않습니다.\n";
        default:
            return $baseContent . "일반 파일의 Mock 내용입니다.\n";
    }
}