<?php

require_once __DIR__ . '/public_html/vendor/autoload.php';

$app = require_once __DIR__ . '/public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tender;
use App\Models\Attachment;
use App\Services\AttachmentService;
use App\Services\FileConverterService;
use App\Http\Controllers\Admin\AttachmentController;
use Illuminate\Http\Request;

echo "=== 첨부파일 기능 테스트 ===" . PHP_EOL;

try {
    // 1. 기본 서비스 인스턴스 생성 테스트
    echo "1. 서비스 인스턴스 생성 테스트..." . PHP_EOL;
    $fileConverter = new FileConverterService();
    $attachmentService = new AttachmentService($fileConverter);
    $attachmentController = new AttachmentController($attachmentService);
    echo "   ✅ 모든 서비스 인스턴스 생성 성공" . PHP_EOL;
    
    // 2. 테스트용 공고 조회
    echo "\n2. 테스트용 공고 조회..." . PHP_EOL;
    $tender = Tender::first();
    if (!$tender) {
        throw new Exception('테스트용 공고를 찾을 수 없습니다.');
    }
    echo "   ✅ 공고 조회 성공: {$tender->tender_no}" . PHP_EOL;
    
    // 3. 첨부파일 정보 추출 테스트
    echo "\n3. 첨부파일 정보 추출 테스트..." . PHP_EOL;
    $attachmentData = $attachmentService->extractAttachmentsFromTender($tender);
    echo "   ✅ 첨부파일 정보 추출 성공: " . count($attachmentData) . "개 파일" . PHP_EOL;
    
    if (count($attachmentData) > 0) {
        echo "   📄 첫 번째 파일: {$attachmentData[0]['original_name']}" . PHP_EOL;
        echo "   🔗 파일 URL: {$attachmentData[0]['file_url']}" . PHP_EOL;
    }
    
    // 4. 데이터베이스 저장 테스트
    echo "\n4. 데이터베이스 저장 테스트..." . PHP_EOL;
    $savedCount = $attachmentService->collectAttachmentsForTender($tender);
    echo "   ✅ 데이터베이스 저장 성공: {$savedCount}개 파일" . PHP_EOL;
    
    // 5. 첨부파일 목록 조회 테스트
    echo "\n5. 첨부파일 목록 조회 테스트..." . PHP_EOL;
    $attachments = Attachment::where('tender_id', $tender->id)->get();
    echo "   ✅ 첨부파일 목록 조회 성공: " . $attachments->count() . "개 파일" . PHP_EOL;
    
    foreach ($attachments as $attachment) {
        echo "     - {$attachment->original_name} (상태: {$attachment->download_status})" . PHP_EOL;
    }
    
    // 6. 컨트롤러 메서드 테스트 (Mock Request)
    echo "\n6. 컨트롤러 메서드 테스트..." . PHP_EOL;
    
    // 첨부파일 수집 테스트
    $response = $attachmentController->collect($tender);
    $responseData = $response->getData(true);
    
    if ($responseData['success']) {
        echo "   ✅ 첨부파일 수집 API 성공: {$responseData['message']}" . PHP_EOL;
    } else {
        echo "   ❌ 첨부파일 수집 API 실패: {$responseData['message']}" . PHP_EOL;
    }
    
    // 7. 라우트 존재 확인
    echo "\n7. 라우트 존재 확인..." . PHP_EOL;
    $routes = [
        'admin.attachments.collect',
        'admin.attachments.download_all_as_hwp', 
        'admin.attachments.download_hwp_zip',
        'admin.attachments.index'
    ];
    
    foreach ($routes as $routeName) {
        try {
            $routeExists = \Illuminate\Support\Facades\Route::has($routeName);
            if ($routeExists) {
                echo "   ✅ 라우트 존재: {$routeName}" . PHP_EOL;
            } else {
                echo "   ❌ 라우트 없음: {$routeName}" . PHP_EOL;
            }
        } catch (Exception $e) {
            echo "   ❌ 라우트 확인 오류: {$routeName} - {$e->getMessage()}" . PHP_EOL;
        }
    }

} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . PHP_EOL;
    echo "스택 트레이스:" . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
}

echo PHP_EOL . "=== 테스트 완료 ===" . PHP_EOL;