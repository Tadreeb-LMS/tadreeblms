<?php

namespace Tests\Unit;

use App\Services\Kpi\KpiProcessingEngine;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class KpiProcessingEngineTest extends TestCase
{
    #[Test]
    public function it_supports_multiple_kpi_types_with_consistent_weighting()
    {
        $engine = new KpiProcessingEngine();

        $totalActiveWeight = 100.0;
        $payload = [
            'by_type' => [
                'completion' => 80,
                'score' => 60,
                'time' => 40,
            ],
        ];

        $completion = $engine->calculate([
            'type' => 'completion',
            'weight' => 50,
            'is_active' => true,
        ], $payload, $totalActiveWeight);

        $score = $engine->calculate([
            'type' => 'score',
            'weight' => 30,
            'is_active' => true,
        ], $payload, $totalActiveWeight);

        $time = $engine->calculate([
            'type' => 'time',
            'weight' => 20,
            'is_active' => true,
        ], $payload, $totalActiveWeight);

        $this->assertSame(80.0, $completion['value']);
        $this->assertSame(40.0, $completion['weighted_score']);

        $this->assertSame(60.0, $score['value']);
        $this->assertSame(18.0, $score['weighted_score']);

        $this->assertSame(40.0, $time['value']);
        $this->assertSame(8.0, $time['weighted_score']);
    }

    #[Test]
    public function it_produces_deterministic_results_for_same_input()
    {
        $engine = new KpiProcessingEngine();

        $config = [
            'type' => 'activity',
            'weight' => 25,
            'is_active' => true,
        ];

        $payload = [
            'by_type' => [
                'activity' => 72.6,
            ],
        ];

        $first = $engine->calculate($config, $payload, 80.0);
        $second = $engine->calculate($config, $payload, 80.0);

        $this->assertSame($first, $second);
    }

    #[Test]
    public function it_handles_missing_or_incomplete_data_without_failing()
    {
        $engine = new KpiProcessingEngine();

        $missingMetric = $engine->calculate([
            'type' => 'score',
            'weight' => 10,
            'is_active' => true,
        ], [], 100.0);

        $inactive = $engine->calculate([
            'type' => 'completion',
            'weight' => 40,
            'is_active' => false,
        ], ['value' => 92], 100.0);

        $zeroTotalWeight = $engine->calculate([
            'type' => 'completion',
            'weight' => 40,
            'is_active' => true,
        ], ['value' => 92], 0.0);

        $this->assertSame(0.0, $missingMetric['value']);
        $this->assertSame(0.0, $missingMetric['weighted_score']);
        $this->assertFalse($missingMetric['excluded']);

        $this->assertTrue($inactive['excluded']);
        $this->assertNull($inactive['value']);
        $this->assertNull($inactive['weighted_score']);

        $this->assertSame(92.0, $zeroTotalWeight['value']);
        $this->assertSame(0.0, $zeroTotalWeight['weighted_score']);
        $this->assertFalse($zeroTotalWeight['excluded']);
    }

    #[Test]
    public function it_safely_handles_null_and_negative_weights()
    {
        $engine = new KpiProcessingEngine();

        $nullWeight = $engine->calculate([
            'type' => 'completion',
            'weight' => null,
            'is_active' => true,
        ], ['value' => 80], 100.0);

        $negativeWeight = $engine->calculate([
            'type' => 'completion',
            'weight' => -15,
            'is_active' => true,
        ], ['value' => 80], 100.0);

        $this->assertSame(80.0, $nullWeight['value']);
        $this->assertSame(0.0, $nullWeight['weighted_score']);

        $this->assertSame(80.0, $negativeWeight['value']);
        $this->assertSame(0.0, $negativeWeight['weighted_score']);
    }
}
