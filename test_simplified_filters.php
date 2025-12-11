<?php

require_once __DIR__ . '/public_html/vendor/autoload.php';

$app = require_once __DIR__ . '/public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\Admin\TenderController;
use App\Services\TenderCollectorService;
use App\Services\NaraApiService;
use Illuminate\Http\Request;

echo "=== 간소화된 필터링 시스템 테스트 ===" . PHP_EOL;

try {
    // TenderController 인스턴스 생성
    $collector = new TenderCollectorService(new NaraApiService());
    $naraApi = new NaraApiService();
    $controller = new TenderController($collector, $naraApi);
    
    echo "1. 전체 공고 (필터 없음):" . PHP_EOL;
    $totalCount = App\Models\Tender::count();
    echo "   ✅ 총 {$totalCount}건" . PHP_EOL;
    
    echo "\n2. 상태별 필터링:" . PHP_EOL;
    $activeCount = App\Models\Tender::where('status', 'active')->count();
    $closedCount = App\Models\Tender::where('status', 'closed')->count();
    echo "   📈 진행중: {$activeCount}건" . PHP_EOL;
    echo "   📋 마감: {$closedCount}건" . PHP_EOL;
    
    echo "\n3. 업종코드별 필터링 (상위 3개):" . PHP_EOL;
    $topPatterns = [
        '81111598' => '패키지소프트웨어/정보시스템개발서비스',
        '81111899' => '정보시스템유지관리서비스',
        '81112002' => '데이터처리/빅데이터분석서비스'
    ];
    
    foreach ($topPatterns as $pattern => $name) {
        $count = App\Models\Tender::where('pub_prcrmnt_clsfc_no', 'like', $pattern . '%')->count();
        echo "   🔧 {$name}: {$count}건" . PHP_EOL;
    }
    
    echo "\n4. 복합 필터링 테스트 (진행중 + 특정 업종):" . PHP_EOL;
    $complexCount = App\Models\Tender::where('status', 'active')
                                    ->where('pub_prcrmnt_clsfc_no', 'like', '81111598%')
                                    ->count();
    echo "   🎯 진행중 + 패키지소프트웨어개발: {$complexCount}건" . PHP_EOL;

    echo "\n5. 검색어 + 업종 필터링 테스트:" . PHP_EOL;
    $searchCount = App\Models\Tender::where('title', 'like', '%시스템%')
                                   ->where('pub_prcrmnt_clsfc_no', 'like', '81111%')
                                   ->count();
    echo "   🔍 '시스템' 포함 + 정보시스템 관련: {$searchCount}건" . PHP_EOL;

} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . PHP_EOL;
    echo "스택 트레이스:" . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
}

echo PHP_EOL . "=== 테스트 완료 ===" . PHP_EOL;