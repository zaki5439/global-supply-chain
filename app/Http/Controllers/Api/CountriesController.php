<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CountriesController extends Controller
{
    /**
     * Get all countries sorted alphabetically (A-Z)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Try to get from cache first (5 minutes cache)
            $cacheKey = 'all_countries_list';
            $countries = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () {
                return DB::table('countries')
                    ->select('id', 'name', 'iso2', 'iso3', 'region')
                    ->orderBy('name', 'asc')
                    ->get();
            });

            // If no countries found, return empty array with appropriate message
            if ($countries->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No countries found. Please run seeder: php artisan db:seed --class=AllCountriesSeeder',
                    'data' => [],
                    'count' => 0
                ]);
            }

            // Format response - compatible with dashboard dropdown
            $data = $countries->map(function ($country) {
                // Get default coordinates for the country (simplified)
                $coordinates = $this->getCountryCoordinates($country->name);
                
                return [
                    'id' => $country->id,
                    'name' => $country->name,
                    'iso2' => $country->iso2,
                    'iso3' => $country->iso3,
                    'region' => $country->region,
                    'population' => $country->population ?? 0,
                    'gdp' => $country->gdp ?? 0,
                    'latitude' => $coordinates['lat'],
                    'longitude' => $coordinates['lon'],
                    // For dropdown display value: "Name,ISO3,lat,lon"
                    'display_value' => "{$country->name},{$country->iso3},{$coordinates['lat']},{$coordinates['lon']}"
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $data,
                'count' => $data->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve countries',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get default coordinates for country
     */
    private function getCountryCoordinates(string $countryName): array
    {
        $coordinates = [
            'Indonesia' => ['lat' => -0.7893, 'lon' => 113.9213],
            'United States' => ['lat' => 37.0902, 'lon' => -95.7129],
            'Germany' => ['lat' => 51.1657, 'lon' => 10.4515],
            'China' => ['lat' => 35.8617, 'lon' => 104.1954],
            'Vietnam' => ['lat' => 14.0583, 'lon' => 108.2772],
            'Singapore' => ['lat' => 1.3521, 'lon' => 103.8198],
            'Japan' => ['lat' => 36.2048, 'lon' => 138.2529],
            'India' => ['lat' => 20.5937, 'lon' => 78.9629],
            'Australia' => ['lat' => -25.2744, 'lon' => 133.7751],
            'Malaysia' => ['lat' => 4.2105, 'lon' => 101.9758],
            'United Kingdom' => ['lat' => 55.3781, 'lon' => -3.436],
            'France' => ['lat' => 46.2276, 'lon' => 2.2137],
            'Brazil' => ['lat' => -14.235, 'lon' => -51.9253],
            'Mexico' => ['lat' => 23.6345, 'lon' => -102.5528],
            'Thailand' => ['lat' => 15.870032, 'lon' => 100.992541],
            'Philippines' => ['lat' => 12.8797, 'lon' => 121.7740],
            'South Korea' => ['lat' => 35.9078, 'lon' => 127.7669],
            'Canada' => ['lat' => 56.1304, 'lon' => -106.3468],
            'Spain' => ['lat' => 40.463667, 'lon' => -3.74922],
            'Italy' => ['lat' => 41.871940, 'lon' => 12.56738],
            'Russia' => ['lat' => 61.52401, 'lon' => 105.31875],
            'Saudi Arabia' => ['lat' => 23.88329, 'lon' => 45.07923],
            'United Arab Emirates' => ['lat' => 23.42411, 'lon' => 53.84778],
            'Egypt' => ['lat' => 26.82261, 'lon' => 30.80289],
            'Nigeria' => ['lat' => 9.08197, 'lon' => 8.67539],
            'South Africa' => ['lat' => -30.55973, 'lon' => 22.93742],
            'New Zealand' => ['lat' => -40.900557, 'lon' => 174.88597],
            'Pakistan' => ['lat' => 30.37453, 'lon' => 69.34511],
            'Bangladesh' => ['lat' => 23.68041, 'lon' => 90.35635],
            'Turkey' => ['lat' => 38.96375, 'lon' => 35.24328],
            'Greece' => ['lat' => 39.07469, 'lon' => 21.82412],
            'Netherlands' => ['lat' => 52.13263, 'lon' => 5.29163],
            'Switzerland' => ['lat' => 46.81828, 'lon' => 8.22753],
            'Sweden' => ['lat' => 60.12816, 'lon' => 18.64349],
            'Norway' => ['lat' => 60.47202, 'lon' => 8.46972],
            'Poland' => ['lat' => 51.91938, 'lon' => 19.14514],
            'Belgium' => ['lat' => 50.50353, 'lon' => 4.47941],
            'Austria' => ['lat' => 47.51629, 'lon' => 14.5502],
            'Portugal' => ['lat' => 39.39999, 'lon' => -8.22436],
            'Czech Republic' => ['lat' => 49.81749, 'lon' => 15.47298],
            'Ireland' => ['lat' => 53.41291, 'lon' => -8.24389],
            'Denmark' => ['lat' => 56.26392, 'lon' => 9.50195],
            'Hungary' => ['lat' => 47.16264, 'lon' => 19.5033],
            'Romania' => ['lat' => 45.94316, 'lon' => 24.96676],
            'Hong Kong' => ['lat' => 22.3193, 'lon' => 114.1694],
            'Taiwan' => ['lat' => 23.6978, 'lon' => 120.9605],
            'Israel' => ['lat' => 31.0461, 'lon' => 34.8516],
            'Kenya' => ['lat' => -0.0236, 'lon' => 37.9062],
            'Morocco' => ['lat' => 31.7917, 'lon' => -7.0926],
            'Ethiopia' => ['lat' => 9.1450, 'lon' => 40.4897],
            'Argentina' => ['lat' => -38.4161, 'lon' => -63.6167],
            'Chile' => ['lat' => -35.6751, 'lon' => -71.5430],
            'Peru' => ['lat' => -9.1900, 'lon' => -75.0152],
            'Colombia' => ['lat' => 4.5709, 'lon' => -74.2973],
            'Venezuela' => ['lat' => 6.4238, 'lon' => -66.5897],
            'Myanmar' => ['lat' => 21.9162, 'lon' => 95.9560],
            'Cambodia' => ['lat' => 12.5657, 'lon' => 104.9910],
            'Laos' => ['lat' => 19.8523, 'lon' => 102.4955],
            'Ukraine' => ['lat' => 48.3794, 'lon' => 31.1656],
            'Finland' => ['lat' => 61.9241, 'lon' => 25.7482],
        ];

        return $coordinates[$countryName] ?? ['lat' => 0, 'lon' => 0];
    }

    /**
     * Get countries grouped by region
     * 
     * @return JsonResponse
     */
    public function groupByRegion(): JsonResponse
    {
        try {
            $countries = Country::all()
                ->groupBy('region')
                ->map(function ($group) {
                    return $group->sortBy('name')->values()->map(function ($country) {
                        return [
                            'id' => $country->id,
                            'name' => $country->name,
                            'iso3' => $country->iso3_code,
                            'flag' => $country->flag_emoji,
                        ];
                    });
                });

            return response()->json([
                'status' => 'success',
                'data' => $countries
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve countries by region',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single country by ID or ISO code
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $identifier = $request->query('id') ?? $request->query('iso');
            
            if (!$identifier) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Country ID or ISO code is required'
                ], 400);
            }

            $country = Country::where('id', $identifier)
                ->orWhere('iso2_code', $identifier)
                ->orWhere('iso3_code', $identifier)
                ->orWhere('name', $identifier)
                ->first();

            if (!$country) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Country not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $country->id,
                    'name' => $country->name,
                    'official_name' => $country->official_name,
                    'iso2' => $country->iso2_code,
                    'iso3' => $country->iso3_code,
                    'region' => $country->region,
                    'sub_region' => $country->sub_region,
                    'currencies' => $country->currencies,
                    'languages' => $country->languages,
                    'coordinates' => $country->coordinates,
                    'flag' => $country->flag_emoji,
                    'capital' => $country->capital,
                    'population' => $country->population,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve country',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search countries by name or region
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->query('q');
            $region = $request->query('region');

            if (!$query && !$region) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Search query or region filter is required'
                ], 400);
            }

            $countries = Country::query();

            if ($query) {
                $countries->where('name', 'like', "%{$query}%")
                    ->orWhere('official_name', 'like', "%{$query}%")
                    ->orWhere('iso2_code', 'like', "%{$query}%")
                    ->orWhere('iso3_code', 'like', "%{$query}%");
            }

            if ($region) {
                $countries->where('region', $region);
            }

            $results = $countries->sortBy('name')->select('id', 'name', 'iso3_code', 'region', 'flag_emoji')->get();

            return response()->json([
                'status' => 'success',
                'data' => $results,
                'count' => $results->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics about countries database
     * 
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        try {
            $totalCountries = Country::count();
            $totalRegions = Country::distinct('region')->count();
            $regions = Country::distinct('region')->pluck('region');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_countries' => $totalCountries,
                    'total_regions' => $totalRegions,
                    'regions' => $regions,
                    'last_updated' => Country::latest('updated_at')->first()?->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
