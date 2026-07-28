<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Check if columns exist
$columns = DB::select('SHOW COLUMNS FROM countries');
$existing = array_map(fn($c) => $c->Field, $columns);

if (!in_array('population', $existing)) {
    DB::statement('ALTER TABLE countries ADD COLUMN population BIGINT NULL COMMENT "Total population"');
    echo "✓ Added population column\n";
} else {
    echo "✓ population column already exists\n";
}

if (!in_array('gdp', $existing)) {
    DB::statement('ALTER TABLE countries ADD COLUMN gdp BIGINT NULL COMMENT "GDP in USD"');
    echo "✓ Added gdp column\n";
} else {
    echo "✓ gdp column already exists\n";
}

echo "\nCurrent columns:\n";
foreach ($columns as $c) {
    echo "  - " . $c->Field . " (" . $c->Type . ")\n";
}

echo "\n✅ Column check complete!\n";
?>
