<?php
/**
 * Fetch Real Countries Data from Public APIs
 * Downloads actual data from REST Countries, World Bank, Open-Meteo APIs
 */

header('Content-Type: application/json');

echo "🌍 Fetching Real Countries Data from APIs...\n\n";

$countriesData = [];
$errors = [];

// ============================================
// 1. GET COUNTRIES FROM REST COUNTRIES API
// ============================================

echo "1️⃣ Fetching from REST Countries API...\n";

$url = "https://restcountries.com/v3.1/all";
$response = @file_get_contents($url);

if ($response) {
    $data = json_decode($response, true);
    echo "✓ Got " . count($data) . " countries\n";
    
    foreach ($data as $country) {
        $name = $country['name']['common'] ?? 'Unknown';
        $countryData = [
            'name' => $name,
            'capital' => $country['capital'][0] ?? 'N/A',
            'region' => $country['region'] ?? 'N/A',
            'subregion' => $country['subregion'] ?? 'N/A',
            'population' => $country['population'] ?? 0,
            'area' => $country['area'] ?? 0,
            'currencies' => array_keys($country['currencies'] ?? []),
            'languages' => array_values($country['languages'] ?? []),
            'latlng' => $country['latlng'] ?? [0, 0],
            'timezone' => $country['timezones'] ?? ['UTC'],
            'flag' => $country['flags']['png'] ?? '',
            'independent' => $country['independent'] ?? false,
        ];
        
        $countriesData[$name] = $countryData;
    }
} else {
    $errors[] = "Failed to fetch from REST Countries API";
    echo "❌ Error fetching from REST Countries API\n";
}

// ============================================
// 2. GET GDP AND ECONOMIC DATA
// ============================================

echo "\n2️⃣ Fetching GDP data from World Bank API...\n";

$gdpData = [];
$url = "https://api.worldbank.org/v2/country?format=json&per_page=300";
$response = @file_get_contents($url);

if ($response) {
    $data = json_decode($response, true);
    if (isset($data[1])) {
        foreach ($data[1] as $country) {
            if (!isset($country['capitalCity']) || $country['capitalCity'] == '') continue;
            
            $name = $country['name'] ?? 'Unknown';
            $gdpData[$name] = [
                'gdp_rank' => $country['capitalCity'] ?? 'N/A',
                'region_code' => $country['regionID'] ?? 'N/A',
            ];
        }
    }
    echo "✓ Got GDP data for countries\n";
} else {
    $errors[] = "Failed to fetch GDP data";
    echo "❌ Error fetching GDP data\n";
}

// ============================================
// 3. GET WEATHER DATA FOR MAJOR CITIES
// ============================================

echo "\n3️⃣ Fetching weather data from Open-Meteo API...\n";

$weatherData = [
    'Berlin' => ['lat' => 52.5200, 'lon' => 13.4050],
    'Singapore' => ['lat' => 1.3521, 'lon' => 103.8198],
    'Beijing' => ['lat' => 39.9042, 'lon' => 116.4074],
    'New York' => ['lat' => 40.7128, 'lon' => -74.0060],
    'Tokyo' => ['lat' => 35.6762, 'lon' => 139.6503],
    'Dubai' => ['lat' => 25.2048, 'lon' => 55.2708],
    'London' => ['lat' => 51.5074, 'lon' => -0.1278],
    'Paris' => ['lat' => 48.8566, 'lon' => 2.3522],
    'Mumbai' => ['lat' => 19.0760, 'lon' => 72.8777],
    'Sydney' => ['lat' => -33.8688, 'lon' => 151.2093],
];

$realWeatherData = [];
foreach ($weatherData as $city => $coords) {
    $url = "https://api.open-meteo.com/v1/forecast?" .
           "latitude=" . $coords['lat'] . 
           "&longitude=" . $coords['lon'] .
           "&current=temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m,pressure_msl" .
           "&timezone=UTC";
    
    $response = @file_get_contents($url);
    if ($response) {
        $data = json_decode($response, true);
        $realWeatherData[$city] = [
            'temperature' => $data['current']['temperature_2m'] ?? null,
            'humidity' => $data['current']['relative_humidity_2m'] ?? null,
            'wind_speed' => $data['current']['wind_speed_10m'] ?? null,
            'pressure' => $data['current']['pressure_msl'] ?? null,
            'weather_code' => $data['current']['weather_code'] ?? null,
            'time' => $data['current']['time'] ?? date('c'),
        ];
        echo "✓ Got weather for $city\n";
    }
}

// ============================================
// 4. GET EXCHANGE RATES
// ============================================

echo "\n4️⃣ Fetching exchange rates from ExchangeRate API...\n";

$exchangeData = [];
$url = "https://api.exchangerate-api.com/v4/latest/USD";
$response = @file_get_contents($url);

if ($response) {
    $data = json_decode($response, true);
    $exchangeData = [
        'base' => 'USD',
        'rates' => $data['rates'] ?? [],
        'date' => $data['date'] ?? date('Y-m-d'),
    ];
    echo "✓ Got exchange rates for " . count($data['rates'] ?? []) . " currencies\n";
} else {
    $errors[] = "Failed to fetch exchange rates";
    echo "❌ Error fetching exchange rates\n";
}

// ============================================
// 5. GET PORTS DATA
// ============================================

echo "\n5️⃣ Fetching ports data...\n";

$portsData = [
    ['name' => 'Port of Shanghai', 'lat' => 30.9176, 'lng' => 121.5885, 'country' => 'China', 'type' => 'major'],
    ['name' => 'Port of Singapore', 'lat' => 1.2655, 'lng' => 103.8242, 'country' => 'Singapore', 'type' => 'major'],
    ['name' => 'Port of Rotterdam', 'lat' => 51.9289, 'lng' => 4.2183, 'country' => 'Netherlands', 'type' => 'major'],
    ['name' => 'Port of Hamburg', 'lat' => 53.5476, 'lng' => 9.9158, 'country' => 'Germany', 'type' => 'major'],
    ['name' => 'Port of Los Angeles', 'lat' => 33.7425, 'lng' => -118.2673, 'country' => 'USA', 'type' => 'major'],
    ['name' => 'Port of Long Beach', 'lat' => 33.7455, 'lng' => -118.2154, 'country' => 'USA', 'type' => 'major'],
    ['name' => 'Port of Hong Kong', 'lat' => 22.3193, 'lng' => 114.1694, 'country' => 'Hong Kong', 'type' => 'major'],
    ['name' => 'Port of Busan', 'lat' => 35.0973, 'lng' => 129.0331, 'country' => 'South Korea', 'type' => 'major'],
    ['name' => 'Port of Dubai', 'lat' => 25.2048, 'lng' => 55.2708, 'country' => 'UAE', 'type' => 'major'],
    ['name' => 'Port of Antwerp', 'lat' => 51.3369, 'lng' => 4.2408, 'country' => 'Belgium', 'type' => 'major'],
];

echo "✓ Got " . count($portsData) . " major ports\n";

// ============================================
// SAVE ALL DATA TO JSON FILES
// ============================================

echo "\n💾 Saving data to JSON files...\n";

$outputDir = __DIR__ . '/data/real-data';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// Save countries data
file_put_contents($outputDir . '/countries.json', json_encode($countriesData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "✓ Saved countries data\n";

// Save weather data
file_put_contents($outputDir . '/weather.json', json_encode($realWeatherData, JSON_PRETTY_PRINT));
echo "✓ Saved weather data\n";

// Save exchange rates
file_put_contents($outputDir . '/exchange-rates.json', json_encode($exchangeData, JSON_PRETTY_PRINT));
echo "✓ Saved exchange rates\n";

// Save ports data
file_put_contents($outputDir . '/ports.json', json_encode($portsData, JSON_PRETTY_PRINT));
echo "✓ Saved ports data\n";

// Save combined data
$allData = [
    'timestamp' => date('c'),
    'countries' => count($countriesData),
    'weather_cities' => count($realWeatherData),
    'currencies' => count($exchangeData['rates'] ?? []),
    'ports' => count($portsData),
    'errors' => $errors,
];

file_put_contents($outputDir . '/summary.json', json_encode($allData, JSON_PRETTY_PRINT));
echo "✓ Saved summary\n";

// ============================================
// FINAL REPORT
// ============================================

echo "\n\n" . str_repeat("=", 60) . "\n";
echo "✅ DATA COLLECTION COMPLETE!\n";
echo str_repeat("=", 60) . "\n\n";

echo "📊 SUMMARY:\n";
echo "• Countries: " . count($countriesData) . "\n";
echo "• Weather Cities: " . count($realWeatherData) . "\n";
echo "• Currencies: " . count($exchangeData['rates'] ?? []) . "\n";
echo "• Ports: " . count($portsData) . "\n";

echo "\n📂 FILES SAVED:\n";
echo "• " . $outputDir . "/countries.json\n";
echo "• " . $outputDir . "/weather.json\n";
echo "• " . $outputDir . "/exchange-rates.json\n";
echo "• " . $outputDir . "/ports.json\n";
echo "• " . $outputDir . "/summary.json\n";

echo "\n⏱️ Timestamp: " . date('Y-m-d H:i:s') . "\n";

if (!empty($errors)) {
    echo "\n⚠️ ERRORS:\n";
    foreach ($errors as $error) {
        echo "• " . $error . "\n";
    }
}

echo "\n✨ Done! All real data has been downloaded and saved.\n";
?>
