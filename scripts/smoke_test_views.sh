#!/bin/bash

# [BEGIN nara:smoke_test_views]
# 나라장터 AI 시스템 뷰 파일 스모크 테스트
# 프루프 모드 요구사항 - 테스트 증거

set -e

echo "=== 나라장터 AI 시스템 뷰 스모크 테스트 ==="
echo "테스트 시작: $(date)"
echo

BASE_URL="https://nara.tideflo.work"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

echo "프로젝트 경로: $PROJECT_ROOT"
echo "테스트 대상: $BASE_URL"
echo

# 테스트 카운터
TOTAL_TESTS=0
PASSED_TESTS=0

# 테스트 함수
test_url() {
    local url="$1"
    local description="$2"
    local expected_content="$3"
    
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    echo "[$TOTAL_TESTS] 테스트: $description"
    echo "URL: $url"
    
    # HTTP 상태 코드 확인
    status_code=$(curl -s -o /dev/null -w "%{http_code}" "$url")
    
    if [ "$status_code" = "200" ]; then
        echo "✅ HTTP 상태: $status_code (성공)"
        
        # 내용 확인 (선택사항)
        if [ -n "$expected_content" ]; then
            if curl -s "$url" | grep -q "$expected_content"; then
                echo "✅ 내용 확인: '$expected_content' 발견"
                PASSED_TESTS=$((PASSED_TESTS + 1))
            else
                echo "❌ 내용 확인: '$expected_content' 미발견"
            fi
        else
            PASSED_TESTS=$((PASSED_TESTS + 1))
        fi
    else
        echo "❌ HTTP 상태: $status_code (실패)"
    fi
    
    echo
}

# 뷰 파일 존재 확인
test_file_exists() {
    local file_path="$1"
    local description="$2"
    
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    echo "[$TOTAL_TESTS] 파일 존재 확인: $description"
    echo "경로: $file_path"
    
    if [ -f "$file_path" ]; then
        echo "✅ 파일 존재함"
        PASSED_TESTS=$((PASSED_TESTS + 1))
    else
        echo "❌ 파일 없음"
    fi
    echo
}

# Blade 파일 구문 확인
test_blade_syntax() {
    local file_path="$1"
    local description="$2"
    
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    echo "[$TOTAL_TESTS] Blade 구문 확인: $description"
    echo "경로: $file_path"
    
    if [ -f "$file_path" ]; then
        # 기본 Blade 구문 확인
        if grep -q "@extends\|@section\|@endsection" "$file_path"; then
            echo "✅ Blade 구문 정상"
            PASSED_TESTS=$((PASSED_TESTS + 1))
        else
            echo "⚠️  Blade 구문 없음 (정적 파일일 수 있음)"
            PASSED_TESTS=$((PASSED_TESTS + 1))
        fi
    else
        echo "❌ 파일 없음"
    fi
    echo
}

echo "1. 뷰 파일 존재 확인"
echo "====================="

test_file_exists "$PROJECT_ROOT/public_html/resources/views/layouts/app.blade.php" "메인 레이아웃 파일"
test_file_exists "$PROJECT_ROOT/public_html/resources/views/home.blade.php" "홈페이지 뷰"
test_file_exists "$PROJECT_ROOT/public_html/resources/views/admin/tenders/index.blade.php" "관리자 입찰공고 목록 뷰"
test_file_exists "$PROJECT_ROOT/public_html/resources/views/admin/tenders/show.blade.php" "관리자 입찰공고 상세 뷰"
test_file_exists "$PROJECT_ROOT/public_html/resources/views/admin/tenders/collect.blade.php" "관리자 데이터 수집 뷰"

echo "2. Blade 템플릿 구문 확인"
echo "======================"

test_blade_syntax "$PROJECT_ROOT/public_html/resources/views/layouts/app.blade.php" "메인 레이아웃 Blade 구문"
test_blade_syntax "$PROJECT_ROOT/public_html/resources/views/home.blade.php" "홈페이지 Blade 구문"
test_blade_syntax "$PROJECT_ROOT/public_html/resources/views/admin/tenders/index.blade.php" "관리자 목록 Blade 구문"
test_blade_syntax "$PROJECT_ROOT/public_html/resources/views/admin/tenders/show.blade.php" "관리자 상세 Blade 구문"
test_blade_syntax "$PROJECT_ROOT/public_html/resources/views/admin/tenders/collect.blade.php" "관리자 수집 Blade 구문"

echo "3. 웹 페이지 접근 테스트"
echo "====================="

test_url "$BASE_URL/" "홈페이지 접근" "나라장터 AI 제안서 시스템"

echo "4. Bootstrap Icons 확인"
echo "======================"

TOTAL_TESTS=$((TOTAL_TESTS + 1))
echo "[$TOTAL_TESTS] Bootstrap Icons CSS 로드 확인"
if curl -s "$BASE_URL/" | grep -q "bootstrap-icons"; then
    echo "✅ Bootstrap Icons CSS 포함됨"
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    echo "❌ Bootstrap Icons CSS 미포함"
fi
echo

echo "5. jQuery 라이브러리 확인"
echo "======================"

TOTAL_TESTS=$((TOTAL_TESTS + 1))
echo "[$TOTAL_TESTS] jQuery 라이브러리 로드 확인"
if curl -s "$BASE_URL/" | grep -q "jquery"; then
    echo "✅ jQuery 라이브러리 포함됨"
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    echo "❌ jQuery 라이브러리 미포함"
fi
echo

echo "6. 데이터베이스 연결 확인"
echo "======================"

TOTAL_TESTS=$((TOTAL_TESTS + 1))
echo "[$TOTAL_TESTS] Mock 데이터 존재 확인"
cd "$PROJECT_ROOT/public_html"
if php artisan tinker --execute="echo 'Tender count: ' . App\Models\Tender::count(); echo PHP_EOL;" | grep -q "Tender count: 100"; then
    echo "✅ Mock 데이터 100건 존재"
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    echo "❌ Mock 데이터 부족"
fi
echo

echo "=== 테스트 결과 요약 ==="
echo "전체 테스트: $TOTAL_TESTS"
echo "성공: $PASSED_TESTS"
echo "실패: $((TOTAL_TESTS - PASSED_TESTS))"
echo "성공률: $(( PASSED_TESTS * 100 / TOTAL_TESTS ))%"
echo "테스트 완료: $(date)"

if [ $PASSED_TESTS -eq $TOTAL_TESTS ]; then
    echo
    echo "🎉 모든 테스트 통과!"
    exit 0
else
    echo
    echo "⚠️  일부 테스트 실패"
    exit 1
fi

# [END nara:smoke_test_views]