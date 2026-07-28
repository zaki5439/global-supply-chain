<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Port; 

class ExternalApiService
{
    /**
     * 1. Open-Meteo API (Cuaca)
     * Mengembalikan data fallback yang aman jika API down
     */
    public function getWeather(float $lat, float $lng): array
    {
        try {
            $response = Http::timeout(5)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $lat,
                'longitude' => $lng,
                'current_weather' => true,
                'hourly' => 'precipitation,windspeed_10m'
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $windspeed = $data['current_weather']['windspeed'] ?? 0;
                return [
                    'temperature' => $data['current_weather']['temperature'] ?? null,
                    'windspeed' => $windspeed,
                    'precipitation' => $data['hourly']['precipitation'][0] ?? 0,
                    'is_storm_risk' => $windspeed > 60 // Logika sederhana badai > 60km/h
                ];
            }
        } catch (\Exception $e) {
            Log::error("OpenMeteo Error: " . $e->getMessage());
        }
        
        // Fallback response aman
        return ['temperature' => null, 'windspeed' => 0, 'precipitation' => 0, 'is_storm_risk' => false];
    }

    /**
     * 2. World Bank API (Makroekonomi)
     */
    public function getWorldBankData(string $countryCode): array
    {
        $indicators = [
            'gdp' => 'NY.GDP.MKTP.CD',
            'inflation' => 'FP.CPI.TOTL.ZG',
            'population' => 'SP.POP.TOTL',
            'export' => 'NE.EXP.GNFS.CD',
            'import' => 'NE.IMP.GNFS.CD'
        ];
        
        $results = [];
        // Kita hit satu-satu dengan timeout kecil, namun lebih baik lagi jika dipanggil paralel menggunakan Http::pool
        foreach ($indicators as $key => $indicator) {
            try {
                $url = "https://api.worldbank.org/v2/country/{$countryCode}/indicator/{$indicator}?format=json&per_page=1";
                $response = Http::timeout(3)->get($url);
                if ($response->successful() && isset($response->json()[1][0])) {
                    $results[$key] = $response->json()[1][0]['value'] ?? null;
                } else {
                    $results[$key] = null;
                }
            } catch (\Exception $e) {
                Log::error("World Bank API Error ({$key}): " . $e->getMessage());
                $results[$key] = null; // Fallback per indikator
            }
        }
        return $results;
    }

    /**
     * 3. REST Countries API (Profil Negara)
     */
    public function getCountryDetails(string $countryName): array
    {
        try {
            $response = Http::timeout(5)->get("https://restcountries.com/v3.1/name/{$countryName}");
            if ($response->successful()) {
                $data = $response->json()[0];
                return [
                    'name' => $data['name']['common'] ?? $countryName,
                    'region' => $data['region'] ?? 'Unknown',
                    'currencies' => $data['currencies'] ?? [],
                    'languages' => $data['languages'] ?? [],
                    'latlng' => $data['latlng'] ?? [0, 0]
                ];
            }
        } catch (\Exception $e) {
            Log::error("REST Countries Error: " . $e->getMessage());
        }
        return ['name' => $countryName, 'region' => 'Unknown', 'currencies' => [], 'languages' => [], 'latlng' => [0, 0]];
    }

    /**
     * 4. ExchangeRate API (Kurs)
     */
    public function getExchangeRates(string $baseCurrency = 'USD'): array
    {
        try {
            $response = Http::timeout(5)->get("https://api.exchangerate-api.com/v4/latest/{$baseCurrency}");
            if ($response->successful()) {
                return $response->json('rates') ?? [];
            }
        } catch (\Exception $e) {
            Log::error("ExchangeRate Error: " . $e->getMessage());
        }
        return [];
    }

    /**
     * 5. World Port Index (Marine Traffic Alternative)
     * Menggunakan query statis database fallback
     */
    public static function getWorldPorts(string $countryIso3 = null)
    {
        try {
            $query = Port::query()->select('id', 'name', 'latitude', 'longitude', 'country_id');
            if ($countryIso3) {
                $query->whereHas('country', fn($q) => $q->where('iso3', $countryIso3));
            }
            return $query->get();
        } catch (\Exception $e) {
            Log::error("World Port Query Error: " . $e->getMessage());
            return collect(); // Fallback return empty Collection agar tidak crash saat dilooping frontend
        }
    }

    /**
     * 6. GNews API (Berita & Sentimen)
     * Dengan database caching
     */
    public function getNews(string $countryName): array
    {
        $keyword = "economy logistics geopolitics {$countryName}";
        $queryHash = hash('sha256', $keyword);
        
        try {
            // Cek cache
            $cache = DB::table('news_cache')->where('query_hash', $queryHash)->where('expires_at', '>', now())->first();
            if ($cache) {
                return json_decode($cache->response_payload, true)['articles'] ?? [];
            }

            // Hit API jika cache kosong / expired
            $response = Http::timeout(5)->get("https://gnews.io/api/v4/search", [
                'q' => $keyword,
                'token' => env('GNEWS_API_KEY', 'demo_key'),
                'lang' => 'en',
                'max' => 5
            ]);

            if ($response->successful()) {
                $payload = $response->json();
                DB::table('news_cache')->updateOrInsert(
                    ['query_hash' => $queryHash],
                    [
                        'source_api' => 'gnews',
                        'request_params' => json_encode(['q' => $keyword]),
                        'response_payload' => json_encode($payload),
                        'fetched_at' => now(),
                        'expires_at' => now()->addHours(6)
                    ]
                );
                return $payload['articles'] ?? [];
            }
        } catch (\Exception $e) {
            Log::error("GNews Error: " . $e->getMessage());
        }
        
        return []; // Fallback array kosong
    }
}
