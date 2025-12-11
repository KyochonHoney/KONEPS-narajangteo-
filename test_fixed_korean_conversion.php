<?php
// [BEGIN nara:test_fixed_korean_conversion]

require 'public_html/vendor/autoload.php';
$app = require_once 'public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== 수정된 한글 HTML 변환 테스트 ===\n\n";

// 테스트용 Mock 파일들 생성
$testFiles = [
    'test_document.pdf' => "나라장터 과업지시서\n\n프로젝트명: 웹사이트 구축 사업\n\n주요 요구사항:\n1. PHP 또는 Java 기반 개발\n2. 반응형 웹 디자인 적용\n3. 데이터베이스 연동\n\n예산: 5억원\n기간: 6개월",
    'proposal_template.docx' => "제안서 템플릿\n\n회사명: 타이드플로\n기술스택: PHP, Laravel, Vue.js\n\n사업 경험:\n- 정부기관 웹사이트 구축 10건\n- 대기업 플랫폼 개발 경험\n- AI/ML 프로젝트 다수 수행",
    'requirements.txt' => "기술 요구사항 목록\n\n필수 기술:\n- 백엔드: PHP 8.0 이상\n- 프론트엔드: Vue.js 3.0\n- 데이터베이스: MySQL 8.0\n- 클라우드: AWS 또는 Azure\n\n추가 요구사항:\n- 모바일 앱 연동\n- 실시간 알림 기능\n- 보안 인증 (SSL)"
];

$fileConverter = new App\Services\FileConverterService();

echo "1. 테스트 파일 생성 및 변환 테스트...\n\n";

foreach ($testFiles as $fileName => $content) {
    echo "📁 테스트 파일: {$fileName}\n";
    
    // Mock 원본 파일 생성
    $tempPath = 'temp_test/' . $fileName;
    Storage::put($tempPath, $content);
    echo "   ✅ 원본 파일 생성: " . strlen($content) . " bytes\n";
    
    try {
        // 한글 HTML로 변환
        $convertedPath = $fileConverter->convertToHwp($tempPath, $fileName);
        
        if ($convertedPath && Storage::exists($convertedPath)) {
            $convertedSize = Storage::size($convertedPath);
            echo "   ✅ 변환 성공: {$convertedPath} ({$convertedSize} bytes)\n";
            
            // 변환된 HTML 내용 확인
            $htmlContent = Storage::get($convertedPath);
            
            // HTML 구조 검증
            $hasUtf8 = str_contains($htmlContent, 'charset="UTF-8"');
            $hasKoreanFont = str_contains($htmlContent, '맑은 고딕');
            $hasTitle = str_contains($htmlContent, '<title>');
            $hasContent = str_contains($htmlContent, htmlspecialchars($content));
            
            echo "   📋 HTML 구조 검증:\n";
            echo "      - UTF-8 인코딩: " . ($hasUtf8 ? "✅" : "❌") . "\n";
            echo "      - 한글 폰트 설정: " . ($hasKoreanFont ? "✅" : "❌") . "\n";
            echo "      - 제목 태그: " . ($hasTitle ? "✅" : "❌") . "\n";
            echo "      - 원본 내용 포함: " . ($hasContent ? "✅" : "❌") . "\n";
            
            // 미리보기 (첫 100자)
            $preview = substr(strip_tags($htmlContent), 0, 100);
            echo "   👀 내용 미리보기: " . trim($preview) . "...\n";
            
        } else {
            echo "   ❌ 변환 실패\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ 변환 오류: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "2. 변환 통계 확인...\n";
$stats = $fileConverter->getConversionStats();
foreach ($stats as $key => $value) {
    echo "   {$key}: {$value}\n";
}

echo "\n3. 지원 형식 확인...\n";
$supportedFormats = $fileConverter->getSupportedFormats();
echo "   지원하는 파일 형식: " . count($supportedFormats) . "개\n";
foreach (array_slice($supportedFormats, 0, 10) as $ext => $desc) {
    echo "   - .{$ext}: {$desc}\n";
}

echo "\n4. 생성된 HTML 파일 경로 확인...\n";
$convertedFiles = Storage::files('converted_korean');
if (count($convertedFiles) > 0) {
    echo "   변환된 파일 목록:\n";
    foreach (array_slice($convertedFiles, 0, 5) as $file) {
        $size = Storage::size($file);
        echo "   📄 {$file} ({$size} bytes)\n";
    }
} else {
    echo "   ⚠️  변환된 파일이 없습니다.\n";
}

// 정리
echo "\n5. 테스트 파일 정리...\n";
foreach ($testFiles as $fileName => $content) {
    $tempPath = 'temp_test/' . $fileName;
    if (Storage::exists($tempPath)) {
        Storage::delete($tempPath);
        echo "   🗑️  임시 파일 삭제: {$tempPath}\n";
    }
}

echo "\n=== 한글 HTML 변환 테스트 완료 ===\n";
echo "\n📌 주요 개선사항:\n";
echo "   - HWP → HTML 형식으로 변경 (실제로 열 수 있음)\n";
echo "   - UTF-8 인코딩으로 한글 완전 지원\n";
echo "   - 맑은 고딕 폰트 및 CSS 스타일 적용\n";
echo "   - 모든 웹 브라우저에서 열람 가능\n";
echo "   - 인쇄 최적화 스타일 포함\n";

// [END nara:test_fixed_korean_conversion]