<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CountrySeederWithFallback extends Seeder
{
    private const REST_COUNTRIES_API = 'https://restcountries.com/v3.1/all';
    private const REQUEST_TIMEOUT = 120;
    private const RETRY_TIMES = 3;
    private const RETRY_DELAY = 100;
    private const BATCH_SIZE = 50;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌍 Seeding countries...');
        
        try {
            // Try to fetch from API first
            $countries = $this->fetchCountriesFromAPI();
            
            if (empty($countries)) {
                $this->command->warn('⚠️ API returned no data, using fallback data');
                $countries = $this->getFallbackCountriesData();
            }

            $this->command->info("✓ Processing " . count($countries) . " countries");
            
            // Seed countries in batches with transaction
            $this->seedCountriesInBatches($countries);
            
            $this->command->info('✅ Country seeding completed successfully!');

        } catch (\Exception $e) {
            $this->command->error('❌ Error during seeding: ' . $e->getMessage());
            Log::error('CountrySeeder Error: ' . $e->getMessage());
        }
    }

    /**
     * Fetch all countries from REST Countries API
     */
    private function fetchCountriesFromAPI(): array
    {
        try {
            $this->command->line('Connecting to REST Countries API...');
            
            $response = Http::timeout(self::REQUEST_TIMEOUT)
                ->retry(self::RETRY_TIMES, self::RETRY_DELAY)
                ->get(self::REST_COUNTRIES_API);

            if (!$response->successful()) {
                throw new \Exception("API returned status: " . $response->status());
            }

            $data = $response->json();
            
            if (!is_array($data) || empty($data)) {
                throw new \Exception("Invalid or empty API response");
            }

            return $data;

        } catch (\Exception $e) {
            $this->command->warn("Failed to fetch from API: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Seed countries in batches with database transaction
     */
    private function seedCountriesInBatches(array $allCountries): void
    {
        $batches = array_chunk($allCountries, self::BATCH_SIZE);
        $processedCount = 0;
        $createdCount = 0;
        $updatedCount = 0;

        foreach ($batches as $batchIndex => $batch) {
            DB::transaction(function () use ($batch, &$processedCount, &$createdCount, &$updatedCount) {
                foreach ($batch as $countryData) {
                    try {
                        $result = $this->processCountryData($countryData);
                        
                        if ($result['created']) {
                            $createdCount++;
                        } elseif ($result['updated']) {
                            $updatedCount++;
                        }
                        
                        $processedCount++;

                    } catch (\Exception $e) {
                        Log::warning('Failed to process country: ' . $e->getMessage());
                    }
                }
            });

            $this->command->line("Processed batch " . ($batchIndex + 1) . " of " . count($batches));
        }
        
        $this->command->newLine();
        $this->command->line("📊 Results:");
        $this->command->line("  • Total countries processed: " . $processedCount);
        $this->command->line("  • New countries created: " . $createdCount);
        $this->command->line("  • Existing countries updated: " . $updatedCount);
    }

    /**
     * Process and store individual country data
     */
    private function processCountryData($countryData): array
    {
        if (is_object($countryData)) {
            $countryData = (array) $countryData;
        }
        
        if (!is_array($countryData)) {
            throw new \Exception("Country data is not in valid format");
        }

        $commonName = $countryData['name']['common'] ?? null;
        $officialName = $countryData['name']['official'] ?? null;
        $iso2Code = $countryData['cca2'] ?? null;
        $iso3Code = $countryData['cca3'] ?? null;

        if (!$commonName || !$iso2Code || !$iso3Code) {
            throw new \Exception("Missing required country data");
        }

        $currencies = $this->extractCurrencies($countryData['currencies'] ?? []);
        $languages = $this->extractLanguages($countryData['languages'] ?? []);

        $latlng = $countryData['latlng'] ?? [];
        $latitude = isset($latlng[0]) ? floatval($latlng[0]) : null;
        $longitude = isset($latlng[1]) ? floatval($latlng[1]) : null;

        $region = $countryData['region'] ?? null;
        $subRegion = $countryData['subregion'] ?? null;
        $flagEmoji = $countryData['flag'] ?? null;
        $capital = is_array($countryData['capital'] ?? null) ? $countryData['capital'][0] : null;
        $population = isset($countryData['population']) ? intval($countryData['population']) : null;

        $created = false;
        $updated = false;

        $country = Country::updateOrCreate(
            ['iso3_code' => $iso3Code],
            [
                'name' => $commonName,
                'official_name' => $officialName,
                'iso2_code' => $iso2Code,
                'region' => $region,
                'sub_region' => $subRegion,
                'currencies' => $currencies,
                'languages' => $languages,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'flag_emoji' => $flagEmoji,
                'capital' => $capital,
                'population' => $population,
            ]
        );

        if ($country->wasRecentlyCreated) {
            $created = true;
        } else {
            $updated = true;
        }

        return compact('created', 'updated');
    }

    /**
     * Extract currencies
     */
    private function extractCurrencies(array $currenciesData): string
    {
        if (empty($currenciesData)) {
            return '';
        }
        return implode(',', array_keys($currenciesData));
    }

    /**
     * Extract languages
     */
    private function extractLanguages(array $languagesData): string
    {
        if (empty($languagesData)) {
            return '';
        }
        return implode(',', array_keys($languagesData));
    }

    /**
     * Get fallback countries data (popular countries)
     */
    private function getFallbackCountriesData(): array
    {
        return [
            [
                'name' => ['common' => 'Indonesia', 'official' => 'Republic of Indonesia'],
                'cca2' => 'ID',
                'cca3' => 'IDN',
                'region' => 'Asia',
                'subregion' => 'Southeast Asia',
                'capital' => ['Jakarta'],
                'latlng' => [-0.7893, 113.9213],
                'population' => 285721236,
                'currencies' => ['IDR' => ['name' => 'Indonesian rupiah', 'symbol' => 'Rp']],
                'languages' => ['ind' => 'Indonesian'],
                'flag' => '🇮🇩'
            ],
            [
                'name' => ['common' => 'United States', 'official' => 'United States of America'],
                'cca2' => 'US',
                'cca3' => 'USA',
                'region' => 'Americas',
                'subregion' => 'North America',
                'capital' => ['Washington, D.C.'],
                'latlng' => [37.0902, -95.7129],
                'population' => 338289857,
                'currencies' => ['USD' => ['name' => 'United States dollar', 'symbol' => '$']],
                'languages' => ['eng' => 'English'],
                'flag' => '🇺🇸'
            ],
            [
                'name' => ['common' => 'Germany', 'official' => 'Federal Republic of Germany'],
                'cca2' => 'DE',
                'cca3' => 'DEU',
                'region' => 'Europe',
                'subregion' => 'Central Europe',
                'capital' => ['Berlin'],
                'latlng' => [51.1657, 10.4515],
                'population' => 83369843,
                'currencies' => ['EUR' => ['name' => 'Euro', 'symbol' => '€']],
                'languages' => ['deu' => 'German'],
                'flag' => '🇩🇪'
            ],
            [
                'name' => ['common' => 'China', 'official' => "People's Republic of China"],
                'cca2' => 'CN',
                'cca3' => 'CHN',
                'region' => 'Asia',
                'subregion' => 'Eastern Asia',
                'capital' => ['Beijing'],
                'latlng' => [35.8617, 104.1954],
                'population' => 1425887337,
                'currencies' => ['CNY' => ['name' => 'Chinese yuan', 'symbol' => '¥']],
                'languages' => ['zho' => 'Chinese'],
                'flag' => '🇨🇳'
            ],
            [
                'name' => ['common' => 'Vietnam', 'official' => 'Socialist Republic of Vietnam'],
                'cca2' => 'VN',
                'cca3' => 'VNM',
                'region' => 'Asia',
                'subregion' => 'Southeast Asia',
                'capital' => ['Hanoi'],
                'latlng' => [14.0583, 108.2772],
                'population' => 98186856,
                'currencies' => ['VND' => ['name' => 'Vietnamese đồng', 'symbol' => '₫']],
                'languages' => ['vie' => 'Vietnamese'],
                'flag' => '🇻🇳'
            ],
            [
                'name' => ['common' => 'Singapore', 'official' => 'Republic of Singapore'],
                'cca2' => 'SG',
                'cca3' => 'SGP',
                'region' => 'Asia',
                'subregion' => 'Southeast Asia',
                'capital' => ['Singapore'],
                'latlng' => [1.3521, 103.8198],
                'population' => 5917600,
                'currencies' => ['SGD' => ['name' => 'Singapore dollar', 'symbol' => '$']],
                'languages' => ['eng' => 'English', 'zho' => 'Chinese', 'tam' => 'Tamil', 'msa' => 'Malay'],
                'flag' => '🇸🇬'
            ],
            [
                'name' => ['common' => 'Japan', 'official' => 'Japan'],
                'cca2' => 'JP',
                'cca3' => 'JPN',
                'region' => 'Asia',
                'subregion' => 'Eastern Asia',
                'capital' => ['Tokyo'],
                'latlng' => [36.2048, 138.2529],
                'population' => 123294513,
                'currencies' => ['JPY' => ['name' => 'Japanese yen', 'symbol' => '¥']],
                'languages' => ['jpn' => 'Japanese'],
                'flag' => '🇯🇵'
            ],
            [
                'name' => ['common' => 'India', 'official' => 'Republic of India'],
                'cca2' => 'IN',
                'cca3' => 'IND',
                'region' => 'Asia',
                'subregion' => 'Southern Asia',
                'capital' => ['New Delhi'],
                'latlng' => [20.5937, 78.9629],
                'population' => 1417173173,
                'currencies' => ['INR' => ['name' => 'Indian rupee', 'symbol' => '₹']],
                'languages' => ['hin' => 'Hindi', 'eng' => 'English'],
                'flag' => '🇮🇳'
            ],
            [
                'name' => ['common' => 'Australia', 'official' => 'Commonwealth of Australia'],
                'cca2' => 'AU',
                'cca3' => 'AUS',
                'region' => 'Oceania',
                'subregion' => 'Australia and New Zealand',
                'capital' => ['Canberra'],
                'latlng' => [-25.2744, 133.7751],
                'population' => 26068792,
                'currencies' => ['AUD' => ['name' => 'Australian dollar', 'symbol' => '$']],
                'languages' => ['eng' => 'English'],
                'flag' => '🇦🇺'
            ],
            [
                'name' => ['common' => 'Malaysia', 'official' => 'Malaysia'],
                'cca2' => 'MY',
                'cca3' => 'MYS',
                'region' => 'Asia',
                'subregion' => 'Southeast Asia',
                'capital' => ['Kuala Lumpur'],
                'latlng' => [4.2105, 101.9758],
                'population' => 34005300,
                'currencies' => ['MYR' => ['name' => 'Malaysian ringgit', 'symbol' => 'RM']],
                'languages' => ['msa' => 'Malay'],
                'flag' => '🇲🇾'
            ],
            [
                'name' => ['common' => 'United Kingdom', 'official' => 'United Kingdom of Great Britain and Northern Ireland'],
                'cca2' => 'GB',
                'cca3' => 'GBR',
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'capital' => ['London'],
                'latlng' => [55.3781, -3.436],
                'population' => 67736802,
                'currencies' => ['GBP' => ['name' => 'British pound', 'symbol' => '£']],
                'languages' => ['eng' => 'English'],
                'flag' => '🇬🇧'
            ],
            [
                'name' => ['common' => 'France', 'official' => 'French Republic'],
                'cca2' => 'FR',
                'cca3' => 'FRA',
                'region' => 'Europe',
                'subregion' => 'Western Europe',
                'capital' => ['Paris'],
                'latlng' => [46.2276, 2.2137],
                'population' => 68014976,
                'currencies' => ['EUR' => ['name' => 'Euro', 'symbol' => '€']],
                'languages' => ['fra' => 'French'],
                'flag' => '🇫🇷'
            ],
            [
                'name' => ['common' => 'Brazil', 'official' => 'Federative Republic of Brazil'],
                'cca2' => 'BR',
                'cca3' => 'BRA',
                'region' => 'Americas',
                'subregion' => 'South America',
                'capital' => ['Brasília'],
                'latlng' => [-14.235, -51.9253],
                'population' => 216422446,
                'currencies' => ['BRL' => ['name' => 'Brazilian real', 'symbol' => 'R$']],
                'languages' => ['por' => 'Portuguese'],
                'flag' => '🇧🇷'
            ],
            [
                'name' => ['common' => 'Mexico', 'official' => 'Mexican United States'],
                'cca2' => 'MX',
                'cca3' => 'MEX',
                'region' => 'Americas',
                'subregion' => 'North America',
                'capital' => ['Mexico City'],
                'latlng' => [23.6345, -102.5528],
                'population' => 128932753,
                'currencies' => ['MXN' => ['name' => 'Mexican peso', 'symbol' => '$']],
                'languages' => ['spa' => 'Spanish'],
                'flag' => '🇲🇽'
            ],
            [
                'name' => ['common' => 'Thailand', 'official' => 'Kingdom of Thailand'],
                'cca2' => 'TH',
                'cca3' => 'THA',
                'region' => 'Asia',
                'subregion' => 'Southeast Asia',
                'capital' => ['Bangkok'],
                'latlng' => [15.870032, 100.992541],
                'population' => 71801915,
                'currencies' => ['THB' => ['name' => 'Thai baht', 'symbol' => '฿']],
                'languages' => ['tha' => 'Thai'],
                'flag' => '🇹🇭'
            ],
            [
                'name' => ['common' => 'Philippines', 'official' => 'Republic of the Philippines'],
                'cca2' => 'PH',
                'cca3' => 'PHL',
                'region' => 'Asia',
                'subregion' => 'Southeast Asia',
                'capital' => ['Manila'],
                'latlng' => [12.8797, 121.7740],
                'population' => 120133437,
                'currencies' => ['PHP' => ['name' => 'Philippine peso', 'symbol' => '₱']],
                'languages' => ['fil' => 'Filipino', 'eng' => 'English'],
                'flag' => '🇵🇭'
            ],
            [
                'name' => ['common' => 'South Korea', 'official' => 'Republic of Korea'],
                'cca2' => 'KR',
                'cca3' => 'KOR',
                'region' => 'Asia',
                'subregion' => 'Eastern Asia',
                'capital' => ['Seoul'],
                'latlng' => [35.9078, 127.7669],
                'population' => 51480000,
                'currencies' => ['KRW' => ['name' => 'South Korean won', 'symbol' => '₩']],
                'languages' => ['kor' => 'Korean'],
                'flag' => '🇰🇷'
            ],
            [
                'name' => ['common' => 'Canada', 'official' => 'Canada'],
                'cca2' => 'CA',
                'cca3' => 'CAN',
                'region' => 'Americas',
                'subregion' => 'North America',
                'capital' => ['Ottawa'],
                'latlng' => [56.1304, -106.3468],
                'population' => 39566248,
                'currencies' => ['CAD' => ['name' => 'Canadian dollar', 'symbol' => '$']],
                'languages' => ['eng' => 'English', 'fra' => 'French'],
                'flag' => '🇨🇦'
            ]
        ];
    }
}
