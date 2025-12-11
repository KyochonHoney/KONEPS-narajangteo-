#!/bin/bash

# 새로운 스케줄러 설정 검증 스크립트
# 작성일: 2025-09-10
# 목적: 매시간 → 매일 오후 6시 통합 작업으로 변경 후 검증

echo "======================================"
echo "새로운 스케줄러 설정 검증 테스트"
echo "======================================"

cd /home/tideflo/nara/public_html

echo "1. 변경된 스케줄러 목록 확인"
echo "--------------------------------------"
php artisan schedule:list

echo ""
echo "2. 통합 작업 기능 테스트 (매일 오후 6시 작업 내용)"
echo "--------------------------------------"
echo "=== 마감상태 업데이트 + 마감임박 알림 테스트 ==="

php artisan tinker --execute="
echo \"📋 1단계: 마감상태 자동 업데이트\\n\";
\$tenderCollector = app(\App\Services\TenderCollectorService::class);
\$updateStats = \$tenderCollector->updateTenderStatuses();

echo \"총 확인: {\$updateStats['total_checked']} 건\\n\";
echo \"상태 변경: {\$updateStats['status_changed']} 건\\n\";
echo \"오류: {\$updateStats['errors']} 건\\n\";

echo \"\\n📊 2단계: 마감임박 공고 확인 (3일 이내)\\n\";
\$urgentCount = \App\Models\Tender::where('status', 'active')
    ->whereNotNull('bid_clse_dt')
    ->whereRaw('DATE(bid_clse_dt) <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)')
    ->whereRaw('DATE(bid_clse_dt) >= CURDATE()')
    ->count();

echo \"마감임박 공고: \$urgentCount 건\\n\";

echo \"\\n🎯 3단계: D-Day 공고 확인 (오늘 마감)\\n\";
\$ddayCount = \App\Models\Tender::where('status', 'active')
    ->whereRaw('DATE(bid_clse_dt) = CURDATE()')
    ->count();

echo \"D-Day 공고: \$ddayCount 건\\n\";

echo \"\\n📈 4단계: 전체 현황\\n\";
\$totalActive = \App\Models\Tender::where('status', 'active')->count();
\$totalClosed = \App\Models\Tender::where('status', 'closed')->count();

echo \"활성 공고: \$totalActive 건\\n\";
echo \"마감 공고: \$totalClosed 건\\n\";
"

echo ""
echo "3. 스케줄러 변경 사항 요약"
echo "--------------------------------------"
echo "변경 전:"
echo "- 매시간 정각: 마감상태 자동 체크 (24회/일)"
echo "- 매일 오후 6시: 마감임박 공고 알림"
echo ""
echo "변경 후:"
echo "- 매일 오후 6시: 마감상태 업데이트 + 마감임박 알림 (통합 작업)"
echo ""
echo "장점:"
echo "✅ 불필요한 매시간 처리 제거"
echo "✅ 하루 1회로 시스템 부하 감소"
echo "✅ 마감상태 체크와 알림을 동시에 처리"
echo "✅ 포괄적인 현황 로깅"

echo ""
echo "4. 현재 스케줄러 작업 개수"
echo "--------------------------------------"
echo "총 3개 작업:"
echo "1. 매일 새벽 2시: 공고 자동 수집 (최근 7일)"
echo "2. 매주 월요일 1시: 주간 데이터 재동기화"
echo "3. 매일 오후 6시: 마감상태 업데이트 + 마감임박 알림 (신규 통합)"

echo ""
echo "5. 다음 실행 예정"
echo "--------------------------------------"
php artisan schedule:list | grep "Next Due"

echo ""
echo "6. 로그 확인"
echo "--------------------------------------"
if [ -f storage/logs/laravel.log ]; then
    echo "최근 로그 (마지막 3줄):"
    tail -n 3 storage/logs/laravel.log
else
    echo "로그 파일이 아직 생성되지 않았습니다."
fi

echo ""
echo "======================================"
echo "새로운 스케줄러 설정 검증 완료!"
echo "======================================"
echo ""
echo "⏰ 다음 통합 작업 실행: 매일 오후 6시"
echo "📋 작업 내용: 마감상태 체크 + 마감임박 알림 + 전체 현황"