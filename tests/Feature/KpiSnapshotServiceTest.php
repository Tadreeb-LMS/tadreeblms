<?php

namespace Tests\Feature;

use App\Models\Kpi;
use App\Models\KpiSnapshot;
use App\Services\Kpi\KpiSnapshotService;
use App\Services\KpiCalculationService;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class KpiSnapshotServiceTest extends TestCase
{
    #[Test]
    public function it_persists_and_reuses_current_snapshot_for_fast_reads()
    {
        config([
            'kpi.snapshots.version' => 1,
            'kpi.snapshots.max_age_minutes' => 5,
        ]);

        $kpi = Kpi::query()->create([
            'name' => 'Completion KPI',
            'code' => 'COURSE_COMPLETION',
            'type' => 'completion',
            'description' => 'Tracks course completion.',
            'weight' => 25,
            'is_active' => true,
        ]);

        $calculationService = Mockery::mock(KpiCalculationService::class);
        $calculationService
            ->shouldReceive('calculateForKpi')
            ->once()
            ->andReturn([
                'excluded' => false,
                'value' => 80.5,
                'weighted_score' => 20.13,
            ]);

        $this->app->instance(KpiCalculationService::class, $calculationService);
        $snapshotService = $this->app->make(KpiSnapshotService::class);

        $kpis = Kpi::query()->with('courses:id')->get();

        $snapshotService->attachCalculations($kpis, 100);
        $snapshotService->attachCalculations($kpis, 100);

        $kpi->refresh();
        $currentSnapshot = $kpi->currentSnapshot;

        $this->assertNotNull($currentSnapshot);
        $this->assertSame(1, KpiSnapshot::query()->count());
        $this->assertTrue($currentSnapshot->is_current);
        $this->assertNull($currentSnapshot->previous_snapshot_id);
        $this->assertSame(80.5, (float) $currentSnapshot->value);
        $this->assertSame(20.13, (float) $currentSnapshot->weighted_score);
    }

    #[Test]
    public function it_creates_new_snapshot_version_and_links_to_previous_one()
    {
        config([
            'kpi.snapshots.version' => 1,
            'kpi.snapshots.max_age_minutes' => 60,
        ]);

        $kpi = Kpi::query()->create([
            'name' => 'Score KPI',
            'code' => 'ASSESSMENT_SCORE',
            'type' => 'score',
            'description' => 'Tracks assessment score.',
            'weight' => 40,
            'is_active' => true,
        ]);

        $calculationService = Mockery::mock(KpiCalculationService::class);
        $calculationService
            ->shouldReceive('calculateForKpi')
            ->twice()
            ->andReturn([
                'excluded' => false,
                'value' => 60.0,
                'weighted_score' => 24.0,
            ]);

        $this->app->instance(KpiCalculationService::class, $calculationService);
        $snapshotService = $this->app->make(KpiSnapshotService::class);

        $kpis = Kpi::query()->with('courses:id')->get();
        $snapshotService->attachCalculations($kpis, 100);

        $firstSnapshot = KpiSnapshot::query()->where('kpi_id', $kpi->id)->where('is_current', true)->first();
        $this->assertNotNull($firstSnapshot);

        config(['kpi.snapshots.version' => 2]);

        $kpis = Kpi::query()->with('courses:id')->get();
        $snapshotService->attachCalculations($kpis, 100);

        $kpi->refresh();
        $currentSnapshot = $kpi->currentSnapshot;
        $historicalSnapshot = KpiSnapshot::query()->where('id', $firstSnapshot->id)->first();

        $this->assertSame(2, KpiSnapshot::query()->count());
        $this->assertFalse((bool) $historicalSnapshot->is_current);
        $this->assertTrue((bool) $currentSnapshot->is_current);
        $this->assertSame($firstSnapshot->id, $currentSnapshot->previous_snapshot_id);
        $this->assertSame(2, (int) $currentSnapshot->calculation_version);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
