<?php

require_once __DIR__ . '/public_html/vendor/autoload.php';

$app = require_once __DIR__ . '/public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tender;
use App\Services\TenderAnalysisService;
use App\Models\User;

echo "=== AI 분석 시스템 테스트 ===" . PHP_EOL;

try {
    // 첫 번째 공고로 테스트
    $tender = Tender::first();
    if (!$tender) {
        echo '테스트할 공고가 없습니다.' . PHP_EOL;
        exit;
    }

    echo '공고: ' . $tender->tender_no . ' - ' . substr($tender->title, 0, 50) . '...' . PHP_EOL;
    echo '업종코드: ' . $tender->pub_prcrmnt_clsfc_no . PHP_EOL;
    echo '예산: ' . number_format($tender->budget_amount ?? 0) . '원' . PHP_EOL;

    $analysisService = new TenderAnalysisService();
    $user = User::first();

    echo PHP_EOL . '🤖 AI 분석 실행 중...' . PHP_EOL;
    
    $analysis = $analysisService->analyzeTender($tender, $user);
    
    echo PHP_EOL . '=== 🎯 AI 분석 결과 ===' . PHP_EOL;
    echo '총점: ' . $analysis->total_score . '점 (100점 만점)' . PHP_EOL;
    echo '기술적 적합성: ' . $analysis->technical_score . '점 (40점 만점)' . PHP_EOL;
    echo '사업 영역 적합성: ' . $analysis->experience_score . '점 (25점 만점)' . PHP_EOL;
    echo '규모 적합성: ' . $analysis->budget_score . '점 (20점 만점)' . PHP_EOL;
    echo '기타 점수: ' . $analysis->other_score . '점 (15점 만점)' . PHP_EOL;
    echo '추천도: ' . $analysis->recommendation_text . PHP_EOL;
    echo '처리 시간: ' . $analysis->processing_time . 'ms' . PHP_EOL;
    echo '분석 ID: ' . $analysis->id . PHP_EOL;
    
    // 상세 분석 결과 표시
    $details = $analysis->analysis_data;
    if (isset($details['technical_analysis']['matched_keywords']) && count($details['technical_analysis']['matched_keywords']) > 0) {
        echo PHP_EOL . '🔧 매칭된 기술 키워드: ' . implode(', ', $details['technical_analysis']['matched_keywords']) . PHP_EOL;
    }
    
    if (isset($details['recommendation'])) {
        echo PHP_EOL . '💡 추천 사유: ' . $details['recommendation'] . PHP_EOL;
    }
    
    if (isset($details['key_insights']) && is_array($details['key_insights'])) {
        echo PHP_EOL . '✨ 주요 인사이트:' . PHP_EOL;
        foreach ($details['key_insights'] as $insight) {
            echo '   - ' . $insight . PHP_EOL;
        }
    }

    echo PHP_EOL . '✅ AI 분석 시스템 테스트 완료!' . PHP_EOL;
    echo '🌐 웹에서 확인: https://nara.tideflo.work/admin/analyses/' . $analysis->id . PHP_EOL;

} catch (Exception $e) {
    echo '❌ 분석 실패: ' . $e->getMessage() . PHP_EOL;
    echo '스택 트레이스:' . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
}

echo PHP_EOL . "=== 테스트 완료 ===" . PHP_EOL;