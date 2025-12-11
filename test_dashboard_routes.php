<?php

require 'public_html/vendor/autoload.php';
$app = require_once 'public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== 대시보드 라우트 테스트 ===\n\n";

// 테스트할 라우트들
$routesToTest = [
    'admin.dashboard' => '/admin/dashboard',
    'admin.tenders.index' => '/admin/tenders',
    'admin.attachments.index' => '/admin/attachments',
    'admin.analyses.index' => '/admin/analyses',
    'admin.tenders.collect' => '/admin/tenders/collect'
];

echo "1. 라우트 존재 여부 확인...\n";
foreach ($routesToTest as $name => $path) {
    try {
        $url = route($name);
        echo "   ✅ {$name}: {$url}\n";
    } catch (Exception $e) {
        echo "   ❌ {$name}: 라우트 없음 - " . $e->getMessage() . "\n";
    }
}

echo "\n2. 대시보드 뷰 파일 확인...\n";
$viewFiles = [
    'admin.dashboard' => 'resources/views/admin/dashboard.blade.php',
    'admin.tenders.index' => 'resources/views/admin/tenders/index.blade.php',
    'admin.attachments.index' => 'resources/views/admin/attachments/index.blade.php',
    'admin.analyses.index' => 'resources/views/admin/analyses/index.blade.php'
];

foreach ($viewFiles as $viewName => $filePath) {
    if (file_exists('public_html/' . $filePath)) {
        echo "   ✅ {$viewName}: {$filePath} 존재\n";
    } else {
        echo "   ❌ {$viewName}: {$filePath} 없음\n";
    }
}

echo "\n3. 컨트롤러 메서드 확인...\n";
$controllers = [
    'AuthController@adminDashboard' => 'app/Http/Controllers/AuthController.php',
    'Admin\\TenderController@index' => 'app/Http/Controllers/Admin/TenderController.php',
    'Admin\\AttachmentController@index' => 'app/Http/Controllers/Admin/AttachmentController.php',
    'Admin\\AnalysisController@index' => 'app/Http/Controllers/Admin/AnalysisController.php'
];

foreach ($controllers as $method => $filePath) {
    if (file_exists('public_html/' . $filePath)) {
        echo "   ✅ {$method}: 컨트롤러 파일 존재\n";
    } else {
        echo "   ❌ {$method}: 컨트롤러 파일 없음\n";
    }
}

echo "\n4. 대시보드 메뉴 링크 검증...\n";
$dashboardFile = 'public_html/resources/views/admin/dashboard.blade.php';
if (file_exists($dashboardFile)) {
    $content = file_get_contents($dashboardFile);
    
    // 빈 링크 확인
    $emptyLinks = substr_count($content, 'href="#"');
    echo "   빈 링크(href=\"#\") 개수: {$emptyLinks}\n";
    
    // 라우트 링크 확인
    $routeLinks = substr_count($content, 'route(');
    echo "   라우트 링크 개수: {$routeLinks}\n";
    
    if ($emptyLinks == 0) {
        echo "   ✅ 모든 메뉴 링크가 실제 라우트로 연결됨\n";
    } else {
        echo "   ⚠️  일부 메뉴가 빈 링크로 남아있음\n";
    }
}

echo "\n=== 테스트 완료 ===\n";