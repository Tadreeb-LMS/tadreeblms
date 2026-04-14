<?php

namespace App\Services\Kpi;

use App\Models\Kpi;
use App\Models\KpiUserCursor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

/**
 * IncrementalKpiProcessingService
 *
 * Core engine for cursor-based incremental KPI calculations.
 *
 * Design principles:
 *  – Cursors are stored per (user_id, kpi_id).
 *  – Only events with id > cursor.last_event_id are fetched on each run.
 *  – State updates are atomic: the cursor advances inside a DB transaction.
 *  – Dirty cursors trigger a full event-stream recalculation before resuming
 *    incremental processing.
 *  – The service is idempotent: processing the same event set twice yields
 *    the same result.
 */
class IncrementalKpiProcessingService
{
    protected KpiEventIncrementalApplier $applier;
    protected ?LoggerInterface $logger;

    /** Maximum events processed per cursor per call (prevents memory exhaustion). */
    public const BATCH_SIZE = 1000;

    public function __construct(KpiEventIncrementalApplier $applier, ?LoggerInterface $logger = null)
    {
        $this->applier = $applier;
        $this->logger  = $logger;
    }

    // ─── Public API ──────────────────────────────────────────────────────────

    /**
     * Process new events for a single (user, KPI) pair.
     *
     * Returns an associative result array:
     *   processed   int    Events processed in this run
     *   value       float  Updated computed KPI value (0–100)
     *   was_dirty   bool   Whether a full recalculation was required
     *   cursor_id   int    ID of the cursor record
     *
     * @throws \Throwable on DB failure (cursor is marked dirty for recovery)
     */
    public function processForUser(int $userId, Kpi $kpi): array
    {
        $cursor = $this->getOrCreateCursor($userId, $kpi);

        // Dirty cursors must be fully rebuilt before resuming incremental mode.
        if ($cursor->is_dirty) {
            return $this->fullRecalculationForUser($userId, $kpi, $cursor);
        }

        return $this->incrementalUpdateForUser($userId, $kpi, $cursor);
    }

    /**
     * Process new events for all active KPIs and all users that have relevant
     * events beyond their current cursor.
     *
     * Returns a summary array keyed by "user_id:kpi_id".
     */
    public function processAll(): array
    {
        $activeKpis = Kpi::where('is_active', true)->get();
        $summary    = [];

        foreach ($activeKpis as $kpi) {
            $relevantTypes = $this->applier->relevantEventTypes($kpi->type);
            if (empty($relevantTypes)) {
                continue;
            }

            // Find all distinct user IDs that have any event beyond their cursor.
            $userIds = $this->usersWithPendingEvents($kpi, $relevantTypes);

            foreach ($userIds as $userId) {
                $key = "{$userId}:{$kpi->id}";
                try {
                    $summary[$key] = $this->processForUser((int) $userId, $kpi);
                } catch (\Throwable $e) {
                    $this->log('error', 'processAll: failed for user/kpi', [
                        'user_id' => $userId,
                        'kpi_id'  => $kpi->id,
                        'error'   => $e->getMessage(),
                    ]);
                    $summary[$key] = ['error' => $e->getMessage()];
                }
            }
        }

        return $summary;
    }

    /**
     * Force a full recalculation by scanning the entire event history for this
     * user/KPI.  Resets the cursor to clean state afterwards.
     *
     * Use this when you need guaranteed accuracy (e.g. after a bulk data import
     * that may have inserted events with occurred_at in the past).
     */
    public function forceFullRecalculation(int $userId, Kpi $kpi): array
    {
        $cursor = $this->getOrCreateCursor($userId, $kpi);
        return $this->fullRecalculationForUser($userId, $kpi, $cursor);
    }

    /**
     * Reset the cursor for a specific (user, KPI) pair.
     * The next call to processForUser() will perform a full recalculation.
     */
    public function resetCursor(int $userId, int $kpiId): void
    {
        $cursor = KpiUserCursor::where('user_id', $userId)
            ->where('kpi_id', $kpiId)
            ->first();

        if ($cursor) {
            $cursor->reset();
        }
    }

    /**
     * Return the current computed value for a user/KPI without triggering any
     * processing.  Returns null if no cursor exists yet.
     */
    public function getCachedValue(int $userId, int $kpiId): ?float
    {
        $cursor = KpiUserCursor::where('user_id', $userId)
            ->where('kpi_id', $kpiId)
            ->first();

        return $cursor ? $cursor->computed_value : null;
    }

    /**
     * Retrieve the cursor for (userId, kpi) or create a fresh one.
     */
    public function getOrCreateCursor(int $userId, Kpi $kpi): KpiUserCursor
    {
        return KpiUserCursor::firstOrCreate(
            ['user_id' => $userId, 'kpi_id' => $kpi->id],
            [
                'last_event_id'     => null,
                'computed_value'    => null,
                'event_count'       => 0,
                'checkpoint_data'   => null,
                'is_dirty'          => false,
                'last_processed_at' => null,
            ]
        );
    }

    // ─── Private: incremental update ─────────────────────────────────────────

    private function incrementalUpdateForUser(int $userId, Kpi $kpi, KpiUserCursor $cursor): array
    {
        $relevantTypes = $this->applier->relevantEventTypes($kpi->type);
        if (empty($relevantTypes)) {
            return $this->noopResult($cursor);
        }

        $events = $this->fetchNewEvents($userId, $cursor->last_event_id, $relevantTypes);

        if ($events->isEmpty()) {
            return $this->noopResult($cursor);
        }

        $value      = $cursor->computed_value ?? 0.0;
        $count      = $cursor->event_count;
        $checkpoint = $cursor->checkpoint_data;
        $maxEventId = $cursor->last_event_id ?? 0;
        $scopedCourseLookup = $this->buildScopedCourseLookup($kpi);
        $hasCategoryMapping = $this->kpiHasCategoryMapping($kpi);
        $processed = 0;

        foreach ($events as $event) {
            $payload = is_string($event->payload)
                ? (json_decode($event->payload, true) ?? [])
                : (array) ($event->payload ?? []);

            if (!$this->eventBelongsToKpiScope($payload, $scopedCourseLookup, $hasCategoryMapping)) {
                continue;
            }

            $result     = $this->applier->apply($kpi->type, $event->event_type, $payload, $value, $count, $checkpoint);
            $value      = $result['value'];
            $count      = $result['count'];
            $checkpoint = $result['checkpoint'];
            $maxEventId = (int) $event->id;
            $processed++;
        }

        try {
            DB::transaction(function () use ($cursor, $maxEventId, $value, $count, $checkpoint) {
                // Re-read inside transaction to detect concurrent writes.
                $fresh = KpiUserCursor::lockForUpdate()->find($cursor->id);
                if (!$fresh) {
                    return;
                }

                // Idempotency guard: if another process already advanced the cursor
                // past our batch, skip the update to avoid overwriting newer state.
                if ($fresh->last_event_id !== null && $fresh->last_event_id >= $maxEventId) {
                    return;
                }

                $fresh->advance($maxEventId, $value, $count, $checkpoint);
            });
        } catch (\Throwable $e) {
            $cursor->markDirty();
            $this->log('error', 'incrementalUpdate: transaction failed, cursor marked dirty', [
                'cursor_id' => $cursor->id,
                'user_id'   => $userId,
                'kpi_id'    => $kpi->id,
                'error'     => $e->getMessage(),
            ]);
            throw $e;
        }

        $cursor->refresh();

        return [
            'processed' => $processed,
            'value'     => $cursor->getEffectiveValue(),
            'was_dirty' => false,
            'cursor_id' => $cursor->id,
        ];
    }

    // ─── Private: full recalculation ─────────────────────────────────────────

    private function fullRecalculationForUser(int $userId, Kpi $kpi, KpiUserCursor $cursor): array
    {
        $relevantTypes = $this->applier->relevantEventTypes($kpi->type);

        $value      = 0.0;
        $count      = 0;
        $checkpoint = [];
        $maxEventId = 0;
        $processed  = 0;
        $scopedCourseLookup = $this->buildScopedCourseLookup($kpi);
        $hasCategoryMapping = $this->kpiHasCategoryMapping($kpi);

        // Stream events in chunks to avoid loading the entire table into memory.
        DB::table('lms_kpi_events')
            ->where('user_id', $userId)
            ->whereIn('event_type', $relevantTypes)
            ->orderBy('id')
            ->chunkById(self::BATCH_SIZE, function ($chunk) use (
                $kpi, $scopedCourseLookup, $hasCategoryMapping, &$value, &$count, &$checkpoint, &$maxEventId, &$processed
            ) {
                foreach ($chunk as $event) {
                    $payload = is_string($event->payload)
                        ? (json_decode($event->payload, true) ?? [])
                        : (array) ($event->payload ?? []);

                    if (!$this->eventBelongsToKpiScope($payload, $scopedCourseLookup, $hasCategoryMapping)) {
                        continue;
                    }

                    $result     = $this->applier->apply($kpi->type, $event->event_type, $payload, $value, $count, $checkpoint);
                    $value      = $result['value'];
                    $count      = $result['count'];
                    $checkpoint = $result['checkpoint'];
                    $maxEventId = (int) $event->id;
                    $processed++;
                }
            });

        try {
            DB::transaction(function () use ($cursor, $maxEventId, $value, $count, $checkpoint) {
                $fresh = KpiUserCursor::lockForUpdate()->find($cursor->id);
                if (!$fresh) {
                    return;
                }
                $fresh->last_event_id   = $maxEventId > 0 ? $maxEventId : null;
                $fresh->computed_value  = round($value, 4);
                $fresh->event_count     = $count;
                $fresh->checkpoint_data = $checkpoint ?: null;
                $fresh->is_dirty        = false;
                $fresh->last_processed_at = now();
                $fresh->save();
            });
        } catch (\Throwable $e) {
            // Do NOT mark dirty here – we are already in recovery mode.
            $this->log('error', 'fullRecalculation: transaction failed', [
                'cursor_id' => $cursor->id,
                'user_id'   => $cursor->user_id,
                'kpi_id'    => $cursor->kpi_id,
                'error'     => $e->getMessage(),
            ]);
            throw $e;
        }

        $cursor->refresh();

        return [
            'processed' => $processed,
            'value'     => $cursor->getEffectiveValue(),
            'was_dirty' => true,
            'cursor_id' => $cursor->id,
        ];
    }

    // ─── Private: helpers ────────────────────────────────────────────────────

    /**
     * Fetch events for a user that have not yet been processed (id > lastEventId).
     * Results are ordered by id ASC to guarantee monotonic cursor advancement.
     */
    private function fetchNewEvents(int $userId, ?int $lastEventId, array $relevantTypes)
    {
        return DB::table('lms_kpi_events')
            ->where('user_id', $userId)
            ->whereIn('event_type', $relevantTypes)
            ->when($lastEventId !== null, fn ($q) => $q->where('id', '>', $lastEventId))
            ->orderBy('id')
            ->limit(self::BATCH_SIZE)
            ->get();
    }

    /**
     * Find all user IDs that have at least one event beyond their current cursor
     * for the given KPI.
     *
     * @param  Kpi      $kpi
     * @param  string[] $relevantTypes
     * @return \Illuminate\Support\Collection
     */
    private function usersWithPendingEvents(Kpi $kpi, array $relevantTypes)
    {
        // Users with a cursor — only include those whose cursor lags behind the
        // latest available event.
        $withCursor = DB::table('lms_kpi_events as e')
            ->join('kpi_user_cursors as c', function ($join) use ($kpi) {
                $join->on('c.user_id', '=', 'e.user_id')
                    ->where('c.kpi_id', '=', $kpi->id);
            })
            ->whereIn('e.event_type', $relevantTypes)
            ->whereRaw('e.id > COALESCE(c.last_event_id, 0)')
            ->whereNotNull('e.user_id')
            ->distinct()
            ->pluck('e.user_id');

        // Users without any cursor (first time) who have relevant events.
        $withoutCursor = DB::table('lms_kpi_events')
            ->whereNotNull('user_id')
            ->whereIn('event_type', $relevantTypes)
            ->whereNotIn('user_id', function ($sub) use ($kpi) {
                $sub->select('user_id')
                    ->from('kpi_user_cursors')
                    ->where('kpi_id', $kpi->id);
            })
            ->distinct()
            ->pluck('user_id');

        return $withCursor->merge($withoutCursor)->unique()->values();
    }

    private function noopResult(KpiUserCursor $cursor): array
    {
        return [
            'processed' => 0,
            'value'     => $cursor->getEffectiveValue(),
            'was_dirty' => false,
            'cursor_id' => $cursor->id,
        ];
    }

    /**
     * @return array<int, true>
     */
    private function buildScopedCourseLookup(Kpi $kpi): array
    {
        if (!method_exists($kpi, 'resolveScopedCourseIds')) {
            return [];
        }

        return collect($kpi->resolveScopedCourseIds())
            ->mapWithKeys(function ($id) {
                return [(int) $id => true];
            })
            ->toArray();
    }

    private function eventBelongsToKpiScope(array $payload, array $scopedCourseLookup, bool $hasCategoryMapping): bool
    {
        if (empty($scopedCourseLookup) && !$hasCategoryMapping) {
            return true;
        }

        $courseId = isset($payload['course_id']) ? (int) $payload['course_id'] : 0;
        if ($courseId <= 0) {
            return false;
        }

        return isset($scopedCourseLookup[$courseId]);
    }

    private function kpiHasCategoryMapping(Kpi $kpi): bool
    {
        if (method_exists($kpi, 'relationLoaded') && $kpi->relationLoaded('categories')) {
            return $kpi->categories->isNotEmpty();
        }

        return method_exists($kpi, 'categories') && $kpi->categories()->exists();
    }

    private function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->{$level}('[IncrementalKpi] ' . $message, $context);
        }
    }
}
