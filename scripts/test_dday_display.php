#!/usr/bin/env php
<?php

// Laravel 환경 초기화
require_once '/home/tideflo/nara/public_html/vendor/autoload.php';

$app = require_once '/home/tideflo/nara/public_html/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tender;
use Illuminate\Support\Facades\View;

echo "=== D-Day 표시 기능 테스트 ===\n\n";

// 테스트 공고들 가져오기
$tenders = Tender::with('category')->take(10)->get();

echo "총 " . $tenders->count() . "개 공고 테스트\n\n";

$results = [
    'dday_today' => 0,
    'dday_urgent' => 0, 
    'dday_warning' => 0,
    'dday_normal' => 0,
    'dday_expired' => 0,
    'dday_undefined' => 0
];

foreach ($tenders as $i => $tender) {
    $ddayDisplay = $tender->dday_display;
    $colorClass = $tender->dday_color_class;
    $formattedDate = $tender->formatted_bid_close_date;
    $daysRemaining = $tender->days_remaining;
    
    printf("공고 %02d: %s\n", $i+1, mb_substr($tender->title, 0, 60));
    printf("  입찰마감: %s\n", $formattedDate ?: '미정');
    printf("  D-Day: %s (남은일: %s일)\n", $ddayDisplay, $daysRemaining ?? 'N/A');
    printf("  CSS클래스: %s\n", $colorClass);
    
    // 통계 업데이트
    if (strpos($colorClass, 'dday-today') !== false) {
        $results['dday_today']++;
    } elseif (strpos($colorClass, 'dday-urgent') !== false) {
        $results['dday_urgent']++;
    } elseif (strpos($colorClass, 'dday-warning') !== false) {
        $results['dday_warning']++;
    } elseif (strpos($colorClass, 'dday-normal') !== false) {
        $results['dday_normal']++;
    } elseif (strpos($colorClass, 'dday-expired') !== false) {
        $results['dday_expired']++;
    } else {
        $results['dday_undefined']++;
    }
    
    echo "\n";
}

echo "=== 통계 ===\n";
printf("D-Day (오늘): %d개\n", $results['dday_today']);
printf("긴급 (D-1): %d개\n", $results['dday_urgent']);  
printf("주의 (D-3): %d개\n", $results['dday_warning']);
printf("여유 (D-4+): %d개\n", $results['dday_normal']);
printf("마감: %d개\n", $results['dday_expired']);
printf("미정: %d개\n", $results['dday_undefined']);

echo "\n=== 테스트 완료 ===\n";

// HTML 렌더링 예시
echo "\n=== HTML 렌더링 예시 ===\n";
$sampleTender = $tenders->first();
if ($sampleTender) {
    echo "<td>\n";
    if ($sampleTender->formatted_bid_close_date) {
        echo "    " . $sampleTender->formatted_bid_close_date . "\n";
        echo "    <br>\n";
        echo "    <small class=\"" . $sampleTender->dday_color_class . "\">\n";
        echo "        " . $sampleTender->dday_display . "\n";
        echo "    </small>\n";
    } else {
        echo "    <span class=\"text-muted\">미정</span>\n";
    }
    echo "</td>\n";
}