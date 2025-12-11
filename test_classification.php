<?php

require_once __DIR__ . '/public_html/vendor/autoload.php';

$app = require_once __DIR__ . '/public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tender;

echo "Classification Code Analysis\n";
echo "============================\n\n";

$tender = Tender::first();
if ($tender) {
    $metadata = json_decode($tender->metadata, true);
    
    echo "Sample tender classification:\n";
    echo "Code: " . ($metadata['pubPrcrmntClsfcNo'] ?? 'None') . "\n";
    echo "Name: " . ($metadata['pubPrcrmntClsfcNm'] ?? 'None') . "\n";
    echo "Large: " . ($metadata['pubPrcrmntLrgClsfcNm'] ?? 'None') . "\n";
    echo "Mid: " . ($metadata['pubPrcrmntMidClsfcNm'] ?? 'None') . "\n\n";
}

// Search for IT classification codes
$itCodes = [
    '8111200201', '8111200202', '8111229901', '8111181101',
    '8111189901', '8111219901', '8111159801', '8111159901', '8115169901'
];

$foundItCodes = 0;
$classificationCounts = [];

echo "Analyzing " . Tender::count() . " tenders...\n";

foreach (Tender::all() as $tender) {
    $metadata = json_decode($tender->metadata, true);
    $code = $metadata['pubPrcrmntClsfcNo'] ?? '';
    $name = $metadata['pubPrcrmntClsfcNm'] ?? '';
    
    if (!empty($code)) {
        $key = $code . '|' . $name;
        $classificationCounts[$key] = ($classificationCounts[$key] ?? 0) + 1;
        
        if (in_array($code, $itCodes)) {
            echo "Found IT service tender: {$tender->tender_no} - {$code} ({$name})\n";
            $foundItCodes++;
        }
    }
}

echo "\nIT service tenders found: {$foundItCodes}\n";

echo "\nTop 15 classification codes:\n";
arsort($classificationCounts);
$top15 = array_slice($classificationCounts, 0, 15, true);

foreach ($top15 as $key => $count) {
    list($code, $name) = explode('|', $key, 2);
    echo "- {$code} ({$name}): {$count} tenders\n";
}

echo "\nIT and software related classifications:\n";
foreach ($classificationCounts as $key => $count) {
    list($code, $name) = explode('|', $key, 2);
    if (strpos($name, '소프트웨어') !== false || 
        strpos($name, '정보시스템') !== false || 
        strpos($name, '데이터') !== false ||
        strpos($name, '전산') !== false) {
        echo "- {$code} ({$name}): {$count} tenders\n";
    }
}

echo "\nAnalysis complete.\n";