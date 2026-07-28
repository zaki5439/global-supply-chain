<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class CountryDataController extends Controller
{
    private const WORLD_BANK_API_URL = 'https://api.worldbank.org/v2/country';
    private const OPEN_METEO_API_URL = 'https://api.open-meteo.com/v1/forecast';
    private const REQUEST_TIMEOUT = 10;
    
    // Mapping country names to ISO codes for World Bank API
    private $countryCodeMapping = [
        'Indonesia' => 'IDN',
        'Germany' => 'DEU',
        'United States' => 'USA',
        'China' => 'CHN',
        'Vietnam' => 'VNM',
        'Singapore' => 'SGP',
        'Japan' => 'JPN',
        'India' => 'IND',
        'Australia' => 'AUS',
        'Malaysia' => 'MYS',
    ];

    /**
     * Get comprehensive country data including GDP, Population, and Weather Risk
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCountryData(Request $request)
    {
        try {
            $country = $request->query('country');
            $isoCode = $request->query('iso_code');
            
            if (!$country || !$isoCode) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Country and iso_code parameters are required',
                    'code' => 'MISSING_PARAMS'
                ], 400);
            }

            // Get coordinates for the country
            $coordinates = $this->getCountryCoordinates($country, $isoCode);
            
            // Fetch data from various APIs CONCURRENTLY
            $responses = Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($isoCode, $coordinates) {
                $wbOptions = [
                    'format' => 'json',
                    'per_page' => 5
                ];
                $weatherOptions = [
                    'latitude' => $coordinates['lat'],
                    'longitude' => $coordinates['lon'],
                    'current' => 'temperature_2m,precipitation,wind_speed_10m,weather_code',
                    'hourly' => 'precipitation_probability',
                    'timezone' => 'auto',
                    'forecast_days' => 1
                ];
                
                $curlOpts = ['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]];

                return [
                    $pool->as('gdp')->withOptions($curlOpts)->timeout(5)->get(self::WORLD_BANK_API_URL . "/{$isoCode}/indicator/NY.GDP.MKTP.CD", $wbOptions),
                    $pool->as('pop')->withOptions($curlOpts)->timeout(5)->get(self::WORLD_BANK_API_URL . "/{$isoCode}/indicator/SP.POP.TOTL", $wbOptions),
                    $pool->as('infl')->withOptions($curlOpts)->timeout(5)->get(self::WORLD_BANK_API_URL . "/{$isoCode}/indicator/FP.CPI.TOTL.ZG", $wbOptions),
                    $pool->as('weather')->withOptions($curlOpts)->timeout(5)->get(self::OPEN_METEO_API_URL, $weatherOptions),
                ];
            });
            
            $gdpData = $this->parseWorldBankResponse($responses['gdp']);
            $populationData = $this->parseWorldBankResponse($responses['pop']);
            $inflationData = $this->parseWorldBankResponse($responses['infl']);
            $weatherData = $this->parseWeatherResponse($responses['weather']);
            
            // Calculate weather risk score
            $weatherRiskScore = $this->calculateWeatherRiskScore($weatherData);
            
            $currencyData = $this->getCurrencyData($country);
            $newsSentiment = $this->getNewsSentiment($country);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'gdp' => [
                        'value' => $gdpData,
                        'formatted' => $this->formatGDP($gdpData),
                        'unit' => 'USD',
                        'year' => date('Y') - 1
                    ],
                    'population' => [
                        'value' => $populationData,
                        'formatted' => $this->formatPopulation($populationData),
                        'unit' => 'people',
                        'year' => date('Y') - 1
                    ],
                    'inflation' => [
                        'value' => $inflationData,
                        'formatted' => $inflationData !== null ? number_format($inflationData, 1) . '%' : 'N/A',
                    ],
                    'currency' => $currencyData,
                    'news_sentiment' => $newsSentiment,
                    'weather_risk' => [
                        'score' => $weatherRiskScore,
                        'components' => $weatherData,
                        'risk_level' => $this->getRiskLevel($weatherRiskScore)
                    ],
                    'country' => $country,
                    'iso_code' => $isoCode,
                    'timestamp' => now()->toIso8601String()
                ]
            ]);

        } catch (Exception $e) {
            return $this->handleApiException($e);
        }
    }

    /**
     * Get coordinates dynamically
     */
    private function getCountryCoordinates(string $countryName, string $isoCode): array
    {
        $coordinates = [
            'Indonesia' => ['lat' => -0.7893, 'lon' => 113.9213],
            'Germany' => ['lat' => 51.1657, 'lon' => 10.4515],
            'United States of America' => ['lat' => 37.0902, 'lon' => -95.7129],
            'China' => ['lat' => 35.8617, 'lon' => 104.1954],
            'Singapore' => ['lat' => 1.3521, 'lon' => 103.8198],
            'Japan' => ['lat' => 36.2048, 'lon' => 138.2529],
        ];

        if (isset($coordinates[$countryName])) {
            return $coordinates[$countryName];
        }
        
        try {
            // Dynamic fetch
            $res = Http::withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])->timeout(3)->get("https://restcountries.com/v3.1/alpha/" . $isoCode);
            if ($res->successful()) {
                $data = $res->json();
                if (isset($data[0]['capitalInfo']['latlng'])) {
                    return [
                        'lat' => $data[0]['capitalInfo']['latlng'][0],
                        'lon' => $data[0]['capitalInfo']['latlng'][1]
                    ];
                } elseif (isset($data[0]['latlng'])) {
                    return [
                        'lat' => $data[0]['latlng'][0],
                        'lon' => $data[0]['latlng'][1]
                    ];
                }
            }
        } catch (Exception $e) {
            // Fallback
        }

        return ['lat' => 0, 'lon' => 0];
    }

    /**
     * Fetch data from World Bank API
     */
    private function parseWorldBankResponse($response): ?float
    {
        if ($response instanceof \Exception || !$response->successful()) {
            return null;
        }
        
        $data = $response->json();
        if (isset($data[1]) && is_array($data[1])) {
            foreach ($data[1] as $entry) {
                if (isset($entry['value']) && $entry['value'] !== null) {
                    return (float) $entry['value'];
                }
            }
        }
        return null;
    }

    private function parseWeatherResponse($response): array
    {
        if ($response instanceof \Exception || !$response->successful()) {
            return $this->getDefaultWeatherData();
        }
        
        $data = $response->json();
        return [
            'temperature' => $data['current']['temperature_2m'] ?? 0,
            'precipitation' => $data['current']['precipitation'] ?? 0,
            'wind_speed' => $data['current']['wind_speed_10m'] ?? 0,
            'weather_code' => $data['current']['weather_code'] ?? 0,
            'precipitation_probability' => $data['hourly']['precipitation_probability'][0] ?? 0,
            'unit_temperature' => $data['current_units']['temperature_2m'] ?? '°C',
            'unit_precipitation' => $data['current_units']['precipitation'] ?? 'mm',
            'unit_wind' => $data['current_units']['wind_speed_10m'] ?? 'km/h'
        ];
    }



    /**
     * Default weather data in case of API failure
     */
    private function getDefaultWeatherData(): array
    {
        return [
            'temperature' => 25.0,
            'precipitation' => 5.0,
            'wind_speed' => 15.0,
            'weather_code' => 0,
            'precipitation_probability' => 30,
            'unit_temperature' => '°C',
            'unit_precipitation' => 'mm',
            'unit_wind' => 'km/h'
        ];
    }

    /**
     * Calculate weather risk score based on weather parameters
     */
    private function calculateWeatherRiskScore(array $weatherData): int
    {
        $score = 0;
        
        // Temperature risk (extreme temperatures)
        $temp = $weatherData['temperature'];
        if ($temp >= 35 || $temp <= 0) $score += 40;
        elseif ($temp >= 30 || $temp <= 5) $score += 25;
        elseif ($temp >= 25 || $temp <= 10) $score += 10;
        
        // Precipitation risk (heavy rain)
        $precip = $weatherData['precipitation'];
        if ($precip >= 50) $score += 40;
        elseif ($precip >= 20) $score += 25;
        elseif ($precip >= 10) $score += 10;
        
        // Wind speed risk (strong winds)
        $wind = $weatherData['wind_speed'];
        if ($wind >= 60) $score += 40;
        elseif ($wind >= 40) $score += 25;
        elseif ($wind >= 20) $score += 10;
        
        // Precipitation probability risk
        $precipProb = $weatherData['precipitation_probability'];
        if ($precipProb >= 80) $score += 30;
        elseif ($precipProb >= 50) $score += 15;
        
        // Cap score at 100
        return min(100, $score);
    }

    /**
     * Determine risk level based on score
     */
    private function getRiskLevel(int $score): string
    {
        if ($score >= 80) return 'CRITICAL';
        if ($score >= 60) return 'HIGH';
        if ($score >= 40) return 'MEDIUM';
        if ($score >= 20) return 'LOW';
        return 'MINIMAL';
    }

    /**
     * Format GDP value for display
     */
    private function formatGDP(?float $gdpValue): string
    {
        if (!$gdpValue || $gdpValue <= 0) {
            return 'Tidak Tersedia';
        }

        if ($gdpValue >= 1e12) {
            return '$' . round($gdpValue / 1e12, 2) . ' Triliun';
        } elseif ($gdpValue >= 1e9) {
            return '$' . round($gdpValue / 1e9, 2) . ' Miliar';
        } elseif ($gdpValue >= 1e6) {
            return '$' . round($gdpValue / 1e6, 2) . ' Juta';
        }

        return '$' . number_format($gdpValue, 2);
    }

    /**
     * Format population value for display
     */
    private function formatPopulation(?float $populationValue): string
    {
        if (!$populationValue || $populationValue <= 0) {
            return 'Tidak Tersedia';
        }

        if ($populationValue >= 1e9) {
            return round($populationValue / 1e9, 2) . ' Miliar';
        } elseif ($populationValue >= 1e6) {
            return round($populationValue / 1e6, 2) . ' Juta';
        } elseif ($populationValue >= 1e3) {
            return round($populationValue / 1e3, 2) . ' Ribu';
        }

        return number_format($populationValue);
    }
    
    private function getCurrencyData(string $countryName): array
    {
        $currencies = [
            'Indonesia' => ['code' => 'IDR', 'rate' => 15500],
            'Germany' => ['code' => 'EUR', 'rate' => 0.92],
            'United States' => ['code' => 'USD', 'rate' => 1.00],
            'China' => ['code' => 'CNY', 'rate' => 7.23],
            'Vietnam' => ['code' => 'VND', 'rate' => 24500],
            'Singapore' => ['code' => 'SGD', 'rate' => 1.35],
            'Japan' => ['code' => 'JPY', 'rate' => 150.5],
            'India' => ['code' => 'INR', 'rate' => 83.2],
            'Australia' => ['code' => 'AUD', 'rate' => 1.54],
            'Malaysia' => ['code' => 'MYR', 'rate' => 4.75],
        ];
        
        $currency = $currencies[$countryName] ?? ['code' => 'USD', 'rate' => 1.00];
        return [
            'code' => $currency['code'],
            'rate' => $currency['rate'],
            'formatted' => $currency['code'] . ' (1 USD = ' . $currency['rate'] . ' ' . $currency['code'] . ')'
        ];
    }
    
    private function getNewsSentiment(string $countryName): string
    {
        // Simple mock sentiment based on country name length just for simulation
        $hash = crc32($countryName . date('Y-m-d'));
        $mod = $hash % 100;
        
        if ($mod > 80) return 'Negative';
        if ($mod > 40) return 'Positive';
        return 'Neutral';
    }

    /**
     * Handle API exceptions
     */
    private function handleApiException(Exception $e): \Illuminate\Http\JsonResponse
    {
        Log::error("Exception in CountryDataController: " . $e->getMessage(), [
            'exception' => get_class($e),
            'code' => $e->getCode(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan dalam pengambilan data. Silakan coba lagi.',
            'code' => 'SERVER_ERROR',
            'fallback_data' => $this->getFallbackData()
        ], 500);
    }

    /**
     * Fallback data for error scenarios
     */
    private function getFallbackData(): array
    {
        return [
            'gdp' => [
                'value' => null,
                'formatted' => 'Tidak Tersedia',
                'unit' => 'USD',
                'year' => date('Y') - 1
            ],
            'population' => [
                'value' => null,
                'formatted' => 'Tidak Tersedia',
                'unit' => 'people',
                'year' => date('Y') - 1
            ],
            'weather_risk' => [
                'score' => 0,
                'components' => $this->getDefaultWeatherData(),
                'risk_level' => 'MINIMAL'
            ]
        ];
    }
}