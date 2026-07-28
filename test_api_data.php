<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== TESTING API RESPONSE ===\n\n";

// Get some countries with their population and GDP
$countries = DB::table('countries')
    ->whereIn('iso3', ['USA', 'CHN', 'AFG', 'IND'])
    ->select('name', 'iso3', 'population', 'gdp')
    ->get();

foreach ($countries as $c) {
    $pop = number_format($c->population);
    $gdp = number_format($c->gdp);
    echo "$c->name ($c->iso3)\n";
    echo "  Population: $pop\n";
    echo "  GDP: \$$gdp\n\n";
}

echo "✅ Data retrieved successfully!\n";
?>
