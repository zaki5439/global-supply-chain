<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CurrencyController extends Controller
{
    /**
     * List of major currencies for conversion
     */
    private $supportedCurrencies = [
        'USD' => 'US Dollar',
        'EUR' => 'Euro',
        'GBP' => 'British Pound',
        'JPY' => 'Japanese Yen',
        'CNY' => 'Chinese Yuan',
        'INR' => 'Indian Rupee',
        'SGD' => 'Singapore Dollar',
        'IDR' => 'Indonesian Rupiah',
        'MYR' => 'Malaysian Ringgit',
        'PHP' => 'Philippine Peso',
        'THB' => 'Thai Baht',
        'VND' => 'Vietnamese Dong',
        'KRW' => 'South Korean Won',
        'AUD' => 'Australian Dollar',
        'NZD' => 'New Zealand Dollar',
        'CAD' => 'Canadian Dollar',
        'CHF' => 'Swiss Franc',
        'HKD' => 'Hong Kong Dollar',
        'AED' => 'UAE Dirham',
        'SAR' => 'Saudi Riyal',
        'RUB' => 'Russian Ruble',
        'BRL' => 'Brazilian Real',
        'MXN' => 'Mexican Peso',
        'ZAR' => 'South African Rand',
    ];

    /**
     * Get list of supported currencies
     * GET /api/currencies
     */
    public function index(): JsonResponse
    {
        $currencies = collect($this->supportedCurrencies)->map(function ($name, $code) {
            return [
                'code' => $code,
                'name' => $name
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => $currencies,
            'count' => $currencies->count()
        ]);
    }

    /**
     * Get exchange rates for a base currency
     * GET /api/exchange-rates?base=USD&targets=EUR,GBP,JPY
     */
    public function getExchangeRates(Request $request): JsonResponse
    {
        $base = strtoupper($request->query('base', 'USD'));
        $targets = explode(',', strtoupper($request->query('targets', 'EUR,GBP,JPY,CNY,INR')));

        try {
            $cacheKey = "exchange_rates_{$base}_" . md5(implode('_', $targets));
            
            // Try to get from cache (24 hour TTL)
            $cached = Cache::get($cacheKey);
            if ($cached) {
                Log::info("Exchange rates cache hit for: {$base}");
                return response()->json([
                    'status' => 'success',
                    'base' => $base,
                    'data' => $cached,
                    'cached' => true,
                    'timestamp' => now()->toIso8601String()
                ]);
            }

            // Fetch from external API
            Log::info("Fetching exchange rates from ExchangeRate API for: {$base}");
            
            $rates = $this->fetchExchangeRates($base, $targets);

            if ($rates) {
                // Cache the result for 24 hours
                Cache::put($cacheKey, $rates, now()->addHours(24));

                return response()->json([
                    'status' => 'success',
                    'base' => $base,
                    'data' => $rates,
                    'cached' => false,
                    'timestamp' => now()->toIso8601String()
                ]);
            } else {
                // Return fallback data
                $fallbackRates = $this->getFallbackRates($base, $targets);
                return response()->json([
                    'status' => 'success',
                    'base' => $base,
                    'data' => $fallbackRates,
                    'cached' => false,
                    'demo' => true,
                    'timestamp' => now()->toIso8601String()
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Currency exchange error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch exchange rates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert amount from one currency to another
     * GET /api/convert?amount=100&from=USD&to=EUR
     */
    public function convert(Request $request): JsonResponse
    {
        $amount = (float) $request->query('amount', 1);
        $from = strtoupper($request->query('from', 'USD'));
        $to = strtoupper($request->query('to', 'EUR'));

        if ($amount <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Amount must be greater than 0'
            ], 400);
        }

        try {
            $rates = $this->fetchExchangeRates($from, [$to]);

            if ($rates && isset($rates[$to])) {
                $rate = $rates[$to];
                $result = $amount * $rate;

                return response()->json([
                    'status' => 'success',
                    'from' => [
                        'currency' => $from,
                        'name' => $this->supportedCurrencies[$from],
                        'amount' => $amount
                    ],
                    'to' => [
                        'currency' => $to,
                        'name' => $this->supportedCurrencies[$to],
                        'amount' => round($result, 2)
                    ],
                    'rate' => round($rate, 6),
                    'timestamp' => now()->toIso8601String()
                ]);
            } else {
                // Fallback rates
                $fallbackRate = $this->getFallbackRate($from, $to);
                $result = $amount * $fallbackRate;

                return response()->json([
                    'status' => 'success',
                    'from' => [
                        'currency' => $from,
                        'name' => $this->supportedCurrencies[$from],
                        'amount' => $amount
                    ],
                    'to' => [
                        'currency' => $to,
                        'name' => $this->supportedCurrencies[$to],
                        'amount' => round($result, 2)
                    ],
                    'rate' => round($fallbackRate, 6),
                    'demo' => true,
                    'timestamp' => now()->toIso8601String()
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Currency conversion error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to convert currency'
            ], 500);
        }
    }

    /**
     * Fetch exchange rates from external API
     */
    private function fetchExchangeRates(string $base, array $targets): ?array
    {
        try {
            // Try ExchangeRate-API.com (free tier available)
            $response = Http::timeout(5)->get("https://api.exchangerate-api.com/v4/latest/{$base}");

            if ($response->successful()) {
                $data = $response->json();
                $rates = [];

                foreach ($targets as $target) {
                    if (isset($data['rates'][$target])) {
                        $rates[$target] = $data['rates'][$target];
                    }
                }

                return !empty($rates) ? $rates : null;
            }
        } catch (\Exception $e) {
            Log::warning("Exchange rate API call failed: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Get fallback exchange rates for demo/testing
     */
    private function getFallbackRates(string $base, array $targets): array
    {
        $rates = $this->getFixedRates();

        if (!isset($rates[$base])) {
            return [];
        }

        $result = [];
        $baseRate = $rates[$base];

        foreach ($targets as $target) {
            if (isset($rates[$target])) {
                $result[$target] = round($rates[$target] / $baseRate, 6);
            }
        }

        return $result;
    }

    /**
     * Get fallback rate for single currency pair
     */
    private function getFallbackRate(string $from, string $to): float
    {
        $rates = $this->getFixedRates();

        if (!isset($rates[$from]) || !isset($rates[$to])) {
            return 1.0;
        }

        return round($rates[$to] / $rates[$from], 6);
    }

    /**
     * Fixed exchange rates (relative to USD = 1.0)
     */
    private function getFixedRates(): array
    {
        return [
            'USD' => 1.0,
            'EUR' => 0.92,
            'GBP' => 0.79,
            'JPY' => 149.50,
            'CNY' => 7.24,
            'INR' => 83.12,
            'SGD' => 1.35,
            'IDR' => 15750.00,
            'MYR' => 4.75,
            'PHP' => 56.50,
            'THB' => 36.25,
            'VND' => 24500.00,
            'KRW' => 1315.00,
            'AUD' => 1.53,
            'NZD' => 1.65,
            'CAD' => 1.36,
            'CHF' => 0.88,
            'HKD' => 7.81,
            'AED' => 3.67,
            'SAR' => 3.75,
            'RUB' => 95.50,
            'BRL' => 4.97,
            'MXN' => 17.05,
            'ZAR' => 18.50,
        ];
    }
}
