<?php
/**
 * Real-time News Testing Script
 * Verifies that GNews API is fetching live news data
 */

require 'vendor/autoload.php';

use Illuminate\Support\Facades\Http;

echo "=== REAL-TIME NEWS API TEST ===\n\n";

// Test 1: Direct API Call
echo "[1] Testing GNews API directly...\n";
try {
    $response = Http::timeout(10)->get('https://gnews.io/api/v4/search', [
        'q' => 'supply chain logistics Indonesia',
        'token' => env('GNEWS_API_KEY', 'demo'),
        'lang' => 'en',
        'max' => 5,
        'sortby' => 'publishedAt'
    ]);

    if ($response->successful()) {
        $data = $response->json();
        echo "✓ API Response Successful (Status: 200)\n";
        echo "  Total Articles Available: " . ($data['totalArticles'] ?? 'N/A') . "\n";
        echo "  Articles Retrieved: " . count($data['articles'] ?? []) . "\n\n";
        
        if (isset($data['articles']) && count($data['articles']) > 0) {
            echo "  Latest 3 Articles:\n";
            foreach (array_slice($data['articles'], 0, 3) as $i => $article) {
                $date = \Carbon\Carbon::parse($article['publishedAt'])->diffForHumans();
                echo "  " . ($i+1) . ". " . substr($article['title'], 0, 70) . "...\n";
                echo "     Published: $date\n";
                echo "     Source: " . $article['source']['name'] . "\n";
            }
        }
    } else {
        echo "✗ API Response Failed (Status: " . $response->status() . ")\n";
        echo "  Message: " . $response->body() . "\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n[2] Testing NewsService integration...\n";
try {
    $newsService = app(\App\Services\NewsService::class);
    $news = $newsService->getNewsByCountry('Indonesia', 5);
    
    echo "✓ NewsService executed successfully\n";
    echo "  Articles fetched: " . count($news) . "\n\n";
    
    if (count($news) > 0) {
        echo "  Sample Articles:\n";
        foreach (array_slice($news, 0, 2) as $i => $article) {
            echo "  " . ($i+1) . ". " . substr($article['title'], 0, 70) . "...\n";
            echo "     Sentiment: " . $article['sentiment_label'] . "\n";
            echo "     Published: " . $article['published_at_human'] . "\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n[3] Testing API Endpoint...\n";
try {
    $response = Http::get('http://127.0.0.1:8000/api/news', [
        'country' => 'Indonesia',
        'max' => 5
    ]);
    
    if ($response->successful()) {
        $data = $response->json();
        echo "✓ API Endpoint Working\n";
        echo "  Status: " . $data['status'] . "\n";
        echo "  Articles: " . $data['count'] . "\n";
        echo "  Timestamp: " . $data['timestamp'] . "\n\n";
        
        if (isset($data['data']) && count($data['data']) > 0) {
            echo "  Real-time Articles:\n";
            foreach (array_slice($data['data'], 0, 2) as $i => $article) {
                echo "  " . ($i+1) . ". " . substr($article['title'], 0, 70) . "...\n";
                echo "     Sentiment: " . $article['sentiment_label'] . "\n";
                echo "     URL: " . $article['url'] . "\n";
            }
        }
    } else {
        echo "✗ API Response failed\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
echo "\nServer Status: http://127.0.0.1:8000\n";
echo "News Endpoint: http://127.0.0.1:8000/api/news?country=Indonesia\n";
echo "Global Endpoint: http://127.0.0.1:8000/api/news/global\n";
