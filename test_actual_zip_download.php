<?php

require_once __DIR__ . '/public_html/vendor/autoload.php';

$app = require_once __DIR__ . '/public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\Admin\AttachmentController;
use App\Services\AttachmentService;
use App\Services\FileConverterService;
use App\Models\Tender;
use App\Models\Attachment;

echo "=== 실제 ZIP 파일 생성 및 저장 테스트 ===" . PHP_EOL;

try {
    // 테스트용 공고 조회 (ID 81)
    $tender = Tender::find(81);
    if (!$tender) {
        throw new Exception('ID 81 공고를 찾을 수 없습니다.');
    }
    
    echo "테스트 공고: {$tender->tender_no}" . PHP_EOL;
    
    // 완료된 첨부파일 확인
    $completedFiles = Attachment::where('tender_id', $tender->id)
                                ->where('download_status', 'completed')
                                ->get();
    
    if ($completedFiles->isEmpty()) {
        echo "❌ 완료된 첨부파일이 없습니다." . PHP_EOL;
        exit;
    }
    
    echo "완료된 파일: " . $completedFiles->count() . "개" . PHP_EOL;
    
    // ZIP 파일 수동 생성
    $zipFileName = 'hwp_files_' . $tender->tender_no . '_' . date('YmdHis') . '.zip';
    $zipPath = storage_path('app/temp/' . $zipFileName);
    
    echo "ZIP 파일 경로: {$zipPath}" . PHP_EOL;
    
    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
        throw new Exception('ZIP 파일을 생성할 수 없습니다.');
    }
    
    $addedFiles = 0;
    foreach ($completedFiles as $attachment) {
        if (\Illuminate\Support\Facades\Storage::exists($attachment->local_path)) {
            $fileContent = \Illuminate\Support\Facades\Storage::get($attachment->local_path);
            $fileName = $attachment->file_name ?: $attachment->original_name;
            
            echo "파일 추가 중: {$fileName} (" . strlen($fileContent) . " bytes)" . PHP_EOL;
            
            // ZIP에 파일 추가
            $zip->addFromString($fileName, $fileContent);
            $addedFiles++;
        } else {
            echo "⚠️  파일 없음: {$attachment->original_name} ({$attachment->local_path})" . PHP_EOL;
        }
    }
    
    $zip->close();
    
    if ($addedFiles === 0) {
        echo "❌ 추가된 파일이 없습니다." . PHP_EOL;
        if (file_exists($zipPath)) {
            unlink($zipPath);
        }
    } else {
        echo "✅ ZIP 파일 생성 완료!" . PHP_EOL;
        echo "   - 파일 수: {$addedFiles}개" . PHP_EOL;
        echo "   - ZIP 크기: " . filesize($zipPath) . " bytes" . PHP_EOL;
        echo "   - 저장 위치: {$zipPath}" . PHP_EOL;
        
        // 다운로드 가능한 URL 생성
        echo "\n🔗 다운로드 URL 테스트:" . PHP_EOL;
        echo "   https://nara.tideflo.work/admin/attachments/download-hwp-zip/{$tender->id}" . PHP_EOL;
        
        // 컨트롤러를 통한 실제 테스트
        echo "\n🧪 컨트롤러 테스트:" . PHP_EOL;
        try {
            $fileConverter = new FileConverterService();
            $attachmentService = new AttachmentService($fileConverter);
            $controller = new AttachmentController($attachmentService);
            
            $response = $controller->downloadAllHwpAsZip($tender);
            echo "   ✅ 컨트롤러 응답 성공 - 타입: " . get_class($response) . PHP_EOL;
            
            // 임시로 생성한 ZIP 파일 정리
            if (file_exists($zipPath)) {
                unlink($zipPath);
                echo "   🗑️  임시 ZIP 파일 삭제됨" . PHP_EOL;
            }
            
        } catch (Exception $e) {
            echo "   ❌ 컨트롤러 오류: " . $e->getMessage() . PHP_EOL;
        }
    }

} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== 테스트 완료 ===" . PHP_EOL;