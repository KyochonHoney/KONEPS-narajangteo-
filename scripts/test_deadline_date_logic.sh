#!/bin/bash

# 마감 로직 날짜 기준 변경 테스트 스크립트
# 작성일: 2025-09-10
# 목적: 시간 단위에서 날짜 단위로 변경된 마감 로직 검증

echo "======================================"
echo "날짜 기준 마감 로직 변경 검증 테스트"
echo "======================================"

cd /home/tideflo/nara/public_html

echo "1. 현재 시간 및 날짜 확인"
echo "--------------------------------------"
php artisan tinker --execute="
echo \"현재 시각: \" . now()->format('Y-m-d H:i:s') . \"\\n\";
echo \"오늘 날짜: \" . now()->format('Y-m-d') . \"\\n\";
echo \"내일 날짜: \" . now()->addDay()->format('Y-m-d') . \"\\n\";
echo \"어제 날짜: \" . now()->subDay()->format('Y-m-d') . \"\\n\";
"

echo ""
echo "2. 마감일별 공고 상태 샘플 확인"
echo "--------------------------------------"
php artisan tinker --execute="
\$samples = \App\Models\Tender::where('status', 'active')
    ->whereNotNull('bid_clse_dt')
    ->where('bid_clse_dt', '!=', '')
    ->select(['tender_no', 'title', 'bid_clse_dt', 'status'])
    ->orderBy('bid_clse_dt')
    ->limit(10)
    ->get();

echo \"=== 활성 공고 마감일 샘플 ===\\n\";
foreach(\$samples as \$tender) {
    \$closeDate = \Carbon\Carbon::parse(\$tender->bid_clse_dt);
    \$daysRemaining = \$tender->days_remaining;
    \$ddayDisplay = \$tender->dday_display;
    \$isExpired = \$tender->is_expired ? 'Y' : 'N';
    
    echo \"[\$tender->tender_no] \$closeDate->format('Y-m-d H:i') | D-day: \$ddayDisplay | 마감: \$isExpired | 남은일수: \$daysRemaining\\n\";
}
"

echo ""
echo "3. D-Day 로직 테스트 (오늘 마감인 공고)"
echo "--------------------------------------"
php artisan tinker --execute="
\$today = \Carbon\Carbon::now()->format('Y-m-d');
\$todayTenders = \App\Models\Tender::whereRaw('DATE(bid_clse_dt) = ?', [\$today])
    ->select(['tender_no', 'title', 'bid_clse_dt', 'status'])
    ->limit(5)
    ->get();

echo \"=== 오늘(\$today) 마감인 공고 ===\\n\";
if(\$todayTenders->count() > 0) {
    foreach(\$todayTenders as \$tender) {
        \$daysRemaining = \$tender->days_remaining;
        \$ddayDisplay = \$tender->dday_display;
        \$isExpired = \$tender->is_expired ? 'Y' : 'N';
        
        echo \"[\$tender->tender_no] 마감: \$tender->bid_clse_dt | D-day: \$ddayDisplay | 만료: \$isExpired | 남은일수: \$daysRemaining\\n\";
    }
} else {
    echo \"오늘 마감인 공고가 없습니다.\\n\";
}
"

echo ""
echo "4. 어제 마감된 공고 확인"
echo "--------------------------------------"
php artisan tinker --execute="
\$yesterday = \Carbon\Carbon::now()->subDay()->format('Y-m-d');
\$yesterdayTenders = \App\Models\Tender::whereRaw('DATE(bid_clse_dt) = ?', [\$yesterday])
    ->select(['tender_no', 'title', 'bid_clse_dt', 'status'])
    ->limit(5)
    ->get();

echo \"=== 어제(\$yesterday) 마감된 공고 ===\\n\";
if(\$yesterdayTenders->count() > 0) {
    foreach(\$yesterdayTenders as \$tender) {
        \$daysRemaining = \$tender->days_remaining;
        \$ddayDisplay = \$tender->dday_display;
        \$isExpired = \$tender->is_expired ? 'Y' : 'N';
        
        echo \"[\$tender->tender_no] 마감: \$tender->bid_clse_dt | D-day: \$ddayDisplay | 만료: \$isExpired | 남은일수: \$daysRemaining\\n\";
    }
} else {
    echo \"어제 마감된 공고가 없습니다.\\n\";
}
"

echo ""
echo "5. 상태 업데이트 로직 테스트"
echo "--------------------------------------"
php artisan tinker --execute="
echo \"=== 상태 업데이트 실행 (날짜 기준) ===\\n\";
\$stats = app(\App\Services\TenderCollectorService::class)->updateTenderStatuses();
echo \"총 확인: {\$stats['total_checked']} 건\\n\";
echo \"상태 변경: {\$stats['status_changed']} 건\\n\";
echo \"오류: {\$stats['errors']} 건\\n\";
"

echo ""
echo "6. 변경 후 데이터베이스 상태"
echo "--------------------------------------"
php artisan tinker --execute="
\$activeTenders = \App\Models\Tender::where('status', 'active')->count();
\$closedTenders = \App\Models\Tender::where('status', 'closed')->count();

echo \"활성 공고: \$activeTenders 건\\n\";
echo \"마감 공고: \$closedTenders 건\\n\";

// D-Day 0인 공고 (오늘 마감)
\$ddayZero = \App\Models\Tender::where('status', 'active')
    ->whereNotNull('bid_clse_dt')
    ->whereRaw('DATE(bid_clse_dt) = CURDATE()')
    ->count();
echo \"D-Day 공고 (오늘 마감): \$ddayZero 건\\n\";

// 마감임박 공고 (3일 이내)
\$urgent = \App\Models\Tender::where('status', 'active')
    ->whereNotNull('bid_clse_dt')
    ->whereRaw('DATE(bid_clse_dt) <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)')
    ->whereRaw('DATE(bid_clse_dt) >= CURDATE()')
    ->count();
echo \"마감임박 공고 (3일이내): \$urgent 건\\n\";
"

echo ""
echo "7. 예시 시나리오 검증"
echo "--------------------------------------"
echo "시나리오: 9월 24일 10시 마감 공고가 9월 24일 22시에도 D-Day로 표시되는지 확인"
php artisan tinker --execute="
// 임시 테스트용 더미 데이터 생성 (메모리에서만)
\$testTender = new \App\Models\Tender();
\$testTender->bid_clse_dt = '2025-09-24 10:00:00';
\$testTender->status = 'active';

// 현재 시각을 9월 24일 22시로 가정한 테스트
\Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2025-09-24 22:00:00'));

\$daysRemaining = \$testTender->days_remaining;
\$ddayDisplay = \$testTender->dday_display;
\$isExpired = \$testTender->is_expired ? 'Y' : 'N';

echo \"테스트 결과 (9월 24일 22시 시점):\\n\";
echo \"마감일: 2025-09-24 10:00:00\\n\";
echo \"D-Day 표시: \$ddayDisplay\\n\";
echo \"만료 여부: \$isExpired\\n\";
echo \"남은 일수: \$daysRemaining\\n\";

// 다음날(9월 25일)로 넘어갔을 때 테스트
\Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2025-09-25 01:00:00'));

\$daysRemaining2 = \$testTender->days_remaining;
\$ddayDisplay2 = \$testTender->dday_display;
\$isExpired2 = \$testTender->is_expired ? 'Y' : 'N';

echo \"\\n테스트 결과 (9월 25일 01시 시점):\\n\";
echo \"마감일: 2025-09-24 10:00:00\\n\";
echo \"D-Day 표시: \$ddayDisplay2\\n\";
echo \"만료 여부: \$isExpired2\\n\";
echo \"남은 일수: \$daysRemaining2\\n\";

// 테스트 시간 원복
\Carbon\Carbon::setTestNow();
echo \"\\n테스트 완료 (현재 시각으로 원복)\\n\";
"

echo ""
echo "======================================"
echo "날짜 기준 마감 로직 검증 완료!"
echo "======================================"
echo ""
echo "주요 변경사항:"
echo "- 마감 판단: 시간 단위 → 날짜 단위로 변경"
echo "- D-Day 계산: 당일 마감은 D-Day로 표시" 
echo "- 상태 변경: 날짜가 넘어가야 마감으로 변경"
echo "- 예시: 24일 10시 마감 → 24일 22시에도 D-Day 표시"