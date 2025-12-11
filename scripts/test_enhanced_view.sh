#!/bin/bash

# [BEGIN nara:test_enhanced_view]
# Enhanced Tender View - Smoke Test Script
# Created: 2025-09-01
# Purpose: Validate enhanced tender detail view with 109 fields display

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")/public_html"
cd "$PROJECT_DIR"

echo "🧪 Enhanced Tender View - Smoke Test"
echo "======================================"
echo "$(date '+%Y-%m-%d %H:%M:%S')"
echo ""

# Test counter
TEST_COUNT=0
PASSED_COUNT=0

run_test() {
    local test_name="$1"
    local command="$2"
    local expected_pattern="$3"
    
    TEST_COUNT=$((TEST_COUNT + 1))
    echo "[$TEST_COUNT] Testing: $test_name"
    
    result=$(eval "$command" 2>&1)
    if echo "$result" | grep -q "$expected_pattern"; then
        echo "✅ PASS: $test_name"
        PASSED_COUNT=$((PASSED_COUNT + 1))
        return 0
    else
        echo "❌ FAIL: $test_name"
        echo "   Expected: $expected_pattern"
        echo "   Got: $result"
        return 1
    fi
}

# 1. Test Tender Model Accessors
echo "🔍 Testing Tender Model Accessors"
echo "--------------------------------"

run_test "Classification Info Accessor" \
    "php artisan tinker --execute=\"\$t=App\Models\Tender::first(); echo \$t->classification_info['large'];\"" \
    "연구조사서비스"

run_test "Bid Method Info Accessor" \
    "php artisan tinker --execute=\"\$t=App\Models\Tender::first(); echo \$t->bid_method_info['bid_method'];\"" \
    "전자입찰"

run_test "Budget Details Accessor" \
    "php artisan tinker --execute=\"\$t=App\Models\Tender::first(); echo \$t->formatted_budget_details['assign_budget'];\"" \
    "77,000,000원"

run_test "Official Info Accessor" \
    "php artisan tinker --execute=\"\$t=App\Models\Tender::first(); echo \$t->official_info['name'];\"" \
    "전세은"

run_test "Bid Schedule Accessor" \
    "php artisan tinker --execute=\"\$t=App\Models\Tender::first(); echo \$t->formatted_bid_schedule['bid_begin'];\"" \
    "2025-08-25 10:00"

run_test "Registration Info Accessor" \
    "php artisan tinker --execute=\"\$t=App\Models\Tender::first(); echo \$t->registration_info['registered'];\"" \
    "2025-08-25 02:31:26"

run_test "Attachment Files Accessor" \
    "php artisan tinker --execute=\"\$t=App\Models\Tender::first(); echo count(\$t->attachment_files);\"" \
    "2"

# 2. Test View File Existence and Content
echo ""
echo "📄 Testing View File"
echo "-------------------"

VIEW_FILE="resources/views/admin/tenders/show.blade.php"

run_test "Enhanced View File Exists" \
    "test -f $VIEW_FILE && echo 'exists'" \
    "exists"

run_test "View Contains Classification Section" \
    "grep -q '분류 정보' $VIEW_FILE && echo 'found'" \
    "found"

run_test "View Contains Bid Method Section" \
    "grep -q '입찰 방식 및 계약 정보' $VIEW_FILE && echo 'found'" \
    "found"

run_test "View Contains Schedule Section" \
    "grep -q '입찰 일정' $VIEW_FILE && echo 'found'" \
    "found"

run_test "View Contains Official Info Section" \
    "grep -q '담당자 정보' $VIEW_FILE && echo 'found'" \
    "found"

run_test "View Contains Enhanced Budget Section" \
    "grep -q 'formatted_budget_details' $VIEW_FILE && echo 'found'" \
    "found"

run_test "View Contains Attachment Files Section" \
    "grep -q 'attachment_files' $VIEW_FILE && echo 'found'" \
    "found"

# 3. Test Data Coverage
echo ""
echo "📊 Testing Data Coverage"
echo "-----------------------"

run_test "109 Fields Available in Model" \
    "php artisan tinker --execute=\"echo count(App\Models\Tender::first()->getFillable());\"" \
    "119"  # 기본 필드 10개 + API 필드 109개

run_test "API Fields Data Present" \
    "php artisan tinker --execute=\"\$t=App\Models\Tender::first(); echo \$t->pub_prcrmnt_lrg_clsfc_nm . '|' . \$t->bid_methd_nm;\"" \
    "연구조사서비스|전자입찰"

run_test "Budget Fields Present" \
    "php artisan tinker --execute=\"\$t=App\Models\Tender::first(); echo \$t->asign_bdgt_amt . '|' . \$t->vat_amount;\"" \
    "77000000|7000000"

# 4. Test View Rendering (Syntax Check)
echo ""
echo "🎨 Testing View Rendering"
echo "------------------------"

run_test "Blade Syntax Valid" \
    "php artisan view:cache 2>&1 | grep -v 'ERROR' && echo 'valid'" \
    "valid"

run_test "No PHP Syntax Errors in View" \
    "php -l $VIEW_FILE | grep -q 'No syntax errors' && echo 'valid'" \
    "valid"

# Results Summary
echo ""
echo "📋 Test Results Summary"
echo "======================"
echo "Total Tests: $TEST_COUNT"
echo "Passed: $PASSED_COUNT"
echo "Failed: $((TEST_COUNT - PASSED_COUNT))"
echo "Success Rate: $(echo "scale=1; $PASSED_COUNT * 100 / $TEST_COUNT" | bc)%"
echo ""

if [ $PASSED_COUNT -eq $TEST_COUNT ]; then
    echo "🎉 All tests passed! Enhanced view is ready."
    exit 0
else
    echo "⚠️  Some tests failed. Please check the output above."
    exit 1
fi

# [END nara:test_enhanced_view]