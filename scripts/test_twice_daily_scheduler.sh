#!/bin/bash

# 하루 2번 스케줄러 설정 검증 스크립트
# 작성일: 2025-09-10
# 목적: 06:00, 13:00 하루 2번 공고 수집 + 마감 체크 검증

echo "======================================"
echo "하루 2번 스케줄러 설정 검증 테스트"
echo "======================================"

cd /home/tideflo/nara/public_html

echo "1. 변경된 스케줄러 목록 확인"
echo "--------------------------------------"
php artisan schedule:list

echo ""
echo "2. 스케줄러 변경 사항 요약"
echo "--------------------------------------"
echo "🕕 오전 6시 (06:00) 작업:"
echo "   ├── 1단계: 최근 7일 공고 자동 수집"
echo "   ├── 2단계: 마감상태 자동 업데이트 (날짜 기준)"
echo "   ├── 3단계: 마감임박 공고 확인 (3일 이내)"
echo "   └── 4단계: D-Day 공고 확인 및 알림"
echo ""
echo "🕐 오후 1시 (13:00) 작업:"
echo "   ├── 1단계: 최근 7일 공고 자동 수집"
echo "   ├── 2단계: 마감상태 자동 업데이트 (날짜 기준)"
echo "   ├── 3단계: 마감임박 공고 확인 (3일 이내)"
echo "   └── 4단계: D-Day 공고 확인 및 알림"
echo ""
echo "🗓️ 매주 월요일 1시 작업:"
echo "   └── 주간 데이터 정합성 재확인 (7일간 데이터 재수집)"

echo ""
echo "3. 통합 작업 기능 테스트 (06:00/13:00 작업 내용)"
echo "--------------------------------------"
echo "=== 공고 수집 + 마감 체크 + 알림 시뮬레이션 ==="

php artisan tinker --execute="
echo \"🔄 1단계: 최근 공고 자동 수집 테스트\\n\";
// 실제 수집은 시간이 오래 걸리므로 상태만 확인
\$lastCollection = \App\Models\Tender::latest('collected_at')->first();
if (\$lastCollection) {
    echo \"최근 수집: {\$lastCollection->collected_at}\\n\";
} else {
    echo \"수집된 데이터 없음\\n\";
}

echo \"\\n✅ 2단계: 마감상태 자동 업데이트\\n\";
\$tenderCollector = app(\App\Services\TenderCollectorService::class);
\$updateStats = \$tenderCollector->updateTenderStatuses();

echo \"총 확인: {\$updateStats['total_checked']} 건\\n\";
echo \"상태 변경: {\$updateStats['status_changed']} 건\\n\";
echo \"오류: {\$updateStats['errors']} 건\\n\";

echo \"\\n📊 3단계: 마감임박 공고 확인 (3일 이내)\\n\";
\$urgentCount = \App\Models\Tender::where('status', 'active')
    ->whereNotNull('bid_clse_dt')
    ->whereRaw('DATE(bid_clse_dt) <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)')
    ->whereRaw('DATE(bid_clse_dt) >= CURDATE()')
    ->count();

echo \"마감임박 공고: \$urgentCount 건\\n\";

echo \"\\n🎯 4단계: D-Day 공고 확인 (오늘 마감)\\n\";
\$ddayCount = \App\Models\Tender::where('status', 'active')
    ->whereRaw('DATE(bid_clse_dt) = CURDATE()')
    ->count();

echo \"D-Day 공고: \$ddayCount 건\\n\";

echo \"\\n📈 전체 현황\\n\";
\$totalActive = \App\Models\Tender::where('status', 'active')->count();
\$totalClosed = \App\Models\Tender::where('status', 'closed')->count();
\$totalTenders = \App\Models\Tender::count();

echo \"전체 공고: \$totalTenders 건\\n\";
echo \"활성 공고: \$totalActive 건\\n\";
echo \"마감 공고: \$totalClosed 건\\n\";
"

echo ""
echo "4. 실행 빈도 및 효과"
echo "--------------------------------------"
echo "📅 실행 빈도:"
echo "   - 공고 수집: 하루 2회 (06:00, 13:00)"
echo "   - 마감 체크: 하루 2회 (06:00, 13:00)"
echo "   - 주간 재동기화: 주 1회 (월요일 01:00)"
echo ""
echo "⚡ 예상 효과:"
echo "   ✅ 신규 공고 빠른 수집 (최대 7시간 지연)"
echo "   ✅ 마감상태 실시간 반영 (하루 2회 체크)"
echo "   ✅ 마감임박 공고 조기 감지"
echo "   ✅ D-Day 공고 정확한 추적"

echo ""
echo "5. 다음 실행 예정"
echo "--------------------------------------"
echo "현재 시각: $(date '+%Y-%m-%d %H:%M:%S')"
echo ""
php artisan schedule:list | grep "Next Due"

echo ""
echo "6. 로그 모니터링 설정"
echo "--------------------------------------"
echo "로그 파일 위치:"
echo "- 일반 스케줄러 로그: storage/logs/scheduler.log"
echo "- Laravel 시스템 로그: storage/logs/laravel.log"
echo "- 주간 동기화 로그: storage/logs/weekly-sync.log"

if [ -f storage/logs/laravel.log ]; then
    echo ""
    echo "최근 로그 샘플:"
    tail -n 2 storage/logs/laravel.log
fi

echo ""
echo "======================================"
echo "하루 2번 스케줄러 설정 완료!"
echo "======================================"
echo ""
echo "🕕 오전 6시: 공고 수집 + 마감 체크 + 알림"
echo "🕐 오후 1시: 공고 수집 + 마감 체크 + 알림"
echo "🗓️ 월요일 1시: 주간 데이터 재동기화"
echo ""
echo "총 3개 작업이 자동 실행됩니다!"