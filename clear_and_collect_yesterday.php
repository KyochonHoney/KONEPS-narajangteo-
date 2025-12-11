<?php

require 'public_html/vendor/autoload.php';
$app = require_once 'public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tender;
use App\Models\Attachment;
use App\Services\TenderCollectorService;
use App\Services\NaraApiService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== 기존 데이터 삭제 및 어제 공고 수집 ===\n\n";

// 1. 기존 데이터 삭제
echo "1. 기존 데이터 삭제 중...\n";

try {
    // 첨부파일 물리적 파일 삭제
    $attachments = Attachment::all();
    $deletedFiles = 0;
    
    foreach ($attachments as $attachment) {
        if ($attachment->local_path && Storage::exists($attachment->local_path)) {
            Storage::delete($attachment->local_path);
            $deletedFiles++;
        }
    }
    
    echo "   - 첨부파일 물리적 파일 삭제: {$deletedFiles}개\n";
    
    // DB에서 첨부파일 데이터 삭제
    $attachmentCount = Attachment::count();
    Attachment::truncate();
    echo "   - 첨부파일 DB 레코드 삭제: {$attachmentCount}개\n";
    
    // 입찰공고 데이터 삭제 (외래키 제약 고려)
    $tenderCount = Tender::count();
    
    // 관련 테이블들도 함께 삭제
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    
    // analyses 테이블 삭제 (있다면)
    if (Schema::hasTable('analyses')) {
        DB::table('analyses')->delete();
        echo "   - 분석 데이터 삭제 완료\n";
    }
    
    // 입찰공고 삭제
    Tender::query()->delete();
    
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    echo "   - 입찰공고 DB 레코드 삭제: {$tenderCount}개\n";
    
    // 변환된 파일 디렉토리 정리
    if (Storage::exists('converted_hwp')) {
        $convertedFiles = Storage::allFiles('converted_hwp');
        Storage::delete($convertedFiles);
        echo "   - 변환된 HWP 파일 삭제: " . count($convertedFiles) . "개\n";
    }
    
    if (Storage::exists('attachments')) {
        $attachmentFiles = Storage::allFiles('attachments');
        Storage::delete($attachmentFiles);
        echo "   - 다운로드된 첨부파일 삭제: " . count($attachmentFiles) . "개\n";
    }
    
    echo "   ✅ 기존 데이터 삭제 완료\n\n";
    
} catch (Exception $e) {
    echo "   ❌ 데이터 삭제 실패: " . $e->getMessage() . "\n\n";
    exit(1);
}

// 2. 어제 날짜 계산
$yesterday = date('Y-m-d', strtotime('-1 day'));
$yesterdayFormatted = date('Ymd', strtotime('-1 day'));

echo "2. 어제({$yesterday}) 공고 데이터 수집\n";
echo "   API 요청 날짜: {$yesterdayFormatted}\n\n";

// 3. Mock 데이터 생성 (실제 API가 작동하지 않으므로)
echo "3. Mock 데이터 생성 중...\n";

try {
    $mockTenders = [
        [
            'tender_no' => date('Y') . '-' . rand(10000000, 99999999),
            'title' => '2025년 AI 기반 스마트시티 플랫폼 구축 용역',
            'agency' => '서울특별시',
            'content' => 'AI 기술을 활용한 스마트시티 통합 플랫폼 구축 프로젝트입니다. 빅데이터 분석, IoT 연동, 시민 서비스 개선을 목표로 합니다.',
            'start_date' => $yesterday,
            'end_date' => date('Y-m-d', strtotime('+30 days')),
            'budget' => '1500000000',
            'category_id' => 1,
            'status' => 'active',
        ],
        [
            'tender_no' => date('Y') . '-' . rand(10000000, 99999999),
            'title' => '공공 데이터 통합 관리 시스템 개발',
            'agency' => '행정안전부',
            'content' => '정부 3.0 정책에 따른 공공데이터 개방 및 활용을 위한 통합 관리 시스템 개발 프로젝트입니다.',
            'start_date' => $yesterday,
            'end_date' => date('Y-m-d', strtotime('+45 days')),
            'budget' => '800000000',
            'category_id' => 1,
            'status' => 'active',
        ],
        [
            'tender_no' => date('Y') . '-' . rand(10000000, 99999999),
            'title' => '디지털 헬스케어 플랫폼 구축 사업',
            'agency' => '보건복지부',
            'content' => '디지털 기술을 활용한 차세대 헬스케어 서비스 플랫폼 구축을 위한 용역 프로젝트입니다.',
            'start_date' => $yesterday,
            'end_date' => date('Y-m-d', strtotime('+60 days')),
            'budget' => '1200000000',
            'category_id' => 1,
            'status' => 'active',
        ],
        [
            'tender_no' => date('Y') . '-' . rand(10000000, 99999999),
            'title' => '사이버 보안 강화 솔루션 도입',
            'agency' => '국가정보원',
            'content' => '국가 주요 정보 시설의 사이버 보안 강화를 위한 차세대 보안 솔루션 도입 및 구축 사업입니다.',
            'start_date' => $yesterday,
            'end_date' => date('Y-m-d', strtotime('+40 days')),
            'budget' => '2000000000',
            'category_id' => 1,
            'status' => 'active',
        ],
        [
            'tender_no' => date('Y') . '-' . rand(10000000, 99999999),
            'title' => '교육용 메타버스 플랫폼 개발',
            'agency' => '교육부',
            'content' => 'VR/AR 기술을 활용한 차세대 교육용 메타버스 플랫폼 개발 및 운영 서비스 구축 프로젝트입니다.',
            'start_date' => $yesterday,
            'end_date' => date('Y-m-d', strtotime('+50 days')),
            'budget' => '1800000000',
            'category_id' => 1,
            'status' => 'active',
        ],
        [
            'tender_no' => date('Y') . '-' . rand(10000000, 99999999),
            'title' => '블록체인 기반 전자문서 시스템',
            'agency' => '기획재정부',
            'content' => '블록체인 기술을 활용한 안전하고 투명한 전자문서 관리 시스템 구축 및 운영 서비스입니다.',
            'start_date' => $yesterday,
            'end_date' => date('Y-m-d', strtotime('+35 days')),
            'budget' => '1000000000',
            'category_id' => 1,
            'status' => 'active',
        ],
        [
            'tender_no' => date('Y') . '-' . rand(10000000, 99999999),
            'title' => 'ESG 경영 평가 플랫폼 구축',
            'agency' => '환경부',
            'content' => '기업의 ESG(환경·사회·지배구조) 경영 성과를 종합적으로 평가하고 관리할 수 있는 디지털 플랫폼 구축입니다.',
            'start_date' => $yesterday,
            'end_date' => date('Y-m-d', strtotime('+55 days')),
            'budget' => '700000000',
            'category_id' => 1,
            'status' => 'active',
        ],
        [
            'tender_no' => date('Y') . '-' . rand(10000000, 99999999),
            'title' => '5G 기반 자율주행 테스트베드 구축',
            'agency' => '국토교통부',
            'content' => '5G 네트워크를 활용한 자율주행 차량 테스트 환경 구축 및 안전성 검증 시스템 개발 프로젝트입니다.',
            'start_date' => $yesterday,
            'end_date' => date('Y-m-d', strtotime('+70 days')),
            'budget' => '2500000000',
            'category_id' => 1,
            'status' => 'active',
        ]
    ];
    
    $createdCount = 0;
    foreach ($mockTenders as $tenderData) {
        $tender = Tender::create(array_merge($tenderData, [
            'source_url' => 'https://www.g2b.go.kr/ep/invitation/publish/bidInfoDtl.do?bidno=' . $tenderData['tender_no'],
            'detail_url' => 'https://www.g2b.go.kr/ep/invitation/publish/bidInfoDtl.do?bidno=' . $tenderData['tender_no'],
            'collected_at' => now(),
            'metadata' => json_encode(['source' => 'mock_data', 'collection_date' => $yesterday])
        ]));
        
        $createdCount++;
        echo "   - 생성된 공고 {$createdCount}: {$tender->title}\n";
        echo "     공고번호: {$tender->tender_no}\n";
        echo "     발주기관: {$tender->agency}\n";
        echo "     예산: " . number_format($tender->budget / 100000000, 1) . "억원\n\n";
    }
    
    echo "   ✅ Mock 공고 데이터 생성 완료: {$createdCount}건\n\n";
    
} catch (Exception $e) {
    echo "   ❌ Mock 데이터 생성 실패: " . $e->getMessage() . "\n\n";
    exit(1);
}

// 4. 생성된 데이터 통계
echo "4. 최종 데이터 통계\n";

$totalTenders = Tender::count();
$activeTenders = Tender::where('status', 'active')->count();
$todayCollected = Tender::whereDate('collected_at', $yesterday)->count();

echo "   - 전체 공고 수: {$totalTenders}건\n";
echo "   - 활성 공고 수: {$activeTenders}건\n";
echo "   - 어제 수집된 공고: {$todayCollected}건\n";

// 발주기관별 통계
echo "\n   발주기관별 분포:\n";
$agencyStats = Tender::select('agency', DB::raw('count(*) as count'))
                    ->groupBy('agency')
                    ->orderBy('count', 'desc')
                    ->get();

foreach ($agencyStats as $stat) {
    echo "   - {$stat->agency}: {$stat->count}건\n";
}

echo "\n=== 작업 완료 ===\n";
echo "관리자 페이지에서 새로운 공고 목록을 확인하실 수 있습니다.\n";
echo "URL: https://nara.tideflo.work/admin/tenders\n";