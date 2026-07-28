<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExternalApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DataIntegrationController extends Controller
{
    public function __construct(
        private ExternalApiService $apiService
    ) {}

    /**
     * Contoh pemanggilan data gabungan (API 1-4 & 6)
     * GET /api/insights?country=Indonesia&iso3=IDN
     */
    public function getCountryInsights(Request $request): JsonResponse
    {
        $countryName = $request->query('country', 'Indonesia');
        $iso3 = $request->query('iso3', 'IDN');

        // Mendapatkan data dengan aman (jika API down, service mengembalikan array kosong / null)
        return response()->json([
            'status' => 'success',
            'data' => [
                'country_profile' => $this->apiService->getCountryDetails($countryName),
                'macroeconomics'  => $this->apiService->getWorldBankData($iso3),
                'exchange_rates'  => $this->apiService->getExchangeRates('USD'),
                'recent_news'     => $this->apiService->getNews($countryName),
                // Data cuaca membutuhkan koordinat yang didapat dari country_profile
            ]
        ]);
    }

    /**
     * 7. OpenStreetMap GeoJSON Endpoint (Untuk Leaflet.js)
     * GET /api/geojson/ports?iso3=IDN
     */
    public function getPortsGeoJson(Request $request): JsonResponse
    {
        $iso3 = $request->query('iso3');
        
        // Memanggil API 5 secara statis
        $ports = ExternalApiService::getWorldPorts($iso3);

        // Format data menjadi struktur standar GeoJSON
        $features = $ports->map(function ($port) {
            return [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [ (float) $port->longitude, (float) $port->latitude ] // Leaflet GeoJSON format: [lng, lat]
                ],
                'properties' => [
                    'id' => $port->id,
                    'name' => $port->name
                ]
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features
        ]);
    }
}
