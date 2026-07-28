<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySimpleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌍 Seeding countries from static data...');
        
        $countries = $this->getCountriesData();
        
        $createdCount = 0;
        $updatedCount = 0;
        
        foreach ($countries as $countryData) {
            $result = Country::updateOrCreate(
                ['iso3_code' => $countryData['iso3']],
                [
                    'name' => $countryData['name'],
                    'official_name' => $countryData['official'],
                    'iso2_code' => $countryData['iso2'],
                    'region' => $countryData['region'],
                    'sub_region' => $countryData['subregion'],
                    'currencies' => $countryData['currencies'],
                    'languages' => $countryData['languages'],
                    'latitude' => $countryData['lat'],
                    'longitude' => $countryData['lon'],
                    'flag_emoji' => $countryData['flag'],
                    'capital' => $countryData['capital'],
                    'population' => $countryData['population'],
                ]
            );
            
            if ($result->wasRecentlyCreated) {
                $createdCount++;
            } else {
                $updatedCount++;
            }
        }
        
        $this->command->info('✅ Country seeding completed successfully!');
        $this->command->line("📊 Results:");
        $this->command->line("  • New countries created: " . $createdCount);
        $this->command->line("  • Existing countries updated: " . $updatedCount);
        $this->command->line("  • Total countries: " . count($countries));
    }

    /**
     * Get countries data
     */
    private function getCountriesData(): array
    {
        return [
            ['name' => 'Indonesia', 'official' => 'Republic of Indonesia', 'iso2' => 'ID', 'iso3' => 'IDN', 'region' => 'Asia', 'subregion' => 'Southeast Asia', 'capital' => 'Jakarta', 'lat' => -0.7893, 'lon' => 113.9213, 'population' => 285721236, 'currencies' => 'IDR', 'languages' => 'id', 'flag' => '🇮🇩'],
            ['name' => 'United States', 'official' => 'United States of America', 'iso2' => 'US', 'iso3' => 'USA', 'region' => 'Americas', 'subregion' => 'North America', 'capital' => 'Washington D.C.', 'lat' => 37.0902, 'lon' => -95.7129, 'population' => 338289857, 'currencies' => 'USD', 'languages' => 'en', 'flag' => '🇺🇸'],
            ['name' => 'Germany', 'official' => 'Federal Republic of Germany', 'iso2' => 'DE', 'iso3' => 'DEU', 'region' => 'Europe', 'subregion' => 'Central Europe', 'capital' => 'Berlin', 'lat' => 51.1657, 'lon' => 10.4515, 'population' => 83369843, 'currencies' => 'EUR', 'languages' => 'de', 'flag' => '🇩🇪'],
            ['name' => 'China', 'official' => 'People\'s Republic of China', 'iso2' => 'CN', 'iso3' => 'CHN', 'region' => 'Asia', 'subregion' => 'Eastern Asia', 'capital' => 'Beijing', 'lat' => 35.8617, 'lon' => 104.1954, 'population' => 1425887337, 'currencies' => 'CNY', 'languages' => 'zh', 'flag' => '🇨🇳'],
            ['name' => 'Vietnam', 'official' => 'Socialist Republic of Vietnam', 'iso2' => 'VN', 'iso3' => 'VNM', 'region' => 'Asia', 'subregion' => 'Southeast Asia', 'capital' => 'Hanoi', 'lat' => 14.0583, 'lon' => 108.2772, 'population' => 98186856, 'currencies' => 'VND', 'languages' => 'vi', 'flag' => '🇻🇳'],
            ['name' => 'Singapore', 'official' => 'Republic of Singapore', 'iso2' => 'SG', 'iso3' => 'SGP', 'region' => 'Asia', 'subregion' => 'Southeast Asia', 'capital' => 'Singapore', 'lat' => 1.3521, 'lon' => 103.8198, 'population' => 5917600, 'currencies' => 'SGD', 'languages' => 'en,zh,ta,ms', 'flag' => '🇸🇬'],
            ['name' => 'Japan', 'official' => 'Japan', 'iso2' => 'JP', 'iso3' => 'JPN', 'region' => 'Asia', 'subregion' => 'Eastern Asia', 'capital' => 'Tokyo', 'lat' => 36.2048, 'lon' => 138.2529, 'population' => 123294513, 'currencies' => 'JPY', 'languages' => 'ja', 'flag' => '🇯🇵'],
            ['name' => 'India', 'official' => 'Republic of India', 'iso2' => 'IN', 'iso3' => 'IND', 'region' => 'Asia', 'subregion' => 'Southern Asia', 'capital' => 'New Delhi', 'lat' => 20.5937, 'lon' => 78.9629, 'population' => 1417173173, 'currencies' => 'INR', 'languages' => 'hi,en', 'flag' => '🇮🇳'],
            ['name' => 'Australia', 'official' => 'Commonwealth of Australia', 'iso2' => 'AU', 'iso3' => 'AUS', 'region' => 'Oceania', 'subregion' => 'Australia and New Zealand', 'capital' => 'Canberra', 'lat' => -25.2744, 'lon' => 133.7751, 'population' => 26068792, 'currencies' => 'AUD', 'languages' => 'en', 'flag' => '🇦🇺'],
            ['name' => 'Malaysia', 'official' => 'Malaysia', 'iso2' => 'MY', 'iso3' => 'MYS', 'region' => 'Asia', 'subregion' => 'Southeast Asia', 'capital' => 'Kuala Lumpur', 'lat' => 4.2105, 'lon' => 101.9758, 'population' => 34005300, 'currencies' => 'MYR', 'languages' => 'ms', 'flag' => '🇲🇾'],
            ['name' => 'United Kingdom', 'official' => 'United Kingdom of Great Britain and Northern Ireland', 'iso2' => 'GB', 'iso3' => 'GBR', 'region' => 'Europe', 'subregion' => 'Northern Europe', 'capital' => 'London', 'lat' => 55.3781, 'lon' => -3.436, 'population' => 67736802, 'currencies' => 'GBP', 'languages' => 'en', 'flag' => '🇬🇧'],
            ['name' => 'France', 'official' => 'French Republic', 'iso2' => 'FR', 'iso3' => 'FRA', 'region' => 'Europe', 'subregion' => 'Western Europe', 'capital' => 'Paris', 'lat' => 46.2276, 'lon' => 2.2137, 'population' => 68014976, 'currencies' => 'EUR', 'languages' => 'fr', 'flag' => '🇫🇷'],
            ['name' => 'Brazil', 'official' => 'Federative Republic of Brazil', 'iso2' => 'BR', 'iso3' => 'BRA', 'region' => 'Americas', 'subregion' => 'South America', 'capital' => 'Brasília', 'lat' => -14.235, 'lon' => -51.9253, 'population' => 216422446, 'currencies' => 'BRL', 'languages' => 'pt', 'flag' => '🇧🇷'],
            ['name' => 'Mexico', 'official' => 'Mexican United States', 'iso2' => 'MX', 'iso3' => 'MEX', 'region' => 'Americas', 'subregion' => 'North America', 'capital' => 'Mexico City', 'lat' => 23.6345, 'lon' => -102.5528, 'population' => 128932753, 'currencies' => 'MXN', 'languages' => 'es', 'flag' => '🇲🇽'],
            ['name' => 'Thailand', 'official' => 'Kingdom of Thailand', 'iso2' => 'TH', 'iso3' => 'THA', 'region' => 'Asia', 'subregion' => 'Southeast Asia', 'capital' => 'Bangkok', 'lat' => 15.870032, 'lon' => 100.992541, 'population' => 71801915, 'currencies' => 'THB', 'languages' => 'th', 'flag' => '🇹🇭'],
            ['name' => 'Philippines', 'official' => 'Republic of the Philippines', 'iso2' => 'PH', 'iso3' => 'PHL', 'region' => 'Asia', 'subregion' => 'Southeast Asia', 'capital' => 'Manila', 'lat' => 12.8797, 'lon' => 121.7740, 'population' => 120133437, 'currencies' => 'PHP', 'languages' => 'fil,en', 'flag' => '🇵🇭'],
            ['name' => 'South Korea', 'official' => 'Republic of Korea', 'iso2' => 'KR', 'iso3' => 'KOR', 'region' => 'Asia', 'subregion' => 'Eastern Asia', 'capital' => 'Seoul', 'lat' => 35.9078, 'lon' => 127.7669, 'population' => 51480000, 'currencies' => 'KRW', 'languages' => 'ko', 'flag' => '🇰🇷'],
            ['name' => 'Canada', 'official' => 'Canada', 'iso2' => 'CA', 'iso3' => 'CAN', 'region' => 'Americas', 'subregion' => 'North America', 'capital' => 'Ottawa', 'lat' => 56.1304, 'lon' => -106.3468, 'population' => 39566248, 'currencies' => 'CAD', 'languages' => 'en,fr', 'flag' => '🇨🇦'],
            ['name' => 'Spain', 'official' => 'Kingdom of Spain', 'iso2' => 'ES', 'iso3' => 'ESP', 'region' => 'Europe', 'subregion' => 'Southern Europe', 'capital' => 'Madrid', 'lat' => 40.463667, 'lon' => -3.74922, 'population' => 47351567, 'currencies' => 'EUR', 'languages' => 'es', 'flag' => '🇪🇸'],
            ['name' => 'Italy', 'official' => 'Italian Republic', 'iso2' => 'IT', 'iso3' => 'ITA', 'region' => 'Europe', 'subregion' => 'Southern Europe', 'capital' => 'Rome', 'lat' => 41.871940, 'lon' => 12.56738, 'population' => 58940761, 'currencies' => 'EUR', 'languages' => 'it', 'flag' => '🇮🇹'],
            ['name' => 'Russia', 'official' => 'Russian Federation', 'iso2' => 'RU', 'iso3' => 'RUS', 'region' => 'Europe', 'subregion' => 'Eastern Europe', 'capital' => 'Moscow', 'lat' => 61.52401, 'lon' => 105.31875, 'population' => 144720000, 'currencies' => 'RUB', 'languages' => 'ru', 'flag' => '🇷🇺'],
            ['name' => 'Saudi Arabia', 'official' => 'Kingdom of Saudi Arabia', 'iso2' => 'SA', 'iso3' => 'SAU', 'region' => 'Asia', 'subregion' => 'Western Asia', 'capital' => 'Riyadh', 'lat' => 23.88329, 'lon' => 45.07923, 'population' => 37600000, 'currencies' => 'SAR', 'languages' => 'ar', 'flag' => '🇸🇦'],
            ['name' => 'United Arab Emirates', 'official' => 'United Arab Emirates', 'iso2' => 'AE', 'iso3' => 'ARE', 'region' => 'Asia', 'subregion' => 'Western Asia', 'capital' => 'Abu Dhabi', 'lat' => 23.42411, 'lon' => 53.84778, 'population' => 9890400, 'currencies' => 'AED', 'languages' => 'ar', 'flag' => '🇦🇪'],
            ['name' => 'Egypt', 'official' => 'Arab Republic of Egypt', 'iso2' => 'EG', 'iso3' => 'EGY', 'region' => 'Africa', 'subregion' => 'Northern Africa', 'capital' => 'Cairo', 'lat' => 26.82261, 'lon' => 30.80289, 'population' => 110662103, 'currencies' => 'EGP', 'languages' => 'ar', 'flag' => '🇪🇬'],
            ['name' => 'Nigeria', 'official' => 'Federal Republic of Nigeria', 'iso2' => 'NG', 'iso3' => 'NGA', 'region' => 'Africa', 'subregion' => 'Western Africa', 'capital' => 'Abuja', 'lat' => 9.08197, 'lon' => 8.67539, 'population' => 223804632, 'currencies' => 'NGN', 'languages' => 'en', 'flag' => '🇳🇬'],
            ['name' => 'South Africa', 'official' => 'Republic of South Africa', 'iso2' => 'ZA', 'iso3' => 'ZAF', 'region' => 'Africa', 'subregion' => 'Southern Africa', 'capital' => 'Pretoria', 'lat' => -30.55973, 'lon' => 22.93742, 'population' => 60142978, 'currencies' => 'ZAR', 'languages' => 'af,en,zu,xh', 'flag' => '🇿🇦'],
            ['name' => 'New Zealand', 'official' => 'New Zealand', 'iso2' => 'NZ', 'iso3' => 'NZL', 'region' => 'Oceania', 'subregion' => 'Australia and New Zealand', 'capital' => 'Wellington', 'lat' => -40.900557, 'lon' => 174.88597, 'population' => 5301100, 'currencies' => 'NZD', 'languages' => 'en,mi', 'flag' => '🇳🇿'],
            ['name' => 'Pakistan', 'official' => 'Islamic Republic of Pakistan', 'iso2' => 'PK', 'iso3' => 'PAK', 'region' => 'Asia', 'subregion' => 'Southern Asia', 'capital' => 'Islamabad', 'lat' => 30.37453, 'lon' => 69.34511, 'population' => 240485658, 'currencies' => 'PKR', 'languages' => 'ur,en', 'flag' => '🇵🇰'],
            ['name' => 'Bangladesh', 'official' => 'People\'s Republic of Bangladesh', 'iso2' => 'BD', 'iso3' => 'BGD', 'region' => 'Asia', 'subregion' => 'Southern Asia', 'capital' => 'Dhaka', 'lat' => 23.68041, 'lon' => 90.35635, 'population' => 173562364, 'currencies' => 'BDT', 'languages' => 'bn', 'flag' => '🇧🇩'],
            ['name' => 'Turkey', 'official' => 'Republic of Türkiye', 'iso2' => 'TR', 'iso3' => 'TUR', 'region' => 'Europe', 'subregion' => 'Western Asia', 'capital' => 'Ankara', 'lat' => 38.96375, 'lon' => 35.24328, 'population' => 88776563, 'currencies' => 'TRY', 'languages' => 'tr', 'flag' => '🇹🇷'],
            ['name' => 'Greece', 'official' => 'Hellenic Republic', 'iso2' => 'GR', 'iso3' => 'GRC', 'region' => 'Europe', 'subregion' => 'Southern Europe', 'capital' => 'Athens', 'lat' => 39.07469, 'lon' => 21.82412, 'population' => 10640801, 'currencies' => 'EUR', 'languages' => 'el', 'flag' => '🇬🇷'],
            ['name' => 'Netherlands', 'official' => 'Kingdom of the Netherlands', 'iso2' => 'NL', 'iso3' => 'NLD', 'region' => 'Europe', 'subregion' => 'Western Europe', 'capital' => 'Amsterdam', 'lat' => 52.13263, 'lon' => 5.29163, 'population' => 18053407, 'currencies' => 'EUR', 'languages' => 'nl', 'flag' => '🇳🇱'],
            ['name' => 'Switzerland', 'official' => 'Swiss Confederation', 'iso2' => 'CH', 'iso3' => 'CHE', 'region' => 'Europe', 'subregion' => 'Central Europe', 'capital' => 'Bern', 'lat' => 46.81828, 'lon' => 8.22753, 'population' => 8776000, 'currencies' => 'CHF', 'languages' => 'de,fr,it,rm', 'flag' => '🇨🇭'],
            ['name' => 'Sweden', 'official' => 'Kingdom of Sweden', 'iso2' => 'SE', 'iso3' => 'SWE', 'region' => 'Europe', 'subregion' => 'Northern Europe', 'capital' => 'Stockholm', 'lat' => 60.12816, 'lon' => 18.64349, 'population' => 10549347, 'currencies' => 'SEK', 'languages' => 'sv', 'flag' => '🇸🇪'],
            ['name' => 'Norway', 'official' => 'Kingdom of Norway', 'iso2' => 'NO', 'iso3' => 'NOR', 'region' => 'Europe', 'subregion' => 'Northern Europe', 'capital' => 'Oslo', 'lat' => 60.47202, 'lon' => 8.46972, 'population' => 5640000, 'currencies' => 'NOK', 'languages' => 'no', 'flag' => '🇳🇴'],
            ['name' => 'Poland', 'official' => 'Republic of Poland', 'iso2' => 'PL', 'iso3' => 'POL', 'region' => 'Europe', 'subregion' => 'Central Europe', 'capital' => 'Warsaw', 'lat' => 51.91938, 'lon' => 19.14514, 'population' => 37750000, 'currencies' => 'PLN', 'languages' => 'pl', 'flag' => '🇵🇱'],
            ['name' => 'Belgium', 'official' => 'Kingdom of Belgium', 'iso2' => 'BE', 'iso3' => 'BEL', 'region' => 'Europe', 'subregion' => 'Western Europe', 'capital' => 'Brussels', 'lat' => 50.50353, 'lon' => 4.47941, 'population' => 11590000, 'currencies' => 'EUR', 'languages' => 'nl,fr,de', 'flag' => '🇧🇪'],
            ['name' => 'Austria', 'official' => 'Republic of Austria', 'iso2' => 'AT', 'iso3' => 'AUT', 'region' => 'Europe', 'subregion' => 'Central Europe', 'capital' => 'Vienna', 'lat' => 47.51629, 'lon' => 14.55020, 'population' => 9042000, 'currencies' => 'EUR', 'languages' => 'de', 'flag' => '🇦🇹'],
            ['name' => 'Portugal', 'official' => 'Portuguese Republic', 'iso2' => 'PT', 'iso3' => 'PRT', 'region' => 'Europe', 'subregion' => 'Southern Europe', 'capital' => 'Lisbon', 'lat' => 39.39999, 'lon' => -8.22436, 'population' => 10410564, 'currencies' => 'EUR', 'languages' => 'pt', 'flag' => '🇵🇹'],
            ['name' => 'Czech Republic', 'official' => 'Czech Republic', 'iso2' => 'CZ', 'iso3' => 'CZE', 'region' => 'Europe', 'subregion' => 'Central Europe', 'capital' => 'Prague', 'lat' => 49.81749, 'lon' => 15.47298, 'population' => 10510000, 'currencies' => 'CZK', 'languages' => 'cs', 'flag' => '🇨🇿'],
            ['name' => 'Ireland', 'official' => 'Republic of Ireland', 'iso2' => 'IE', 'iso3' => 'IRL', 'region' => 'Europe', 'subregion' => 'Northern Europe', 'capital' => 'Dublin', 'lat' => 53.41291, 'lon' => -8.24389, 'population' => 5301700, 'currencies' => 'EUR', 'languages' => 'ga,en', 'flag' => '🇮🇪'],
            ['name' => 'Denmark', 'official' => 'Kingdom of Denmark', 'iso2' => 'DK', 'iso3' => 'DNK', 'region' => 'Europe', 'subregion' => 'Northern Europe', 'capital' => 'Copenhagen', 'lat' => 56.26392, 'lon' => 9.50195, 'population' => 5944200, 'currencies' => 'DKK', 'languages' => 'da', 'flag' => '🇩🇰'],
            ['name' => 'Hungary', 'official' => 'Hungary', 'iso2' => 'HU', 'iso3' => 'HUN', 'region' => 'Europe', 'subregion' => 'Central Europe', 'capital' => 'Budapest', 'lat' => 47.16264, 'lon' => 19.50330, 'population' => 9689000, 'currencies' => 'HUF', 'languages' => 'hu', 'flag' => '🇭🇺'],
            ['name' => 'Romania', 'official' => 'Romania', 'iso2' => 'RO', 'iso3' => 'ROU', 'region' => 'Europe', 'subregion' => 'Central Europe', 'capital' => 'Bucharest', 'lat' => 45.94316, 'lon' => 24.96676, 'population' => 19395000, 'currencies' => 'RON', 'languages' => 'ro', 'flag' => '🇷🇴'],
        ];
    }
}
