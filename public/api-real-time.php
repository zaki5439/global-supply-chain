<?php
/**
 * Real-Time API Endpoint
 * Fetch fresh data from APIs on demand
 * Usage: api-real-time.php?type=weather&city=Berlin
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$type = $_GET['type'] ?? 'all';
$city = $_GET['city'] ?? 'Berlin';

// ============================================
// HELPER: Fetch with timeout
// ============================================

function fetch_url($url, $timeout = 10) {
    $context = stream_context_create(['http' => ['timeout' => $timeout]]);
    $response = @file_get_contents($url, false, $context);
    return $response ? json_decode($response, true) : null;
}

// ============================================
// WEATHER DATA - REAL-TIME
// ============================================

function get_weather($city) {
    $cities = [
        'Berlin' => ['lat' => 52.5200, 'lon' => 13.4050],
        'Singapore' => ['lat' => 1.3521, 'lon' => 103.8198],
        'Beijing' => ['lat' => 39.9042, 'lon' => 116.4074],
        'New York' => ['lat' => 40.7128, 'lon' => -74.0060],
        'Tokyo' => ['lat' => 35.6762, 'lon' => 139.6503]
    ];
    
    if (!isset($cities[$city])) {
        return ['error' => 'City not found'];
    }
    
    $coords = $cities[$city];
    $url = "https://api.open-meteo.com/v1/forecast?" .
           "latitude=" . $coords['lat'] . 
           "&longitude=" . $coords['lon'] .
           "&current=temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m" .
           "&timezone=UTC";
    
    $data = fetch_url($url);
    
    if (!$data) {
        return ['error' => 'Could not fetch weather data'];
    }
    
    return [
        'city' => $city,
        'temperature' => $data['current']['temperature_2m'] ?? 'N/A',
        'humidity' => $data['current']['relative_humidity_2m'] ?? 'N/A',
        'wind_speed' => $data['current']['wind_speed_10m'] ?? 'N/A',
        'weather_code' => $data['current']['weather_code'] ?? 'N/A',
        'timestamp' => $data['current']['time'] ?? date('c'),
        'source' => 'Open-Meteo (Real-time)'
    ];
}

// ============================================
// EXCHANGE RATES - REAL-TIME
// ============================================

function get_exchange_rates() {
    $url = "https://api.exchangerate-api.com/v4/latest/USD";
    $data = fetch_url($url);
    
    if (!$data) {
        return ['error' => 'Could not fetch exchange rates'];
    }
    
    $rates = $data['rates'];
    
    // Get top currencies
    $top = ['EUR', 'GBP', 'JPY', 'CNY', 'SGD', 'AUD', 'BRL', 'INR', 'CAD', 'CHF'];
    $result = [];
    
    foreach ($top as $curr) {
        if (isset($rates[$curr])) {
            $result[$curr] = $rates[$curr];
        }
    }
    
    return [
        'base' => 'USD',
        'rates' => $result,
        'timestamp' => $data['date'] ?? date('Y-m-d'),
        'source' => 'ExchangeRate API (Real-time)'
    ];
}

// ============================================
// PORTS DATA - STATIC
// ============================================

function get_ports() {
    return [
        'Singapore Port' => [
            'lat' => 1.2655,
            'lon' => 103.8242,
            'country' => 'Singapore',
            'type' => 'major'
        ],
        'Port of Shanghai' => [
            'lat' => 30.9176,
            'lon' => 121.5885,
            'country' => 'China',
            'type' => 'major'
        ],
        'Port of Hamburg' => [
            'lat' => 53.5476,
            'lon' => 9.9158,
            'country' => 'Germany',
            'type' => 'major'
        ],
        'Port of New York' => [
            'lat' => 40.6892,
            'lon' => -74.0445,
            'country' => 'USA',
            'type' => 'major'
        ],
        'Port of Tokyo' => [
            'lat' => 35.4437,
            'lon' => 139.6452,
            'country' => 'Japan',
            'type' => 'major'
        ],
        'Port of Rotterdam' => [
            'lat' => 51.9289,
            'lon' => 4.2183,
            'country' => 'Netherlands',
            'type' => 'major'
        ]
    ];
}

// ============================================
// ALL WEATHER CITIES
// ============================================

function get_all_weather() {
    $cities = ['Berlin', 'Singapore', 'Beijing', 'New York', 'Tokyo'];
    $data = [];
    
    foreach ($cities as $city) {
        $data[$city] = get_weather($city);
    }
    
    return $data;
}

// ============================================
// ROUTER
// ============================================

$response = [];

switch ($type) {
    case 'weather':
        $response = get_weather($city);
        break;
    
    case 'weather-all':
        $response = get_all_weather();
        break;
    
    case 'exchange':
        $response = get_exchange_rates();
        break;
    
    case 'ports':
        $response = get_ports();
        break;
    
    case 'all':
    default:
        $response = [
            'weather' => get_all_weather(),
            'exchange' => get_exchange_rates(),
            'ports' => get_ports(),
            'timestamp' => date('c'),
            'source' => 'Real-time APIs'
        ];
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
