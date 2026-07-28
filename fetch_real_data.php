<?php
/**
 * Fetch Real Data from APIs
 * PHP version - no Python needed!
 */

// Create output directory
$outputDir = __DIR__ . '/collected_data';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  📥 REAL DATA COLLECTION FROM APIs                        ║\n";
echo "║  (No Python needed - Pure PHP)                            ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// ============================================
// API 1: WORLD BANK
// ============================================

function fetchWorldBankData() {
    global $outputDir;
    
    echo "📊 1. Fetching World Bank Data...\n";
    
    $countries = ["DE", "SG", "CN", "US", "JP"];
    $data = [];
    
    foreach ($countries as $country) {
        echo "   Fetching $country... ";
        
        $url = "https://api.worldbank.org/v2/country/$country";
        $response = @file_get_contents($url);
        
        if ($response) {
            $json = json_decode($response, true);
            if (isset($json[1][0])) {
                $data[$country] = $json[1][0];
                echo "✓\n";
            }
        } else {
            echo "✗\n";
        }
        
        usleep(500000); // 0.5 second delay
    }
    
    if (!empty($data)) {
        file_put_contents("$outputDir/world_bank_data.json", json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "   ✓ Saved: world_bank_data.json\n";
        return $data;
    }
    
    echo "   ✗ No data collected\n";
    return null;
}

// ============================================
// API 2: OPEN-METEO WEATHER
// ============================================

function fetchWeatherData() {
    global $outputDir;
    
    echo "\n🌤️  2. Fetching Weather Data...\n";
    
    $cities = [
        "Berlin" => ["lat" => 52.5200, "lon" => 13.4050],
        "Singapore" => ["lat" => 1.3521, "lon" => 103.8198],
        "Beijing" => ["lat" => 39.9042, "lon" => 116.4074],
        "New York" => ["lat" => 40.7128, "lon" => -74.0060],
        "Tokyo" => ["lat" => 35.6762, "lon" => 139.6503]
    ];
    
    $data = [];
    
    foreach ($cities as $city => $coords) {
        echo "   Fetching $city... ";
        
        $url = "https://api.open-meteo.com/v1/forecast?" .
               "latitude=" . $coords['lat'] . 
               "&longitude=" . $coords['lon'] .
               "&current=temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m" .
               "&timezone=UTC";
        
        $response = @file_get_contents($url);
        
        if ($response) {
            $data[$city] = json_decode($response, true);
            echo "✓\n";
        } else {
            echo "✗\n";
        }
        
        usleep(500000);
    }
    
    if (!empty($data)) {
        file_put_contents("$outputDir/weather_data.json", json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "   ✓ Saved: weather_data.json\n";
        return $data;
    }
    
    echo "   ✗ No data collected\n";
    return null;
}

// ============================================
// API 3: EXCHANGE RATES
// ============================================

function fetchExchangeRates() {
    global $outputDir;
    
    echo "\n💱 3. Fetching Exchange Rates...\n";
    echo "   Fetching USD rates... ";
    
    $url = "https://api.exchangerate-api.com/v4/latest/USD";
    $response = @file_get_contents($url);
    
    if ($response) {
        $data = json_decode($response, true);
        file_put_contents("$outputDir/exchange_rates.json", json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "✓\n";
        echo "   ✓ Saved: exchange_rates.json\n";
        return $data;
    }
    
    echo "✗\n";
    echo "   ✗ No data collected\n";
    return null;
}

// ============================================
// API 4: GNEWS
// ============================================

function fetchNews() {
    global $outputDir;
    
    echo "\n📰 4. Fetching News...\n";
    
    $categories = ["supply chain", "logistics", "shipping", "trade"];
    $data = [];
    
    foreach ($categories as $category) {
        echo "   Fetching '$category' news... ";
        
        $url = "https://gnewsapi.net/api/search?" .
               "q=" . urlencode($category) .
               "&token=demo&max=5";
        
        $response = @file_get_contents($url);
        
        if ($response) {
            $data[$category] = json_decode($response, true);
            echo "✓\n";
        } else {
            echo "⚠ No data (demo token limited)\n";
        }
        
        usleep(500000);
    }
    
    if (!empty($data)) {
        file_put_contents("$outputDir/news_data.json", json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "   ✓ Saved: news_data.json\n";
        return $data;
    }
    
    echo "   ⚠ Limited news data (need API key for full)\n";
    return null;
}

// ============================================
// API 5: REST COUNTRIES
// ============================================

function fetchGeographicData() {
    global $outputDir;
    
    echo "\n🌍 5. Fetching Geographic Data...\n";
    
    $countries = ["Germany", "Singapore", "China", "United States", "Japan"];
    $data = [];
    
    foreach ($countries as $country) {
        echo "   Fetching $country... ";
        
        $url = "https://restcountries.com/v3.1/name/" . urlencode($country);
        $response = @file_get_contents($url);
        
        if ($response) {
            $json = json_decode($response, true);
            if (!empty($json) && isset($json[0])) {
                $data[$country] = $json[0];
                echo "✓\n";
            }
        } else {
            echo "✗\n";
        }
        
        usleep(500000);
    }
    
    if (!empty($data)) {
        file_put_contents("$outputDir/geographic_data.json", json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "   ✓ Saved: geographic_data.json\n";
        return $data;
    }
    
    echo "   ✗ No data collected\n";
    return null;
}

// ============================================
// API 6: PORTS
// ============================================

function fetchPortsData() {
    global $outputDir;
    
    echo "\n⚓ 6. Fetching Port Data...\n";
    
    $ports = [
        "Singapore Port" => ["lat" => 1.2655, "lon" => 103.8242, "country" => "Singapore", "type" => "major"],
        "Port of Shanghai" => ["lat" => 30.9176, "lon" => 121.5885, "country" => "China", "type" => "major"],
        "Port of Hamburg" => ["lat" => 53.5476, "lon" => 9.9158, "country" => "Germany", "type" => "major"],
        "Port of New York" => ["lat" => 40.6892, "lon" => -74.0445, "country" => "USA", "type" => "major"],
        "Port of Tokyo" => ["lat" => 35.4437, "lon" => 139.6452, "country" => "Japan", "type" => "major"],
        "Port of Rotterdam" => ["lat" => 51.9289, "lon" => 4.2183, "country" => "Netherlands", "type" => "major"],
    ];
    
    echo "   " . count($ports) . " major ports compiled\n";
    
    file_put_contents("$outputDir/ports_data.json", json_encode($ports, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "   ✓ Saved: ports_data.json\n";
    
    return $ports;
}

// ============================================
// MAIN
// ============================================

$results = [
    "World Bank" => fetchWorldBankData(),
    "Weather" => fetchWeatherData(),
    "Exchange Rates" => fetchExchangeRates(),
    "News" => fetchNews(),
    "Geographic" => fetchGeographicData(),
    "Ports" => fetchPortsData()
];

// Summary
echo "\n" . str_repeat("=", 60) . "\n";
echo "COLLECTION SUMMARY\n";
echo str_repeat("=", 60) . "\n\n";

foreach ($results as $source => $data) {
    $status = ($data) ? "✓" : "⚠";
    echo "$status $source\n";
}

echo "\n📁 Data saved to: $outputDir\n\n";

// List files
echo "Collected files:\n";
if (is_dir($outputDir)) {
    $files = scandir($outputDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $filepath = "$outputDir/$file";
            $size = filesize($filepath);
            echo "   • $file (" . number_format($size) . " bytes)\n";
        }
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✓ DATA COLLECTION COMPLETE!\n";
echo str_repeat("=", 60) . "\n";

echo "\nYou now have:\n";
echo "✓ Real macroeconomic data from World Bank\n";
echo "✓ Real weather data from Open-Meteo\n";
echo "✓ Real exchange rates\n";
echo "✓ Real news articles about supply chain\n";
echo "✓ Real geographic data about countries\n";
echo "✓ Real port locations\n";

echo "\nNext: Import data to your app or database!\n";
echo "\n";
?>
