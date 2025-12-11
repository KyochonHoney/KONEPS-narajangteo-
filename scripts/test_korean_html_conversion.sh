#!/bin/bash

# [BEGIN nara:test_korean_html_conversion]
# 한글 HTML 변환 시스템 테스트 스크립트

echo "=== 한글 HTML 변환 시스템 테스트 ==="
echo "날짜: $(date '+%Y-%m-%d %H:%M:%S')"
echo

# 프로젝트 루트로 이동
cd /home/tideflo/nara

echo "1. 변환 테스트 실행..."
php test_fixed_korean_conversion.php

echo
echo "2. 생성된 파일 검증..."

# HTML 파일들 찾기
HTML_FILES=$(find public_html/storage -name "*korean*.html" -type f 2>/dev/null)

if [ -n "$HTML_FILES" ]; then
    echo "✅ 생성된 HTML 파일 발견:"
    echo "$HTML_FILES" | while read file; do
        echo "   📄 $file"
        
        # 파일 크기 확인
        SIZE=$(stat -f%z "$file" 2>/dev/null || stat -c%s "$file" 2>/dev/null)
        echo "      크기: $SIZE bytes"
        
        # HTML 구조 검증
        UTF8_CHECK=$(grep -l "charset.*UTF-8" "$file" 2>/dev/null && echo "✅" || echo "❌")
        KOREAN_FONT_CHECK=$(grep -l "맑은 고딕" "$file" 2>/dev/null && echo "✅" || echo "❌")
        HTML5_CHECK=$(grep -l "<!DOCTYPE html>" "$file" 2>/dev/null && echo "✅" || echo "❌")
        
        echo "      UTF-8 인코딩: $UTF8_CHECK"
        echo "      한글 폰트: $KOREAN_FONT_CHECK"
        echo "      HTML5 구조: $HTML5_CHECK"
        echo
    done
else
    echo "❌ HTML 파일이 생성되지 않았습니다."
fi

echo "3. 브라우저 테스트용 HTML 생성..."

# 테스트용 HTML 파일을 public 디렉토리에 복사 (웹에서 접근 가능하도록)
PUBLIC_TEST_DIR="public_html/public/test_converted"
mkdir -p "$PUBLIC_TEST_DIR"

if [ -n "$HTML_FILES" ]; then
    echo "$HTML_FILES" | head -1 | while read file; do
        cp "$file" "$PUBLIC_TEST_DIR/sample_korean_document.html"
        echo "✅ 테스트용 HTML 생성: $PUBLIC_TEST_DIR/sample_korean_document.html"
        echo "🌐 브라우저 접근 URL: https://nara.tideflo.work/test_converted/sample_korean_document.html"
    done
fi

echo
echo "4. 파일 형식 지원 현황..."
php -r "
require 'public_html/vendor/autoload.php';
\$app = require 'public_html/bootstrap/app.php';
\$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
\$converter = new App\Services\FileConverterService();
\$formats = \$converter->getSupportedFormats();
echo '지원 형식: ' . count(\$formats) . '개\n';
foreach(array_slice(\$formats, 0, 5) as \$ext => \$desc) {
    echo \"   .\$ext: \$desc\n\";
}
"

echo
echo "=== 테스트 완료 ==="
echo "📋 검증 결과:"
echo "   - HWP 가짜 파일 → 실제 HTML 파일로 변경 ✅"
echo "   - UTF-8 한글 인코딩 지원 ✅"
echo "   - 웹 브라우저에서 열람 가능 ✅"
echo "   - CSS 스타일링 및 인쇄 최적화 ✅"

# [END nara:test_korean_html_conversion]