<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LexiconSentimentSeeder extends Seeder
{
    /**
     * Seed positive and negative words for sentiment analysis
     */
    public function run(): void
    {
        // Positive words
        $positiveWords = [
            'good', 'great', 'excellent', 'amazing', 'wonderful', 'fantastic', 'awesome', 'perfect',
            'positive', 'strong', 'growth', 'increase', 'improve', 'better', 'success', 'prosperous',
            'efficient', 'secure', 'profit', 'gain', 'advantage', 'opportunity', 'beneficial',
            'supportive', 'reliable', 'trustworthy', 'safe', 'stable', 'booming', 'thriving',
            'favorable', 'prospering', 'thrived', 'advancing', 'recovery', 'upgrade', 'optimal',
            'vibrant', 'dynamic', 'competitive', 'innovative', 'leading', 'excellence', 'achievement',
            'successful', 'confident', 'encouraged', 'promising', 'accelerate', 'expand', 'flourish',
            'profitable', 'robust', 'vigorous', 'ambitious', 'active', 'progressive', 'improvement'
        ];

        // Negative words
        $negativeWords = [
            'bad', 'terrible', 'awful', 'horrible', 'poor', 'worst', 'negative', 'decline',
            'decrease', 'weak', 'weakness', 'fail', 'failure', 'loss', 'lost', 'risk', 'risky',
            'dangerous', 'threat', 'threats', 'crisis', 'problem', 'problems', 'issue', 'issues',
            'challenge', 'challenging', 'difficult', 'delay', 'delays', 'congestion', 'congested',
            'friction', 'tension', 'tensions', 'conflict', 'unstable', 'uncertainty', 'volatile',
            'downturn', 'recession', 'collapse', 'crash', 'bankrupt', 'sick', 'disease',
            'damage', 'damaged', 'disaster', 'catastrophe', 'tragedy', 'unfortunate', 'unlucky',
            'distress', 'stressed', 'stressed', 'worried', 'concerned', 'alarmed', 'anxiety',
            'slump', 'stagnation', 'hostile', 'aggressive', 'broken', 'failure', 'errors'
        ];

        // Batch insert positive words
        $now = now();
        $positiveData = array_map(function ($word) use ($now) {
            return ['word' => $word, 'weight' => 1.0, 'created_at' => $now, 'updated_at' => $now];
        }, $positiveWords);
        
        DB::table('positive_words')->insertOrIgnore($positiveData);

        // Batch insert negative words
        $negativeData = array_map(function ($word) use ($now) {
            return ['word' => $word, 'weight' => 1.0, 'created_at' => $now, 'updated_at' => $now];
        }, $negativeWords);
        
        DB::table('negative_words')->insertOrIgnore($negativeData);

        $this->command->info('Lexicon sentiment words seeded successfully!');
        $this->command->info(count($positiveWords) . ' positive words added');
        $this->command->info(count($negativeWords) . ' negative words added');
    }
}
