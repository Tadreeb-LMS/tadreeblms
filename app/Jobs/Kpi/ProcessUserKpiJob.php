<?php

namespace App\Jobs\Kpi;

use App\Models\Kpi;
use App\Services\Kpi\IncrementalKpiProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * ProcessUserKpiJob
 *
 * Queued job that runs incremental KPI processing for a single (user, KPI) pair.
 * Designed to be dispatched from event listeners or scheduled commands when a
 * new LMS event is recorded, enabling near-real-time KPI updates at scale.
 *
 * – Retries up to 3 times with exponential back-off.
 * – On final failure the cursor is marked dirty so the next scheduled run
 *   performs a full recalculation.
 */
class ProcessUserKpiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries   = 3;
    public $timeout = 120;

    protected int $userId;
    protected int $kpiId;
    protected bool $forceFull;

    public function __construct(int $userId, int $kpiId, bool $forceFull = false)
    {
        $this->userId    = $userId;
        $this->kpiId     = $kpiId;
        $this->forceFull = $forceFull;
        $this->onQueue('kpi');
    }

    public function handle(IncrementalKpiProcessingService $service): void
    {
        $kpi = Kpi::find($this->kpiId);

        if (!$kpi || !$kpi->is_active) {
            // KPI deleted or deactivated – nothing to do.
            return;
        }

        if ($this->forceFull) {
            $service->forceFullRecalculation($this->userId, $kpi);
        } else {
            $service->processForUser($this->userId, $kpi);
        }
    }

    /**
     * Called when all retries are exhausted.
     * Mark the cursor dirty so the scheduler can recover.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('[ProcessUserKpiJob] All retries exhausted; marking cursor dirty.', [
            'user_id' => $this->userId,
            'kpi_id'  => $this->kpiId,
            'error'   => $exception->getMessage(),
        ]);

        try {
            app(IncrementalKpiProcessingService::class)
                ->getOrCreateCursor($this->userId, Kpi::findOrFail($this->kpiId))
                ->markDirty();
        } catch (\Throwable $e) {
            Log::error('[ProcessUserKpiJob] Failed to mark cursor dirty.', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Exponential back-off: 10 s, 60 s, 300 s.
     *
     * @return int[]
     */
    public function backoff(): array
    {
        return [10, 60, 300];
    }
}
