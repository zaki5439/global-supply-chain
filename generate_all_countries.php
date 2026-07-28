<?php
$url = "https://restcountries.com/v3.1/all";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (!$data) {
    echo "Failed to fetch data from restcountries API.";
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

$countries = [];
foreach ($data as $c) {
    if (!isset($c['cca2']) || !isset($c['name']['common'])) continue;
    
    $cca2 = $c['cca2'];
    $name = $c['name']['common'];
    $flag = isset($c['flag']) ? $c['flag'] : '';
    
    $pop_num = isset($c['population']) ? $c['population'] : 0;
    if ($pop_num >= 1000000000) {
        $pop = number_format($pop_num / 1000000000, 2) . " Billion";
    } elseif ($pop_num >= 1000000) {
        $pop = number_format($pop_num / 1000000, 1) . " Million";
    } else {
        $pop = number_format($pop_num);
    }
    
    $curr = 'N/A';
    if (isset($c['currencies']) && !empty($c['currencies'])) {
        $curr_code = array_key_first($c['currencies']);
        $curr_sym = isset($c['currencies'][$curr_code]['symbol']) ? $c['currencies'][$curr_code]['symbol'] : $curr_code;
        $curr = "$curr_code ($curr_sym)";
    }
    
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

$path = 'resources/views/country.blade.php';
$content = file_get_contents($path);

// Regex replacement
$content = preg_replace('/[ \t]*const countryData = \[.*?    \];/s', $js_array, $content);

file_put_contents($path, $content);
echo "Added " . count($countries) . " countries.\n";
?>
