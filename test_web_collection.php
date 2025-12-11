<?php

require_once __DIR__ . '/public_html/vendor/autoload.php';

$app = require_once __DIR__ . '/public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\Admin\TenderController;
use App\Services\TenderCollectorService;
use App\Services\NaraApiService;
use Illuminate\Http\Request;

echo "=== 웹 인터페이스 수집 기능 테스트 ===".PHP_EOL;

try {
    // TenderController 인스턴스 생성
    $collector = new TenderCollectorService(new NaraApiService());
    $naraApi = new NaraApiService();
    $controller = new TenderController($collector, $naraApi);
    
    // Request 객체 생성 (today 수집 시뮬레이션)
    $request = new Request();
    $request->merge([
        'type' => 'today'
    ]);
    
    echo "오늘 데이터 수집 테스트 중...".PHP_EOL;
    
    $response = $controller->executeCollection($request);
    $responseData = $response->getData(true);
    
    if ($responseData['success']) {
        echo "✅ 수집 성공!".PHP_EOL;
        echo "결과: ".json_encode($responseData['stats'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).PHP_EOL;
    } else {
        echo "❌ 수집 실패: ".$responseData['message'].PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "❌ 오류 발생: ".$e->getMessage().PHP_EOL;
    echo "스택 트레이스:".PHP_EOL.$e->getTraceAsString().PHP_EOL;
}

echo PHP_EOL."=== 테스트 완료 ===".PHP_EOL;