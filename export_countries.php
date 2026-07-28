<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Get all countries
$countries = DB::table('countries')->orderBy('name')->get();

echo "-- Exported " . count($countries) . " countries\n";
echo "-- Generated at: " . date('Y-m-d H:i:s') . "\n\n";

// Generate INSERT statements
$insertStatements = [];
foreach ($countries as $country) {
    $values = implode("', '", [
        $country->iso2,
        $country->iso3,
        $country->name,
        $country->region
    ]);
    $insertStatements[] = "INSERT INTO countries (iso2, iso3, name, region, created_at, updated_at) VALUES ('" . $values . "', NOW(), NOW());";
}

// Output SQL
foreach ($insertStatements as $sql) {
    echo $sql . "\n";
}

echo "\n-- Total: " . count($insertStatements) . " INSERT statements\n";
?>
