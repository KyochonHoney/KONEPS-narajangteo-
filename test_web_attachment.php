<?php

require_once __DIR__ . '/public_html/vendor/autoload.php';

$app = require_once __DIR__ . '/public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\Admin\AttachmentController;
use App\Services\AttachmentService;
use App\Services\FileConverterService;
use App\Models\Tender;
use Illuminate\Http\Request;

echo "=== 웹 첨부파일 기능 테스트 ===" . PHP_EOL;

try {
    // 테스트용 공고 조회 (ID 81로 가정)
    $tender = Tender::find(81) ?: Tender::first();
    if (!$tender) {
        throw new Exception('테스트용 공고를 찾을 수 없습니다.');
    }
    
    echo "테스트 공고: {$tender->tender_no} - {$tender->title}" . PHP_EOL;
    
    // AttachmentController 인스턴스 생성
    $fileConverter = new FileConverterService();
    $attachmentService = new AttachmentService($fileConverter);
    $attachmentController = new AttachmentController($attachmentService);
    
    // 1. 첨부파일 수집 테스트 (POST /admin/attachments/collect/{tender})
    echo "\n1. 첨부파일 수집 테스트..." . PHP_EOL;
    $collectResponse = $attachmentController->collect($tender);
    $collectData = $collectResponse->getData(true);
    
    if ($collectData['success']) {
        echo "   ✅ 수집 성공: {$collectData['message']}" . PHP_EOL;
    } else {
        echo "   ❌ 수집 실패: {$collectData['message']}" . PHP_EOL;
    }
    
    // 2. 첨부파일 목록 조회 테스트 (GET /admin/attachments?tender_id=XX)
    echo "\n2. 첨부파일 목록 조회 테스트..." . PHP_EOL;
    $request = new Request();
    $request->merge([
        'tender_id' => $tender->id,
        'ajax' => true
    ]);
    
    $indexResponse = $attachmentController->index($request);
    $indexData = $indexResponse->getData(true);
    
    if (isset($indexData['attachments'])) {
        $attachments = $indexData['attachments']['data'];
        echo "   ✅ 목록 조회 성공: " . count($attachments) . "개 파일" . PHP_EOL;
        
        foreach ($attachments as $attachment) {
            echo "     - {$attachment['original_name']} ({$attachment['download_status']})" . PHP_EOL;
        }
    } else {
        echo "   ❌ 목록 조회 실패" . PHP_EOL;
    }
    
    // 3. 모든 파일을 한글로 변환 테스트 (POST /admin/attachments/download-all-as-hwp/{tender})
    echo "\n3. 모든 파일 한글 변환 테스트..." . PHP_EOL;
    try {
        $convertResponse = $attachmentController->downloadAllFilesAsHwp($tender);
        $convertData = $convertResponse->getData(true);
        
        if ($convertData['success']) {
            echo "   ✅ 변환 성공: {$convertData['message']}" . PHP_EOL;
        } else {
            echo "   ❌ 변환 실패: {$convertData['message']}" . PHP_EOL;
        }
    } catch (Exception $e) {
        echo "   ⚠️  변환 시도 중 예외: " . $e->getMessage() . PHP_EOL;
    }
    
    // 4. ZIP 다운로드 테스트 (GET /admin/attachments/download-hwp-zip/{tender})
    echo "\n4. ZIP 다운로드 준비 확인..." . PHP_EOL;
    $completedAttachments = App\Models\Attachment::where('tender_id', $tender->id)
                                                ->where('download_status', 'completed')
                                                ->count();
    echo "   📦 완료된 첨부파일: {$completedAttachments}개" . PHP_EOL;
    
    if ($completedAttachments > 0) {
        echo "   ✅ ZIP 다운로드 가능" . PHP_EOL;
    } else {
        echo "   ⏳ ZIP 다운로드 대기 (파일 변환 완료 후 가능)" . PHP_EOL;
    }

} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . PHP_EOL;
    echo "스택 트레이스:" . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
}

echo PHP_EOL . "=== 테스트 완료 ===" . PHP_EOL;