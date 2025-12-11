<?php

require_once __DIR__ . '/public_html/vendor/autoload.php';

$app = require_once __DIR__ . '/public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\Admin\TenderController;
use App\Services\TenderCollectorService;
use App\Services\NaraApiService;
use Illuminate\Http\Request;

echo "=== 업종코드 패턴 필터링 테스트 ===" . PHP_EOL;

try {
    // TenderController 인스턴스 생성
    $collector = new TenderCollectorService(new NaraApiService());
    $naraApi = new NaraApiService();
    $controller = new TenderController($collector, $naraApi);
    
    echo "업종코드 패턴별 필터링 테스트:" . PHP_EOL;
    
    $patterns = [
        '81112002' => '데이터처리/빅데이터분석서비스',
        '81112299' => '소프트웨어유지및지원서비스', 
        '81111811' => '운영위탁서비스',
        '81111899' => '정보시스템유지관리서비스',
        '81112199' => '인터넷지원개발서비스',
        '81111598' => '패키지소프트웨어/정보시스템개발서비스',
        '81151699' => '공간정보DB구축서비스'
    ];
    
    foreach ($patterns as $pattern => $name) {
        // Request 객체 생성 (패턴 필터링 테스트)
        $request = new Request();
        $request->merge([
            'industry_pattern' => $pattern
        ]);
        
        echo "\n📋 패턴: {$pattern} ({$name})" . PHP_EOL;
        
        // 컨트롤러의 private 메서드를 직접 호출할 수 없으므로 DB 직접 조회
        $count = App\Models\Tender::where('pub_prcrmnt_clsfc_no', 'like', $pattern . '%')->count();
        echo "   ✅ 매칭 건수: {$count}건" . PHP_EOL;
        
        if ($count > 0) {
            // 첫 번째 결과 샘플 확인
            $sample = App\Models\Tender::where('pub_prcrmnt_clsfc_no', 'like', $pattern . '%')->first();
            if ($sample) {
                echo "   📄 샘플: {$sample->title}" . PHP_EOL;
                echo "   🏷️  코드: {$sample->pub_prcrmnt_clsfc_no}" . PHP_EOL;
            }
        }
    }
    
    // 전체 통계
    echo "\n📊 전체 통계:" . PHP_EOL;
    $totalTenders = App\Models\Tender::count();
    echo "   - 전체 공고: {$totalTenders}건" . PHP_EOL;
    
    $targetTenders = App\Models\Tender::where(function($query) use ($patterns) {
        foreach ($patterns as $pattern => $name) {
            $query->orWhere('pub_prcrmnt_clsfc_no', 'like', $pattern . '%');
        }
    })->count();
    
    echo "   - 대상 업종 공고: {$targetTenders}건" . PHP_EOL;
    echo "   - 대상 비율: " . round(($targetTenders / $totalTenders) * 100, 2) . "%" . PHP_EOL;

} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . PHP_EOL;
    echo "스택 트레이스:" . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
}

echo PHP_EOL . "=== 테스트 완료 ===" . PHP_EOL;