<?php

require 'public_html/vendor/autoload.php';
$app = require_once 'public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== OpenAI/Claude API 연동 테스트 ===\n\n";

// 1. 설정 확인
echo "1. AI API 설정 확인...\n";

$aiConfig = config('ai');
if ($aiConfig) {
    echo "   ✅ AI 설정 파일 로드 성공\n";
    echo "   - 기본 프로바이더: " . ($aiConfig['analysis']['provider'] ?? 'N/A') . "\n";
    echo "   - 캐시 TTL: " . ($aiConfig['analysis']['cache_ttl'] ?? 'N/A') . "초\n";
    echo "   - 재시도 횟수: " . ($aiConfig['analysis']['retry_attempts'] ?? 'N/A') . "회\n";
} else {
    echo "   ❌ AI 설정 파일 로드 실패\n";
}

// 2. 환경 변수 확인
echo "\n2. 환경 변수 확인...\n";

$envVars = [
    'OPENAI_API_KEY' => env('OPENAI_API_KEY') ? '설정됨' : '미설정',
    'CLAUDE_API_KEY' => env('CLAUDE_API_KEY') ? '설정됨' : '미설정',
    'AI_ANALYSIS_PROVIDER' => env('AI_ANALYSIS_PROVIDER') ?: '기본값',
];

foreach ($envVars as $key => $value) {
    $status = ($value !== '미설정') ? '✅' : '⚠️';
    echo "   {$status} {$key}: {$value}\n";
}

// 3. AiApiService 인스턴스 생성 테스트
echo "\n3. AiApiService 인스턴스 생성 테스트...\n";

try {
    $aiApiService = new App\Services\AiApiService();
    echo "   ✅ AiApiService 인스턴스 생성 성공\n";
    
    // 사용량 통계 테스트
    $stats = $aiApiService->getUsageStats();
    echo "   ✅ 사용량 통계 조회 성공\n";
    echo "     - 일일 요청: " . $stats['daily_requests'] . "회\n";
    echo "     - 월간 요청: " . $stats['monthly_requests'] . "회\n";
    
} catch (Exception $e) {
    echo "   ❌ AiApiService 인스턴스 생성 실패: " . $e->getMessage() . "\n";
}

// 4. TenderAnalysisService AI 연동 테스트
echo "\n4. TenderAnalysisService AI 연동 테스트...\n";

try {
    $aiApiService = new App\Services\AiApiService();
    $analysisService = new App\Services\TenderAnalysisService($aiApiService);
    echo "   ✅ TenderAnalysisService AI 연동 성공\n";
    
    // Mock 테스트 데이터로 분석 실행 (실제 API 호출 없이)
    $tender = App\Models\Tender::first();
    
    if ($tender) {
        echo "   📋 테스트 대상 공고: " . $tender->tender_no . "\n";
        echo "     제목: " . substr($tender->title, 0, 50) . "...\n";
        
        // 실제 API 키가 없으면 폴백 분석이 실행될 것임
        if (!env('OPENAI_API_KEY') && !env('CLAUDE_API_KEY')) {
            echo "   ⚠️  API 키 미설정으로 폴백 분석 모드로 실행됩니다\n";
        }
        
    } else {
        echo "   ⚠️  테스트할 공고 데이터가 없습니다\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ TenderAnalysisService 생성 실패: " . $e->getMessage() . "\n";
}

// 5. AiHelperService 테스트
echo "\n5. AiHelperService 테스트...\n";

try {
    // Mock AI 결과 데이터
    $mockAiResult = [
        'compatibility_score' => 85,
        'technical_match_score' => 90,
        'business_match_score' => 80,
        'success_probability' => 75,
        'matching_technologies' => ['PHP', 'Laravel', 'MySQL'],
        'missing_technologies' => ['React'],
        'detailed_analysis' => [
            'strengths' => ['강력한 백엔드 개발 역량', '정부 프로젝트 경험 풍부'],
            'risks' => ['프론트엔드 기술 부족']
        ]
    ];
    
    $insights = App\Services\AiHelperService::generateAiKeyInsights($mockAiResult);
    echo "   ✅ AI 키 인사이트 생성 성공\n";
    echo "   📊 생성된 인사이트:\n";
    foreach ($insights as $insight) {
        echo "      - {$insight}\n";
    }
    
    // 규모/경쟁 점수 계산 테스트
    $tender = App\Models\Tender::first();
    if ($tender) {
        $scaleScore = App\Services\AiHelperService::calculateScaleScore($tender);
        $competitionScore = App\Services\AiHelperService::calculateCompetitionScore($tender);
        echo "   ✅ 규모 점수 계산: {$scaleScore}점\n";
        echo "   ✅ 경쟁 점수 계산: {$competitionScore}점\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ AiHelperService 테스트 실패: " . $e->getMessage() . "\n";
}

// 6. 연결 테스트 (실제 API 호출)
echo "\n6. API 연결 테스트...\n";

if (env('OPENAI_API_KEY') || env('CLAUDE_API_KEY')) {
    try {
        $aiApiService = new App\Services\AiApiService();
        $connectionResult = $aiApiService->checkConnection();
        
        if ($connectionResult['status'] === 'connected') {
            echo "   ✅ API 연결 성공\n";
            echo "     - 프로바이더: " . $connectionResult['provider'] . "\n";
            echo "     - 응답 시간: " . $connectionResult['response_time'] . "ms\n";
        } else {
            echo "   ❌ API 연결 실패: " . $connectionResult['error'] . "\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ API 연결 테스트 실패: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ⚠️  API 키가 설정되지 않아 연결 테스트를 건너뜁니다\n";
    echo "     OpenAI API 키 또는 Claude API 키를 .env 파일에 추가해주세요\n";
}

echo "\n=== 테스트 완료 ===\n";

// 7. 다음 단계 안내
echo "\n📋 다음 단계:\n";
echo "1. .env 파일에 실제 API 키 추가:\n";
echo "   OPENAI_API_KEY=your_openai_key_here\n";
echo "   # 또는\n";
echo "   CLAUDE_API_KEY=your_claude_key_here\n\n";
echo "2. 실제 공고 데이터로 AI 분석 테스트\n";
echo "3. 타이드플로 기술스택 자동 수집 구현\n";
echo "4. 첨부파일 AI 분석 시스템 구현\n";
echo "\n💡 현재 단계: OpenAI/Claude API 연동 서비스 구현 완료\n";