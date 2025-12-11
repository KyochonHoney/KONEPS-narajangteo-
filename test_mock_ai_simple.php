<?php

require 'public_html/vendor/autoload.php';
$app = require_once 'public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Mock AI 간단 테스트 ===\n\n";

// 1. 관리자 사용자 확인 및 생성
echo "1. 관리자 사용자 확인...\n";

$adminUser = App\Models\User::where('email', 'admin@tideflo.work')->first();

if (!$adminUser) {
    echo "   ⚠️  관리자 사용자가 없습니다. 생성 중...\n";
    
    $adminUser = App\Models\User::create([
        'name' => 'Mock Test Admin',
        'email' => 'admin@tideflo.work', 
        'email_verified_at' => now(),
        'password' => bcrypt('password'),
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    // 관리자 역할 부여 (role 테이블에 데이터가 있다면)
    try {
        $adminUser->assignRole('admin');
        echo "   ✅ 관리자 사용자 생성 완료 (역할 부여됨)\n";
    } catch (Exception $e) {
        echo "   ✅ 관리자 사용자 생성 완료 (역할 부여 건너뜀)\n";
    }
} else {
    echo "   ✅ 관리자 사용자 존재: " . $adminUser->name . "\n";
}

// 2. Mock AI API 직접 테스트
echo "\n2. Mock AI API 직접 테스트...\n";

try {
    $aiApiService = new App\Services\AiApiService();
    
    // 테스트 데이터
    $tenderData = [
        'tender_no' => 'TEST001',
        'title' => '웹사이트 구축 프로젝트',
        'ntce_instt_nm' => '테스트 기관',
        'budget' => '5억원',
        'ntce_cont' => 'PHP Laravel 기반 웹사이트 개발'
    ];
    
    $companyProfile = [
        'id' => 1,
        'company_name' => '타이드플로',
        'tech_stack' => ['PHP', 'Laravel', 'MySQL', 'JavaScript'],
        'specialties' => ['웹 개발', '시스템 구축']
    ];
    
    echo "   🤖 Mock AI 분석 실행 중...\n";
    $startTime = microtime(true);
    
    $aiResult = $aiApiService->analyzeTender($tenderData, $companyProfile, []);
    
    $endTime = microtime(true);
    $processingTime = round(($endTime - $startTime) * 1000);
    
    echo "   ✅ Mock AI 분석 완료 (처리시간: {$processingTime}ms)\n";
    echo "   📊 호환성 점수: " . $aiResult['compatibility_score'] . "점\n";
    echo "   🔧 기술 매칭 점수: " . $aiResult['technical_match_score'] . "점\n";
    echo "   💼 비즈니스 점수: " . $aiResult['business_match_score'] . "점\n";
    echo "   🎯 성공 확률: " . $aiResult['success_probability'] . "%\n";
    echo "   💡 추천 의견: " . substr($aiResult['recommendation'], 0, 60) . "...\n";
    
    echo "   🔗 매칭 기술: " . implode(', ', $aiResult['matching_technologies']) . "\n";
    if (!empty($aiResult['missing_technologies'])) {
        echo "   ⚠️  부족 기술: " . implode(', ', $aiResult['missing_technologies']) . "\n";
    }
    
    echo "   🧪 Mock 여부: " . ($aiResult['is_mock'] ? 'Yes' : 'No') . "\n";
    echo "   📝 분석 기준: " . $aiResult['mock_analysis_basis'] . "\n";
    
} catch (Exception $e) {
    echo "   ❌ Mock AI API 테스트 실패: " . $e->getMessage() . "\n";
}

// 3. 실제 공고로 완전한 Mock AI 분석 테스트
echo "\n3. 실제 공고 완전한 분석 테스트...\n";

$tender = App\Models\Tender::first();

if ($tender) {
    echo "   📋 테스트 공고: " . $tender->tender_no . "\n";
    echo "   📝 제목: " . substr($tender->title, 0, 50) . "...\n";
    
    try {
        $aiApiService = new App\Services\AiApiService();
        $analysisService = new App\Services\TenderAnalysisService($aiApiService);
        
        echo "   🤖 Mock AI 완전 분석 실행 중...\n";
        $startTime = microtime(true);
        
        $analysis = $analysisService->analyzeTender($tender, $adminUser);
        
        $endTime = microtime(true);
        $processingTime = round(($endTime - $startTime) * 1000);
        
        echo "   ✅ 완전 분석 완료 (처리시간: {$processingTime}ms)\n";
        echo "   📊 종합 점수: " . $analysis->total_score . "점\n";
        echo "   🔧 기술 점수: " . $analysis->technical_score . "점\n";
        echo "   💼 사업 점수: " . $analysis->experience_score . "점\n";
        echo "   📈 상태: " . $analysis->status . "\n";
        echo "   🆔 분석 ID: " . $analysis->id . "\n";
        
        // 분석 데이터 확인
        $analysisData = is_string($analysis->analysis_data) ? 
            json_decode($analysis->analysis_data, true) : 
            $analysis->analysis_data;
            
        if (isset($analysisData['is_ai_analysis']) && $analysisData['is_ai_analysis']) {
            echo "   🤖 AI 분석 모드: " . ($analysisData['ai_model'] ?? 'N/A') . "\n";
            
            if (isset($analysisData['ai_analysis']['is_mock'])) {
                echo "   🧪 Mock AI 사용: " . ($analysisData['ai_analysis']['is_mock'] ? 'Yes' : 'No') . "\n";
            }
        }
        
    } catch (Exception $e) {
        echo "   ❌ 완전 분석 실패: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "   ❌ 테스트할 공고 데이터가 없습니다\n";
}

// 4. 웹 UI 접근 테스트 안내
echo "\n4. 웹 UI 테스트 준비 완료!\n";
echo "   🌐 URL: https://nara.tideflo.work\n";
echo "   👤 테스트 계정: admin@tideflo.work / password\n";
echo "   📝 단계:\n";
echo "     1. 위 URL로 접속\n";
echo "     2. 테스트 계정으로 로그인\n";
echo "     3. '공고 관리' 메뉴 클릭\n";
echo "     4. 임의 공고 선택 → 'AI 분석' 버튼 클릭\n";
echo "     5. Mock AI 분석 결과 확인\n";

echo "\n=== Mock AI 간단 테스트 완료 ===\n";

echo "\n💡 Mock AI 특징:\n";
echo "- 실제 API 키 없이 AI 분석 시뮬레이션\n";
echo "- 프로젝트 내용에 따라 다른 분석 결과\n";
echo "- 매번 약간씩 다른 점수 (랜덤 변동)\n";
echo "- 상세한 기술스택 분석 및 추천 의견\n";
echo "- 웹 UI에서 즉시 테스트 가능\n\n";

echo "🔄 실제 AI로 전환:\n";
echo ".env → AI_ANALYSIS_PROVIDER=openai + OPENAI_API_KEY 설정\n";