<?php

require_once __DIR__ . '/public_html/vendor/autoload.php';

$app = require_once __DIR__ . '/public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\Admin\AttachmentController;
use App\Services\AttachmentService;
use App\Services\FileConverterService;
use App\Models\Tender;
use App\Models\Attachment;

echo "=== ZIP 다운로드 오류 진단 ===" . PHP_EOL;

try {
    // 1. 테스트용 공고 조회 (ID 81)
    $tender = Tender::find(81);
    if (!$tender) {
        throw new Exception('ID 81 공고를 찾을 수 없습니다.');
    }
    
    echo "테스트 공고: {$tender->tender_no} - {$tender->title}" . PHP_EOL;
    
    // 2. 해당 공고의 첨부파일 조회
    echo "\n2. 첨부파일 상태 확인..." . PHP_EOL;
    $attachments = Attachment::where('tender_id', $tender->id)->get();
    
    echo "   - 전체 첨부파일: " . $attachments->count() . "개" . PHP_EOL;
    
    foreach ($attachments as $attachment) {
        echo "     * {$attachment->original_name}" . PHP_EOL;
        echo "       상태: {$attachment->download_status}" . PHP_EOL;
        echo "       로컬 경로: " . ($attachment->local_path ?: '없음') . PHP_EOL;
        
        if ($attachment->local_path) {
            $fullPath = storage_path('app/' . $attachment->local_path);
            $exists = file_exists($fullPath);
            echo "       파일 존재: " . ($exists ? '✅' : '❌') . " ({$fullPath})" . PHP_EOL;
            
            if ($exists) {
                echo "       파일 크기: " . filesize($fullPath) . " bytes" . PHP_EOL;
            }
        }
        echo PHP_EOL;
    }
    
    // 3. completed 상태 파일 확인
    $completedFiles = Attachment::where('tender_id', $tender->id)
                                ->where('download_status', 'completed')
                                ->get();
    
    echo "3. 완료된 파일 상세 확인..." . PHP_EOL;
    echo "   - 완료된 첨부파일: " . $completedFiles->count() . "개" . PHP_EOL;
    
    if ($completedFiles->isEmpty()) {
        echo "   ⚠️  완료된 파일이 없어 ZIP 생성 불가" . PHP_EOL;
    } else {
        foreach ($completedFiles as $file) {
            if ($file->local_path && \Illuminate\Support\Facades\Storage::exists($file->local_path)) {
                echo "     ✅ {$file->original_name} - 파일 존재" . PHP_EOL;
            } else {
                echo "     ❌ {$file->original_name} - 파일 없음" . PHP_EOL;
            }
        }
    }
    
    // 4. temp 디렉토리 확인
    echo "\n4. temp 디렉토리 확인..." . PHP_EOL;
    $tempDir = storage_path('app/temp');
    
    if (!file_exists($tempDir)) {
        echo "   ⚠️  temp 디렉토리 없음: {$tempDir}" . PHP_EOL;
        echo "   🔧 temp 디렉토리 생성 중..." . PHP_EOL;
        if (mkdir($tempDir, 0755, true)) {
            echo "   ✅ temp 디렉토리 생성 성공" . PHP_EOL;
        } else {
            echo "   ❌ temp 디렉토리 생성 실패" . PHP_EOL;
        }
    } else {
        echo "   ✅ temp 디렉토리 존재: {$tempDir}" . PHP_EOL;
        echo "   권한: " . substr(sprintf('%o', fileperms($tempDir)), -4) . PHP_EOL;
    }
    
    // 5. ZipArchive 확장 확인
    echo "\n5. ZipArchive 확장 확인..." . PHP_EOL;
    if (class_exists('ZipArchive')) {
        echo "   ✅ ZipArchive 클래스 사용 가능" . PHP_EOL;
    } else {
        echo "   ❌ ZipArchive 클래스 없음 - ZIP 확장 설치 필요" . PHP_EOL;
    }
    
    // 6. 실제 ZIP 생성 테스트 (작은 규모)
    if ($completedFiles->count() > 0) {
        echo "\n6. ZIP 생성 테스트..." . PHP_EOL;
        
        try {
            $fileConverter = new FileConverterService();
            $attachmentService = new AttachmentService($fileConverter);
            $controller = new AttachmentController($attachmentService);
            
            // downloadAllHwpAsZip 메서드 직접 호출
            $response = $controller->downloadAllHwpAsZip($tender);
            echo "   ✅ ZIP 생성 성공 (Response 타입: " . get_class($response) . ")" . PHP_EOL;
            
        } catch (Exception $e) {
            echo "   ❌ ZIP 생성 실패: " . $e->getMessage() . PHP_EOL;
            echo "   스택 트레이스:" . PHP_EOL;
            echo "   " . str_replace("\n", "\n   ", $e->getTraceAsString()) . PHP_EOL;
        }
    }

} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . PHP_EOL;
    echo "스택 트레이스:" . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
}

echo PHP_EOL . "=== 진단 완료 ===" . PHP_EOL;