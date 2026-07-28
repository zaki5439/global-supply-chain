<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DATABASE VERIFICATION ===\n\n";
echo "Total Countries: " . DB::table('countries')->count() . "\n";
echo "Total Regions: " . DB::table('countries')->distinct('region')->count() . "\n\n";

echo "Countries by Region:\n";
$regions = DB::table('countries')
    ->groupBy('region')
    ->select('region', DB::raw('count(*) as total'))
    ->orderBy('total', 'desc')
    ->get();

foreach ($regions as $r) {
    echo "  - " . $r->region . ": " . $r->total . " countries\n";
}

echo "\nFirst 5 countries (sorted A-Z):\n";
$first5 = DB::table('countries')->orderBy('name')->limit(5)->get();
foreach ($first5 as $c) {
    echo "  - " . $c->name . " (" . $c->iso3 . ")\n";
}

echo "\n✅ Database verification complete!\n";
?>
