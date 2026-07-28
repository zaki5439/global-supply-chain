<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PopulateCountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌍 Populating countries table...');
        
        // Get countries data
        $countries = $this->getCountriesData();
        
        // Disable foreign keys temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Clear existing data
        DB::table('countries')->truncate();
        
        // Insert countries
        $chunks = array_chunk($countries, 50);
        $totalInserted = 0;
        
        foreach ($chunks as $chunk) {
            DB::table('countries')->insert($chunk);
            $totalInserted += count($chunk);
            $this->command->line("Inserted $totalInserted countries...");
        }
        
        // Re-enable foreign keys
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        $this->command->info("✅ Successfully inserted $totalInserted countries!");
    }

    /**
     * Get countries data with proper column names
     */
    private function getCountriesData(): array
    {
        return [
            ['name' => 'Indonesia', 'iso2_code' => 'ID', 'iso3_code' => 'IDN', 'region' => 'Asia', 'latitude' => -0.7893, 'longitude' => 113.9213, 'created_at' => now()],
            ['name' => 'United States', 'iso2_code' => 'US', 'iso3_code' => 'USA', 'region' => 'Americas', 'latitude' => 37.0902, 'longitude' => -95.7129, 'created_at' => now()],
            ['name' => 'Germany', 'iso2_code' => 'DE', 'iso3_code' => 'DEU', 'region' => 'Europe', 'latitude' => 51.1657, 'longitude' => 10.4515, 'created_at' => now()],
            ['name' => 'China', 'iso2_code' => 'CN', 'iso3_code' => 'CHN', 'region' => 'Asia', 'latitude' => 35.8617, 'longitude' => 104.1954, 'created_at' => now()],
            ['name' => 'Vietnam', 'iso2_code' => 'VN', 'iso3_code' => 'VNM', 'region' => 'Asia', 'latitude' => 14.0583, 'longitude' => 108.2772, 'created_at' => now()],
            ['name' => 'Singapore', 'iso2_code' => 'SG', 'iso3_code' => 'SGP', 'region' => 'Asia', 'latitude' => 1.3521, 'longitude' => 103.8198, 'created_at' => now()],
            ['name' => 'Japan', 'iso2_code' => 'JP', 'iso3_code' => 'JPN', 'region' => 'Asia', 'latitude' => 36.2048, 'longitude' => 138.2529, 'created_at' => now()],
            ['name' => 'India', 'iso2_code' => 'IN', 'iso3_code' => 'IND', 'region' => 'Asia', 'latitude' => 20.5937, 'longitude' => 78.9629, 'created_at' => now()],
            ['name' => 'Australia', 'iso2_code' => 'AU', 'iso3_code' => 'AUS', 'region' => 'Oceania', 'latitude' => -25.2744, 'longitude' => 133.7751, 'created_at' => now()],
            ['name' => 'Malaysia', 'iso2_code' => 'MY', 'iso3_code' => 'MYS', 'region' => 'Asia', 'latitude' => 4.2105, 'longitude' => 101.9758, 'created_at' => now()],
            ['name' => 'United Kingdom', 'iso2_code' => 'GB', 'iso3_code' => 'GBR', 'region' => 'Europe', 'latitude' => 55.3781, 'longitude' => -3.436, 'created_at' => now()],
            ['name' => 'France', 'iso2_code' => 'FR', 'iso3_code' => 'FRA', 'region' => 'Europe', 'latitude' => 46.2276, 'longitude' => 2.2137, 'created_at' => now()],
            ['name' => 'Brazil', 'iso2_code' => 'BR', 'iso3_code' => 'BRA', 'region' => 'Americas', 'latitude' => -14.235, 'longitude' => -51.9253, 'created_at' => now()],
            ['name' => 'Mexico', 'iso2_code' => 'MX', 'iso3_code' => 'MEX', 'region' => 'Americas', 'latitude' => 23.6345, 'longitude' => -102.5528, 'created_at' => now()],
            ['name' => 'Thailand', 'iso2_code' => 'TH', 'iso3_code' => 'THA', 'region' => 'Asia', 'latitude' => 15.870032, 'longitude' => 100.992541, 'created_at' => now()],
            ['name' => 'Philippines', 'iso2_code' => 'PH', 'iso3_code' => 'PHL', 'region' => 'Asia', 'latitude' => 12.8797, 'longitude' => 121.7740, 'created_at' => now()],
            ['name' => 'South Korea', 'iso2_code' => 'KR', 'iso3_code' => 'KOR', 'region' => 'Asia', 'latitude' => 35.9078, 'longitude' => 127.7669, 'created_at' => now()],
            ['name' => 'Canada', 'iso2_code' => 'CA', 'iso3_code' => 'CAN', 'region' => 'Americas', 'latitude' => 56.1304, 'longitude' => -106.3468, 'created_at' => now()],
            ['name' => 'Spain', 'iso2_code' => 'ES', 'iso3_code' => 'ESP', 'region' => 'Europe', 'latitude' => 40.463667, 'longitude' => -3.74922, 'created_at' => now()],
            ['name' => 'Italy', 'iso2_code' => 'IT', 'iso3_code' => 'ITA', 'region' => 'Europe', 'latitude' => 41.871940, 'longitude' => 12.56738, 'created_at' => now()],
            ['name' => 'Russia', 'iso2_code' => 'RU', 'iso3_code' => 'RUS', 'region' => 'Europe', 'latitude' => 61.52401, 'longitude' => 105.31875, 'created_at' => now()],
            ['name' => 'Saudi Arabia', 'iso2_code' => 'SA', 'iso3_code' => 'SAU', 'region' => 'Asia', 'latitude' => 23.88329, 'longitude' => 45.07923, 'created_at' => now()],
            ['name' => 'United Arab Emirates', 'iso2_code' => 'AE', 'iso3_code' => 'ARE', 'region' => 'Asia', 'latitude' => 23.42411, 'longitude' => 53.84778, 'created_at' => now()],
            ['name' => 'Egypt', 'iso2_code' => 'EG', 'iso3_code' => 'EGY', 'region' => 'Africa', 'latitude' => 26.82261, 'longitude' => 30.80289, 'created_at' => now()],
            ['name' => 'Nigeria', 'iso2_code' => 'NG', 'iso3_code' => 'NGA', 'region' => 'Africa', 'latitude' => 9.08197, 'longitude' => 8.67539, 'created_at' => now()],
            ['name' => 'South Africa', 'iso2_code' => 'ZA', 'iso3_code' => 'ZAF', 'region' => 'Africa', 'latitude' => -30.55973, 'longitude' => 22.93742, 'created_at' => now()],
            ['name' => 'New Zealand', 'iso2_code' => 'NZ', 'iso3_code' => 'NZL', 'region' => 'Oceania', 'latitude' => -40.900557, 'longitude' => 174.88597, 'created_at' => now()],
            ['name' => 'Pakistan', 'iso2_code' => 'PK', 'iso3_code' => 'PAK', 'region' => 'Asia', 'latitude' => 30.37453, 'longitude' => 69.34511, 'created_at' => now()],
            ['name' => 'Bangladesh', 'iso2_code' => 'BD', 'iso3_code' => 'BGD', 'region' => 'Asia', 'latitude' => 23.68041, 'longitude' => 90.35635, 'created_at' => now()],
            ['name' => 'Turkey', 'iso2_code' => 'TR', 'iso3_code' => 'TUR', 'region' => 'Europe', 'latitude' => 38.96375, 'longitude' => 35.24328, 'created_at' => now()],
            ['name' => 'Greece', 'iso2_code' => 'GR', 'iso3_code' => 'GRC', 'region' => 'Europe', 'latitude' => 39.07469, 'longitude' => 21.82412, 'created_at' => now()],
            ['name' => 'Netherlands', 'iso2_code' => 'NL', 'iso3_code' => 'NLD', 'region' => 'Europe', 'latitude' => 52.13263, 'longitude' => 5.29163, 'created_at' => now()],
            ['name' => 'Switzerland', 'iso2_code' => 'CH', 'iso3_code' => 'CHE', 'region' => 'Europe', 'latitude' => 46.81828, 'longitude' => 8.22753, 'created_at' => now()],
            ['name' => 'Sweden', 'iso2_code' => 'SE', 'iso3_code' => 'SWE', 'region' => 'Europe', 'latitude' => 60.12816, 'longitude' => 18.64349, 'created_at' => now()],
            ['name' => 'Norway', 'iso2_code' => 'NO', 'iso3_code' => 'NOR', 'region' => 'Europe', 'latitude' => 60.47202, 'longitude' => 8.46972, 'created_at' => now()],
            ['name' => 'Poland', 'iso2_code' => 'PL', 'iso3_code' => 'POL', 'region' => 'Europe', 'latitude' => 51.91938, 'longitude' => 19.14514, 'created_at' => now()],
            ['name' => 'Belgium', 'iso2_code' => 'BE', 'iso3_code' => 'BEL', 'region' => 'Europe', 'latitude' => 50.50353, 'longitude' => 4.47941, 'created_at' => now()],
            ['name' => 'Austria', 'iso2_code' => 'AT', 'iso3_code' => 'AUT', 'region' => 'Europe', 'latitude' => 47.51629, 'longitude' => 14.55020, 'created_at' => now()],
            ['name' => 'Portugal', 'iso2_code' => 'PT', 'iso3_code' => 'PRT', 'region' => 'Europe', 'latitude' => 39.39999, 'longitude' => -8.22436, 'created_at' => now()],
            ['name' => 'Czech Republic', 'iso2_code' => 'CZ', 'iso3_code' => 'CZE', 'region' => 'Europe', 'latitude' => 49.81749, 'longitude' => 15.47298, 'created_at' => now()],
            ['name' => 'Ireland', 'iso2_code' => 'IE', 'iso3_code' => 'IRL', 'region' => 'Europe', 'latitude' => 53.41291, 'longitude' => -8.24389, 'created_at' => now()],
            ['name' => 'Denmark', 'iso2_code' => 'DK', 'iso3_code' => 'DNK', 'region' => 'Europe', 'latitude' => 56.26392, 'longitude' => 9.50195, 'created_at' => now()],
            ['name' => 'Hungary', 'iso2_code' => 'HU', 'iso3_code' => 'HUN', 'region' => 'Europe', 'latitude' => 47.16264, 'longitude' => 19.50330, 'created_at' => now()],
            ['name' => 'Romania', 'iso2_code' => 'RO', 'iso3_code' => 'ROU', 'region' => 'Europe', 'latitude' => 45.94316, 'longitude' => 24.96676, 'created_at' => now()],
            ['name' => 'Thailand', 'iso2_code' => 'TH', 'iso3_code' => 'THA', 'region' => 'Asia', 'latitude' => 15.870032, 'longitude' => 100.992541, 'created_at' => now()],
            ['name' => 'Hong Kong', 'iso2_code' => 'HK', 'iso3_code' => 'HKG', 'region' => 'Asia', 'latitude' => 22.3193, 'longitude' => 114.1694, 'created_at' => now()],
            ['name' => 'Taiwan', 'iso2_code' => 'TW', 'iso3_code' => 'TWN', 'region' => 'Asia', 'latitude' => 23.6978, 'longitude' => 120.9605, 'created_at' => now()],
            ['name' => 'Israel', 'iso2_code' => 'IL', 'iso3_code' => 'ISR', 'region' => 'Asia', 'latitude' => 31.0461, 'longitude' => 34.8516, 'created_at' => now()],
            ['name' => 'Kenya', 'iso2_code' => 'KE', 'iso3_code' => 'KEN', 'region' => 'Africa', 'latitude' => -0.0236, 'longitude' => 37.9062, 'created_at' => now()],
            ['name' => 'Morocco', 'iso2_code' => 'MA', 'iso3_code' => 'MAR', 'region' => 'Africa', 'latitude' => 31.7917, 'longitude' => -7.0926, 'created_at' => now()],
            ['name' => 'Ethiopia', 'iso2_code' => 'ET', 'iso3_code' => 'ETH', 'region' => 'Africa', 'latitude' => 9.1450, 'longitude' => 40.4897, 'created_at' => now()],
            ['name' => 'Argentina', 'iso2_code' => 'AR', 'iso3_code' => 'ARG', 'region' => 'Americas', 'latitude' => -38.4161, 'longitude' => -63.6167, 'created_at' => now()],
            ['name' => 'Chile', 'iso2_code' => 'CL', 'iso3_code' => 'CHL', 'region' => 'Americas', 'latitude' => -35.6751, 'longitude' => -71.5430, 'created_at' => now()],
            ['name' => 'Peru', 'iso2_code' => 'PE', 'iso3_code' => 'PER', 'region' => 'Americas', 'latitude' => -9.1900, 'longitude' => -75.0152, 'created_at' => now()],
            ['name' => 'Colombia', 'iso2_code' => 'CO', 'iso3_code' => 'COL', 'region' => 'Americas', 'latitude' => 4.5709, 'longitude' => -74.2973, 'created_at' => now()],
            ['name' => 'Venezuela', 'iso2_code' => 'VE', 'iso3_code' => 'VEN', 'region' => 'Americas', 'latitude' => 6.4238, 'longitude' => -66.5897, 'created_at' => now()],
            ['name' => 'Myanmar', 'iso2_code' => 'MM', 'iso3_code' => 'MMR', 'region' => 'Asia', 'latitude' => 21.9162, 'longitude' => 95.9560, 'created_at' => now()],
            ['name' => 'Cambodia', 'iso2_code' => 'KH', 'iso3_code' => 'KHM', 'region' => 'Asia', 'latitude' => 12.5657, 'longitude' => 104.9910, 'created_at' => now()],
            ['name' => 'Laos', 'iso2_code' => 'LA', 'iso3_code' => 'LAO', 'region' => 'Asia', 'latitude' => 19.8523, 'longitude' => 102.4955, 'created_at' => now()],
        ];
    }
}
