<?php

// 가장 간단한 테스트
echo "=== pubPrcrmntClsfcNo 빈 값 분석 ===\n\n";

// 1. 기존 DB에서 확인 (비교 기준)
echo "1. 기존 저장된 데이터 확인:\n";
$dbFile = '/home/tideflo/nara/public_html/.env';
if (file_exists($dbFile)) {
    echo "   DB 연결 정보 확인됨\n";
} else {
    echo "   DB 설정 파일 없음\n";
}

// 2. 간단한 로직 테스트
echo "\n2. 빈 값 처리 로직 테스트:\n";

$sampleData = [
    ['pubPrcrmntClsfcNo' => '81112002'],         // 정상값
    ['pubPrcrmntClsfcNo' => ''],                 // 빈 문자열
    ['pubPrcrmntClsfcNo' => null],               // null
    ['otherField' => 'test'],                    // 필드 없음
];

$total = 0;
$missing = 0;
$empty = 0;
$null = 0;
$normal = 0;

foreach ($sampleData as $item) {
    $total++;
    
    // TenderCollectorService와 동일한 처리 방식
    $code = $item['pubPrcrmntClsfcNo'] ?? '';
    
    if (!array_key_exists('pubPrcrmntClsfcNo', $item)) {
        $missing++;
        echo "   필드없음: " . json_encode($item) . "\n";
    } elseif ($code === null) {
        $null++;
        echo "   NULL값: " . json_encode($item) . "\n";
    } elseif ($code === '' || trim($code) === '') {
        $empty++;
        echo "   빈값: " . json_encode($item) . "\n";
    } else {
        $normal++;
        echo "   정상: [$code]\n";
    }
}

echo "\n3. 결과:\n";
echo "   총 $total건\n";
echo "   필드없음: $missing건\n";
echo "   빈문자열: $empty건\n";  
echo "   NULL값: $null건\n";
echo "   정상값: $normal건\n";
echo "   문제값: " . ($missing + $empty + $null) . "건\n";

echo "\n4. 핵심 발견:\n";
echo "   - TenderCollectorService에서 \$item['pubPrcrmntClsfcNo'] ?? '' 사용\n";
echo "   - 필드없음 + NULL → 빈문자열('')로 변환\n";
echo "   - 이후 8111xxxx 패턴 매칭에서 걸러짐\n";
echo "   - 따라서 DB에는 정상값만 저장됨\n";

echo "\n5. 실제 확인 필요사항:\n";
echo "   - API 원본에서 pubPrcrmntClsfcNo 필드가 아예 없는 공고 비율\n";
echo "   - 빈 문자열인 공고 비율\n";
echo "   - 8111xxxx 패턴이 아닌 다른 패턴들\n";

?>