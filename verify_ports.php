<?php
$data = json_decode(file_get_contents('resources/views/ports-expanded-complete.json'), true);
echo "Total ports: " . count($data) . "\n";

$newCountries = array('Iceland', 'Ireland', 'Monaco', 'Bosnia and Herzegovina', 'Montenegro', 'Albania', 'Ukraine', 'Turkmenistan', 'Georgia', 'Azerbaijan', 'Mauritania', 'Cape Verde', 'Eritrea', 'Timor-Leste', 'North Korea');

$countries = array();
foreach($data as $port) {
    $countries[$port['country']] = true;
}

echo "\nNew countries added:\n";
foreach($newCountries as $country) {
    if(array_key_exists($country, $countries)) {
        echo "✓ " . $country . "\n";
    } else {
        echo "✗ " . $country . "\n";
    }
}

echo "\nSample new ports:\n";
$count = 0;
foreach($data as $port) {
    if(in_array($port['country'], $newCountries)) {
        echo "- " . $port['name'] . " (" . $port['country'] . ")\n";
        $count++;
        if($count >= 10) break;
    }
}
?>
