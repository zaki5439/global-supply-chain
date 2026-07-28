<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class NewsService
{
    protected $externalApiService;
    protected $apiKey;
    protected $cacheDuration = 6; // hours

    public function __construct()
    {
        $this->externalApiService = new ExternalApiService();
        $this->apiKey = env('GNEWS_API_KEY', 'demo');
    }

    /**
     * Get real-time news for a country
     */
    public function getNewsByCountry(string $countryName, int $maxResults = 10): array
    {
        // Clean country name (frontend sometimes sends "Indonesia,IDN,-0.7893,113.9213")
        $cleanCountryName = explode(',', $countryName)[0];
        
        $keyword = "supply chain logistics economy {$cleanCountryName}";
        $queryHash = hash('sha256', $keyword);

        try {
            // If using demo key, skip GNews API and use Google News RSS
            if ($this->apiKey === 'demo' || $this->apiKey === 'YOUR_GNEWS_API_KEY_HERE') {
                Log::info("Demo mode: Fetching real-time RSS news for: {$cleanCountryName}");
                return $this->fetchRssNews("economy logistics supply chain {$cleanCountryName}", $cleanCountryName);
            }

            // Check cache first (only for real API keys)
            $cached = DB::table('news_cache')
                ->where('query_hash', $queryHash)
                ->where('expires_at', '>', now())
                ->first();

            if ($cached) {
                Log::info("News cache hit for: {$countryName}");
                $payload = json_decode($cached->response_payload, true);
                return $this->formatNews($payload['articles'] ?? [], $countryName);
            }

            // Fetch from GNews API
            Log::info("Fetching real-time news from GNews for: {$countryName}");
            $response = Http::timeout(5)->get('https://gnews.io/api/v4/search', [
                'q' => $keyword,
                'token' => $this->apiKey,
                'lang' => 'en',
                'max' => min($maxResults, 10),
                'sortby' => 'publishedAt'
            ]);

            if ($response->successful()) {
                $payload = $response->json();
                
                // Only cache if we got valid data
                if (!empty($payload['articles'])) {
                    DB::table('news_cache')->updateOrInsert(
                        ['query_hash' => $queryHash],
                        [
                            'source_api' => 'gnews',
                            'request_params' => json_encode(['q' => $keyword, 'max' => $maxResults]),
                            'response_payload' => json_encode($payload),
                            'fetched_at' => now(),
                            'expires_at' => now()->addHours($this->cacheDuration),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                }

                return $this->formatNews($payload['articles'] ?? [], $cleanCountryName);
            } else {
                Log::warning("GNews API failed with status: {$response->status()}");
                return $this->fetchRssNews("economy logistics {$cleanCountryName}", $cleanCountryName);
            }
        } catch (\Exception $e) {
            Log::error("NewsService Error: " . $e->getMessage());
            return $this->fetchRssNews("economy logistics {$cleanCountryName}", $cleanCountryName);
        }
    }

    /**
     * Get global supply chain news
     */
    public function getGlobalNews(int $maxResults = 15): array
    {
        $keyword = "supply chain logistics shipping ports global";
        $queryHash = hash('sha256', $keyword);

        try {
            // If using demo key, skip API and use RSS
            if ($this->apiKey === 'demo' || $this->apiKey === 'YOUR_GNEWS_API_KEY_HERE') {
                Log::info("Demo mode: Fetching real-time global RSS news");
                return $this->fetchRssNews("global supply chain logistics shipping");
            }

            // Check cache
            $cached = DB::table('news_cache')
                ->where('query_hash', $queryHash)
                ->where('expires_at', '>', now())
                ->first();

            if ($cached) {
                Log::info("Global news cache hit");
                $payload = json_decode($cached->response_payload, true);
                return $this->formatNews($payload['articles'] ?? [], 'Global');
            }

            // Fetch from API
            Log::info("Fetching real-time global news from GNews");
            $response = Http::timeout(5)->get('https://gnews.io/api/v4/search', [
                'q' => $keyword,
                'token' => $this->apiKey,
                'lang' => 'en',
                'max' => min($maxResults, 10),
                'sortby' => 'publishedAt'
            ]);

            if ($response->successful()) {
                $payload = $response->json();

                // Cache the response
                if (!empty($payload['articles'])) {
                    DB::table('news_cache')->updateOrInsert(
                        ['query_hash' => $queryHash],
                        [
                            'source_api' => 'gnews',
                            'request_params' => json_encode(['q' => $keyword, 'max' => $maxResults]),
                            'response_payload' => json_encode($payload),
                            'fetched_at' => now(),
                            'expires_at' => now()->addHours($this->cacheDuration),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                }

                return $this->formatNews($payload['articles'] ?? [], 'Global');
            }

            return $this->fetchRssNews("global supply chain logistics shipping");
        } catch (\Exception $e) {
            Log::error("Global NewsService Error: " . $e->getMessage());
            return $this->fetchRssNews("global supply chain logistics shipping");
        }
    }

    /**
     * Fetch Real-Time News via Google News RSS
     */
    private function fetchRssNews(string $query, string $context = 'Global'): array
    {
        try {
            $url = "https://news.google.com/rss/search?q=" . urlencode($query) . "&hl=en-US&gl=US&ceid=US:en";
            $response = Http::timeout(8)->get($url);
            
            if ($response->successful()) {
                $xml = simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA);
                
                if ($xml && isset($xml->channel->item)) {
                    $articles = [];
                    $idx = 0;
                    foreach ($xml->channel->item as $item) {
                        if ($idx >= 10) break;
                        
                        $title = (string) $item->title;
                        // Google News often appends " - Publisher Name" to the title
                        $parts = explode(' - ', $title);
                        $source = count($parts) > 1 ? array_pop($parts) : 'Google News';
                        $cleanTitle = implode(' - ', $parts);
                        
                        $articles[] = [
                            'title' => $cleanTitle,
                            'description' => strip_tags((string) $item->description),
                            'url' => (string) $item->link,
                            'image' => "https://picsum.photos/400/250?random=" . crc32($title),
                            'source' => ['name' => $source],
                            'publishedAt' => (string) $item->pubDate
                        ];
                        $idx++;
                    }
                    return $this->formatNews($articles, $context);
                }
            }
        } catch (\Exception $e) {
            Log::error("RSS Fetch Error: " . $e->getMessage());
        }
        
        return []; // Return empty if RSS fails
    }

    /**
     * Format news articles with sentiment score
     */
    private function formatNews(array $articles, string $context = ''): array
    {
        return array_map(function ($article) use ($context) {
            $sentiment = $this->analyzeSentiment($article['description'] ?? '');

            return [
                'id' => md5($article['url'] ?? ''),
                'title' => $article['title'] ?? 'Unknown',
                'description' => $article['description'] ?? '',
                'url' => $article['url'] ?? '#',
                'image' => $article['image'] ?? '',
                'source' => $article['source']['name'] ?? 'Unknown',
                'published_at' => $article['publishedAt'] ?? now()->toIso8601String(),
                'published_at_human' => $this->getTimeAgo($article['publishedAt'] ?? now()->toIso8601String()),
                'sentiment_score' => $sentiment['score'],
                'sentiment_label' => $sentiment['label'],
                'sentiment_icon' => $sentiment['icon'],
                'context' => $context
            ];
        }, $articles);
    }

    /**
     * Analyze sentiment of text
     */
    private function analyzeSentiment(string $text): array
    {
        $positive = ['growth', 'increase', 'surge', 'boost', 'recovery', 'strong', 'success', 'gain', 'rise', 'expand'];
        $negative = ['decline', 'fall', 'risk', 'crisis', 'fail', 'loss', 'collapse', 'warning', 'challenge', 'disruption', 'shortage'];

        $textLower = strtolower($text);
        $positiveCount = 0;
        $negativeCount = 0;

        foreach ($positive as $word) {
            $positiveCount += substr_count($textLower, $word);
        }

        foreach ($negative as $word) {
            $negativeCount += substr_count($textLower, $word);
        }

        $total = $positiveCount + $negativeCount;
        if ($total === 0) {
            return ['score' => 0, 'label' => 'neutral', 'icon' => 'dash-circle'];
        }

        $score = round((($positiveCount - $negativeCount) / $total) * 100);

        if ($score > 20) {
            $label = 'positive';
            $icon = 'emoji-smile';
        } elseif ($score < -20) {
            $label = 'negative';
            $icon = 'emoji-frown';
        } else {
            $label = 'neutral';
            $icon = 'dash-circle';
        }

        return ['score' => $score, 'label' => $label, 'icon' => $icon];
    }

    /**
     * Get human-readable time ago
     */
    private function getTimeAgo(string $publishedAt): string
    {
        try {
            $date = \Carbon\Carbon::parse($publishedAt);
            return $date->diffForHumans();
        } catch (\Exception $e) {
            return 'Recently';
        }
    }
}
