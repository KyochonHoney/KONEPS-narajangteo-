<?php

require_once __DIR__ . '/public_html/vendor/autoload.php';

$app = require_once __DIR__ . '/public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\TenderCollectorService;

echo "=== 필터링 디버깅 ===" . PHP_EOL;

$service = new TenderCollectorService(app(App\Services\NaraApiService::class));

echo "현재 collectTendersWithAdvancedFilters의 기본 분류코드:" . PHP_EOL;

// TenderCollectorService의 기본 분류코드 확인
$reflection = new ReflectionClass($service);
$method = $reflection->getMethod('collectTendersWithAdvancedFilters');

// 메소드의 기본 파라미터 확인
$params = $method->getParameters();
foreach ($params as $param) {
    if ($param->getName() === 'classificationCodes' && $param->isDefaultValueAvailable()) {
        $defaultValue = $param->getDefaultValue();
        echo "- " . implode(', ', $defaultValue) . PHP_EOL;
    }
}

echo PHP_EOL . "=== 실제 필터링 테스트 ===" . PHP_EOL;

// 샘플 아이템으로 필터링 테스트
$testItems = [
    [
        'bidNtceNo' => 'TEST001',
        'bidNtceNm' => '테스트 공고 1',
        'pubPrcrmntClsfcNo' => '1468'  // 우리가 원하는 코드
    ],
    [
        'bidNtceNo' => 'TEST002', 
        'bidNtceNm' => '테스트 공고 2',
        'pubPrcrmntClsfcNo' => '73169006'  // 다른 코드
    ],
    [
        'bidNtceNo' => 'TEST003',
        'bidNtceNm' => '소프트웨어 개발 공고',
        'pubPrcrmntClsfcNo' => []  // 빈 배열
    ]
];

foreach ($testItems as $item) {
    $classification = is_array($item['pubPrcrmntClsfcNo']) ? 
        (empty($item['pubPrcrmntClsfcNo']) ? '' : reset($item['pubPrcrmntClsfcNo'])) :
        (string)$item['pubPrcrmntClsfcNo'];
    
    echo "공고: {$item['bidNtceNo']}" . PHP_EOL;
    echo "- 분류코드: '{$classification}'" . PHP_EOL;
    echo "- 1468과 일치: " . ($classification === '1468' ? 'YES' : 'NO') . PHP_EOL;
    echo "- 필터 통과: " . (in_array($classification, ['1468']) ? 'YES' : 'NO') . PHP_EOL;
    echo PHP_EOL;
}

echo "=== 디버깅 완료 ===" . PHP_EOL;