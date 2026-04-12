<?php

namespace App\Console\Commands\Kpi;

use App\Models\Kpi;
use App\Services\Kpi\IncrementalKpiProcessingService;
use Illuminate\Console\Command;

/**
 * ProcessIncrementalKpiCommand
 *
 * Artisan command that drives the cursor-based incremental KPI engine.
 *
 * Usage examples:
 *   php artisan kpi:process-incremental
 *   php artisan kpi:process-incremental --user=42
 *   php artisan kpi:process-incremental --kpi=COURSE_COMPLETION_RATE
 *   php artisan kpi:process-incremental --force-full
 *   php artisan kpi:process-incremental --user=42 --kpi=ASSESSMENT_PASS_RATE --force-full
 */
class ProcessIncrementalKpiCommand extends Command
{
    protected $signature = 'kpi:process-incremental
        {--user=    : Process only this user ID}
        {--kpi=     : Process only the KPI with this code}
        {--force-full : Force a full recalculation (ignores current cursor state)}';

    protected $description = 'Process new KPI events incrementally using per-user cursors.';

    protected IncrementalKpiProcessingService $service;

    public function __construct(IncrementalKpiProcessingService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle(): int
    {
        $userId    = $this->option('user')      ? (int) $this->option('user') : null;
        $kpiCode   = $this->option('kpi')       ? (string) $this->option('kpi') : null;
        $forceFull = (bool) $this->option('force-full');

        $kpis = $this->resolveKpis($kpiCode);

        if ($kpis->isEmpty()) {
            $this->warn('No active KPIs found' . ($kpiCode ? " matching code [{$kpiCode}]" : '') . '.');
            return 0;
        }

        $totalProcessed = 0;
        $errors         = 0;

        foreach ($kpis as $kpi) {
            $userIds = $userId !== null ? collect([$userId]) : $this->resolveUserIds($kpi);

            foreach ($userIds as $uid) {
                try {
                    $result = $forceFull
                        ? $this->service->forceFullRecalculation((int) $uid, $kpi)
                        : $this->service->processForUser((int) $uid, $kpi);

                    $totalProcessed += $result['processed'] ?? 0;

                    if ($this->getOutput()->isVerbose()) {
                        $this->line(sprintf(
                            '  user=%d kpi=%s events=%d value=%.2f dirty=%s',
                            $uid,
                            $kpi->code,
                            $result['processed'],
                            $result['value'],
                            $result['was_dirty'] ? 'yes' : 'no'
                        ));
                    }
                } catch (\Throwable $e) {
                    $errors++;
                    $this->error("  Failed user={$uid} kpi={$kpi->code}: " . $e->getMessage());
                }
            }
        }

        $this->info("Done. Total events processed: {$totalProcessed}. Errors: {$errors}.");

        return $errors === 0 ? 0 : 1;
    }

    private function resolveKpis(?string $kpiCode)
    {
        $query = Kpi::where('is_active', true);

        if ($kpiCode !== null) {
            $query->where('code', $kpiCode);
        }

        return $query->get();
    }

    private function resolveUserIds(Kpi $kpi): \Illuminate\Support\Collection
    {
        // Find all users that have events relevant to this KPI type or have
        // an existing cursor for it (including dirty ones awaiting recovery).
        $relevantTypes = app(
            \App\Services\Kpi\KpiEventIncrementalApplier::class
        )->relevantEventTypes($kpi->type);

        $fromEvents = \Illuminate\Support\Facades\DB::table('lms_kpi_events')
            ->whereIn('event_type', $relevantTypes)
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        $fromCursors = \Illuminate\Support\Facades\DB::table('kpi_user_cursors')
            ->where('kpi_id', $kpi->id)
            ->pluck('user_id');

        return $fromEvents->merge($fromCursors)->unique()->values();
    }
}
