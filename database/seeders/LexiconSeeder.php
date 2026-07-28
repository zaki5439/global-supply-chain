<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LexiconSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $positiveWords = [
            'efficient', 'recovery', 'growth', 'stable', 'expansion',
            'resilient', 'sustainable', 'optimal', 'compliance', 'boom',
            'upgrade', 'seamless', 'integrated', 'robust', 'surplus',
            'secure', 'advancement', 'prosperity', 'breakthrough', 'streamlined',
            'profitable', 'innovation', 'agile', 'revitalized', 'capacity'
        ];

        $negativeWords = [
            'disruption', 'delay', 'shortage', 'crisis', 'inflation',
            'tariff', 'strike', 'bottleneck', 'bankrupt', 'recession',
            'crash', 'penalty', 'volatile', 'deficit', 'sanction',
            'conflict', 'congestion', 'hazard', 'default', 'embargo',
            'cyberattack', 'backlog', 'unstable', 'shutdown', 'scarcity'
        ];

        $positiveData = array_map(function ($word) use ($now) {
            return ['word' => $word, 'weight' => 1.00, 'created_at' => $now, 'updated_at' => $now];
        }, $positiveWords);

        $negativeData = array_map(function ($word) use ($now) {
            return ['word' => $word, 'weight' => 1.00, 'created_at' => $now, 'updated_at' => $now];
        }, $negativeWords);

        DB::table('positive_words')->insertOrIgnore($positiveData);
        DB::table('negative_words')->insertOrIgnore($negativeData);
        
        $this->command->info('Lexicon dictionaries seeded successfully!');
    }
}
