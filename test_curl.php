<?php
$url = "https://restcountries.com/v3.1/all";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
if(curl_errno($ch)){
    echo 'Curl error: ' . curl_error($ch);
}
curl_close($ch);

$data = json_decode($response, true);

if (!$data) {
    echo "Failed to fetch data from restcountries API.\n";
    echo $response;
    exit(1);
}
echo "Success";
