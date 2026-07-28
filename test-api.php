<?php
// Test API endpoint
$url = 'http://127.0.0.1:8000/api/countries';
$options = [
    'http' => [
        'timeout' => 10,
        'method' => 'GET',
        'header' => 'Accept: application/json'
    ]
];
$context = stream_context_create($options);
$json = @file_get_contents($url, false, $context);

if ($json === false) {
    echo "❌ API Request failed\n";
    echo "Trying direct database check...\n";
    
    require 'vendor/autoload.php';
    require 'bootstrap/app.php';
    
    $app = app();
    echo "✓ App loaded\n";
    
    $count = DB::table('countries')->count();
    echo "✓ Countries in DB: $count\n";
    
    $first = DB::table('countries')->orderBy('name')->limit(5)->get();
    echo "✓ First 5:\n";
    foreach ($first as $c) {
        echo "  - {$c->name} ({$c->iso3})\n";
    }
} else {
    $data = json_decode($json, true);
    echo "✅ API Response:\n";
    echo "Status: {$data['status']}\n";
    echo "Count: {$data['count']}\n";
    echo "First 5:\n";
    foreach (array_slice($data['data'], 0, 5) as $c) {
        echo "  - {$c['name']} ({$c['iso3']})\n";
    }
}
?>
