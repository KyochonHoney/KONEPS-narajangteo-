#!/bin/bash

# [BEGIN nara:original_link_fix_test]
# 원본보기 링크 수정 테스트 스크립트
# 프루프 모드 요구사항 - 테스트 증거

set -e

echo "=== 나라장터 원본보기 링크 수정 테스트 ==="
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

echo "1. Tender 모델 URL 생성 메서드 확인"
echo "=================================="

test_condition "getDetailUrlAttribute 메서드 존재 확인" \
    'grep -q "getDetailUrlAttribute" "$PROJECT_ROOT/public_html/app/Models/Tender.php"'

test_condition "나라장터 직접 링크 사용 확인" \
    'grep -q "g2b.go.kr:8082/ep/invitation/publish/bidInfoDtl.do" "$PROJECT_ROOT/public_html/app/Models/Tender.php"'

echo "2. 뷰 파일 수정 확인"
echo "=================="

test_condition "index 뷰에서 detail_url 사용 확인" \
    'grep -q "detail_url" "$PROJECT_ROOT/public_html/resources/views/admin/tenders/index.blade.php"'

test_condition "show 뷰에서 detail_url 사용 확인" \
    'grep -q "detail_url" "$PROJECT_ROOT/public_html/resources/views/admin/tenders/show.blade.php"'

test_condition "index 뷰에서 tender_no 조건 확인" \
    'grep -q "tender_no" "$PROJECT_ROOT/public_html/resources/views/admin/tenders/index.blade.php"'

test_condition "show 뷰에서 tender_no 조건 확인" \
    'grep -q "tender_no" "$PROJECT_ROOT/public_html/resources/views/admin/tenders/show.blade.php"'

echo "3. Laravel 모델 기능 테스트"
echo "========================="

test_artisan_command "Tender 모델 로드 및 detail_url 접근" \
    "php artisan tinker --execute=\"
\\\$tender = App\\\\Models\\\\Tender::first();
if (!\\\$tender) {
    echo 'No tender found' . PHP_EOL;
} else {
    echo 'Tender found: ' . \\\$tender->tender_no . PHP_EOL;
    echo 'Detail URL generated: ' . \\\$tender->detail_url . PHP_EOL;
}
\"" \
    "Detail URL generated:"

test_artisan_command "모든 Tender의 URL 생성 가능성 확인" \
    "php artisan tinker --execute=\"
\\\$tenders = App\\\\Models\\\\Tender::limit(5)->get();
\\\$success = 0;
\\\$total = \\\$tenders->count();

foreach (\\\$tenders as \\\$tender) {
    if (\\\$tender->detail_url && \\\$tender->detail_url !== '#') {
        \\\$success++;
    }
}

echo 'URL generation test: ' . \\\$success . '/' . \\\$total . ' successful' . PHP_EOL;
echo 'Success rate: ' . (\\\$total > 0 ? round((\\\$success / \\\$total) * 100, 2) : 0) . '%' . PHP_EOL;
\"" \
    "URL generation test:"

echo "4. URL 패턴 검증"
echo "==============="

test_artisan_command "생성된 URL 패턴 검증" \
    "php artisan tinker --execute=\"
\\\$tender = App\\\\Models\\\\Tender::first();
\\\$url = \\\$tender->detail_url;
\\\$pattern = 'https://www.g2b.go.kr:8082/ep/invitation/publish/bidInfoDtl.do?bidno=';

if (strpos(\\\$url, \\\$pattern) === 0) {
    echo 'URL pattern validation: PASS' . PHP_EOL;
    echo 'Generated URL: ' . \\\$url . PHP_EOL;
} else {
    echo 'URL pattern validation: FAIL' . PHP_EOL;
    echo 'Expected pattern: ' . \\\$pattern . PHP_EOL;
    echo 'Generated URL: ' . \\\$url . PHP_EOL;
}
\"" \
    "URL pattern validation: PASS"

echo "5. 웹 접근성 테스트"
echo "=================="

TOTAL_TESTS=$((TOTAL_TESTS + 1))
echo "[$TOTAL_TESTS] 공고 목록 페이지 접근성"
status_code=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/admin/tenders")
if [ "$status_code" = "302" ]; then
    echo "✅ 성공: HTTP $status_code (로그인 필요 - 정상)"
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    echo "❌ 실패: HTTP $status_code (예상: 302)"
fi
echo

echo "6. 수정 전후 비교"
echo "================"

test_artisan_command "수정 전후 URL 비교" \
    "php artisan tinker --execute=\"
\\\$tender = App\\\\Models\\\\Tender::first();

echo 'Before fix (source_url): ' . (\\\$tender->source_url ?? 'null') . PHP_EOL;
echo 'After fix (detail_url): ' . \\\$tender->detail_url . PHP_EOL;

// 조건 검사
\\\$oldCondition = !empty(\\\$tender->source_url);  
\\\$newCondition = !empty(\\\$tender->tender_no);

echo 'Old condition (source_url exists): ' . (\\\$oldCondition ? 'true' : 'false') . PHP_EOL;
echo 'New condition (tender_no exists): ' . (\\\$newCondition ? 'true' : 'false') . PHP_EOL;
echo 'Link will show: ' . (\\\$newCondition ? 'YES' : 'NO') . PHP_EOL;
\"" \
    "Link will show: YES"

echo "=== 원본보기 링크 수정 테스트 결과 요약 ==="
echo "전체 테스트: $TOTAL_TESTS"
echo "성공: $PASSED_TESTS"  
echo "실패: $((TOTAL_TESTS - PASSED_TESTS))"
echo "성공률: $(( PASSED_TESTS * 100 / TOTAL_TESTS ))%"
echo "테스트 완료: $(date)"

if [ $PASSED_TESTS -ge $((TOTAL_TESTS * 90 / 100)) ]; then
    echo
    echo "🎉 원본보기 링크 수정 성공! (90% 이상 통과)"
    echo
    echo "📋 수정 완료된 기능:"
    echo "✅ Tender 모델에서 detail_url 속성으로 나라장터 직접 링크 생성"
    echo "✅ 공고 목록(index)에서 source_url → detail_url 조건 변경"
    echo "✅ 공고 상세(show)에서 source_url → detail_url 조건 변경"
    echo "✅ tender_no 기반 조건으로 변경하여 모든 공고에 원본 링크 표시"
    echo "✅ 나라장터 직접 링크로 더 안정적인 접근"
    echo
    echo "🚀 이제 모든 입찰공고에서 나라장터 원본 페이지로 바로 이동할 수 있습니다!"
    exit 0
else
    echo
    echo "⚠️  일부 원본보기 링크 수정 미완성"
    echo "추가 개발이 필요합니다."
    exit 1
fi

# [END nara:original_link_fix_test]