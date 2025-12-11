<?php

require_once __DIR__ . '/public_html/vendor/autoload.php';

$app = require_once __DIR__ . '/public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tender;

echo "=== 공고 등록일 분석 ===\n\n";

$tenders = Tender::whereNotNull('rgst_dt')->get();
$registrationDates = [];

foreach ($tenders as $tender) {
    $date = substr($tender->rgst_dt, 0, 10); // YYYY-MM-DD 부분만 추출
    if (!isset($registrationDates[$date])) {
        $registrationDates[$date] = 0;
    }
    $registrationDates[$date]++;
}

ksort($registrationDates);

echo "공고 등록일별 분포:\n";
foreach ($registrationDates as $date => $count) {
    echo "- " . $date . ": " . $count . "개\n";
}

echo "\n총 등록일이 있는 공고: " . count($tenders) . "개\n";
if (!empty($registrationDates)) {
    echo '등록일 범위: ' . min(array_keys($registrationDates)) . ' ~ ' . max(array_keys($registrationDates)) . "\n";
}

// 최근 일주일과 한 달 통계
$recent7days = 0;
$recent30days = 0;
$today = date('Y-m-d');

foreach ($registrationDates as $date => $count) {
    $daysDiff = (strtotime($today) - strtotime($date)) / (24 * 3600);
    
    if ($daysDiff <= 7) {
        $recent7days += $count;
    }
    if ($daysDiff <= 30) {
        $recent30days += $count;
    }
}

echo "\n=== 기간별 통계 ===\n";
echo "최근 7일 등록: " . $recent7days . "개\n";
echo "최근 30일 등록: " . $recent30days . "개\n";
echo "전체 436개 중 등록일 불명: " . (436 - count($tenders)) . "개\n";