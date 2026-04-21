<?php

namespace App\Listeners\Kpi;

use App\Events\Kpi\KpiEvent;
use App\Jobs\Kpi\ProcessUserKpiJob;
use App\Models\Kpi;
use App\Services\Kpi\KpiEventIncrementalApplier;
use App\Services\LmsEventRecorder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Listener that records KPI events to the lms_kpi_events table.
 *
 * This listener is responsible for consuming KpiEvent instances dispatched
 * through the KPI event system and persisting them to the lms_kpi_events table
 * via the LmsEventRecorder service.
 *
 * Key Responsibilities:
 * - Listen for KpiEvent instances
 * - Extract standardized data from event objects
 * - Delegate persistence to LmsEventRecorder
 * - Handle and log recording failures without breaking user flow
 *
 * Integration:
 * - Registered in KpiEventServiceProvider
 * - Invoked synchronously when events are dispatched
 * - Works with all concrete KpiEvent subclasses
 *
 * Never throws exceptions (following LmsEventRecorder's fail-safe design).
 */
class RecordKpiEventListener implements ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 3;

    public $backoff = [10, 30, 120];

    public $queue = 'kpi';

    /**
     * LMS event recorder service.
     *
     * @var LmsEventRecorder
     */
    protected $recorder;

    /**
     * Create the event listener.
     *
     * @param LmsEventRecorder $recorder
     */
    public function __construct(LmsEventRecorder $recorder)
    {
        $this->recorder = $recorder;
    }

    /**
     * Handle the KPI event.
     *
     * Extracts event data and delegates to LmsEventRecorder for persistence.
     * Records are stored in the lms_kpi_events table for later KPI calculations.
     *
     * @param KpiEvent $event
     * @return void
     */
    public function handle(KpiEvent $event): void
    {
        // Extract event components
        $userId = $event->getUserId();
        $eventType = $event->getEventType();
        $payload = $event->getPayload();
        $occurredAt = $event->getOccurredAt();

        // Delegate to LmsEventRecorder
        $success = $this->recorder->record($userId, $eventType, $payload, $occurredAt);

        if (!$success && env('APP_DEBUG')) {
            Log::warning('Failed to record KPI event', [
                'event_type' => $eventType,
                'user_id' => $userId,
            ]);

            return;
        }

        if (!$userId) {
            return;
        }

        $kpiIds = $this->resolveTargetKpiIds($eventType);
        foreach ($kpiIds as $kpiId) {
            ProcessUserKpiJob::dispatch((int) $userId, (int) $kpiId);
        }
    }

    /**
     * @param string $eventType
     * @return int[]
     */
    protected function resolveTargetKpiIds(string $eventType): array
    {
        $kpiTypes = collect(KpiEventIncrementalApplier::EVENT_TYPE_MAP)
            ->filter(function (array $eventTypes) use ($eventType) {
                return in_array($eventType, $eventTypes, true);
            })
            ->keys()
            ->values()
            ->toArray();

        if (empty($kpiTypes)) {
            return [];
        }

        return Kpi::query()
            ->where('is_active', true)
            ->whereIn('type', $kpiTypes)
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->values()
            ->toArray();
    }
}
