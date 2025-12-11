<?php

require 'public_html/vendor/autoload.php';
$app = require_once 'public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Mock AI 분석 실제 테스트 ===\n\n";

// 1. Mock 모드 확인
echo "1. Mock AI 모드 확인...\n";
$provider = config('ai.analysis.provider', 'unknown');
echo "   현재 AI 프로바이더: {$provider}\n";

if ($provider === 'mock') {
    echo "   ✅ Mock AI 모드 활성화됨\n";
} else {
    echo "   ⚠️  Mock 모드가 아닙니다. .env에서 AI_ANALYSIS_PROVIDER=mock으로 설정해주세요\n";
    exit;
}

// 2. Mock AI API 연결 테스트
echo "\n2. Mock AI API 연결 테스트...\n";

try {
    $aiApiService = new App\Services\AiApiService();
    $connectionResult = $aiApiService->checkConnection();
    
    echo "   ✅ 연결 상태: " . $connectionResult['status'] . "\n";
    echo "   🤖 AI 프로바이더: " . $connectionResult['provider'] . "\n";
    echo "   💬 응답 메시지: " . $connectionResult['test_result']['message'] . "\n";
    
} catch (Exception $e) {
    echo "   ❌ 연결 테스트 실패: " . $e->getMessage() . "\n";
    exit;
}

// 3. 실제 공고 데이터로 Mock AI 분석 테스트
echo "\n3. 실제 공고 Mock AI 분석 테스트...\n";

$tenders = App\Models\Tender::take(3)->get();

if ($tenders->count() == 0) {
    echo "   ❌ 테스트할 공고 데이터가 없습니다\n";
    exit;
}

foreach ($tenders as $index => $tender) {
    echo "\n   📋 공고 " . ($index + 1) . ": {$tender->tender_no}\n";
    echo "     제목: " . substr($tender->title, 0, 60) . "...\n";
    echo "     발주기관: " . $tender->ntce_instt_nm . "\n";
    
    try {
        $startTime = microtime(true);
        
        // 테스트 사용자 생성 (관리자 계정 사용)
        $testUser = App\Models\User::where('email', 'admin@tideflo.work')->first();
        
        // Mock AI 분석 실행
        $aiApiService = new App\Services\AiApiService();
        $analysisService = new App\Services\TenderAnalysisService($aiApiService);
        
        echo "     🤖 Mock AI 분석 실행 중...\n";
        $analysis = $analysisService->analyzeTender($tender, $testUser);
        
        $endTime = microtime(true);
        $processingTime = round(($endTime - $startTime) * 1000);
        
        echo "     ✅ 분석 완료 (처리시간: {$processingTime}ms)\n";
        echo "     📊 종합 점수: " . $analysis->total_score . "점\n";
        echo "     🔧 기술 점수: " . $analysis->technical_score . "점\n";
        echo "     💼 사업 점수: " . $analysis->experience_score . "점\n";
        
        // 분석 데이터에서 AI 정보 추출
        $analysisData = json_decode($analysis->analysis_data, true);
        
        if (isset($analysisData['ai_analysis'])) {
            $aiAnalysis = $analysisData['ai_analysis'];
            
            echo "     🎯 호환성 점수: " . ($aiAnalysis['compatibility_score'] ?? 'N/A') . "점\n";
            echo "     📈 성공 확률: " . ($aiAnalysis['success_probability'] ?? 'N/A') . "%\n";
            echo "     💡 추천 의견: " . substr($aiAnalysis['recommendation'] ?? '', 0, 50) . "...\n";
            
            if (isset($aiAnalysis['matching_technologies'])) {
                echo "     🔗 매칭 기술: " . implode(', ', $aiAnalysis['matching_technologies']) . "\n";
            }
            
            if (isset($aiAnalysis['is_mock']) && $aiAnalysis['is_mock']) {
                echo "     🧪 Mock 분석 기준: " . ($aiAnalysis['mock_analysis_basis'] ?? 'N/A') . "\n";
            }
        }
        
        echo "     🏷️  분석 상태: " . $analysis->status . "\n";
        
    } catch (Exception $e) {
        echo "     ❌ 분석 실패: " . $e->getMessage() . "\n";
    }
    
    echo "\n     " . str_repeat('-', 60) . "\n";
}

// 4. Mock AI 다양성 테스트 (여러 번 실행해서 결과 다른지 확인)
echo "\n4. Mock AI 결과 다양성 테스트...\n";

$testTender = $tenders->first();
echo "   테스트 공고: " . substr($testTender->title, 0, 40) . "...\n";

$scores = [];
for ($i = 1; $i <= 5; $i++) {
    try {
        $testUser = App\Models\User::where('email', 'admin@tideflo.work')->first();
        $aiApiService = new App\Services\AiApiService();
        $analysisService = new App\Services\TenderAnalysisService($aiApiService);
        $analysis = $analysisService->analyzeTender($testTender, $testUser);
        
        $scores[] = $analysis->total_score;
        echo "   실행 {$i}: {$analysis->total_score}점\n";
        
    } catch (Exception $e) {
        echo "   실행 {$i}: 실패 - " . $e->getMessage() . "\n";
    }
}

if (count($scores) > 1) {
    $uniqueScores = array_unique($scores);
    if (count($uniqueScores) > 1) {
        echo "   ✅ Mock AI 결과 다양성 확인: " . count($uniqueScores) . "가지 다른 점수\n";
        echo "   📊 점수 범위: " . min($scores) . "점 ~ " . max($scores) . "점\n";
    } else {
        echo "   ⚠️  모든 결과가 동일함 (개선 필요)\n";
    }
}

// 5. Mock AI 사용량 통계
echo "\n5. Mock AI 사용량 통계...\n";

try {
    $stats = $aiApiService->getUsageStats();
    echo "   📈 일일 요청: " . $stats['daily_requests'] . "회\n";
    echo "   📈 월간 요청: " . $stats['monthly_requests'] . "회\n";
    echo "   💾 캐시 적중률: " . $stats['cache_hit_rate'] . "%\n";
    
} catch (Exception $e) {
    echo "   ⚠️  통계 조회 실패: " . $e->getMessage() . "\n";
}

echo "\n=== Mock AI 테스트 완료 ===\n";

// 6. 웹 UI에서 테스트 안내
echo "\n🌐 웹 UI에서 테스트하기:\n";
echo "1. https://nara.tideflo.work/login 접속\n";
echo "2. 관리자 계정으로 로그인\n";
echo "3. '공고 관리' → 임의 공고 선택 → 'AI 분석' 버튼 클릭\n";
echo "4. Mock AI 분석 결과 확인\n\n";

echo "💡 주요 특징:\n";
echo "- 실제 API 호출 없이 AI 분석 시뮬레이션\n";
echo "- 프로젝트 유형별로 다른 분석 결과 생성\n";
echo "- 매번 약간씩 다른 점수로 현실적인 변동성 구현\n";
echo "- 상세한 기술스택 매칭, 성공 확률, 위험 요소 분석 포함\n";
echo "- 실제 API 키 설정 시 바로 실제 AI로 전환 가능\n\n";

echo "🔄 실제 AI로 전환하려면:\n";
echo ".env 파일에서 AI_ANALYSIS_PROVIDER=openai 또는 claude로 변경 + API 키 설정\n";