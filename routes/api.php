<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PortController;
use App\Http\Controllers\Api\RiskIntelligenceController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\WatchlistComparisonController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\CountryDataController;
use App\Http\Controllers\Api\CountriesController;

// Countries API endpoints
Route::get('/countries', [CountriesController::class, 'index']); // Get all countries
Route::get('/countries/stats', [CountriesController::class, 'stats']); // Get countries statistics
Route::get('/countries/search', [CountriesController::class, 'search']); // Search countries
Route::get('/countries/by-region', [CountriesController::class, 'groupByRegion']); // Group by region
Route::get('/countries/{id}', [CountriesController::class, 'show']); // Get single country

// Currency API endpoints
Route::get('/currencies', [CurrencyController::class, 'index']); // Get all supported currencies
Route::get('/exchange-rates', [CurrencyController::class, 'getExchangeRates']); // Get exchange rates
Route::get('/convert', [CurrencyController::class, 'convert']); // Convert currency

// Legacy endpoints
Route::get('/country-info', [RiskIntelligenceController::class, 'getCountries']);
Route::get('/news', [NewsController::class, 'getNewsByCountry']);
Route::get('/news/global', [NewsController::class, 'getGlobalNews']);
Route::get('/currency', [RiskIntelligenceController::class, 'getCurrency']);
Route::get('/risk', [RiskIntelligenceController::class, 'getRisk']);
Route::get('/risk/score/{iso2}', [RiskIntelligenceController::class, 'getRiskScore']);
Route::get('/ports', [PortController::class, 'searchApi']);
Route::get('/country-data', [CountryDataController::class, 'getCountryData']);

Route::get('/compare', [WatchlistComparisonController::class, 'compare']);
Route::get('/watchlists', [WatchlistComparisonController::class, 'index']);
Route::post('/watchlists', [WatchlistComparisonController::class, 'store']);
Route::delete('/watchlists/{id}', [WatchlistComparisonController::class, 'destroy']);

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::apiResource('admin/ports', PortController::class);
});
