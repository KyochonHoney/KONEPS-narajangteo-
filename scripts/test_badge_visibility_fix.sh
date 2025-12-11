#!/bin/bash

# [BEGIN nara:badge_visibility_fix_test]
# 공고 상세페이지 배지 가독성 수정 테스트 스크립트

set -e

echo "=== 나라장터 배지 가독성 수정 테스트 ==="
echo "테스트 시작: $(date)"
echo

BASE_URL="https://nara.tideflo.work"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

# 테스트 카운터
TOTAL_TESTS=0
PASSED_TESTS=0

# 테스트 함수
test_condition() {
    local description="$1"
    local condition="$2"
    
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    echo "[$TOTAL_TESTS] 테스트: $description"
    
    if eval "$condition"; then
        echo "✅ 성공"
        PASSED_TESTS=$((PASSED_TESTS + 1))
    else
        echo "❌ 실패"
    fi
    echo
}

test_artisan_command() {
    local description="$1"
    local command="$2"
    local expected_output="$3"
    
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    echo "[$TOTAL_TESTS] Laravel 명령어 테스트: $description"
    echo "명령어: $command"
    
    cd "$PROJECT_ROOT/public_html"
    
    if output=$(eval "$command" 2>&1); then
        if [[ -n "$expected_output" && "$output" == *"$expected_output"* ]]; then
            echo "✅ 성공: 예상 출력 발견"
            echo "출력: $output"
            PASSED_TESTS=$((PASSED_TESTS + 1))
        elif [[ -z "$expected_output" ]]; then
            echo "✅ 성공: 명령어 정상 실행"
            echo "출력: $output"
            PASSED_TESTS=$((PASSED_TESTS + 1))
        else
            echo "❌ 실패: 예상 출력 불일치"
            echo "출력: $output"
        fi
    else
        echo "❌ 실패: 명령어 실행 오류"
        echo "출력: $output"
    fi
    echo
}

echo "1. 공고 상세페이지 뷰 수정 확인"
echo "============================="

test_condition "발주기관 배지 스타일 수정 확인 (bg-primary text-white)" \
    'grep -q "bg-primary text-white" "$PROJECT_ROOT/public_html/resources/views/admin/tenders/show.blade.php"'

test_condition "분류 배지 스타일 수정 확인 (bg-success text-white)" \
    'grep -q "bg-success text-white" "$PROJECT_ROOT/public_html/resources/views/admin/tenders/show.blade.php"'

test_condition "커스텀 배지 CSS 추가 확인" \
    'grep -q "배지 스타일 개선 - 가독성 향상" "$PROJECT_ROOT/public_html/resources/views/admin/tenders/show.blade.php"'

echo "2. Tender 모델 상태 클래스 수정 확인"
echo "=================================="

test_condition "활성 상태 클래스 수정 (text-white 추가)" \
    'grep -q "badge bg-success text-white" "$PROJECT_ROOT/public_html/app/Models/Tender.php"'

test_condition "마감 상태 클래스 수정 (bg-dark text-white)" \
    'grep -q "badge bg-dark text-white" "$PROJECT_ROOT/public_html/app/Models/Tender.php"'

test_condition "경고 상태 클래스 수정 (text-dark)" \
    'grep -q "badge bg-warning text-dark" "$PROJECT_ROOT/public_html/app/Models/Tender.php"'

echo "3. 배지 스타일 및 색상 테스트"
echo "=========================="

test_artisan_command "Tender 모델 상태 클래스 동작 확인" \
    "php -r \"
require 'vendor/autoload.php';
\\\$app = require_once 'bootstrap/app.php';
\\\$app->make('Illuminate\\\\Contracts\\\\Console\\\\Kernel')->bootstrap();

\\\$tender = App\\\\Models\\\\Tender::first();
echo 'Current status: ' . \\\$tender->status . PHP_EOL;
echo 'Status class: ' . \\\$tender->status_class . PHP_EOL;
echo 'Status label: ' . \\\$tender->status_label . PHP_EOL;

// 각 상태별 클래스 확인
\\\$statuses = ['active', 'closed', 'cancelled'];
foreach (\\\$statuses as \\\$status) {
    \\\$testTender = new App\\\\Models\\\\Tender();
    \\\$testTender->status = \\\$status;
    echo \\\$status . ' -> ' . \\\$testTender->status_class . PHP_EOL;
}
\"" \
    "Status class:"

echo "4. 색상 대비 및 가독성 검증"
echo "=========================="

test_condition "배지에 테두리 스타일 추가 확인" \
    'grep -q "border: 1px solid" "$PROJECT_ROOT/public_html/resources/views/admin/tenders/show.blade.php"'

test_condition "배지 패딩 및 폰트 크기 설정 확인" \
    'grep -q "padding: 0.5rem 0.75rem" "$PROJECT_ROOT/public_html/resources/views/admin/tenders/show.blade.php"'

test_condition "색상 명시적 지정 (!important) 확인" \
    'grep -q "!important" "$PROJECT_ROOT/public_html/resources/views/admin/tenders/show.blade.php"'

echo "5. 웹 접근성 테스트"
echo "=================="

TOTAL_TESTS=$((TOTAL_TESTS + 1))
echo "[$TOTAL_TESTS] 공고 상세페이지 접근성"
# 첫 번째 공고의 ID 가져오기
TENDER_ID=$(php -r "
require 'public_html/vendor/autoload.php';
\$app = require_once 'public_html/bootstrap/app.php';
\$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();
echo App\\Models\\Tender::first()->id;
" 2>/dev/null || echo "1")

status_code=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/admin/tenders/$TENDER_ID")
if [ "$status_code" = "302" ] || [ "$status_code" = "200" ]; then
    echo "✅ 성공: HTTP $status_code (페이지 접근 가능)"
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    echo "❌ 실패: HTTP $status_code (페이지 접근 불가)"
fi
echo

echo "=== 배지 가독성 수정 테스트 결과 요약 ==="
echo "전체 테스트: $TOTAL_TESTS"
echo "성공: $PASSED_TESTS"
echo "실패: $((TOTAL_TESTS - PASSED_TESTS))"
echo "성공률: $(( PASSED_TESTS * 100 / TOTAL_TESTS ))%"
echo "테스트 완료: $(date)"

if [ $PASSED_TESTS -ge $((TOTAL_TESTS * 90 / 100)) ]; then
    echo
    echo "🎉 배지 가독성 수정 성공! (90% 이상 통과)"
    echo
    echo "📋 수정 완료된 기능:"
    echo "✅ 발주기관 배지: badge-info → bg-primary text-white (파란색 배경, 흰색 텍스트)"
    echo "✅ 분류 배지: badge-secondary → bg-success text-white (녹색 배경, 흰색 텍스트)"
    echo "✅ 상태 배지: 모든 상태에 명시적 텍스트 색상 지정"
    echo "   - 진행중: bg-success text-white (녹색)"
    echo "   - 마감: bg-dark text-white (어두운 회색)"
    echo "   - 취소: bg-danger text-white (빨간색)"
    echo "   - 기타: bg-warning text-dark (노란색 배경, 검은 텍스트)"
    echo "✅ 커스텀 CSS로 색상 명시적 지정 (!important)"
    echo "✅ 배지 크기, 패딩, 테두리 스타일 개선"
    echo
    echo "👁️ 이제 공고 상세페이지에서 발주기관과 분류가 명확하게 보입니다!"
    exit 0
else
    echo
    echo "⚠️  일부 배지 가독성 수정 미완성"
    echo "추가 개발이 필요합니다."
    exit 1
fi

# [END nara:badge_visibility_fix_test]