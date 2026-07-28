<?php
namespace App\Http\Controllers;

use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ExternalApiService;
use App\Services\DataScienceService;

class WatchlistComparisonController extends Controller
{
    public function __construct(
        private ExternalApiService $apiService,
        private DataScienceService $dsService
    ) {}

    public function compare(Request $request)
    {
        $countryA = $request->query('country_a', 'Indonesia');
        $countryB = $request->query('country_b', 'United States');

        // Simulate real factors for demonstration, since we don't have all data in DB
        $dataA = $this->dsService->calculateTotalRisk([
            'weather' => rand(10, 80), 'inflation' => rand(10, 80), 'news' => rand(10, 80), 'currency' => rand(10, 80)
        ], $countryA);

        $dataB = $this->dsService->calculateTotalRisk([
            'weather' => rand(10, 80), 'inflation' => rand(10, 80), 'news' => rand(10, 80), 'currency' => rand(10, 80)
        ], $countryB);

        return response()->json([
            'status' => 'success',
            'data' => [
                'country_a' => [
                    'name' => $countryA,
                    'gdp_growth' => round(rand(10, 70)/10, 2),
                    'inflation' => $dataA['components']['inflation_contribution'],
                    'risk_score' => $dataA['overall_risk_score'],
                    'weather_desc' => 'Calculated Risk: ' . $dataA['components']['weather_contribution'],
                    'currency_rate' => $dataA['components']['currency_contribution'],
                    'formatted_risk' => $dataA['formatted_output']
                ],
                'country_b' => [
                    'name' => $countryB,
                    'gdp_growth' => round(rand(10, 70)/10, 2),
                    'inflation' => $dataB['components']['inflation_contribution'],
                    'risk_score' => $dataB['overall_risk_score'],
                    'weather_desc' => 'Calculated Risk: ' . $dataB['components']['weather_contribution'],
                    'currency_rate' => $dataB['components']['currency_contribution'],
                    'formatted_risk' => $dataB['formatted_output']
                ]
            ]
        ]);
    }

    public function index()
    {
        $watchlists = Watchlist::with('watchable')->where('user_id', Auth::id())->get();
        
        $formatted = $watchlists->map(function($item) {
            $trends = ['Up', 'Down', 'Stable'];
            return [
                'id' => $item->id,
                'entity_name' => $item->watchable->name ?? 'Unknown',
                'type' => class_basename($item->watchable_type),
                'current_risk_score' => rand(40, 90),
                'trend' => $trends[array_rand($trends)]
            ];
        });

        return response()->json(['status' => 'success', 'data' => $formatted]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'watchable_id' => 'required',
            'watchable_type' => 'required|string'
        ]);

        $watchableId = $request->watchable_id;

        // Resolve ISO2 string to Country integer ID
        if ($request->watchable_type === 'App\\Models\\Country' && !is_numeric($watchableId)) {
            $country = \App\Models\Country::where('iso2', $watchableId)->first();
            if ($country) {
                $watchableId = $country->id;
            } else {
                return response()->json(['status' => 'error', 'message' => 'Negara tidak ditemukan di database.'], 404);
            }
        }

        // Resolve Port name to Port integer ID
        if ($request->watchable_type === 'App\\Models\\Port' && !is_numeric($watchableId)) {
            $port = \App\Models\Port::where('name', $watchableId)->first();
            if ($port) {
                $watchableId = $port->id;
            } else {
                return response()->json(['status' => 'error', 'message' => 'Pelabuhan tidak ditemukan di database.'], 404);
            }
        }

        Watchlist::firstOrCreate([
            'user_id' => Auth::id(),
            'watchable_type' => $request->watchable_type,
            'watchable_id' => $watchableId,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Ditambahkan ke Favorit!']);
    }

    public function destroy($id)
    {
        Watchlist::where('id', $id)->where('user_id', Auth::id())->delete();
        return response()->json(['status' => 'success', 'message' => 'Dihapus dari Favorit.']);
    }
}
