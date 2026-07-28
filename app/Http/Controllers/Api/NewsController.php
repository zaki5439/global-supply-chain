<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NewsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class NewsController extends Controller
{
    public function __construct(private NewsService $newsService) {}

    /**
     * GET /api/news?country=Indonesia
     * 
     * Fetch real-time news for a specific country from GNews API
     */
    public function getNewsByCountry(Request $request): JsonResponse
    {
        try {
            $country = $request->query('country');
            $maxResults = min((int)$request->query('max', 10), 20);
            
            if (!$country) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Country parameter is required',
                    'code' => 'MISSING_PARAMETER'
                ], 400);
            }

            if (strlen($country) > 100) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Country name is too long',
                    'code' => 'INVALID_PARAMETER'
                ], 400);
            }

            $news = $this->newsService->getNewsByCountry($country, $maxResults);

            return response()->json([
                'status' => 'success',
                'data' => $news,
                'count' => count($news),
                'country' => $country,
                'timestamp' => now()->toIso8601String()
            ]);

        } catch (Exception $e) {
            Log::error("NewsController Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch news',
                'code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * GET /api/news/global
     * 
     * Fetch global supply chain news
     */
    public function getGlobalNews(Request $request): JsonResponse
    {
        try {
            $maxResults = min((int)$request->query('max', 15), 30);
            
            $news = $this->newsService->getGlobalNews($maxResults);

            return response()->json([
                'status' => 'success',
                'data' => $news,
                'count' => count($news),
                'type' => 'global',
                'timestamp' => now()->toIso8601String()
            ]);

        } catch (Exception $e) {
            Log::error("GlobalNewsController Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch global news',
                'code' => 'SERVER_ERROR'
            ], 500);
        }
    }
}
