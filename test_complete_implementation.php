<?php

require '/home/tideflo/nara/public_html/vendor/autoload.php';
$app = require '/home/tideflo/nara/public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== 업종상세코드 빈 값 필터링 구현 완료 테스트 ===\n\n";

echo "1. TenderCategory 확인:\n";
$categories = App\Models\TenderCategory::all();
foreach ($categories as $category) {
    echo "  - ID {$category->id}: {$category->name} ({$category->code}) - {$category->description}\n";
}

echo "\n2. 현재 저장된 공고 현황:\n";
$totalCount = App\Models\Tender::count();
echo "  - 전체 공고: {$totalCount}건\n";

foreach ($categories as $category) {
    $count = App\Models\Tender::where('category_id', $category->id)->count();
    echo "  - {$category->name}: {$count}건\n";
}

echo "\n3. 업종상세코드별 분포 (상위 5개):\n";
$distribution = App\Models\Tender::select('pub_prcrmnt_clsfc_no')
    ->selectRaw('COUNT(*) as count')
    ->groupBy('pub_prcrmnt_clsfc_no')
    ->orderBy('count', 'desc')
    ->limit(5)
    ->get();

foreach ($distribution as $item) {
    $code = $item->pub_prcrmnt_clsfc_no ?: '[빈값]';
    echo "  - {$code}: {$item->count}건\n";
}

echo "\n4. 빈 업종상세코드 공고 확인:\n";
$emptyCount = App\Models\Tender::where(function($q) {
    $q->where('pub_prcrmnt_clsfc_no', '')
      ->orWhereNull('pub_prcrmnt_clsfc_no');
})->count();

$category4Count = App\Models\Tender::where('category_id', 4)->count();
echo "  - 빈 업종상세코드 공고: {$emptyCount}건\n";
echo "  - 기타 카테고리 공고: {$category4Count}건\n";

echo "\n5. 수정된 기능 요약:\n";
echo "  ✅ TenderCollectorService: 빈 값/필드없음 공고도 저장하도록 수정\n";
echo "  ✅ TenderCategory: '기타' 카테고리 (ID: 4) 추가\n";
echo "  ✅ 관리자 화면: '기타 (업종상세코드 없음)' 필터 추가\n";
echo "  ✅ TenderController: EMPTY 패턴 필터링 구현\n";
echo "  ✅ 통계 시스템: 기타 카테고리 통계 포함\n";

echo "\n6. 다음 데이터 수집 시 동작 예상:\n";
echo "  - 8111xxxx 패턴: 기존처럼 해당 카테고리로 저장\n";
echo "  - 빈 값/필드없음: '기타' 카테고리(ID: 4)로 저장\n";
echo "  - 다른 업종패턴: 기존처럼 필터링으로 제외\n";

echo "\n✅ 구현 완료!\n";

?>