<?php

// Laravel 환경 설정
require_once __DIR__ . '/public_html/bootstrap/app.php';
$app = require_once __DIR__ . '/public_html/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== 실제 OpenAI 기반 제안서 생성 테스트 ===" . PHP_EOL;
echo "" . PHP_EOL;

// 테스트할 공고 선택
$tender = App\Models\Tender::where('tender_no', 'R25BK01034597')->first();
if (!$tender) {
    echo "공고 데이터를 찾을 수 없습니다." . PHP_EOL;
    exit;
}

echo "공고 정보: " . $tender->title . PHP_EOL;
echo "" . PHP_EOL;

// 회사 프로필 생성 (테스트용)
$companyProfile = App\Models\CompanyProfile::first();
if (!$companyProfile) {
    echo "회사 프로필 데이터를 찾을 수 없습니다." . PHP_EOL;
    exit;
}

echo "회사 프로필: " . $companyProfile->company_name . PHP_EOL;
echo "" . PHP_EOL;

// 동적 제안서 생성기 테스트
try {
    $generator = new App\Services\DynamicProposalGenerator(
        app(App\Services\AiApiService::class),
        app(App\Services\ProposalStructureAnalyzer::class)
    );
    
    echo "동적 제안서 생성 시작..." . PHP_EOL;
    echo "" . PHP_EOL;
    
    $result = $generator->generateDynamicProposal($tender, $companyProfile, []);
    
    echo "생성 완료!" . PHP_EOL;
    echo "" . PHP_EOL;
    echo "제목: " . $result['title'] . PHP_EOL;
    echo "섹션 수: " . $result['sections_generated'] . PHP_EOL;
    echo "내용 길이: " . $result['content_length'] . " 문자" . PHP_EOL;
    echo "신뢰도: " . $result['confidence_score'] . "%" . PHP_EOL;
    echo "품질 평가: " . $result['generation_quality'] . PHP_EOL;
    echo "동적 생성 여부: " . ($result['is_dynamic_generated'] ? '예' : '아니오') . PHP_EOL;
    echo "" . PHP_EOL;
    
    // 처음 500자 미리보기
    echo "=== 제안서 내용 미리보기 (처음 500자) ===" . PHP_EOL;
    echo substr($result['content'], 0, 500) . "..." . PHP_EOL;
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    echo "" . PHP_EOL;
    echo "Stack trace:" . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}