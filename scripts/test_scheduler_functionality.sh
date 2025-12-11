#!/bin/bash

# 스케줄러 기능 전체 검증 스크립트
# 작성일: 2025-09-10
# 목적: 스케줄러 설정 및 동작 확인

echo "======================================"
echo "나라장터 스케줄러 기능 검증 테스트"
echo "======================================"

cd /home/tideflo/nara/public_html

echo "1. 스케줄러 목록 확인"
echo "--------------------------------------"
php artisan schedule:list

echo ""
echo "2. 현재 데이터베이스 상태 확인"
echo "--------------------------------------"
php artisan tinker --execute="
\$totalTenders = \App\Models\Tender::count();
\$activeTenders = \App\Models\Tender::where('status', 'active')->count();
\$closedTenders = \App\Models\Tender::where('status', 'closed')->count();
\$urgentTenders = \App\Models\Tender::where('status', 'active')
    ->whereNotNull('bid_clse_dt')
    ->where('bid_clse_dt', '<=', now()->addDays(3))
    ->where('bid_clse_dt', '>=', now())
    ->count();

echo \"전체 공고: \$totalTenders 건\\n\";
echo \"활성 공고: \$activeTenders 건\\n\";
echo \"마감 공고: \$closedTenders 건\\n\";
echo \"마감임박 공고 (3일이내): \$urgentTenders 건\\n\";
"

echo ""
echo "3. 마감상태 업데이트 로직 테스트"
echo "--------------------------------------"
php artisan tinker --execute="
echo \"=== 상태 업데이트 실행 ===\\n\";
\$stats = app(\App\Services\TenderCollectorService::class)->updateTenderStatuses();
echo \"총 확인: {\$stats['total_checked']} 건\\n\";
echo \"상태 변경: {\$stats['status_changed']} 건\\n\";
echo \"오류: {\$stats['errors']} 건\\n\";
"

echo ""
echo "4. 공고 데이터 수집 테스트 (오늘)"
echo "--------------------------------------"
php artisan tender:collect --today

echo ""
echo "5. 스케줄러 작업별 세부 정보"
echo "--------------------------------------"
echo "매일 오전 2시: 최근 7일 공고 자동 수집"
echo "매시간: 공고 마감상태 자동 업데이트 (새벽 2-3시 제외)"
echo "매주 월요일 오전 1시: 주간 데이터 정합성 재확인"
echo "매일 오후 6시: 마감임박 공고 알림"

echo ""
echo "6. 시스템 크론 작업 설정 가이드"
echo "--------------------------------------"
echo "다음 명령어를 시스템 crontab에 추가하세요:"
echo "* * * * * cd /home/tideflo/nara/public_html && php artisan schedule:run >> /dev/null 2>&1"
echo ""
echo "설정 명령어: crontab -e"

echo ""
echo "7. 로그 파일 확인"
echo "--------------------------------------"
if [ -f storage/logs/scheduler.log ]; then
    echo "스케줄러 로그 (최근 5줄):"
    tail -n 5 storage/logs/scheduler.log
else
    echo "스케줄러 로그 파일이 아직 생성되지 않았습니다."
fi

echo ""
if [ -f storage/logs/laravel.log ]; then
    echo "Laravel 로그 (최근 3줄):"
    tail -n 3 storage/logs/laravel.log
else
    echo "Laravel 로그 파일이 없습니다."
fi

echo ""
echo "======================================"
echo "스케줄러 검증 완료!"
echo "======================================"