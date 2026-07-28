<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== POPULATION & GDP DATA VERIFICATION ===\n\n";

// Check total with data
$withData = DB::table('countries')->whereNotNull('population')->whereNotNull('gdp')->count();
echo "Countries with Population & GDP: $withData / 197\n\n";

// Sample data
echo "Sample Countries (sorted by GDP - top 10):\n";
$top10 = DB::table('countries')
    ->where('gdp', '>', 0)
    ->orderBy('gdp', 'desc')
    ->limit(10)
    ->get(['name', 'iso3', 'population', 'gdp']);

foreach ($top10 as $c) {
    $pop = number_format($c->population);
    $gdp = number_format($c->gdp);
    echo "  - {$c->name} ({$c->iso3}): Population {$pop}, GDP ${gdp}\n";
}

echo "\n✅ Data verification complete!\n";
?>
