<?php

require 'public_html/vendor/autoload.php';
$app = require_once 'public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tender;
use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

echo "=== ZIP 다운로드 기능 테스트 ===\n\n";

$tender = Tender::first();
if (!$tender) {
    echo "Tender 데이터가 없습니다.\n";
    exit;
}

echo "테스트 대상 공고: " . $tender->tender_no . " - " . $tender->title . "\n\n";

// 완료된 첨부파일 조회
$attachments = Attachment::where('tender_id', $tender->id)
                        ->where('download_status', 'completed')
                        ->get();

echo "1. 변환 완료된 첨부파일: " . $attachments->count() . "개\n";

foreach ($attachments as $attachment) {
    echo "   - " . $attachment->file_name . " (원본: " . $attachment->original_name . ")\n";
    echo "     경로: " . $attachment->local_path . "\n";
    if (Storage::exists($attachment->local_path)) {
        $size = Storage::size($attachment->local_path);
        echo "     크기: " . $size . " bytes ✓\n";
    } else {
        echo "     ❌ 파일이 존재하지 않음\n";
    }
    echo "\n";
}

if ($attachments->isEmpty()) {
    echo "❌ 다운로드할 파일이 없습니다.\n";
    exit;
}

// ZIP 파일 생성 테스트
echo "2. ZIP 파일 생성 테스트\n";

$zipFileName = 'hwp_files_' . $tender->tender_no . '_' . date('YmdHis') . '.zip';
$zipPath = storage_path('app/temp/' . $zipFileName);

// temp 디렉토리 생성
if (!file_exists(dirname($zipPath))) {
    mkdir(dirname($zipPath), 0755, true);
}

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
    echo "❌ ZIP 파일을 생성할 수 없습니다.\n";
    exit;
}

$addedFiles = 0;
foreach ($attachments as $attachment) {
    if (Storage::exists($attachment->local_path)) {
        $fileContent = Storage::get($attachment->local_path);
        $fileName = $attachment->file_name ?: $attachment->original_name;
        
        // ZIP에 파일 추가
        $zip->addFromString($fileName, $fileContent);
        $addedFiles++;
        echo "   📁 추가됨: " . $fileName . " (" . strlen($fileContent) . " bytes)\n";
    }
}

$zip->close();

echo "\n3. ZIP 파일 정보\n";
echo "   파일명: " . $zipFileName . "\n";
echo "   경로: " . $zipPath . "\n";
echo "   추가된 파일: " . $addedFiles . "개\n";

if (file_exists($zipPath)) {
    $zipSize = filesize($zipPath);
    echo "   ZIP 크기: " . $zipSize . " bytes\n";
    echo "   ✅ ZIP 파일 생성 성공\n";
    
    // ZIP 파일 내용 확인
    $zip = new ZipArchive();
    if ($zip->open($zipPath) === TRUE) {
        echo "\n4. ZIP 파일 내용:\n";
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $fileInfo = $zip->statIndex($i);
            echo "   - " . $fileInfo['name'] . " (" . $fileInfo['size'] . " bytes)\n";
        }
        $zip->close();
    }
} else {
    echo "   ❌ ZIP 파일이 생성되지 않았습니다.\n";
}

echo "\n=== 테스트 완료 ===\n";

// 테스트용 ZIP 파일 정리
if (file_exists($zipPath)) {
    unlink($zipPath);
    echo "테스트 ZIP 파일 삭제됨\n";
}