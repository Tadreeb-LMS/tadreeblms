<?php

namespace Tests\Feature;

use App\Models\Auth\User;
use App\Models\Course;
use App\Models\LiveSession;
use App\Models\LiveSessionAttendance;
use App\Models\Stripe\SubscribeCourse;
use Carbon\Carbon;
use Tests\TestCase;

class CourseLiveSessionAttendanceApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.key' => 'base64:' . base64_encode(str_repeat('a', 32))]);

        $this->withoutMiddleware();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /** @test */
    public function it_records_scheduled_session_attendance_before_returning_the_meeting_link()
    {
        Carbon::setTestNow('2026-06-04 09:55:00');

        $user = factory(User::class)->create();
        $course = $this->course();
        $session = $this->liveSession($course, [
            'session_date' => '2026-06-04',
            'session_time' => '10:00:00',
            'meeting_link' => 'https://meet.example.com/session',
        ]);
        $this->subscribe($user, $course);

        $response = $this->actingAs($user)->postJson(route('courses.attendance', $course->slug), [
            'session_id' => $session->id,
        ]);

        $response->assertOk()
            ->assertJson(['meeting_link' => 'https://meet.example.com/session']);

        $this->assertDatabaseHas('live_session_attendances', [
            'live_session_id' => $session->id,
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function it_does_not_create_duplicate_attendance_when_join_is_clicked_twice()
    {
        Carbon::setTestNow('2026-06-04 09:55:00');

        $user = factory(User::class)->create();
        $course = $this->course();
        $session = $this->liveSession($course, [
            'session_date' => '2026-06-04',
            'session_time' => '10:00:00',
            'meeting_link' => 'https://teams.example.com/session',
        ]);
        $this->subscribe($user, $course);

        $this->actingAs($user)->postJson(route('courses.attendance', $course->slug), ['session_id' => $session->id])->assertOk();
        $this->actingAs($user)->postJson(route('courses.attendance', $course->slug), ['session_id' => $session->id])->assertOk();

        $this->assertSame(1, LiveSessionAttendance::where('live_session_id', $session->id)
            ->where('user_id', $user->id)
            ->count());
    }

    /** @test */
    public function it_blocks_redirect_when_the_scheduled_session_has_no_meeting_link()
    {
        Carbon::setTestNow('2026-06-04 09:55:00');

        $user = factory(User::class)->create();
        $course = $this->course();
        $session = $this->liveSession($course, [
            'session_date' => '2026-06-04',
            'session_time' => '10:00:00',
            'meeting_link' => null,
        ]);
        $this->subscribe($user, $course);

        $response = $this->actingAs($user)->postJson(route('courses.attendance', $course->slug), [
            'session_id' => $session->id,
        ]);

        $response->assertStatus(422);
        $this->assertSame(0, LiveSessionAttendance::where('live_session_id', $session->id)->count());
    }

    /** @test */
    public function it_marks_single_meeting_course_attendance_before_returning_the_meeting_link()
    {
        $user = factory(User::class)->create();
        $course = $this->course([
            'schedule_type' => 'single',
            'meeting_join_url' => 'https://zoom.example.com/course',
        ]);
        $subscription = $this->subscribe($user, $course);

        $response = $this->actingAs($user)->postJson(route('courses.attendance', $course->slug));

        $response->assertOk()
            ->assertJson(['meeting_link' => 'https://zoom.example.com/course']);

        $this->assertDatabaseHas('subscribe_courses', [
            'id' => $subscription->id,
            'is_attended' => 1,
        ]);
    }

    private function course(array $attributes = []): Course
    {
        return Course::create(array_merge([
            'title' => 'Live Course',
            'category_id' => 1,
            'slug' => 'live-course-' . uniqid(),
            'description' => 'Live course description',
            'published' => 1,
            'is_online' => 'Offline',
            'schedule_type' => 'daily',
        ], $attributes));
    }

    private function liveSession(Course $course, array $attributes = []): LiveSession
    {
        return LiveSession::create(array_merge([
            'course_id' => $course->id,
            'provider' => 'zoom',
            'session_date' => Carbon::today()->toDateString(),
            'session_time' => '10:00:00',
            'meeting_link' => 'https://meet.example.com/session',
            'duration' => 60,
            'recurrence_type' => 'daily',
        ], $attributes));
    }

    private function subscribe(User $user, Course $course): SubscribeCourse
    {
        return SubscribeCourse::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 1,
            'has_assesment' => 0,
            'has_feedback' => 0,
        ]);
    }
}
