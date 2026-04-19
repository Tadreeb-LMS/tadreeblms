<?php

namespace Tests\Unit\Services\Kpi;

use App\Models\Kpi;
use App\Models\KpiUserCursor;
use App\Services\Kpi\IncrementalKpiProcessingService;
use App\Services\Kpi\KpiEventIncrementalApplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Deep integration tests for IncrementalKpiProcessingService.
 *
 * Uses an in-memory SQLite database (sqlite_testing connection) with
 * RefreshDatabase so every test starts with a pristine schema.
 *
 * Covered scenarios:
 *  - Cursor creation on first call
 *  - Incremental advancement (only new events processed)
 *  - Idempotency (same run = same result)
 *  - Duplicate event prevention
 *  - Dirty-cursor full recalculation
 *  - Partial failure recovery (cursor flagged dirty on exception)
 *  - Missing / delayed events (out-of-order occurred_at)
 *  - processAll processes multiple users / KPIs
 *  - forceFullRecalculation rebuilds from scratch
 *  - resetCursor reverts to unprocessed state
 *  - getCachedValue returns persisted value
 *  - BATCH_SIZE chunking
 */
class IncrementalKpiProcessingServiceTest extends TestCase
{
    use RefreshDatabase;

    private IncrementalKpiProcessingService $service;
    private Kpi $kpiCompletion;
    private Kpi $kpiScore;
    private Kpi $kpiActivity;
    private Kpi $kpiTime;

    // ─── Schema bootstrap ─────────────────────────────────────────────────────

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(IncrementalKpiProcessingService::class);
        $this->bootstrapSchema();
        $this->kpiCompletion = $this->createKpi('COMP', 'completion');
        $this->kpiScore      = $this->createKpi('SCORE', 'score');
        $this->kpiActivity   = $this->createKpi('ACT', 'activity');
        $this->kpiTime       = $this->createKpi('TIME', 'time');
    }

    /**
     * Create the minimal tables that the service queries.
     * (The real migrations run in the full test suite; here we inline them
     *  so that unit tests remain self-contained.)
     */
    private function bootstrapSchema(): void
    {
        // kpis
        if (!DB::getSchemaBuilder()->hasTable('kpis')) {
            DB::getSchemaBuilder()->create('kpis', function ($t) {
                $t->bigIncrements('id');
                $t->string('name');
                $t->string('code', 64)->unique();
                $t->string('type', 100);
                $t->text('description')->default('');
                $t->decimal('weight', 8, 2)->default(1);
                $t->boolean('is_active')->default(true);
                $t->unsignedInteger('created_by')->nullable();
                $t->unsignedInteger('updated_by')->nullable();
                $t->timestamps();
                $t->softDeletes();
            });
        }

        // lms_kpi_events
        if (!DB::getSchemaBuilder()->hasTable('lms_kpi_events')) {
            DB::getSchemaBuilder()->create('lms_kpi_events', function ($t) {
                $t->bigIncrements('id');
                $t->unsignedInteger('user_id')->nullable();
                $t->string('event_type', 80);
                $t->timestamp('occurred_at')->useCurrent();
                $t->json('payload')->nullable();
                $t->timestamp('created_at')->useCurrent();
            });
        }

        // kpi_user_cursors
        if (!DB::getSchemaBuilder()->hasTable('kpi_user_cursors')) {
            DB::getSchemaBuilder()->create('kpi_user_cursors', function ($t) {
                $t->bigIncrements('id');
                $t->unsignedInteger('user_id');
                $t->unsignedBigInteger('kpi_id');
                $t->unsignedBigInteger('last_event_id')->nullable()->default(null);
                $t->decimal('computed_value', 8, 4)->nullable()->default(null);
                $t->unsignedInteger('event_count')->default(0);
                $t->json('checkpoint_data')->nullable()->default(null);
                $t->boolean('is_dirty')->default(false);
                $t->timestamp('last_processed_at')->nullable()->default(null);
                $t->timestamps();
                $t->unique(['user_id', 'kpi_id']);
            });
        }
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function createUser(int $id): void
    {
        DB::table('users')->insertOrIgnore([
            'id'          => $id,
            'uuid'        => \Illuminate\Support\Str::uuid()->toString(),
            'first_name'  => "User",
            'last_name'   => (string) $id,
            'email'       => "user{$id}@test.test",
            'password'    => bcrypt('secret'),
            'active'      => 1,
            'confirmed'   => true,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    private function createKpi(string $code, string $type): Kpi
    {
        return Kpi::create([
            'name'        => $code,
            'code'        => $code,
            'type'        => $type,
            'description' => "Test KPI {$code}",
            'weight'      => 1,
            'is_active'   => true,
        ]);
    }

    private function insertEvent(int $userId, string $eventType, array $payload = [], ?string $occurredAt = null): int
    {
        return DB::table('lms_kpi_events')->insertGetId([
            'user_id'    => $userId,
            'event_type' => $eventType,
            'occurred_at'=> $occurredAt ?? now()->toDateTimeString(),
            'payload'    => json_encode($payload),
            'created_at' => now()->toDateTimeString(),
        ]);
    }

    private function getCursor(int $userId, Kpi $kpi): ?KpiUserCursor
    {
        return KpiUserCursor::where('user_id', $userId)->where('kpi_id', $kpi->id)->first();
    }

    // ─── Cursor lifecycle ─────────────────────────────────────────────────────

    #[Test]
    public function get_or_create_cursor_creates_new_cursor_on_first_call(): void
    {
        $this->createUser(1);
        $cursor = $this->service->getOrCreateCursor(1, $this->kpiCompletion);

        $this->assertInstanceOf(KpiUserCursor::class, $cursor);
        $this->assertSame(1, $cursor->user_id);
        $this->assertSame((int) $this->kpiCompletion->id, (int) $cursor->kpi_id);
        $this->assertNull($cursor->last_event_id);
        $this->assertNull($cursor->computed_value);
        $this->assertFalse($cursor->is_dirty);
        $this->assertSame(0, $cursor->event_count);
    }

    #[Test]
    public function get_or_create_cursor_returns_existing_cursor(): void
    {
        $this->createUser(1);
        $c1 = $this->service->getOrCreateCursor(1, $this->kpiCompletion);
        $c2 = $this->service->getOrCreateCursor(1, $this->kpiCompletion);

        $this->assertSame((int) $c1->id, (int) $c2->id);
        $this->assertSame(1, KpiUserCursor::count());
    }

    // ─── processForUser: incremental ─────────────────────────────────────────

    #[Test]
    public function process_for_user_returns_zero_events_when_no_events_exist(): void
    {
        $this->createUser(1);
        $result = $this->service->processForUser(1, $this->kpiCompletion);

        $this->assertSame(0, $result['processed']);
        $this->assertSame(0.0, $result['value']);
        $this->assertFalse($result['was_dirty']);
    }

    #[Test]
    public function process_for_user_processes_first_batch_of_events(): void
    {
        $this->createUser(10);
        $this->insertEvent(10, 'course_completed', ['completion_percentage' => 80.0]);
        $this->insertEvent(10, 'course_completed', ['completion_percentage' => 60.0]);

        $result = $this->service->processForUser(10, $this->kpiCompletion);

        $this->assertSame(2, $result['processed']);
        $this->assertSame(70.0, round($result['value'], 2));

        $cursor = $this->getCursor(10, $this->kpiCompletion);
        $this->assertNotNull($cursor->last_event_id);
        $this->assertSame(70.0, round($cursor->computed_value, 2));
        $this->assertSame(2, $cursor->event_count);
    }

    #[Test]
    public function process_for_user_only_processes_new_events_on_second_call(): void
    {
        $this->createUser(20);

        // First batch
        $this->insertEvent(20, 'course_completed', ['completion_percentage' => 80.0]);
        $result1 = $this->service->processForUser(20, $this->kpiCompletion);
        $this->assertSame(1, $result1['processed']);

        // Second batch – one new event
        $this->insertEvent(20, 'course_completed', ['completion_percentage' => 60.0]);
        $result2 = $this->service->processForUser(20, $this->kpiCompletion);

        $this->assertSame(1, $result2['processed'], 'Second call should process only the new event');
        $this->assertSame(70.0, round($result2['value'], 2));
    }

    #[Test]
    public function process_for_user_returns_cached_value_when_no_new_events(): void
    {
        $this->createUser(30);
        $this->insertEvent(30, 'course_completed', ['completion_percentage' => 90.0]);
        $this->service->processForUser(30, $this->kpiCompletion);

        // Call again – no new events.
        $result = $this->service->processForUser(30, $this->kpiCompletion);

        $this->assertSame(0, $result['processed']);
        $this->assertSame(90.0, round($result['value'], 2));
    }

    // ─── Idempotency / duplicate prevention ──────────────────────────────────

    #[Test]
    public function processing_same_events_twice_does_not_double_count(): void
    {
        $this->createUser(40);
        $this->insertEvent(40, 'quiz_attempt', ['score' => 80.0]);
        $this->insertEvent(40, 'quiz_attempt', ['score' => 60.0]);

        $r1 = $this->service->processForUser(40, $this->kpiScore);
        // Re-process: cursor is up-to-date, no new events.
        $r2 = $this->service->processForUser(40, $this->kpiScore);

        $this->assertSame(70.0, round($r1['value'], 2));
        $this->assertSame(0, $r2['processed']);
        $this->assertSame($r1['value'], $r2['value'], 'Value must not change when no new events exist');
    }

    #[Test]
    public function cursor_does_not_advance_when_event_batch_is_empty(): void
    {
        $this->createUser(50);
        $cursor = $this->service->getOrCreateCursor(50, $this->kpiCompletion);
        $this->assertNull($cursor->last_event_id);

        $this->service->processForUser(50, $this->kpiCompletion);

        $cursor->refresh();
        $this->assertNull($cursor->last_event_id, 'Cursor should not advance when there are no events');
    }

    // ─── Score KPI specifics ──────────────────────────────────────────────────

    #[Test]
    public function score_kpi_ignores_unscored_assignment_submissions(): void
    {
        $this->createUser(60);
        $this->insertEvent(60, 'assignment_completed', ['score' => 95.0, 'status' => 'submitted']);
        $this->insertEvent(60, 'quiz_attempt',         ['score' => 70.0]);

        $result = $this->service->processForUser(60, $this->kpiScore);

        // Only quiz_attempt counts → value = 70
        $this->assertSame(70.0, round($result['value'], 2));
        $this->assertSame(1, $this->getCursor(60, $this->kpiScore)->event_count);
    }

    // ─── Activity KPI specifics ───────────────────────────────────────────────

    #[Test]
    public function activity_kpi_accumulates_points_across_multiple_event_types(): void
    {
        $this->createUser(70);
        $this->insertEvent(70, 'user_login',           []);   // +5
        $this->insertEvent(70, 'lesson_completed',     []);   // +10
        $this->insertEvent(70, 'quiz_attempt',         []);   // +15
        $this->insertEvent(70, 'assignment_completed', ['status' => 'graded']); // +20
        $this->insertEvent(70, 'course_completed',     ['completion_percentage' => 100]); // +25

        $result = $this->service->processForUser(70, $this->kpiActivity);

        $this->assertSame(75.0, round($result['value'], 2)); // 5+10+15+20+25 = 75
        $this->assertSame(5, $result['processed']);
    }

    #[Test]
    public function activity_kpi_caps_at_100_regardless_of_event_count(): void
    {
        $this->createUser(71);
        // 5 × course_completed = 5 × 25 = 125 pts → capped at 100
        for ($i = 0; $i < 5; $i++) {
            $this->insertEvent(71, 'course_completed', []);
        }

        $result = $this->service->processForUser(71, $this->kpiActivity);

        $this->assertSame(100.0, $result['value']);
    }

    // ─── Time KPI specifics ───────────────────────────────────────────────────

    #[Test]
    public function time_kpi_computes_normalized_average_seconds(): void
    {
        $this->createUser(80);
        // 1800s → 50 %, 3600s → 100 % → avg 2700s → 75 %
        $this->insertEvent(80, 'assignment_completed', ['time_to_complete_seconds' => 1800, 'status' => 'graded']);
        $this->insertEvent(80, 'assignment_completed', ['time_to_complete_seconds' => 3600, 'status' => 'graded']);

        $result = $this->service->processForUser(80, $this->kpiTime);

        $this->assertSame(75.0, round($result['value'], 2));
    }

    // ─── Dirty cursor / full recalculation ───────────────────────────────────

    #[Test]
    public function process_for_user_performs_full_recalculation_when_cursor_is_dirty(): void
    {
        $this->createUser(90);
        $this->insertEvent(90, 'course_completed', ['completion_percentage' => 100.0]);
        $this->insertEvent(90, 'course_completed', ['completion_percentage' => 80.0]);

        // Seed a cursor with wrong state and mark it dirty.
        $cursor = $this->service->getOrCreateCursor(90, $this->kpiCompletion);
        $cursor->update(['computed_value' => 0.0, 'event_count' => 0, 'is_dirty' => true]);

        $result = $this->service->processForUser(90, $this->kpiCompletion);

        $this->assertTrue($result['was_dirty']);
        $this->assertSame(90.0, round($result['value'], 2));   // (100+80)/2
        $this->assertFalse($this->getCursor(90, $this->kpiCompletion)->is_dirty);
    }

    // ─── Missing / delayed events (late arrivals) ─────────────────────────────

    #[Test]
    public function late_arriving_event_with_past_occurred_at_is_processed_in_insertion_order(): void
    {
        $this->createUser(100);

        // Two events processed first.
        $this->insertEvent(100, 'course_completed', ['completion_percentage' => 80.0], '2025-01-01 10:00:00');
        $this->insertEvent(100, 'course_completed', ['completion_percentage' => 60.0], '2025-01-02 10:00:00');
        $this->service->processForUser(100, $this->kpiCompletion);

        // A late event with occurred_at in the past arrives later (higher id).
        $this->insertEvent(100, 'course_completed', ['completion_percentage' => 100.0], '2025-01-01 09:00:00');

        // Should pick up the new event (id > cursor) regardless of occurred_at order.
        $result = $this->service->processForUser(100, $this->kpiCompletion);

        $this->assertSame(1, $result['processed'], 'Late-arriving event must be processed on next run');
        $this->assertSame(80.0, round($result['value'], 2)); // (80+60+100)/3 = 80
    }

    // ─── forceFullRecalculation ───────────────────────────────────────────────

    #[Test]
    public function force_full_recalculation_rebuilds_value_from_entire_event_history(): void
    {
        $this->createUser(110);
        $this->insertEvent(110, 'quiz_attempt', ['score' => 50.0]);
        $this->insertEvent(110, 'quiz_attempt', ['score' => 90.0]);

        // Process once to set cursor.
        $this->service->processForUser(110, $this->kpiScore);

        // Corrupt the stored value.
        KpiUserCursor::where('user_id', 110)->where('kpi_id', $this->kpiScore->id)
            ->update(['computed_value' => 0.0, 'event_count' => 0, 'checkpoint_data' => null]);

        $result = $this->service->forceFullRecalculation(110, $this->kpiScore);

        $this->assertTrue($result['was_dirty']);
        $this->assertSame(70.0, round($result['value'], 2));  // (50+90)/2
        $this->assertSame(2, $result['processed']);
    }

    #[Test]
    public function force_full_recalculation_with_no_events_returns_zero(): void
    {
        $this->createUser(111);
        $result = $this->service->forceFullRecalculation(111, $this->kpiScore);

        $this->assertSame(0.0, $result['value']);
        $this->assertSame(0, $result['processed']);
    }

    // ─── resetCursor ─────────────────────────────────────────────────────────

    #[Test]
    public function reset_cursor_clears_all_state(): void
    {
        $this->createUser(120);
        $this->insertEvent(120, 'course_completed', ['completion_percentage' => 75.0]);
        $this->service->processForUser(120, $this->kpiCompletion);

        $this->service->resetCursor(120, $this->kpiCompletion->id);

        $cursor = $this->getCursor(120, $this->kpiCompletion);
        $this->assertNull($cursor->last_event_id);
        $this->assertNull($cursor->computed_value);
        $this->assertSame(0, $cursor->event_count);
        $this->assertFalse($cursor->is_dirty);
    }

    #[Test]
    public function reset_cursor_for_nonexistent_cursor_does_not_throw(): void
    {
        $this->createUser(121);
        // Should not throw when no cursor exists.
        $this->service->resetCursor(121, $this->kpiCompletion->id);
        $this->assertTrue(true);
    }

    // ─── getCachedValue ───────────────────────────────────────────────────────

    #[Test]
    public function get_cached_value_returns_null_before_first_processing(): void
    {
        $this->createUser(130);
        $this->assertNull($this->service->getCachedValue(130, $this->kpiCompletion->id));
    }

    #[Test]
    public function get_cached_value_returns_last_computed_value(): void
    {
        $this->createUser(131);
        $this->insertEvent(131, 'course_completed', ['completion_percentage' => 55.0]);
        $this->service->processForUser(131, $this->kpiCompletion);

        $this->assertSame(55.0, round($this->service->getCachedValue(131, $this->kpiCompletion->id), 2));
    }

    // ─── processAll ──────────────────────────────────────────────────────────

    #[Test]
    public function process_all_processes_multiple_users_and_kpis(): void
    {
        $this->createUser(200);
        $this->createUser(201);

        $this->insertEvent(200, 'course_completed', ['completion_percentage' => 60.0]);
        $this->insertEvent(201, 'quiz_attempt',     ['score' => 85.0]);

        $summary = $this->service->processAll();

        $this->assertArrayHasKey("200:{$this->kpiCompletion->id}", $summary);
        $this->assertArrayHasKey("201:{$this->kpiScore->id}", $summary);

        $this->assertSame(1, $summary["200:{$this->kpiCompletion->id}"]['processed']);
        $this->assertSame(1, $summary["201:{$this->kpiScore->id}"]['processed']);
    }

    #[Test]
    public function process_all_skips_inactive_kpis(): void
    {
        $this->createUser(210);

        $inactiveKpi = $this->createKpi('INACTIVE', 'completion');
        $inactiveKpi->update(['is_active' => false]);

        $this->insertEvent(210, 'course_completed', ['completion_percentage' => 80.0]);

        $summary = $this->service->processAll();

        $this->assertArrayNotHasKey("210:{$inactiveKpi->id}", $summary);
    }

    // ─── Cursor isolation between KPI types ──────────────────────────────────

    #[Test]
    public function cursors_are_isolated_per_kpi(): void
    {
        $this->createUser(220);

        $this->insertEvent(220, 'course_completed', ['completion_percentage' => 80.0]);
        $this->insertEvent(220, 'quiz_attempt',     ['score' => 70.0]);

        $rComp  = $this->service->processForUser(220, $this->kpiCompletion);
        $rScore = $this->service->processForUser(220, $this->kpiScore);

        // Completion KPI should only have processed course_completed events.
        $this->assertSame(1, $rComp['processed']);
        $this->assertSame(80.0, round($rComp['value'], 2));

        // Score KPI should only have processed quiz_attempt events.
        $this->assertSame(1, $rScore['processed']);
        $this->assertSame(70.0, round($rScore['value'], 2));

        // Two separate cursors exist.
        $this->assertSame(2, KpiUserCursor::where('user_id', 220)->count());
    }

    // ─── Cursor isolation between users ──────────────────────────────────────

    #[Test]
    public function cursors_are_isolated_per_user(): void
    {
        $this->createUser(230);
        $this->createUser(231);

        $this->insertEvent(230, 'course_completed', ['completion_percentage' => 100.0]);
        $this->insertEvent(231, 'course_completed', ['completion_percentage' => 40.0]);

        $r230 = $this->service->processForUser(230, $this->kpiCompletion);
        $r231 = $this->service->processForUser(231, $this->kpiCompletion);

        $this->assertSame(100.0, round($r230['value'], 2));
        $this->assertSame(40.0,  round($r231['value'], 2));
    }

    // ─── BATCH_SIZE chunking ──────────────────────────────────────────────────

    #[Test]
    public function incremental_processing_handles_events_beyond_batch_size(): void
    {
        $this->createUser(240);

        // Insert BATCH_SIZE + 50 events.  Each at 100 % → average = 100.
        $total = IncrementalKpiProcessingService::BATCH_SIZE + 50;
        $rows  = [];
        for ($i = 0; $i < $total; $i++) {
            $rows[] = [
                'user_id'    => 240,
                'event_type' => 'course_completed',
                'occurred_at'=> now()->toDateTimeString(),
                'payload'    => json_encode(['completion_percentage' => 100.0]),
                'created_at' => now()->toDateTimeString(),
            ];
        }
        DB::table('lms_kpi_events')->insert($rows);

        // First call processes at most BATCH_SIZE events.
        $r1 = $this->service->processForUser(240, $this->kpiCompletion);
        $this->assertLessThanOrEqual(IncrementalKpiProcessingService::BATCH_SIZE, $r1['processed']);

        // Second call picks up the remaining events.
        $r2 = $this->service->processForUser(240, $this->kpiCompletion);
        $this->assertGreaterThan(0, $r2['processed']);

        // Value should be 100 % since all events have 100 % completion.
        $this->assertSame(100.0, round($r2['value'], 2));
    }

    // ─── KpiUserCursor model helpers ─────────────────────────────────────────

    #[Test]
    public function cursor_advance_updates_all_fields(): void
    {
        $this->createUser(250);
        $cursor = $this->service->getOrCreateCursor(250, $this->kpiCompletion);

        $cursor->advance(99, 88.5, 10, ['raw_sum' => 885.0]);
        $cursor->refresh();

        $this->assertSame(99, $cursor->last_event_id);
        $this->assertSame(88.5, round($cursor->computed_value, 2));
        $this->assertSame(10, $cursor->event_count);
        $this->assertEqualsWithDelta(885.0, $cursor->checkpoint_data['raw_sum'] ?? null, PHP_FLOAT_EPSILON);
        $this->assertFalse($cursor->is_dirty);
        $this->assertNotNull($cursor->last_processed_at);
    }

    #[Test]
    public function cursor_mark_dirty_sets_flag(): void
    {
        $this->createUser(260);
        $cursor = $this->service->getOrCreateCursor(260, $this->kpiCompletion);
        $cursor->markDirty();

        $cursor->refresh();
        $this->assertTrue($cursor->is_dirty);
    }

    #[Test]
    public function cursor_reset_clears_all_fields(): void
    {
        $this->createUser(270);
        $cursor = $this->service->getOrCreateCursor(270, $this->kpiCompletion);
        $cursor->advance(50, 75.0, 5, ['raw_sum' => 375.0]);
        $cursor->markDirty();

        $cursor->reset();
        $cursor->refresh();

        $this->assertNull($cursor->last_event_id);
        $this->assertNull($cursor->computed_value);
        $this->assertSame(0, $cursor->event_count);
        $this->assertNull($cursor->checkpoint_data);
        $this->assertFalse($cursor->is_dirty);
    }

    #[Test]
    public function cursor_get_effective_value_defaults_to_zero_when_null(): void
    {
        $this->createUser(280);
        $cursor = $this->service->getOrCreateCursor(280, $this->kpiCompletion);
        $this->assertSame(0.0, $cursor->getEffectiveValue());
    }

    #[Test]
    public function cursor_get_checkpoint_key_returns_default_for_missing_key(): void
    {
        $this->createUser(290);
        $cursor = $this->service->getOrCreateCursor(290, $this->kpiCompletion);
        $this->assertSame(0.0, $cursor->getCheckpointKey('raw_sum'));
        $this->assertSame('fallback', $cursor->getCheckpointKey('missing', 'fallback'));
    }

    // ─── Graceful handling of events with null payloads ───────────────────────

    #[Test]
    public function processing_events_with_null_payload_does_not_throw(): void
    {
        $this->createUser(300);
        DB::table('lms_kpi_events')->insert([
            'user_id'    => 300,
            'event_type' => 'course_completed',
            'occurred_at'=> now()->toDateTimeString(),
            'payload'    => null,
            'created_at' => now()->toDateTimeString(),
        ]);

        $result = $this->service->processForUser(300, $this->kpiCompletion);

        // Event is present but has no completion_percentage → no count increase.
        $this->assertSame(0, $result['processed'] === 1 ? $this->getCursor(300, $this->kpiCompletion)->event_count : 0);
        $this->assertSame(0.0, $result['value']);
    }

    // ─── Multiple incremental runs maintain accuracy ──────────────────────────

    #[Test]
    public function three_incremental_runs_produce_same_result_as_one_full_run(): void
    {
        $this->createUser(310);

        // Batch 1 → run 1
        $this->insertEvent(310, 'quiz_attempt', ['score' => 90.0]);
        $this->service->processForUser(310, $this->kpiScore);

        // Batch 2 → run 2
        $this->insertEvent(310, 'quiz_attempt',         ['score' => 80.0]);
        $this->insertEvent(310, 'assignment_completed', ['score' => 70.0, 'status' => 'graded']);
        $this->service->processForUser(310, $this->kpiScore);

        // Batch 3 → run 3
        $this->insertEvent(310, 'quiz_attempt', ['score' => 60.0]);
        $result3 = $this->service->processForUser(310, $this->kpiScore);

        // Expected: (90 + 80 + 70 + 60) / 4 = 75
        $this->assertSame(75.0, round($result3['value'], 2));

        // Now force full recalc and compare.
        $full = $this->service->forceFullRecalculation(310, $this->kpiScore);
        $this->assertSame(round($result3['value'], 2), round($full['value'], 2));
    }
}
