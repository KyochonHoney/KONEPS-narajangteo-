<?php

require 'public_html/vendor/autoload.php';
$app = require_once 'public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Phase 4: AI 제안서 자동생성 시스템 테스트 ===\n\n";

// 1. 환경 확인
echo "1. 환경 설정 확인...\n";
$aiProvider = config('ai.analysis.provider', 'unknown');
echo "   AI 프로바이더: {$aiProvider}\n";

if ($aiProvider !== 'mock') {
    echo "   ⚠️  Mock 모드가 아닙니다. Mock 테스트를 위해 AI_ANALYSIS_PROVIDER=mock으로 설정 권장\n";
}

// 2. 테스트 사용자 확인
echo "\n2. 테스트 사용자 확인...\n";
$testUser = App\Models\User::where('email', 'admin@tideflo.work')->first();

if (!$testUser) {
    echo "   ⚠️  테스트 사용자가 없습니다. 생성 중...\n";
    $testUser = App\Models\User::create([
        'name' => 'Proposal Test Admin',
        'email' => 'admin@tideflo.work',
        'email_verified_at' => now(),
        'password' => bcrypt('password'),
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "   ✅ 테스트 사용자 생성 완료\n";
} else {
    echo "   ✅ 테스트 사용자 존재: " . $testUser->name . "\n";
}

// 3. 테스트 공고 선택
echo "\n3. 테스트 공고 선택...\n";
$tender = App\Models\Tender::first();

if (!$tender) {
    echo "   ❌ 테스트할 공고 데이터가 없습니다\n";
    exit(1);
}

echo "   📋 선택된 공고: {$tender->tender_no}\n";
echo "   📝 제목: " . substr($tender->title, 0, 60) . "...\n";
echo "   🏢 발주기관: {$tender->ntce_instt_nm}\n";

// 4. AI API 서비스 테스트
echo "\n4. AI API 서비스 기능 테스트...\n";

try {
    $aiApiService = new App\Services\AiApiService();
    
    // 4-1. 제안서 구조 분석 테스트
    echo "   🔍 제안서 구조 분석 테스트...\n";
    $startTime = microtime(true);
    
    $tenderData = [
        'tender_no' => $tender->tender_no,
        'title' => $tender->title,
        'ntce_instt_nm' => $tender->ntce_instt_nm,
        'ntce_cont' => $tender->content ?? $tender->summary
    ];
    
    $structureResult = $aiApiService->analyzeProposalStructure($tenderData);
    $structureTime = round((microtime(true) - $startTime) * 1000);
    
    echo "   ✅ 구조 분석 완료 (처리시간: {$structureTime}ms)\n";
    echo "   📊 섹션 수: " . count($structureResult['sections'] ?? []) . "개\n";
    echo "   📄 예상 페이지: " . ($structureResult['estimated_pages'] ?? 'N/A') . "페이지\n";
    echo "   🔧 복잡도: " . ($structureResult['structure_complexity'] ?? 'N/A') . "\n";
    
    // 4-2. 제안서 생성 테스트
    echo "\n   📝 제안서 생성 테스트...\n";
    $startTime = microtime(true);
    
    $companyProfile = App\Models\CompanyProfile::getTideFloProfile();
    $companyProfileData = [
        'id' => $companyProfile->id,
        'company_name' => $companyProfile->name,
        'tech_stack' => array_keys($companyProfile->technical_keywords),
        'specialties' => $companyProfile->business_areas
    ];
    
    $proposalResult = $aiApiService->generateProposal($tenderData, $companyProfileData, $structureResult);
    $proposalTime = round((microtime(true) - $startTime) * 1000);
    
    echo "   ✅ 제안서 생성 완료 (처리시간: {$proposalTime}ms)\n";
    echo "   📝 제목: " . ($proposalResult['title'] ?? 'N/A') . "\n";
    echo "   📄 내용 길이: " . strlen($proposalResult['content'] ?? '') . "자\n";
    echo "   📊 신뢰도: " . ($proposalResult['confidence_score'] ?? 'N/A') . "점\n";
    echo "   🎯 품질: " . ($proposalResult['generation_quality'] ?? 'N/A') . "\n";
    
} catch (Exception $e) {
    echo "   ❌ AI API 서비스 테스트 실패: " . $e->getMessage() . "\n";
}

// 5. 완전한 제안서 생성 서비스 테스트
echo "\n5. 완전한 제안서 생성 서비스 테스트...\n";

try {
    $aiApiService = new App\Services\AiApiService();
    $proposalService = new App\Services\ProposalGeneratorService($aiApiService);
    
    echo "   🤖 제안서 생성 서비스 실행 중...\n";
    $startTime = microtime(true);
    
    $proposal = $proposalService->generateProposal($tender, $testUser);
    
    $endTime = microtime(true);
    $totalTime = round(($endTime - $startTime) * 1000);
    
    echo "   ✅ 제안서 생성 서비스 완료 (총 처리시간: {$totalTime}ms)\n";
    echo "   🆔 제안서 ID: {$proposal->id}\n";
    echo "   📝 제목: {$proposal->title}\n";
    echo "   📊 상태: {$proposal->status}\n";
    echo "   ⏱️  처리시간: {$proposal->formatted_processing_time}\n";
    echo "   📄 내용 길이: " . strlen($proposal->content) . "자\n";
    
    // AI 분석 데이터 확인
    $aiData = $proposal->ai_analysis_data;
    if (isset($aiData['generation_quality'])) {
        echo "   🎯 생성 품질: " . $aiData['generation_quality'] . "\n";
        echo "   📊 신뢰도: " . ($aiData['confidence_score'] ?? 'N/A') . "점\n";
        echo "   📑 섹션 수: " . ($aiData['sections_count'] ?? 'N/A') . "개\n";
    }
    
    // 제안서 내용 미리보기
    echo "\n   📖 제안서 내용 미리보기 (첫 300자):\n";
    echo "   " . str_repeat('-', 60) . "\n";
    $preview = substr(strip_tags($proposal->content), 0, 300);
    echo "   " . str_replace("\n", "\n   ", $preview) . "...\n";
    echo "   " . str_repeat('-', 60) . "\n";
    
} catch (Exception $e) {
    echo "   ❌ 제안서 생성 서비스 실패: " . $e->getMessage() . "\n";
}

// 6. 다양한 공고 유형 테스트
echo "\n6. 다양한 공고 유형별 제안서 생성 테스트...\n";

$testTenders = App\Models\Tender::take(3)->get();

foreach ($testTenders as $index => $testTender) {
    echo "\n   📋 공고 " . ($index + 1) . ": {$testTender->tender_no}\n";
    echo "     제목: " . substr($testTender->title, 0, 50) . "...\n";
    
    try {
        $startTime = microtime(true);
        $proposal = $proposalService->generateProposal($testTender, $testUser);
        $processingTime = round((microtime(true) - $startTime) * 1000);
        
        echo "     ✅ 생성 완료 (처리시간: {$processingTime}ms)\n";
        echo "     📊 상태: {$proposal->status}\n";
        echo "     🎯 품질: " . ($proposal->ai_analysis_data['generation_quality'] ?? 'N/A') . "\n";
        
    } catch (Exception $e) {
        echo "     ❌ 생성 실패: " . $e->getMessage() . "\n";
    }
}

// 7. 제안서 통계
echo "\n7. 제안서 생성 통계...\n";

try {
    $stats = $proposalService->getGenerationStats();
    echo "   📊 총 생성 제안서: " . $stats['total_generated'] . "개\n";
    echo "   ⏱️  평균 처리시간: " . round($stats['avg_processing_time']) . "ms\n";
    echo "   📈 성공률: " . $stats['success_rate'] . "%\n";
    echo "   📅 오늘 생성: " . $stats['today_generated'] . "개\n";
    
    $statusCounts = App\Models\Proposal::getStatusCounts();
    echo "   📋 상태별 현황:\n";
    echo "     - 처리중: " . $statusCounts['processing'] . "개\n";
    echo "     - 완료: " . $statusCounts['completed'] . "개\n";
    echo "     - 실패: " . $statusCounts['failed'] . "개\n";
    
} catch (Exception $e) {
    echo "   ⚠️  통계 조회 실패: " . $e->getMessage() . "\n";
}

echo "\n=== Phase 4 테스트 완료 ===\n";

// 8. 웹 UI 테스트 안내
echo "\n🌐 웹 UI에서 제안서 생성 테스트하기:\n";
echo "1. https://nara.tideflo.work/login 접속\n";
echo "2. 관리자 계정으로 로그인 (admin@tideflo.work / password)\n";
echo "3. 메뉴에서 '제안서 관리' 클릭\n";
echo "4. '새 제안서 생성' 버튼 클릭\n";
echo "5. 공고 선택 후 생성 실행\n";
echo "6. 생성 완료된 제안서 확인 및 다운로드\n\n";

echo "💡 주요 특징:\n";
echo "- AI 기반 제안서 구조 자동 분석\n";
echo "- 공고 내용에 맞춘 맞춤형 제안서 생성\n";
echo "- 타이드플로 회사 정보 자동 반영\n";
echo "- 마크다운 형식으로 생성 및 다운로드 지원\n";
echo "- Mock AI와 실제 AI API 모두 지원\n\n";

echo "🔄 실제 AI로 전환하려면:\n";
echo ".env 파일에서 AI_ANALYSIS_PROVIDER=claude + CLAUDE_API_KEY 설정\n";