<?php

require_once __DIR__ . '/public_html/vendor/autoload.php';

$app = require_once __DIR__ . '/public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tender;

echo "IT 키워드 필터링 테스트\n";
echo "===================\n\n";

// 분류코드가 없는 공고들 확인
$tendersWithoutCode = Tender::where('pub_prcrmnt_clsfc_no', '')->get();

echo "분류코드가 없는 공고 분석 (총 " . $tendersWithoutCode->count() . "개):\n";
echo "---------------------------------------------\n";

$itKeywords = [
    '소프트웨어', 'SW', 'software',
    '시스템', 'system', '솔루션',
    '프로그램', 'program',
    '개발', '구축', '개선',
    '웹', 'web', '앱', 'app',
    '데이터베이스', 'DB', 'database',
    '정보시스템', '전산', '컴퓨터',
    '네트워크', 'network',
    '서버', 'server',
    '클라우드', 'cloud',
    '디지털서비스', 'digital',
    '데이터', 'data',
    '빅데이터', 'big data',
    'AI', '인공지능',
    '보안', 'security',
    '방화벽', 'firewall',
    '플랫폼', 'platform',
    '인터페이스', 'interface',
    '유지관리', '운영위탁',
    '커스터마이징', 'customizing'
];

function containsItKeywords($text, $keywords) {
    $text = strtolower($text);
    foreach ($keywords as $keyword) {
        if (strpos($text, strtolower($keyword)) !== false) {
            return $keyword;
        }
    }
    return false;
}

$shouldKeep = [];
$shouldRemove = [];

foreach ($tendersWithoutCode as $tender) {
    $text = $tender->title . ' ' . $tender->content;
    $foundKeyword = containsItKeywords($text, $itKeywords);
    
    if ($foundKeyword) {
        $shouldKeep[] = [
            'tender_no' => $tender->tender_no,
            'title' => $tender->title,
            'keyword' => $foundKeyword
        ];
    } else {
        $shouldRemove[] = [
            'tender_no' => $tender->tender_no,
            'title' => $tender->title
        ];
    }
}

echo "IT 키워드가 포함된 공고 (" . count($shouldKeep) . "개) - 유지:\n";
foreach ($shouldKeep as $keep) {
    echo "✅ {$keep['tender_no']}: {$keep['title']} (키워드: {$keep['keyword']})\n";
}

echo "\nIT 키워드가 없는 공고 (" . count($shouldRemove) . "개) - 제거 대상:\n";
foreach ($shouldRemove as $remove) {
    echo "❌ {$remove['tender_no']}: {$remove['title']}\n";
}

echo "\n=== 요약 ===\n";
echo "총 분류코드 없는 공고: " . $tendersWithoutCode->count() . "개\n";
echo "IT 관련 (유지): " . count($shouldKeep) . "개\n";
echo "비IT 관련 (제거): " . count($shouldRemove) . "개\n";

echo "\n분류코드가 없는 공고 중 " . count($shouldRemove) . "개를 제거해야 합니다.\n";