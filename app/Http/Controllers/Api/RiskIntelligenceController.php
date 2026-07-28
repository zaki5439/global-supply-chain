<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExternalApiService;
use App\Services\DataScienceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Services\RiskScoringService;

class RiskIntelligenceController extends Controller
{
    public function __construct(
        private ExternalApiService $apiService,
        private DataScienceService $dsService,
        private RiskScoringService $riskService
    ) {}

    public function getRiskScore(Request $request, $iso2): JsonResponse
    {
        $country = Country::where('iso2', $iso2)->first();
        if (!$country) {
            return response()->json(['error' => 'Country not found'], 404);
        }

        $riskData = $this->riskService->calculateCountryRisk($country);

        return response()->json([
            'status' => 'success',
            'data' => $riskData
        ]);
    }

    public function getCountries(Request $request): JsonResponse
    {
        $country = $request->query('name', 'Indonesia');
        $code = $request->query('code', 'IDN');

        $restData = $this->apiService->getCountryInfo($country);
        $wbData = $this->apiService->getWorldBankIndicator($code);

        return response()->json([
            'status' => 'success',
            'data' => [
                'profile' => $restData,
                'macroeconomics' => $wbData ?? 'Data not available at the moment'
            ]
        ]);
    }

    public function getNews(Request $request): JsonResponse
    {
        $keyword = $request->query('keyword', 'global supply chain');
        $newsData = $this->apiService->getNews($keyword);

        if (!$newsData || empty($newsData['articles'])) {
            return response()->json(['status' => 'error', 'message' => 'No news found'], 404);
        }

        $analyzedArticles = array_map(function ($article) {
            $content = $article['title'] . ' ' . $article['description'];
            $article['sentiment_analysis'] = $this->dsService->analyzeSentiment($content);
            return $article;
        }, $newsData['articles']);

        return response()->json([
            'status' => 'success',
            'articles' => $analyzedArticles
        ]);
    }

    public function getCurrency(Request $request): JsonResponse
    {
        $base = $request->query('base', 'USD');
        return response()->json([
            'status' => 'success',
            'base_currency' => $base,
            'rates' => $this->apiService->getExchangeRate($base)
        ]);
    }

    public function getRisk(Request $request): JsonResponse
    {
        $countryName = $request->query('country', 'Indonesia');
        $code = $request->query('code', 'IDN');

        // 1. Dapatkan Koordinat Negara (Untuk Cuaca)
        $countryInfo = $this->apiService->getCountryInfo($countryName);
        $lat = $countryInfo['latlng'][0] ?? -0.789;
        $lng = $countryInfo['latlng'][1] ?? 113.92;

        // 2. Kalkulasi Indikator Risiko
        
        // A. Cuaca (Bobot 30%) - Konversi dari Open-Meteo
        $weatherData = $this->apiService->getWeather($lat, $lng);
        $weatherRisk = 10; // Default Aman
        if ($weatherData) {
            $wind = $weatherData['windspeed'] ?? 0;
            // Asumsi kecepatan angin ekstrim > 100km/h = Skor 100
            $weatherRisk = min(100, ($wind / 100) * 100);
        }

        // B. Ekonomi & Inflasi (Bobot 20%) - Konversi dari World Bank
        $inflationData = $this->apiService->getWorldBankIndicator($code);
        $inflationRisk = 20; // Default
        if ($inflationData && isset($inflationData['value'])) {
            $inflation = $inflationData['value'];
            // Asumsi inflasi > 10% = Skor 100 (Sangat berisiko)
            $inflationRisk = min(100, max(0, ($inflation / 10) * 100));
        }

        // C. Geopolitik & Berita (Bobot 40%) - Konversi dari Lexicon Analysis GNews
        $newsData = $this->apiService->getNews("economy logistics " . $countryName);
        $newsRisk = 30; // Default
        if ($newsData && !empty($newsData['articles'])) {
            $totalNeg = 0;
            foreach ($newsData['articles'] as $article) {
                $content = $article['title'] . ' ' . $article['description'];
                $sentiment = $this->dsService->analyzeSentiment($content);
                $totalNeg += $sentiment['negative_percent'];
            }
            // Rata-rata persentase sentimen negatif dari top 5 berita
            $newsRisk = min(100, ($totalNeg / count($newsData['articles'])));
        }

        // D. Fluktuasi Kurs / Logistik (Bobot 10%) - Konversi dari ExchangeRate API
        $currencyRisk = 40; // Default
        $exchangeData = $this->apiService->getExchangeRate('USD');
        if ($exchangeData) {
            // Sebagai demo (tanpa API berbayar untuk riwayat volatilitas), 
            // kita simulasikan fluktuasi kurs 20-80
            $currencyRisk = rand(20, 80); 
        }

        // Susun array faktor risiko
        $factors = [
            'weather' => $weatherRisk,
            'inflation' => $inflationRisk,
            'news' => $newsRisk,
            'currency' => $currencyRisk
        ];

        // 3. Kalkulasi Weighted Model via DataScienceService
        $riskData = $this->dsService->calculateTotalRisk($factors, $countryName);

        return response()->json([
            'status' => 'success',
            'country' => $countryName,
            'data' => $riskData
        ]);
    }
}
