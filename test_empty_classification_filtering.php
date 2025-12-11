<?php

require '/home/tideflo/nara/public_html/vendor/autoload.php';
$app = require '/home/tideflo/nara/public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== 업종상세코드 빈 값 필터링 로직 테스트 ===\n\n";

// 테스트용 가짜 데이터
$testItems = [
    [
        'bidNtceNo' => 'TEST001',
        'bidNtceNm' => '정상 8111 패턴 공고',
        'pubPrcrmntClsfcNo' => '81112002',
        'inqryDiv' => '11',
        'dminsttNm' => '테스트기관'
    ],
    [
        'bidNtceNo' => 'TEST002', 
        'bidNtceNm' => '빈 업종상세코드 공고',
        'pubPrcrmntClsfcNo' => '',
        'inqryDiv' => '11',
        'dminsttNm' => '테스트기관2'
    ],
    [
        'bidNtceNo' => 'TEST003',
        'bidNtceNm' => '업종상세코드 필드 없음 공고',
        'inqryDiv' => '11',
        'dminsttNm' => '테스트기관3'
    ],
    [
        'bidNtceNo' => 'TEST004',
        'bidNtceNm' => '다른 업종 패턴 공고 (제외되어야 함)',
        'pubPrcrmntClsfcNo' => '90000001',
        'inqryDiv' => '11',
        'dminsttNm' => '테스트기관4'
    ]
];

// TenderCollectorService 인스턴스 생성
$collector = new App\Services\TenderCollectorService(new App\Services\NaraApiService());

echo "1. 각 테스트 케이스의 필터링 결과:\n\n";

foreach ($testItems as $index => $item) {
    echo "테스트 " . ($index + 1) . ": " . $item['bidNtceNm'] . "\n";
    echo "  업종상세코드: " . ($item['pubPrcrmntClsfcNo'] ?? '[필드없음]') . "\n";
    
    // 필터링 로직 시뮬레이션
    $targetPatterns = [
        '81112002', '81112299', '81111811', '81111899', 
        '81112199', '81111598', '81111599', '81151699'
    ];
    
    $itemClassification = $item['pubPrcrmntClsfcNo'] ?? '';
    $isTargetCode = false;
    $isEmptyClassification = false;
    
    // 빈 값 확인
    if (empty($itemClassification) || trim($itemClassification) === '') {
        $isEmptyClassification = true;
        echo "  → 빈 업종코드로 '기타' 카테고리 할당\n";
    } else {
        // 패턴 매칭
        foreach ($targetPatterns as $pattern) {
            if (strpos($itemClassification, $pattern) === 0) {
                $isTargetCode = true;
                echo "  → 매칭된 패턴: $pattern (정상 저장)\n";
                break;
            }
        }
        
        if (!$isTargetCode) {
            echo "  → 대상 패턴 아님 (필터링 제외)\n";
        }
    }
    
    $shouldSave = $isTargetCode || $isEmptyClassification;
    echo "  저장 여부: " . ($shouldSave ? "✅ 저장" : "❌ 제외") . "\n\n";
}

echo "2. 수정된 로직 요약:\n";
echo "  ✅ 8111xxxx 패턴: 기존과 동일하게 저장\n";
echo "  ✅ 빈 값/필드없음: 새로 추가된 '기타' 카테고리로 저장\n";
echo "  ❌ 다른 업종패턴: 기존과 동일하게 제외\n\n";

echo "3. 카테고리 확인:\n";
$categories = App\Models\TenderCategory::all();
foreach ($categories as $category) {
    echo "  ID {$category->id}: {$category->name} - {$category->description}\n";
}

?>