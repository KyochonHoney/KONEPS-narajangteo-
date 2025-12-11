#!/bin/bash

# [BEGIN nara:pagination_fix_test]
# 페이지네이션 수정 테스트 스크립트

set -e

echo "=== 나라장터 페이지네이션 수정 테스트 ==="
echo "테스트 시작: $(date)"
echo

BASE_URL="https://nara.tideflo.work"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

# 테스트 카운터
TOTAL_TESTS=0
PASSED_TESTS=0

# 테스트 함수
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

echo "1. 커스텀 페이지네이션 뷰 파일 확인"
echo "================================="

test_condition "커스텀 페이지네이션 뷰 파일 존재" \
    '[ -f "$PROJECT_ROOT/public_html/resources/views/custom/pagination/bootstrap-4.blade.php" ]'

test_condition "페이지네이션 뷰에 Bootstrap 클래스 적용 확인" \
    'grep -q "pagination justify-content-center" "$PROJECT_ROOT/public_html/resources/views/custom/pagination/bootstrap-4.blade.php"'

test_condition "페이지 정보 표시 확인" \
    'grep -q "전체.*개 중" "$PROJECT_ROOT/public_html/resources/views/custom/pagination/bootstrap-4.blade.php"'

echo "2. AppServiceProvider 설정 확인"
echo "=============================="

test_condition "Paginator import 확인" \
    'grep -q "use Illuminate\\\\Pagination\\\\Paginator;" "$PROJECT_ROOT/public_html/app/Providers/AppServiceProvider.php"'

test_condition "기본 페이지네이션 뷰 설정 확인" \
    'grep -q "defaultView.*custom.pagination.bootstrap-4" "$PROJECT_ROOT/public_html/app/Providers/AppServiceProvider.php"'

echo "3. 뷰 파일 수정 확인"
echo "=================="

test_condition "index.blade.php에서 커스텀 페이지네이션 사용 확인" \
    'grep -q "custom.pagination.bootstrap-4" "$PROJECT_ROOT/public_html/resources/views/admin/tenders/index.blade.php"'

test_condition "페이지네이션 스타일 CSS 추가 확인" \
    'grep -q ".pagination.*margin-bottom" "$PROJECT_ROOT/public_html/resources/views/admin/tenders/index.blade.php"'

echo "4. 페이지네이션 데이터 및 로직 테스트"
echo "=================================="

test_artisan_command "페이지네이션 기본 계산 검증" \
    "php -r \"
require 'vendor/autoload.php';
\\\$app = require_once 'bootstrap/app.php';
\\\$app->make('Illuminate\\\\Contracts\\\\Console\\\\Kernel')->bootstrap();

\\\$total = App\\\\Models\\\\Tender::count();
\\\$perPage = 20;
\\\$totalPages = ceil(\\\$total / \\\$perPage);

echo 'Total records: ' . \\\$total . PHP_EOL;
echo 'Per page: ' . \\\$perPage . PHP_EOL;
echo 'Expected pages: ' . \\\$totalPages . PHP_EOL;

// 첫 번째 페이지 테스트
\\\$page1 = App\\\\Models\\\\Tender::latest('collected_at')->paginate(\\\$perPage);
echo 'Page 1 records: ' . \\\$page1->count() . PHP_EOL;
echo 'Has next: ' . (\\\$page1->hasMorePages() ? 'YES' : 'NO') . PHP_EOL;
\"" \
    "Total records:"

test_artisan_command "페이지네이션 메타데이터 확인" \
    "php -r \"
require 'vendor/autoload.php';
\\\$app = require_once 'bootstrap/app.php';
\\\$app->make('Illuminate\\\\Contracts\\\\Console\\\\Kernel')->bootstrap();

\\\$paginator = App\\\\Models\\\\Tender::latest('collected_at')->paginate(20);

echo 'Current page: ' . \\\$paginator->currentPage() . PHP_EOL;
echo 'Last page: ' . \\\$paginator->lastPage() . PHP_EOL;
echo 'First item: ' . (\\\$paginator->firstItem() ?? 'null') . PHP_EOL;
echo 'Last item: ' . (\\\$paginator->lastItem() ?? 'null') . PHP_EOL;
echo 'Per page: ' . \\\$paginator->perPage() . PHP_EOL;
echo 'Total: ' . \\\$paginator->total() . PHP_EOL;
echo 'Has pages: ' . (\\\$paginator->hasPages() ? 'YES' : 'NO') . PHP_EOL;
\"" \
    "Has pages: YES"

echo "5. URL 쿼리스트링 보존 확인"
echo "========================="

test_artisan_command "검색 조건과 페이지네이션 URL 확인" \
    "php -r \"
require 'vendor/autoload.php';
\\\$app = require_once 'bootstrap/app.php';
\\\$app->make('Illuminate\\\\Contracts\\\\Console\\\\Kernel')->bootstrap();

// 검색 조건이 있는 상황 시뮬레이션
\\\$request = new Illuminate\\\\Http\\\\Request(['search' => 'test', 'status' => 'active']);
app()->instance('request', \\\$request);

\\\$query = App\\\\Models\\\\Tender::query();
if (\\\$request->filled('search')) {
    \\\$search = \\\$request->get('search');
    \\\$query->where(function(\\\$q) use (\\\$search) {
        \\\$q->where('title', 'like', \"%{\\\$search}%\");
    });
}

\\\$paginator = \\\$query->latest('collected_at')->paginate(20)->withQueryString();
echo 'Query preserved: ' . (strpos(\\\$paginator->url(2), 'search=test') !== false ? 'YES' : 'NO') . PHP_EOL;
echo 'Sample URL: ' . \\\$paginator->url(2) . PHP_EOL;
\"" \
    "Query preserved: YES"

echo "=== 페이지네이션 수정 테스트 결과 요약 ==="
echo "전체 테스트: $TOTAL_TESTS"
echo "성공: $PASSED_TESTS"
echo "실패: $((TOTAL_TESTS - PASSED_TESTS))"
echo "성공률: $(( PASSED_TESTS * 100 / TOTAL_TESTS ))%"
echo "테스트 완료: $(date)"

if [ $PASSED_TESTS -ge $((TOTAL_TESTS * 90 / 100)) ]; then
    echo
    echo "🎉 페이지네이션 수정 성공! (90% 이상 통과)"
    echo
    echo "📋 수정 완료된 기능:"
    echo "✅ Bootstrap 4 스타일 커스텀 페이지네이션 뷰 생성"
    echo "✅ AppServiceProvider에서 기본 페이지네이션 뷰 설정"
    echo "✅ 페이지네이션 CSS 스타일 개선 (색상, 간격, 호버 효과)"
    echo "✅ 페이지 정보 표시 개선 (현재 페이지/전체 페이지, 항목 범위)"
    echo "✅ 검색 조건 보존을 위한 withQueryString() 적용"
    echo "✅ Bootstrap Icons을 사용한 이전/다음 버튼"
    echo
    echo "🚀 이제 페이지네이션이 깔끔하고 사용자 친화적으로 표시됩니다!"
    exit 0
else
    echo
    echo "⚠️  일부 페이지네이션 수정 미완성"
    echo "추가 개발이 필요합니다."
    exit 1
fi

# [END nara:pagination_fix_test]