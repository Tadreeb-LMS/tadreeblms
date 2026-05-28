<?php

namespace Tests\Feature\Backend;

use App\Http\Controllers\Backend\Admin\CoursesController;
use App\Models\Auth\User;
use App\Models\Course;
use App\Models\LiveSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use ReflectionMethod;
use Tests\TestCase;

class CourseTrainerScheduleOverlapTest extends TestCase
{
    private CoursesController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = app(CoursesController::class);
    }

    /** @test */
    public function it_blocks_exact_live_session_overlap_for_the_same_trainer(): void
    {
        [$trainer] = $this->trainerWithLiveSession('2026-05-29', '12:00:00', 90);

        $this->assertTrainerHasOverlap($trainer, '2026-05-29 12:00:00', '2026-05-29 13:30:00');
    }

    /** @test */
    public function it_blocks_partial_live_session_overlap_starting_inside_existing_session(): void
    {
        [$trainer] = $this->trainerWithLiveSession('2026-05-29', '12:00:00', 90);

        $this->assertTrainerHasOverlap($trainer, '2026-05-29 12:45:00', '2026-05-29 13:15:00');
    }

    /** @test */
    public function it_blocks_partial_live_session_overlap_ending_inside_existing_session(): void
    {
        [$trainer] = $this->trainerWithLiveSession('2026-05-29', '12:00:00', 90);

        $this->assertTrainerHasOverlap($trainer, '2026-05-29 11:30:00', '2026-05-29 12:30:00');
    }

    /** @test */
    public function it_blocks_new_session_that_fully_contains_existing_session(): void
    {
        [$trainer] = $this->trainerWithLiveSession('2026-05-29', '12:00:00', 90);

        $this->assertTrainerHasOverlap($trainer, '2026-05-29 11:00:00', '2026-05-29 14:00:00');
    }

    /** @test */
    public function it_allows_adjacent_session_starting_when_existing_session_ends(): void
    {
        [$trainer] = $this->trainerWithLiveSession('2026-05-29', '12:00:00', 90);

        $this->assertTrainerHasNoOverlap($trainer, '2026-05-29 13:30:00', '2026-05-29 14:30:00');
    }

    /** @test */
    public function it_allows_adjacent_session_ending_when_existing_session_starts(): void
    {
        [$trainer] = $this->trainerWithLiveSession('2026-05-29', '12:00:00', 90);

        $this->assertTrainerHasNoOverlap($trainer, '2026-05-29 11:00:00', '2026-05-29 12:00:00');
    }

    /** @test */
    public function it_allows_same_time_for_a_different_trainer(): void
    {
        $firstTrainer = factory(User::class)->create();
        $secondTrainer = factory(User::class)->create();
        $this->courseWithTrainerAndLiveSession($firstTrainer, '2026-05-29', '12:00:00', 90);

        $this->assertTrainerHasNoOverlap($secondTrainer, '2026-05-29 12:00:00', '2026-05-29 13:30:00');
    }

    /** @test */
    public function it_ignores_sessions_from_the_course_being_updated(): void
    {
        [$trainer, $course] = $this->trainerWithLiveSession('2026-05-29', '12:00:00', 90);

        $this->assertTrainerHasNoOverlap($trainer, '2026-05-29 12:00:00', '2026-05-29 13:30:00', $course->id);
    }

    /** @test */
    public function it_blocks_overlap_against_legacy_single_meeting_courses(): void
    {
        $trainer = factory(User::class)->create();
        $course = $this->course([
            'meeting_start_at' => '2026-05-29 12:00:00',
            'meeting_duration' => 90,
        ]);
        DB::table('course_user')->insert(['course_id' => $course->id, 'user_id' => $trainer->id]);

        $this->assertTrainerHasOverlap($trainer, '2026-05-29 12:30:00', '2026-05-29 13:00:00');
    }

    /** @test */
    public function it_blocks_weekly_schedule_generation_when_any_generated_session_overlaps(): void
    {
        [$trainer, $existingCourse] = $this->trainerWithLiveSession('2026-05-29', '12:00:00', 90);

        $request = new Request([
            'schedule_type' => 'weekly',
            'start_date' => '2026-05-29',
            'expire_at' => '2026-06-12',
            'weekly_days' => [5],
            'weekly_time' => '12:30',
            'weekly_duration' => 60,
        ]);

        $this->expectException(ValidationException::class);

        $this->invokePrivate('validateTrainerScheduleAvailability', [$request, [$trainer->id], $existingCourse->id + 1]);
    }

    /** @test */
    public function overlap_validation_message_includes_conflicting_course_and_session_details(): void
    {
        [$trainer, $existingCourse] = $this->trainerWithLiveSession('2026-05-29', '12:00:00', 90, [
            'title' => 'Health and Safety Fundamentals',
        ]);

        $request = new Request([
            'schedule_type' => 'weekly',
            'start_date' => '2026-05-29',
            'expire_at' => '2026-06-12',
            'weekly_days' => [5],
            'weekly_time' => '12:30',
            'weekly_duration' => 60,
        ]);

        try {
            $this->invokePrivate('validateTrainerScheduleAvailability', [$request, [$trainer->id], $existingCourse->id + 1]);
            $this->fail('Expected overlap validation to fail.');
        } catch (ValidationException $exception) {
            $message = $exception->errors()['schedule_type'][0];

            $this->assertStringContainsString('Trainer is already assigned for another course at this time.', $message);
            $this->assertStringContainsString('Health and Safety Fundamentals', $message);
            $this->assertStringContainsString('2026-05-29', $message);
            $this->assertStringContainsString('12:00', $message);
            $this->assertStringContainsString('13:30', $message);
            $this->assertStringContainsString('Requested session: 2026-05-29 12:30 to 13:30', $message);
        }
    }

    /** @test */
    public function it_allows_weekly_schedule_generation_when_generated_sessions_do_not_overlap(): void
    {
        [$trainer, $existingCourse] = $this->trainerWithLiveSession('2026-05-29', '12:00:00', 90);

        $request = new Request([
            'schedule_type' => 'weekly',
            'start_date' => '2026-05-29',
            'expire_at' => '2026-06-12',
            'weekly_days' => [5],
            'weekly_time' => '14:00',
            'weekly_duration' => 60,
        ]);

        $this->invokePrivate('validateTrainerScheduleAvailability', [$request, [$trainer->id], $existingCourse->id + 1]);

        $this->assertTrue(true);
    }

    /** @test */
    public function it_blocks_daily_schedule_when_any_generated_session_overlaps_same_trainer(): void
    {
        [$trainer, $existingCourse] = $this->trainerWithLiveSession('2026-05-30', '09:00:00', 90);

        $request = new Request([
            'schedule_type' => 'daily',
            'start_date' => '2026-05-29',
            'expire_at' => '2026-05-31',
            'daily_time' => '09:30',
            'daily_duration' => 60,
            'daily_repeat' => 'every_day',
        ]);

        $this->expectException(ValidationException::class);

        $this->invokePrivate('validateTrainerScheduleAvailability', [$request, [$trainer->id], $existingCourse->id + 1]);
    }

    /** @test */
    public function it_allows_daily_schedule_when_generated_sessions_do_not_overlap_same_trainer(): void
    {
        [$trainer, $existingCourse] = $this->trainerWithLiveSession('2026-05-30', '09:00:00', 90);

        $request = new Request([
            'schedule_type' => 'daily',
            'start_date' => '2026-05-29',
            'expire_at' => '2026-05-31',
            'daily_time' => '11:00',
            'daily_duration' => 60,
            'daily_repeat' => 'every_day',
        ]);

        $this->invokePrivate('validateTrainerScheduleAvailability', [$request, [$trainer->id], $existingCourse->id + 1]);

        $this->assertTrue(true);
    }

    /** @test */
    public function daily_weekdays_only_does_not_generate_weekend_sessions_that_would_overlap(): void
    {
        [$trainer, $existingCourse] = $this->trainerWithLiveSession('2026-05-30', '09:00:00', 90);

        $request = new Request([
            'schedule_type' => 'daily',
            'start_date' => '2026-05-29',
            'expire_at' => '2026-05-31',
            'daily_time' => '09:30',
            'daily_duration' => 60,
            'daily_repeat' => 'weekdays',
        ]);

        $this->invokePrivate('validateTrainerScheduleAvailability', [$request, [$trainer->id], $existingCourse->id + 1]);

        $this->assertTrue(true);
    }

    /** @test */
    public function it_blocks_custom_schedule_when_one_custom_session_overlaps_same_trainer(): void
    {
        [$trainer, $existingCourse] = $this->trainerWithLiveSession('2026-05-30', '09:00:00', 90);

        $request = new Request([
            'schedule_type' => 'custom',
            'start_date' => '2026-05-29',
            'expire_at' => '2026-05-31',
            'custom_dates' => ['2026-05-29', '2026-05-30'],
            'custom_times' => ['15:00', '09:30'],
            'custom_durations' => [60, 60],
        ]);

        $this->expectException(ValidationException::class);

        $this->invokePrivate('validateTrainerScheduleAvailability', [$request, [$trainer->id], $existingCourse->id + 1]);
    }

    /** @test */
    public function it_allows_custom_schedule_when_all_custom_sessions_are_clear_same_trainer(): void
    {
        [$trainer, $existingCourse] = $this->trainerWithLiveSession('2026-05-30', '09:00:00', 90);

        $request = new Request([
            'schedule_type' => 'custom',
            'start_date' => '2026-05-29',
            'expire_at' => '2026-05-31',
            'custom_dates' => ['2026-05-29', '2026-05-30'],
            'custom_times' => ['15:00', '11:00'],
            'custom_durations' => [60, 60],
        ]);

        $this->invokePrivate('validateTrainerScheduleAvailability', [$request, [$trainer->id], $existingCourse->id + 1]);

        $this->assertTrue(true);
    }

    /** @test */
    public function it_blocks_daily_schedule_against_legacy_single_meeting_for_same_trainer(): void
    {
        $trainer = factory(User::class)->create();
        $course = $this->course([
            'meeting_start_at' => '2026-05-30 09:00:00',
            'meeting_duration' => 90,
        ]);
        DB::table('course_user')->insert(['course_id' => $course->id, 'user_id' => $trainer->id]);

        $request = new Request([
            'schedule_type' => 'daily',
            'start_date' => '2026-05-29',
            'expire_at' => '2026-05-31',
            'daily_time' => '09:30',
            'daily_duration' => 60,
            'daily_repeat' => 'every_day',
        ]);

        $this->expectException(ValidationException::class);

        $this->invokePrivate('validateTrainerScheduleAvailability', [$request, [$trainer->id], $course->id + 1]);
    }

    /** @test */
    public function daily_schedule_generation_creates_expected_every_day_sessions(): void
    {
        $request = new Request([
            'schedule_type' => 'daily',
            'start_date' => '2026-05-29',
            'expire_at' => '2026-05-31',
            'daily_time' => '10:00',
            'daily_duration' => 45,
            'daily_repeat' => 'every_day',
        ]);

        $course = new Course();
        $course->start_date = $request->start_date;
        $course->expire_at = $request->expire_at;

        $sessions = $this->invokePrivate('buildRequestedLiveSessions', [$course, $request]);

        $this->assertSame([
            ['date' => '2026-05-29', 'time' => '10:00', 'duration' => 45],
            ['date' => '2026-05-30', 'time' => '10:00', 'duration' => 45],
            ['date' => '2026-05-31', 'time' => '10:00', 'duration' => 45],
        ], $sessions);
    }

    /** @test */
    public function custom_schedule_generation_ignores_sessions_outside_course_date_range(): void
    {
        $request = new Request([
            'schedule_type' => 'custom',
            'start_date' => '2026-05-29',
            'expire_at' => '2026-05-31',
            'custom_dates' => ['2026-05-28', '2026-05-30', '2026-06-01'],
            'custom_times' => ['10:00', '11:00', '12:00'],
            'custom_durations' => [30, 45, 60],
        ]);

        $course = new Course();
        $course->start_date = $request->start_date;
        $course->expire_at = $request->expire_at;

        $sessions = $this->invokePrivate('buildRequestedLiveSessions', [$course, $request]);

        $this->assertSame([
            ['date' => '2026-05-30', 'time' => '11:00', 'duration' => 45],
        ], $sessions);
    }

    /** @test */
    public function weekly_schedule_generation_uses_request_dates_when_validating_unsaved_course_data(): void
    {
        $request = new Request([
            'schedule_type' => 'weekly',
            'start_date' => '2026-05-29',
            'expire_at' => '2026-06-12',
            'weekly_days' => [5],
            'weekly_time' => '14:00',
            'weekly_duration' => 60,
        ]);

        $course = new Course();
        $course->start_date = $request->start_date;
        $course->expire_at = $request->expire_at;

        $sessions = $this->invokePrivate('buildRequestedLiveSessions', [$course, $request]);

        $this->assertSame([
            ['date' => '2026-05-29', 'time' => '14:00', 'duration' => 60],
            ['date' => '2026-06-05', 'time' => '14:00', 'duration' => 60],
            ['date' => '2026-06-12', 'time' => '14:00', 'duration' => 60],
        ], $sessions);
    }

    private function assertTrainerHasOverlap(User $trainer, string $start, string $end, ?int $ignoreCourseId = null): void
    {
        $this->assertTrue($this->trainerHasOverlap($trainer, $start, $end, $ignoreCourseId));
    }

    private function assertTrainerHasNoOverlap(User $trainer, string $start, string $end, ?int $ignoreCourseId = null): void
    {
        $this->assertFalse($this->trainerHasOverlap($trainer, $start, $end, $ignoreCourseId));
    }

    private function trainerHasOverlap(User $trainer, string $start, string $end, ?int $ignoreCourseId = null): bool
    {
        return $this->invokePrivate('trainerHasOverlappingSession', [
            [$trainer->id],
            Carbon::parse($start),
            Carbon::parse($end),
            $ignoreCourseId,
        ]);
    }

    private function trainerWithLiveSession(string $date, string $time, int $duration, array $courseAttributes = []): array
    {
        $trainer = factory(User::class)->create();
        $course = $this->courseWithTrainerAndLiveSession($trainer, $date, $time, $duration, $courseAttributes);

        return [$trainer, $course];
    }

    private function courseWithTrainerAndLiveSession(User $trainer, string $date, string $time, int $duration, array $courseAttributes = []): Course
    {
        $course = $this->course($courseAttributes);
        DB::table('course_user')->insert(['course_id' => $course->id, 'user_id' => $trainer->id]);

        LiveSession::create([
            'course_id' => $course->id,
            'provider' => 'seed',
            'session_date' => $date,
            'session_time' => $time,
            'duration' => $duration,
            'recurrence_type' => 'weekly',
            'created_by' => $trainer->id,
        ]);

        return $course;
    }

    private function course(array $attributes = []): Course
    {
        return factory(Course::class)->create(array_merge([
            'title' => 'Overlap Test Course ' . uniqid(),
            'slug' => 'overlap-test-course-' . uniqid(),
            'category_id' => 1,
            'course_image' => 'placeholder-1.jpg',
            'description' => 'Overlap detection test course.',
            'price' => 0,
            'start_date' => '2026-05-29',
            'expire_at' => '2026-06-12',
            'published' => 1,
            'is_online' => 'Offline',
        ], $attributes));
    }

    private function invokePrivate(string $method, array $args = [])
    {
        $reflection = new ReflectionMethod($this->controller, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($this->controller, $args);
    }
}
