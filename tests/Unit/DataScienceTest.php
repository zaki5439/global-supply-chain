<?php
namespace Tests\Unit;

use Tests\TestCase;
use App\Services\DataScienceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DataScienceTest extends TestCase
{
    use RefreshDatabase;

    protected DataScienceService $dsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dsService = new DataScienceService();
        
        DB::table('positive_words')->insert([
            ['word' => 'growth', 'weight' => 1.0],
            ['word' => 'stable', 'weight' => 1.0],
        ]);
        
        DB::table('negative_words')->insert([
            ['word' => 'crisis', 'weight' => 1.0],
            ['word' => 'war', 'weight' => 1.0],
        ]);
    }

    public function test_sentiment_analysis_with_mixed_words()
    {
        $text = "Market growth is stable but crisis and war cause panic";
        $result = $this->dsService->analyzeSentiment($text);

        $this->assertEquals(20.0, $result['positive_percent']);
        $this->assertEquals(20.0, $result['negative_percent']);
        $this->assertEquals(60.0, $result['neutral_percent']);
        $this->assertEquals('neutral', $result['label']);
    }

    public function test_weighted_risk_model_extremes()
    {
        $factorsZero = ['weather' => 0, 'inflation' => 0, 'news' => 0, 'currency' => 0];
        $resultZero = $this->dsService->calculateTotalRisk($factorsZero);
        
        $this->assertEquals(0, $resultZero['overall_risk_score']);
        $this->assertEquals('LOW', $resultZero['risk_level']);

        $factorsCritical = ['weather' => 100, 'inflation' => 100, 'news' => 100, 'currency' => 100];
        $resultCritical = $this->dsService->calculateTotalRisk($factorsCritical);
        
        $this->assertEquals(100.0, $resultCritical['overall_risk_score']);
        $this->assertEquals('CRITICAL', $resultCritical['risk_level']);
    }
}
