<?php
require '/home/tideflo/nara/public_html/vendor/autoload.php';
require '/home/tideflo/nara/public_html/bootstrap/app.php';

$api = new App\Services\NaraApiService();
$total = 0;
$nulls = 0;
$empties = 0;
$samples = [];

echo "=== pubPrcrmntClsfcNo NULL/EMPTY 분석 ===\n";

for ($page = 1; $page <= 20; $page++) {
    try {
        $result = $api->getTendersByDateRange('20250831', '20250907', $page, 100);
        if (!isset($result['response']['body']['items'])) break;
        
        $items = $result['response']['body']['items'];
        if (empty($items)) break;
        
        foreach ($items as $item) {
            $total++;
            $code = $item['pubPrcrmntClsfcNo'] ?? null;
            $title = $item['bidNtceNm'] ?? 'N/A';
            
            if (is_null($code)) {
                $nulls++;
                if (count($samples) < 20) $samples[] = "NULL: " . $title;
            } elseif (empty($code) || (is_array($code) && empty($code))) {
                $empties++;
                if (count($samples) < 20) $samples[] = "EMPTY: " . $title;
            }
        }
        
        if ($page % 5 == 0) {
            echo "Page $page: $total checked...\n";
        }
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        break;
    }
}

echo "===== 결과 =====\n";
echo "총 공고: $total 건\n";
echo "NULL 값: $nulls 건\n";
echo "빈 값: $empties 건\n";
echo "정상 값: " . ($total - $nulls - $empties) . " 건\n";

if (!empty($samples)) {
    echo "\n=== NULL/EMPTY 샘플 ===\n";
    foreach ($samples as $sample) {
        echo $sample . "\n";
    }
}
?>