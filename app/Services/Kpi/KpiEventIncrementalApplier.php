<?php

namespace App\Services\Kpi;

use App\Models\KpiUserCursor;

/**
 * KpiEventIncrementalApplier
 *
 * Stateless service responsible for applying a single LMS event to an
 * in-progress cursor state and returning the updated state.  All arithmetic
 * is deterministic and idempotent for a given (event, prior_state) pair.
 *
 * Each KPI type maps to a subset of event types and uses a specific
 * aggregation strategy:
 *
 *   completion → course_completed   (running average of completion_percentage)
 *   score      → quiz_attempt + assignment_completed[graded|approved]
 *                (running average of score)
 *   activity   → all engagement events (accumulated points, max 100)
 *   time       → assignment_completed (running average of time_to_complete_seconds,
 *                normalized: avg_seconds / 3600 * 100, capped at 100)
 */
class KpiEventIncrementalApplier
{
    /**
     * Which lms_kpi_events.event_type values are relevant for each KPI type.
     */
    public const EVENT_TYPE_MAP = [
        'completion' => ['course_completed'],
        'score'      => ['quiz_attempt', 'assignment_completed'],
        'activity'   => ['user_login', 'lesson_completed', 'course_completed', 'quiz_attempt', 'assignment_completed'],
        'time'       => ['assignment_completed'],
    ];

    /**
     * Activity points awarded per event type.
     */
    public const ACTIVITY_POINTS = [
        'user_login'            => 5,
        'lesson_completed'      => 10,
        'quiz_attempt'          => 15,
        'assignment_completed'  => 20,
        'course_completed'      => 25,
    ];

    /**
     * Seconds considered a "full" time KPI score (100 %).
     * Anything >= this value is capped at 100.
     */
    public const TIME_BASELINE_SECONDS = 3600;

    /**
     * Assignment statuses that count toward the score KPI.
     */
    public const SCORED_ASSIGNMENT_STATUSES = ['graded', 'approved'];

    // ─── Public API ──────────────────────────────────────────────────────────

    /**
     * Return all event types that should be fetched for a given KPI type.
     *
     * @param  string $kpiType
     * @return string[]
     */
    public function relevantEventTypes(string $kpiType): array
    {
        return self::EVENT_TYPE_MAP[$kpiType] ?? [];
    }

    /**
     * Apply a single event to the current cursor state and return the new
     * (value, count, checkpointData) triple.
     *
     * If the event is not relevant for this KPI type, or the payload does
     * not supply the fields needed, the cursor state is returned unchanged.
     *
     * @param  string     $kpiType       One of: completion, score, activity, time
     * @param  string     $eventType     The event_type from lms_kpi_events
     * @param  array      $payload       Decoded JSON payload of the event
     * @param  float      $currentValue  Current computed_value (0–100)
     * @param  int        $eventCount    Current event_count
     * @param  array|null $checkpointData Current checkpoint_data
     * @return array{value: float, count: int, checkpoint: array}
     */
    public function apply(
        string $kpiType,
        string $eventType,
        array $payload,
        float $currentValue,
        int $eventCount,
        ?array $checkpointData
    ): array {
        $checkpoint = $checkpointData ?? [];

        switch ($kpiType) {
            case 'completion':
                return $this->applyCompletion($eventType, $payload, $currentValue, $eventCount, $checkpoint);

            case 'score':
                return $this->applyScore($eventType, $payload, $currentValue, $eventCount, $checkpoint);

            case 'activity':
                return $this->applyActivity($eventType, $currentValue, $eventCount, $checkpoint);

            case 'time':
                return $this->applyTime($eventType, $payload, $currentValue, $eventCount, $checkpoint);

            default:
                // Unknown KPI type – pass through unchanged.
                return ['value' => $currentValue, 'count' => $eventCount, 'checkpoint' => $checkpoint];
        }
    }

    /**
     * Apply an entire batch of events sequentially and return the final state.
     *
     * @param  string     $kpiType
     * @param  iterable   $events        Each item: ['event_type' => …, 'payload' => array|null]
     * @param  float      $currentValue
     * @param  int        $eventCount
     * @param  array|null $checkpointData
     * @return array{value: float, count: int, checkpoint: array}
     */
    public function applyBatch(
        string $kpiType,
        iterable $events,
        float $currentValue,
        int $eventCount,
        ?array $checkpointData
    ): array {
        $value      = $currentValue;
        $count      = $eventCount;
        $checkpoint = $checkpointData ?? [];

        foreach ($events as $event) {
            $eventType = (string) ($event['event_type'] ?? '');
            $payload   = is_array($event['payload']) ? $event['payload'] : [];

            $result     = $this->apply($kpiType, $eventType, $payload, $value, $count, $checkpoint);
            $value      = $result['value'];
            $count      = $result['count'];
            $checkpoint = $result['checkpoint'];
        }

        return ['value' => $value, 'count' => $count, 'checkpoint' => $checkpoint];
    }

    // ─── Per-type strategies ─────────────────────────────────────────────────

    protected function applyCompletion(
        string $eventType,
        array $payload,
        float $currentValue,
        int $count,
        array $checkpoint
    ): array {
        if ($eventType !== 'course_completed') {
            return ['value' => $currentValue, 'count' => $count, 'checkpoint' => $checkpoint];
        }

        $pct = isset($payload['completion_percentage']) ? (float) $payload['completion_percentage'] : null;
        if ($pct === null) {
            return ['value' => $currentValue, 'count' => $count, 'checkpoint' => $checkpoint];
        }

        $pct       = min(100.0, max(0.0, $pct));
        $rawSum    = (float) ($checkpoint['raw_sum'] ?? ($currentValue * $count));
        $rawSum   += $pct;
        $newCount  = $count + 1;
        $newValue  = $newCount > 0 ? $rawSum / $newCount : 0.0;

        return [
            'value'      => round(min(100.0, max(0.0, $newValue)), 4),
            'count'      => $newCount,
            'checkpoint' => ['raw_sum' => round($rawSum, 6)],
        ];
    }

    protected function applyScore(
        string $eventType,
        array $payload,
        float $currentValue,
        int $count,
        array $checkpoint
    ): array {
        if (!in_array($eventType, ['quiz_attempt', 'assignment_completed'], true)) {
            return ['value' => $currentValue, 'count' => $count, 'checkpoint' => $checkpoint];
        }

        // For assignments, only count graded/approved submissions.
        if ($eventType === 'assignment_completed') {
            $status = (string) ($payload['status'] ?? '');
            if (!in_array($status, self::SCORED_ASSIGNMENT_STATUSES, true)) {
                return ['value' => $currentValue, 'count' => $count, 'checkpoint' => $checkpoint];
            }
        }

        $score = isset($payload['score']) ? (float) $payload['score'] : null;
        if ($score === null) {
            return ['value' => $currentValue, 'count' => $count, 'checkpoint' => $checkpoint];
        }

        $score    = min(100.0, max(0.0, $score));
        $rawSum   = (float) ($checkpoint['raw_sum'] ?? ($currentValue * $count));
        $rawSum  += $score;
        $newCount = $count + 1;
        $newValue = $newCount > 0 ? $rawSum / $newCount : 0.0;

        return [
            'value'      => round(min(100.0, max(0.0, $newValue)), 4),
            'count'      => $newCount,
            'checkpoint' => ['raw_sum' => round($rawSum, 6)],
        ];
    }

    protected function applyActivity(
        string $eventType,
        float $currentValue,
        int $count,
        array $checkpoint
    ): array {
        $points = self::ACTIVITY_POINTS[$eventType] ?? null;
        if ($points === null) {
            return ['value' => $currentValue, 'count' => $count, 'checkpoint' => $checkpoint];
        }

        $accumulated    = (float) ($checkpoint['accumulated_points'] ?? $currentValue);
        $accumulated   += $points;
        $newValue       = min(100.0, $accumulated);
        $newCount       = $count + 1;

        return [
            'value'      => round($newValue, 4),
            'count'      => $newCount,
            'checkpoint' => ['accumulated_points' => round($accumulated, 6)],
        ];
    }

    protected function applyTime(
        string $eventType,
        array $payload,
        float $currentValue,
        int $count,
        array $checkpoint
    ): array {
        if ($eventType !== 'assignment_completed') {
            return ['value' => $currentValue, 'count' => $count, 'checkpoint' => $checkpoint];
        }

        $seconds = isset($payload['time_to_complete_seconds'])
            ? (float) $payload['time_to_complete_seconds']
            : null;

        if ($seconds === null || $seconds < 0) {
            return ['value' => $currentValue, 'count' => $count, 'checkpoint' => $checkpoint];
        }

        $rawSumSeconds = (float) ($checkpoint['raw_sum_seconds'] ?? 0.0);

        // Reconstruct running sum when checkpoint is missing (first event after
        // a state reset that preserved computed_value but lost checkpoint).
        if (!isset($checkpoint['raw_sum_seconds']) && $count > 0) {
            // Reverse-engineer from the stored normalized value.
            $rawSumSeconds = ($currentValue / 100.0) * self::TIME_BASELINE_SECONDS * $count;
        }

        $rawSumSeconds += $seconds;
        $newCount       = $count + 1;
        $avgSeconds     = $rawSumSeconds / $newCount;
        $newValue       = ($avgSeconds / self::TIME_BASELINE_SECONDS) * 100.0;

        return [
            'value'      => round(min(100.0, max(0.0, $newValue)), 4),
            'count'      => $newCount,
            'checkpoint' => ['raw_sum_seconds' => round($rawSumSeconds, 6)],
        ];
    }
}
