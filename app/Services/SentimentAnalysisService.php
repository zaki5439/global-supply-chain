<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SentimentAnalysisService
{
    /**
     * Analyze sentiment of an array of texts.
     * Returns a float score between 0 and 100, where 100 is highly negative risk,
     * and 0 is highly positive (low risk). Also returns a sentiment label.
     */
    public function analyzeRiskSentiment(array $texts): array
    {
        $positiveWords = Cache::remember('lexicon_positive', 3600, function () {
            return DB::table('positive_words')->pluck('weight', 'word')->toArray();
        });

        $negativeWords = Cache::remember('lexicon_negative', 3600, function () {
            return DB::table('negative_words')->pluck('weight', 'word')->toArray();
        });

        $posScore = 0;
        $negScore = 0;
        $totalWordsAnalyzed = 0;

        foreach ($texts as $text) {
            if (empty($text)) continue;
            
            // Normalize text: lowercase and remove punctuation
            $normalized = strtolower(preg_replace('/[^\w\s]/', '', $text));
            $words = explode(' ', $normalized);

            foreach ($words as $word) {
                if (empty($word)) continue;
                $totalWordsAnalyzed++;
                
                if (isset($positiveWords[$word])) {
                    $posScore += (float) $positiveWords[$word];
                } elseif (isset($negativeWords[$word])) {
                    $negScore += (float) $negativeWords[$word];
                }
            }
        }

        $totalScore = $posScore + $negScore;
        
        if ($totalScore == 0) {
            return [
                'sentiment' => 'Neutral',
                'risk_percentage' => 50, // Neutral is 50% risk
                'pos_score' => 0,
                'neg_score' => 0
            ];
        }

        // Calculate risk percentage. More negative words = higher risk.
        $negativeRatio = $negScore / $totalScore;
        $riskPercentage = round($negativeRatio * 100);

        $sentimentLabel = 'Neutral';
        if ($posScore > $negScore) {
            $sentimentLabel = 'Positive';
        } elseif ($negScore > $posScore) {
            $sentimentLabel = 'Negative';
        }

        return [
            'sentiment' => $sentimentLabel,
            'risk_percentage' => $riskPercentage,
            'pos_score' => $posScore,
            'neg_score' => $negScore,
        ];
    }
}
