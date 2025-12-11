<?php

require 'public_html/vendor/autoload.php';
$app = require_once 'public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tender = App\Models\Tender::first();
echo "Tender metadata analysis:\n";
$metadata = json_decode($tender->metadata, true);

if ($metadata) {
    echo "Looking for attachment-related fields...\n";
    foreach ($metadata as $key => $value) {
        if (strpos(strtolower($key), 'file') !== false || 
            strpos(strtolower($key), 'attach') !== false ||
            strpos(strtolower($key), 'doc') !== false ||
            strpos(strtolower($key), 'atch') !== false) {
            echo $key . ': ' . (is_array($value) ? json_encode($value) : $value) . "\n";
        }
    }
    echo "\nAll available keys: " . implode(', ', array_keys($metadata)) . "\n";
} else {
    echo "No metadata available\n";
}