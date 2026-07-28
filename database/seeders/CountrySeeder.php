<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CountrySeeder extends Seeder
{
    private const REST_COUNTRIES_API = 'https://restcountries.com/v3.1/all';
    private const REQUEST_TIMEOUT = 120; // 120 seconds
    private const RETRY_TIMES = 3; // Retry 3 times
    private const RETRY_DELAY = 100; // 100ms delay between retries
    private const BATCH_SIZE = 50; // Process in batches to avoid memory issues

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌍 Fetching countries from REST Countries API...');
        
        try {
            // Fetch data from REST Countries API
            $countries = $this->fetchCountriesFromAPI();
            
            if (empty($countries)) {
                $this->command->error('❌ No countries data received from API');
                return;
            }

            $this->command->info("✓ Received data for " . count($countries) . " countries");
            
            // Seed countries in batches with transaction
            $this->seedCountriesInBatches($countries);
            
            $this->command->info('✅ Country seeding completed successfully!');

        } catch (\Exception $e) {
            $this->command->error('❌ Error during seeding: ' . $e->getMessage());
            Log::error('CountrySeeder Error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Fetch all countries from REST Countries API with retry mechanism
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
            
            // Handle both array response and object response
            if (is_object($data)) {
                $data = (array) $data;
            }
            
            if (!is_array($data)) {
                // Try to decode if still not array
                $data = json_decode(json_encode($data), true);
            }
            
            // Check if we got valid array of countries
            if (!is_array($data) || empty($data)) {
                throw new \Exception("Invalid API response format or empty data");
            }
            
            // Filter out non-array entries
            $data = array_filter($data, function($item) {
                return is_array($item) || is_object($item);
            });

            return $data;

        } catch (\Exception $e) {
            $this->command->error("Failed to fetch from API: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Seed countries in batches with database transaction
     */
    private function seedCountriesInBatches(array $allCountries): void
    {
        $totalCountries = count($allCountries);
        $batches = array_chunk($allCountries, self::BATCH_SIZE);
        $processedCount = 0;
        $createdCount = 0;
        $updatedCount = 0;

        $progressBar = $this->command->getOutput()->createProgressBar(count($batches));
        $progressBar->start();

        foreach ($batches as $batch) {
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
                        Log::warning('Failed to process country data', [
                            'data' => $countryData,
                            'error' => $e->getMessage()
                        ]);
                        // Continue with next country instead of failing
                    }
                }
            });

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine(2);
        
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
        // Ensure it's an array
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

        // Validate required fields
        if (!$commonName || !$iso2Code || !$iso3Code) {
            throw new \Exception("Missing required country data for: " . json_encode(array_slice($countryData, 0, 3)));
        }

        // Extract currencies
        $currencies = $this->extractCurrencies($countryData['currencies'] ?? []);

        // Extract languages
        $languages = $this->extractLanguages($countryData['languages'] ?? []);

        // Extract geographic data
        $latlng = $countryData['latlng'] ?? [];
        $latitude = isset($latlng[0]) ? floatval($latlng[0]) : null;
        $longitude = isset($latlng[1]) ? floatval($latlng[1]) : null;

        // Extract other fields
        $region = $countryData['region'] ?? null;
        $subRegion = $countryData['subregion'] ?? null;
        $flagEmoji = $countryData['flag'] ?? null;
        $capital = $countryData['capital'][0] ?? null;
        $population = isset($countryData['population']) ? intval($countryData['population']) : null;

        // Use updateOrCreate to handle duplicates
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

        // Track if this was created or updated
        if ($country->wasRecentlyCreated) {
            $created = true;
        } else {
            $updated = true;
        }

        return compact('created', 'updated');
    }

    /**
     * Extract and format currency codes
     */
    private function extractCurrencies(array $currenciesData): string
    {
        if (empty($currenciesData)) {
            return '';
        }

        $currencyCodes = array_keys($currenciesData);
        return implode(',', $currencyCodes);
    }

    /**
     * Extract and format language codes
     */
    private function extractLanguages(array $languagesData): string
    {
        if (empty($languagesData)) {
            return '';
        }

        $languageCodes = array_keys($languagesData);
        return implode(',', $languageCodes);
    }
}
