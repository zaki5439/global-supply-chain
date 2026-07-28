<?php

namespace App\Services;

use App\Models\Country;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Services\SentimentAnalysisService;

class RiskScoringService
{
    protected SentimentAnalysisService $sentimentService;

    public function __construct(SentimentAnalysisService $sentimentService)
    {
        $this->sentimentService = $sentimentService;
    }

    /**
     * Calculate comprehensive risk score for a country.
     * Weights: Weather (30%), Inflation (20%), Currency (10%), News/Political (40%)
     */
    public function calculateCountryRisk(Country $country): array
    {
        // 1. Weather Risk (0-100)
        $weatherRisk = $this->calculateWeatherRisk($country);
        
        // 2. Inflation Risk (0-100)
        $inflationRisk = $this->calculateInflationRisk($country);
        
        // 3. Currency Risk (0-100)
        $currencyRisk = $this->calculateCurrencyRisk($country);
        
        // 4. News/Political Sentiment Risk (0-100)
        $newsRisk = $this->calculateNewsRisk($country);

        // Calculate weighted total score
        $totalRiskScore = 
            ($weatherRisk * 0.30) +
            ($inflationRisk * 0.20) +
            ($currencyRisk * 0.10) +
            ($newsRisk['risk_percentage'] * 0.40);

        $totalRiskScore = round($totalRiskScore);

        $riskLabel = 'Low Risk';
        if ($totalRiskScore >= 70) {
            $riskLabel = 'High Risk';
        } elseif ($totalRiskScore >= 40) {
            $riskLabel = 'Medium Risk';
        }

        return [
            'total_score' => $totalRiskScore,
            'label' => $riskLabel,
            'components' => [
                'weather_risk' => $weatherRisk,
                'inflation_risk' => $inflationRisk,
                'currency_risk' => $currencyRisk,
                'news_risk' => $newsRisk['risk_percentage'],
                'news_sentiment' => $newsRisk['sentiment']
            ]
        ];
    }

    private function calculateWeatherRisk(Country $country): int
    {
        if (!$country->latitude || !$country->longitude) {
            return 20; // Default low risk if no coords
        }

        $cacheKey = "weather_risk_{$country->iso2}";
        return Cache::remember($cacheKey, 3600, function () use ($country) {
            try {
                $response = Http::timeout(5)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $country->latitude,
                    'longitude' => $country->longitude,
                    'current_weather' => true,
                ]);

                if ($response->successful()) {
                    $weather = $response->json('current_weather');
                    $windspeed = $weather['windspeed'] ?? 0;
                    $temperature = $weather['temperature'] ?? 20;

                    $risk = 10;
                    // Extreme wind
                    if ($windspeed > 40) $risk += 50;
                    elseif ($windspeed > 25) $risk += 30;

                    // Extreme temperature
                    if ($temperature < -5 || $temperature > 40) $risk += 30;
                    
                    return min(100, $risk);
                }
            } catch (\Exception $e) {
                // Ignore errors
            }
            return 20;
        });
    }

    private function calculateInflationRisk(Country $country): int
    {
        // Simple logic based on inflation. For now, since WB API is slow and complex,
        // we will use a simplified mock or cached approach based on country ISO.
        // In a real scenario, this would query World Bank for inflation data.
        
        $highRisk = ['AR', 'TR', 'VE', 'ZW', 'SD', 'LB', 'SY'];
        $mediumRisk = ['BR', 'ZA', 'RU', 'EG', 'PK', 'NG'];
        
        if (in_array($country->iso2, $highRisk)) return 90;
        if (in_array($country->iso2, $mediumRisk)) return 60;
        
        return 20;
    }

    private function calculateCurrencyRisk(Country $country): int
    {
        // Simple proxy: Emerging markets usually have higher currency risk
        // In production, compare 30-day volatility via ExchangeRate API
        $lowRisk = ['US', 'GB', 'CH', 'JP', 'AU', 'CA'];
        // Eurozone
        $eurozone = ['DE', 'FR', 'IT', 'ES', 'NL', 'BE', 'AT', 'IE', 'FI', 'PT'];
        
        if (in_array($country->iso2, $lowRisk) || in_array($country->iso2, $eurozone)) {
            return 15;
        }
        
        return 45; // Average risk for others
    }

    private function calculateNewsRisk(Country $country): array
    {
        $cacheKey = "news_sentiment_{$country->iso2}";
        return Cache::remember($cacheKey, 7200, function () use ($country) {
            try {
                // Fetch recent news for the country using GNews (mocking fetch to avoid API limit in test)
                // We'll use the API if needed, or fallback to standard phrases based on country
                
                // Let's perform a real GNews fetch if we have an API key, or use a mock logic
                $apiKey = config('services.gnews.key') ?? 'mock';
                
                if ($apiKey !== 'mock') {
                    $response = Http::timeout(5)->get('https://gnews.io/api/v4/search', [
                        'q' => "{$country->name} economy OR logistics",
                        'token' => $apiKey,
                        'lang' => 'en',
                        'max' => 5
                    ]);

                    if ($response->successful()) {
                        $articles = $response->json('articles') ?? [];
                        $texts = [];
                        foreach ($articles as $article) {
                            $texts[] = $article['title'] . ' ' . $article['description'];
                        }
                        
                        return $this->sentimentService->analyzeRiskSentiment($texts);
                    }
                }
            } catch (\Exception $e) {
                // Ignore
            }

            // Fallback mock text if API fails or no key
            $mockText = "Economy in {$country->name} shows stable growth and positive recovery.";
            
            // For certain countries, make it negative to show variation
            $crisis = ['RU', 'UA', 'SD', 'SY', 'YE'];
            if (in_array($country->iso2, $crisis)) {
                $mockText = "War and crisis lead to severe inflation and disruption in supply chain.";
            }

            return $this->sentimentService->analyzeRiskSentiment([$mockText]);
        });
    }
}
