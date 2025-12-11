#!/bin/bash

# Test Enhanced View - Proof Mode
# 증명모드: 뷰 페이지 수정 검증 테스트

echo "======================================"
echo "Enhanced View 수정사항 검증 테스트"
echo "======================================"
echo

# 테스트 시작 시간
TEST_START=$(date '+%Y-%m-%d %H:%M:%S')
echo "테스트 시작: $TEST_START"
echo

cd /home/tideflo/nara

# 1. Laravel 애플리케이션 상태 확인
echo "1. Laravel 애플리케이션 상태 확인"
echo "--------------------------------"

if php public_html/artisan --version > /dev/null 2>&1; then
    echo "✅ Laravel 애플리케이션 정상"
    php public_html/artisan --version
else
    echo "❌ Laravel 애플리케이션 오류"
    exit 1
fi
echo

# 2. 데이터베이스 연결 및 데이터 확인
echo "2. 데이터베이스 연결 및 테스트 데이터 확인"
echo "-------------------------------------------"

TENDER_COUNT=$(php public_html/artisan tinker --execute="echo App\Models\Tender::count();" 2>/dev/null | tail -1)
if [ "$TENDER_COUNT" -gt 0 ]; then
    echo "✅ 데이터베이스 연결 정상 - $TENDER_COUNT 개 공고"
else
    echo "❌ 데이터베이스 연결 실패 또는 데이터 없음"
    exit 1
fi
echo

# 3. 뷰 파일 수정사항 검증
echo "3. 뷰 파일 수정사항 검증"
echo "------------------------"

VIEW_FILE="/home/tideflo/nara/public_html/resources/views/admin/tenders/show.blade.php"

if [ -f "$VIEW_FILE" ]; then
    echo "✅ 뷰 파일 존재: $VIEW_FILE"
    
    # 수요기관 변경 확인
    if grep -q "수요기관:" "$VIEW_FILE"; then
        echo "✅ '수요기관:' 라벨 변경 확인됨"
    else
        echo "❌ '수요기관:' 라벨 변경 실패"
    fi
    
    # 수요기관 담당자 변경 확인
    if grep -q "수요기관 담당자:" "$VIEW_FILE"; then
        echo "✅ '수요기관 담당자:' 라벨 변경 확인됨"
    else
        echo "❌ '수요기관 담당자:' 라벨 변경 실패"
    fi
    
    # 공고종류 추가 확인
    if grep -q "ntce_kind_nm" "$VIEW_FILE"; then
        echo "✅ 공고종류(ntce_kind_nm) 필드 추가 확인됨"
    else
        echo "❌ 공고종류(ntce_kind_nm) 필드 추가 실패"
    fi
    
    # 업종코드 추가 확인
    if grep -q "업종코드:" "$VIEW_FILE"; then
        echo "✅ 업종코드 표시 추가 확인됨"
    else
        echo "❌ 업종코드 표시 추가 실패"
    fi
    
    # 색상 코딩 확인
    if grep -q "bg-danger.*재공고\|bg-success.*변경공고" "$VIEW_FILE"; then
        echo "✅ 공고종류 색상 코딩 추가 확인됨"
    else
        echo "❌ 공고종류 색상 코딩 추가 실패"
    fi
    
else
    echo "❌ 뷰 파일 없음: $VIEW_FILE"
    exit 1
fi
echo

# 4. Tender 모델 수정사항 확인
echo "4. Tender 모델 수정사항 확인"
echo "---------------------------"

MODEL_FILE="/home/tideflo/nara/public_html/app/Models/Tender.php"

if [ -f "$MODEL_FILE" ]; then
    echo "✅ Tender 모델 파일 존재: $MODEL_FILE"
    
    # safeExtractString 메서드 공개 여부 확인
    if grep -q "public function safeExtractString" "$MODEL_FILE"; then
        echo "✅ safeExtractString 메서드가 public으로 변경됨"
    else
        echo "❌ safeExtractString 메서드가 public으로 변경되지 않음"
    fi
    
    # ntce_kind_nm 필드 포함 확인
    if grep -q "ntce_kind_nm" "$MODEL_FILE"; then
        echo "✅ ntce_kind_nm 필드가 fillable에 포함됨"
    else
        echo "❌ ntce_kind_nm 필드가 fillable에 포함되지 않음"
    fi
    
else
    echo "❌ Tender 모델 파일 없음: $MODEL_FILE"
    exit 1
fi
echo

# 5. 실제 데이터로 기능 테스트
echo "5. 실제 데이터로 기능 테스트"
echo "---------------------------"

SAMPLE_TENDER_DATA=$(php public_html/artisan tinker --execute="
\$tender = App\Models\Tender::first(); 
if (\$tender) {
    echo 'Tender ID: ' . \$tender->id . \"\n\";
    echo 'Agency: ' . \$tender->agency . \"\n\";
    echo 'Exctv NM: ' . (\$tender->exctv_nm ?: 'None') . \"\n\";
    echo 'Ntce Kind NM: ' . (\$tender->ntce_kind_nm ?: 'None') . \"\n\";
    echo 'Classification Code: ' . (\$tender->classification_info['code'] ?: 'None') . \"\n\";
} else {
    echo 'No tender data found';
}
" 2>/dev/null | tail -6)

if [ -n "$SAMPLE_TENDER_DATA" ]; then
    echo "✅ 샘플 데이터 조회 성공:"
    echo "$SAMPLE_TENDER_DATA"
else
    echo "❌ 샘플 데이터 조회 실패"
fi
echo

# 6. 구문 검증 (PHP Syntax Check)
echo "6. PHP 구문 검증"
echo "---------------"

if php -l "$VIEW_FILE" > /dev/null 2>&1; then
    echo "✅ 뷰 파일 PHP 구문 정상"
else
    echo "❌ 뷰 파일 PHP 구문 오류"
    php -l "$VIEW_FILE"
fi

if php -l "$MODEL_FILE" > /dev/null 2>&1; then
    echo "✅ 모델 파일 PHP 구문 정상"
else
    echo "❌ 모델 파일 PHP 구문 오류" 
    php -l "$MODEL_FILE"
fi
echo

# 테스트 종료 시간
TEST_END=$(date '+%Y-%m-%d %H:%M:%S')
echo "테스트 종료: $TEST_END"
echo

echo "======================================"
echo "Enhanced View 수정사항 검증 완료"
echo "======================================"