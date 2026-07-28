<?php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DataScienceService
{
    /**
     * Analyze sentiment of text using Lexicon-Based approach
     * Returns positive count, negative count, and sentiment label
     */
    public function analyzeSentiment(string $text): array
    {
        $cleanText = strtolower(preg_replace('/[^a-zA-Z\s]/', '', $text));
        $words = array_values(array_filter(explode(' ', $cleanText)));
        $totalWords = count($words);

        if ($totalWords === 0) {
            return [
                'positive_score' => 0,
                'negative_score' => 0,
                'sentiment' => 'Neutral',
                'breakdown' => ['positive' => 0, 'neutral' => 100, 'negative' => 0]
            ];
        }

        // Load lexicon dictionaries from cache for performance
        $positiveDict = Cache::remember('lexicon_positive', 3600, fn() => DB::table('positive_words')->pluck('weight', 'word')->toArray());
        $negativeDict = Cache::remember('lexicon_negative', 3600, fn() => DB::table('negative_words')->pluck('weight', 'word')->toArray());

        $positiveCount = 0;
        $negativeCount = 0;

        foreach ($words as $word) {
            if (isset($positiveDict[$word])) $positiveCount++;
            if (isset($negativeDict[$word])) $negativeCount++;
        }

        // Calculate sentiment label based on word counts
        $sentiment = 'Neutral';
        if ($positiveCount > $negativeCount + 1) {
            $sentiment = 'Positive';
        } elseif ($negativeCount > $positiveCount + 1) {
            $sentiment = 'Negative';
        }

        // Calculate percentage breakdown
        $totalSentimentWords = $positiveCount + $negativeCount;
        if ($totalSentimentWords === 0) {
            $breakdown = ['positive' => 0, 'neutral' => 100, 'negative' => 0];
        } else {
            $positivePercent = round(($positiveCount / $totalSentimentWords) * 100);
            $negativePercent = round(($negativeCount / $totalSentimentWords) * 100);
            $neutralPercent = 100 - $positivePercent - $negativePercent;
            $breakdown = [
                'positive' => $positivePercent,
                'neutral' => max(0, $neutralPercent),
                'negative' => $negativePercent
            ];
        }

        return [
            'positive_score' => $positiveCount,
            'negative_score' => $negativeCount,
            'sentiment' => $sentiment,
            'breakdown' => $breakdown
        ];
    }

    /**
     * Legacy method for backward compatibility
     */
    public function analyzeSentimentLegacy(string $text): array
    {
        $cleanText = strtolower(preg_replace('/[^a-zA-Z\s]/', '', $text));
        $words = array_values(array_filter(explode(' ', $cleanText)));
        $totalWords = count($words);

        if ($totalWords === 0) {
            return ['positive_percent' => 0, 'neutral_percent' => 100, 'negative_percent' => 0, 'label' => 'neutral'];
        }

        $positiveDict = Cache::remember('lexicon_positive', 3600, fn() => DB::table('positive_words')->pluck('weight', 'word')->toArray());
        $negativeDict = Cache::remember('lexicon_negative', 3600, fn() => DB::table('negative_words')->pluck('weight', 'word')->toArray());

        $posScore = 0;
        $negScore = 0;

        foreach ($words as $word) {
            if (isset($positiveDict[$word])) $posScore += $positiveDict[$word];
            if (isset($negativeDict[$word])) $negScore += $negativeDict[$word];
        }

        $posPercent = round(($posScore / $totalWords) * 100, 2);
        $negPercent = round(($negScore / $totalWords) * 100, 2);
        $neuPercent = max(0, 100 - ($posPercent + $negPercent)); 

        $label = 'neutral';
        if ($posPercent > $negPercent + 5) $label = 'positive';
        elseif ($negPercent > $posPercent + 5) $label = 'negative';

        return [
            'positive_percent' => $posPercent,
            'neutral_percent' => $neuPercent,
            'negative_percent' => $negPercent,
            'label' => $label,
            'score_normalized' => round(($posPercent - $negPercent) / 100, 2)
        ];
    }

    public function calculateTotalRisk(array $factors, string $countryName = ''): array
    {
        $weatherRisk = $factors['weather'] ?? 50; 
        $inflationRisk = $factors['inflation'] ?? 50;
        $newsRisk = $factors['news'] ?? 50; 
        $currencyRisk = $factors['currency'] ?? 50;

        $totalRisk = round(($weatherRisk * 0.30) + ($inflationRisk * 0.20) + ($newsRisk * 0.40) + ($currencyRisk * 0.10));
        $riskLevel = $this->getRiskLevel($totalRisk);

        return [
            'overall_risk_score' => $totalRisk,
            'risk_level' => $riskLevel,
            'formatted_output' => $countryName ? "{$countryName} : {$totalRisk} ({$riskLevel})" : "",
            'components' => [
                'weather_contribution' => round($weatherRisk * 0.30, 2),
                'inflation_contribution' => round($inflationRisk * 0.20, 2),
                'news_contribution' => round($newsRisk * 0.40, 2),
                'currency_contribution' => round($currencyRisk * 0.10, 2),
            ]
        ];
    }

    private function getRiskLevel(float $score): string
    {
        if ($score >= 61) return 'High Risk';
        if ($score >= 31) return 'Medium Risk';
        return 'Low Risk';
    }
}
