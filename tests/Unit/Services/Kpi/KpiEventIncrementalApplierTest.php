<?php

namespace Tests\Unit\Services\Kpi;

use App\Services\Kpi\KpiEventIncrementalApplier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Deep unit tests for KpiEventIncrementalApplier.
 *
 * All tests are pure – no database, no Laravel container.
 */
class KpiEventIncrementalApplierTest extends TestCase
{
    private KpiEventIncrementalApplier $applier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->applier = new KpiEventIncrementalApplier();
    }

    // ─── relevantEventTypes ───────────────────────────────────────────────────

    #[Test]
    public function relevant_event_types_returns_correct_types_for_completion(): void
    {
        $types = $this->applier->relevantEventTypes('completion');
        $this->assertSame(['course_completed'], $types);
    }

    #[Test]
    public function relevant_event_types_returns_correct_types_for_score(): void
    {
        $types = $this->applier->relevantEventTypes('score');
        $this->assertEqualsCanonicalizing(['quiz_attempt', 'assignment_completed'], $types);
    }

    #[Test]
    public function relevant_event_types_returns_correct_types_for_activity(): void
    {
        $types = $this->applier->relevantEventTypes('activity');
        $expected = ['user_login', 'lesson_completed', 'course_completed', 'quiz_attempt', 'assignment_completed'];
        $this->assertEqualsCanonicalizing($expected, $types);
    }

    #[Test]
    public function relevant_event_types_returns_correct_types_for_time(): void
    {
        $types = $this->applier->relevantEventTypes('time');
        $this->assertSame(['assignment_completed'], $types);
    }

    #[Test]
    public function relevant_event_types_returns_empty_for_unknown_type(): void
    {
        $this->assertSame([], $this->applier->relevantEventTypes('nonexistent'));
    }

    // ─── Completion KPI ───────────────────────────────────────────────────────

    #[Test]
    public function completion_first_event_sets_value_correctly(): void
    {
        $result = $this->applier->apply('completion', 'course_completed', ['completion_percentage' => 80.0], 0.0, 0, null);

        $this->assertSame(80.0, $result['value']);
        $this->assertSame(1, $result['count']);
        $this->assertSame(80.0, $result['checkpoint']['raw_sum']);
    }

    #[Test]
    public function completion_running_average_accumulates_correctly(): void
    {
        // First event: 80 %
        $s1 = $this->applier->apply('completion', 'course_completed', ['completion_percentage' => 80.0], 0.0, 0, null);
        // Second event: 60 %  →  avg = 70
        $s2 = $this->applier->apply('completion', 'course_completed', ['completion_percentage' => 60.0], $s1['value'], $s1['count'], $s1['checkpoint']);
        // Third event: 100 %  →  avg = (80+60+100)/3 = 80
        $s3 = $this->applier->apply('completion', 'course_completed', ['completion_percentage' => 100.0], $s2['value'], $s2['count'], $s2['checkpoint']);

        $this->assertSame(70.0, round($s2['value'], 2));
        $this->assertSame(80.0, round($s3['value'], 2));
        $this->assertSame(3, $s3['count']);
    }

    #[Test]
    public function completion_ignores_irrelevant_event_types(): void
    {
        $irrelevant = ['quiz_attempt', 'user_login', 'lesson_completed', 'assignment_completed'];

        foreach ($irrelevant as $type) {
            $result = $this->applier->apply('completion', $type, ['completion_percentage' => 90.0], 50.0, 5, ['raw_sum' => 250.0]);
            $this->assertSame(50.0, $result['value'], "Expected no change for event type [{$type}]");
            $this->assertSame(5, $result['count']);
        }
    }

    #[Test]
    public function completion_ignores_event_with_missing_percentage(): void
    {
        $result = $this->applier->apply('completion', 'course_completed', [], 60.0, 3, ['raw_sum' => 180.0]);
        $this->assertSame(60.0, $result['value']);
        $this->assertSame(3, $result['count']);
    }

    #[Test]
    public function completion_clamps_percentage_above_100(): void
    {
        $result = $this->applier->apply('completion', 'course_completed', ['completion_percentage' => 150.0], 0.0, 0, null);
        $this->assertSame(100.0, $result['value']);
    }

    #[Test]
    public function completion_clamps_percentage_below_0(): void
    {
        $result = $this->applier->apply('completion', 'course_completed', ['completion_percentage' => -20.0], 0.0, 0, null);
        $this->assertSame(0.0, $result['value']);
    }

    // ─── Score KPI ────────────────────────────────────────────────────────────

    #[Test]
    public function score_quiz_attempt_updates_running_average(): void
    {
        $s1 = $this->applier->apply('score', 'quiz_attempt', ['score' => 90.0], 0.0, 0, null);
        $s2 = $this->applier->apply('score', 'quiz_attempt', ['score' => 70.0], $s1['value'], $s1['count'], $s1['checkpoint']);

        $this->assertSame(90.0, $s1['value']);
        $this->assertSame(80.0, $s2['value']);
        $this->assertSame(2, $s2['count']);
    }

    #[Test]
    public function score_graded_assignment_counts_toward_average(): void
    {
        $result = $this->applier->apply('score', 'assignment_completed', ['score' => 75.0, 'status' => 'graded'], 0.0, 0, null);
        $this->assertSame(75.0, $result['value']);
        $this->assertSame(1, $result['count']);
    }

    #[Test]
    public function score_approved_assignment_counts_toward_average(): void
    {
        $result = $this->applier->apply('score', 'assignment_completed', ['score' => 85.0, 'status' => 'approved'], 0.0, 0, null);
        $this->assertSame(85.0, $result['value']);
    }

    #[Test]
    public function score_submitted_assignment_is_ignored(): void
    {
        $result = $this->applier->apply('score', 'assignment_completed', ['score' => 95.0, 'status' => 'submitted'], 50.0, 2, ['raw_sum' => 100.0]);
        $this->assertSame(50.0, $result['value']);
        $this->assertSame(2, $result['count']);
    }

    #[Test]
    public function score_rejected_assignment_is_ignored(): void
    {
        $result = $this->applier->apply('score', 'assignment_completed', ['score' => 40.0, 'status' => 'rejected'], 70.0, 1, ['raw_sum' => 70.0]);
        $this->assertSame(70.0, $result['value']);
    }

    #[Test]
    public function score_ignores_event_with_no_score_field(): void
    {
        $result = $this->applier->apply('score', 'quiz_attempt', [], 60.0, 3, ['raw_sum' => 180.0]);
        $this->assertSame(60.0, $result['value']);
        $this->assertSame(3, $result['count']);
    }

    // ─── Activity KPI ─────────────────────────────────────────────────────────

    #[Test]
    public function activity_user_login_adds_correct_points(): void
    {
        $result = $this->applier->apply('activity', 'user_login', [], 0.0, 0, null);
        $this->assertSame((float) KpiEventIncrementalApplier::ACTIVITY_POINTS['user_login'], $result['value']);
        $this->assertSame(1, $result['count']);
    }

    #[Test]
    public function activity_course_completed_adds_correct_points(): void
    {
        $result = $this->applier->apply('activity', 'course_completed', [], 0.0, 0, null);
        $this->assertSame((float) KpiEventIncrementalApplier::ACTIVITY_POINTS['course_completed'], $result['value']);
    }

    #[Test]
    public function activity_accumulated_points_cap_at_100(): void
    {
        // Start at 95 points, add a course_completed (25 pts) – should clamp to 100.
        $result = $this->applier->apply('activity', 'course_completed', [], 95.0, 10, ['accumulated_points' => 95.0]);
        $this->assertSame(100.0, $result['value']);
        $this->assertGreaterThan(100.0, $result['checkpoint']['accumulated_points']); // raw uncapped
    }

    #[Test]
    public function activity_unknown_event_type_is_skipped(): void
    {
        $result = $this->applier->apply('activity', 'unknown_event', [], 20.0, 2, ['accumulated_points' => 20.0]);
        $this->assertSame(20.0, $result['value']);
        $this->assertSame(2, $result['count']);
    }

    #[Test]
    public function activity_all_event_types_contribute_different_point_values(): void
    {
        $events = ['user_login', 'lesson_completed', 'quiz_attempt', 'assignment_completed', 'course_completed'];
        $expectedTotals = [];
        $acc = 0;
        foreach ($events as $type) {
            $acc += KpiEventIncrementalApplier::ACTIVITY_POINTS[$type];
            $expectedTotals[$type] = min(100.0, (float) $acc);
        }

        $value = 0.0;
        $count = 0;
        $checkpoint = null;
        foreach ($events as $type) {
            $res   = $this->applier->apply('activity', $type, [], $value, $count, $checkpoint);
            $value = $res['value'];
            $count = $res['count'];
            $checkpoint = $res['checkpoint'];
            $this->assertSame($expectedTotals[$type], $value, "After {$type}");
        }
    }

    // ─── Time KPI ─────────────────────────────────────────────────────────────

    #[Test]
    public function time_first_event_normalizes_correctly(): void
    {
        // 1800s / 3600s * 100 = 50 %
        $result = $this->applier->apply('time', 'assignment_completed', ['time_to_complete_seconds' => 1800], 0.0, 0, null);
        $this->assertSame(50.0, round($result['value'], 2));
        $this->assertSame(1, $result['count']);
        $this->assertSame(1800.0, $result['checkpoint']['raw_sum_seconds']);
    }

    #[Test]
    public function time_running_average_of_seconds_is_normalized(): void
    {
        // 3600s → 100%, then 0s → 50% (avg = 1800s)
        $s1 = $this->applier->apply('time', 'assignment_completed', ['time_to_complete_seconds' => 3600], 0.0, 0, null);
        $s2 = $this->applier->apply('time', 'assignment_completed', ['time_to_complete_seconds' => 0], $s1['value'], $s1['count'], $s1['checkpoint']);

        $this->assertSame(100.0, $s1['value']);
        $this->assertSame(50.0, $s2['value']);
        $this->assertSame(2, $s2['count']);
    }

    #[Test]
    public function time_caps_at_100_for_very_long_assignments(): void
    {
        $result = $this->applier->apply('time', 'assignment_completed', ['time_to_complete_seconds' => 99999], 0.0, 0, null);
        $this->assertSame(100.0, $result['value']);
    }

    #[Test]
    public function time_ignores_negative_seconds(): void
    {
        $result = $this->applier->apply('time', 'assignment_completed', ['time_to_complete_seconds' => -1], 55.0, 2, ['raw_sum_seconds' => 3960.0]);
        $this->assertSame(55.0, $result['value']);
        $this->assertSame(2, $result['count']);
    }

    #[Test]
    public function time_ignores_missing_seconds_field(): void
    {
        $result = $this->applier->apply('time', 'assignment_completed', [], 40.0, 1, ['raw_sum_seconds' => 1440.0]);
        $this->assertSame(40.0, $result['value']);
    }

    #[Test]
    public function time_ignores_irrelevant_event_types(): void
    {
        $result = $this->applier->apply('time', 'quiz_attempt', ['time_to_complete_seconds' => 3600], 50.0, 2, null);
        $this->assertSame(50.0, $result['value']);
    }

    // ─── Unknown KPI type ─────────────────────────────────────────────────────

    #[Test]
    public function unknown_kpi_type_returns_state_unchanged(): void
    {
        $result = $this->applier->apply('unknown_type', 'course_completed', ['completion_percentage' => 80], 42.0, 7, ['some' => 'data']);
        $this->assertSame(42.0, $result['value']);
        $this->assertSame(7, $result['count']);
        $this->assertSame(['some' => 'data'], $result['checkpoint']);
    }

    // ─── applyBatch ───────────────────────────────────────────────────────────

    #[Test]
    public function apply_batch_processes_all_events_in_order(): void
    {
        $events = [
            ['event_type' => 'quiz_attempt',        'payload' => ['score' => 80.0]],
            ['event_type' => 'quiz_attempt',        'payload' => ['score' => 60.0]],
            ['event_type' => 'assignment_completed','payload' => ['score' => 100.0, 'status' => 'graded']],
        ];

        $result = $this->applier->applyBatch('score', $events, 0.0, 0, null);

        // (80 + 60 + 100) / 3 = 80
        $this->assertSame(80.0, round($result['value'], 2));
        $this->assertSame(3, $result['count']);
    }

    #[Test]
    public function apply_batch_with_empty_events_returns_initial_state(): void
    {
        $result = $this->applier->applyBatch('completion', [], 55.5, 3, ['raw_sum' => 166.5]);
        $this->assertSame(55.5, $result['value']);
        $this->assertSame(3, $result['count']);
    }

    #[Test]
    public function apply_batch_handles_mixed_relevant_and_irrelevant_events(): void
    {
        // For 'completion' only 'course_completed' counts.
        $events = [
            ['event_type' => 'user_login',       'payload' => []],
            ['event_type' => 'course_completed',  'payload' => ['completion_percentage' => 80.0]],
            ['event_type' => 'quiz_attempt',       'payload' => ['score' => 90.0]],
            ['event_type' => 'course_completed',  'payload' => ['completion_percentage' => 60.0]],
        ];

        $result = $this->applier->applyBatch('completion', $events, 0.0, 0, null);

        // Only two course_completed events: (80 + 60) / 2 = 70
        $this->assertSame(70.0, round($result['value'], 2));
        $this->assertSame(2, $result['count']);
    }

    // ─── Idempotency / determinism ────────────────────────────────────────────

    #[Test]
    public function apply_is_deterministic_for_same_inputs(): void
    {
        $args = ['completion', 'course_completed', ['completion_percentage' => 75.0], 60.0, 3, ['raw_sum' => 180.0]];

        $r1 = $this->applier->apply(...$args);
        $r2 = $this->applier->apply(...$args);

        $this->assertSame($r1, $r2);
    }

    #[Test]
    public function apply_batch_produces_same_result_when_called_twice_on_identical_batches(): void
    {
        $events = [
            ['event_type' => 'quiz_attempt', 'payload' => ['score' => 55.0]],
            ['event_type' => 'quiz_attempt', 'payload' => ['score' => 85.0]],
        ];

        $r1 = $this->applier->applyBatch('score', $events, 0.0, 0, null);
        $r2 = $this->applier->applyBatch('score', $events, 0.0, 0, null);

        $this->assertSame($r1, $r2);
    }
}
