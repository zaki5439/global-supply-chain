<?php
function getFlagEmoji($countryCode) {
    if (strlen($countryCode) !== 2) return '';
    $countryCode = strtoupper($countryCode);
    $flag = '';
    for ($i = 0; $i < 2; $i++) {
        $flag .= mb_chr(ord($countryCode[$i]) + 127397, 'UTF-8');
    }
    return $flag;
}

$url = "https://cdn.jsdelivr.net/npm/country-list@2.3.0/data.json";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (!$data) {
    echo "Failed to fetch/parse JSON from jsdelivr.\n";
    exit(1);
}

$weathers = [
    ['Clear Sky', 'bi-sun'],
    ['Partly Cloudy', 'bi-cloud-sun'],
    ['Scattered Clouds', 'bi-clouds'],
    ['Light Rain', 'bi-cloud-rain'],
    ['Heavy Rain', 'bi-cloud-rain-heavy'],
    ['Thunderstorms', 'bi-cloud-lightning-rain'],
    ['Snow', 'bi-snow'],
    ['Haze', 'bi-cloud-haze'],
    ['Sunny', 'bi-sun'],
    ['Cloudy', 'bi-clouds']
];

$currencies = ['USD ($)', 'EUR (€)', 'JPY (¥)', 'GBP (£)', 'CNY (Yuan)', 'AUD (A$)', 'CAD (C$)', 'CHF (CHF)', 'HKD (HK$)', 'SGD (S$)'];

$countries = [];
foreach ($data as $c) {
    if (!isset($c['code']) || !isset($c['name'])) continue;
    
    $cca2 = $c['code'];
    $name = $c['name'];
    $flag = getFlagEmoji($cca2);
    
    // Generate random realistic numbers
    $pop_num = rand(100000, 1400000000);
    if ($pop_num >= 1000000000) {
        $pop = number_format($pop_num / 1000000000, 2) . " Billion";
    } elseif ($pop_num >= 1000000) {
        $pop = number_format($pop_num / 1000000, 1) . " Million";
    } else {
        $pop = number_format($pop_num);
    }
    
    $curr = $currencies[array_rand($currencies)];
    
    $gdp = number_format(rand(-30, 80) / 10, 1) . "%";
    $infl = number_format(rand(0, 150) / 10, 1) . "%";
    
    $w = $weathers[array_rand($weathers)];
    $temp = rand(-5, 40);
    $weather = "{$w[0]}, {$temp}°C";
    $w_icon = $w[1];
    
    $name_esc = addslashes($name);
    $curr_esc = addslashes($curr);
    
    $line = "        { id: '$cca2', name: '$name_esc', flag: '$flag', gdp: '$gdp', infl: '$infl', pop: '$pop', curr: '$curr_esc', weather: '$weather', w_icon: '$w_icon' }";
    
    $countries[] = [
        'name' => $name,
        'line' => $line
    ];
}

usort($countries, function($a, $b) {
    return strcmp($a['name'], $b['name']);
});

$js_lines = [];
foreach ($countries as $c) {
    $js_lines[] = $c['line'];
}

$js_array = "    const countryData = [\n" . implode(",\n", $js_lines) . "\n    ];";

$path = 'c:\Users\ACER\supply-chain-app\resources\views\country.blade.php';
$content = file_get_contents($path);

// Regex replacement
$content = preg_replace('/[ \t]*const countryData = \[.*?    \];/s', $js_array, $content);

file_put_contents($path, $content);
echo "Added " . count($countries) . " countries from jsdelivr json directly via curl.\n";
?>
