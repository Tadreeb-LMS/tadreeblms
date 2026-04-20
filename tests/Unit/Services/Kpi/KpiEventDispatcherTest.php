<?php

namespace Tests\Unit\Services\Kpi;

use App\Events\Kpi\AssignmentCompletedEvent;
use App\Events\Kpi\CourseCompletedEvent;
use App\Events\Kpi\LessonCompletedEvent;
use App\Events\Kpi\QuizAttemptedEvent;
use App\Events\Kpi\UserLoginEvent;
use App\Listeners\Kpi\RecordKpiEventListener;
use App\Services\Kpi\KpiEventDispatcher;
use App\Services\LmsEventRecorder;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class KpiEventDispatcherTest extends TestCase
{
    use RefreshDatabase;

    protected KpiEventDispatcher $dispatcher;
    protected LmsEventRecorder $recorder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dispatcher = app(KpiEventDispatcher::class);
        $this->recorder = app(LmsEventRecorder::class);

        Event::fake();
    }

    /**
     * Test that UserLoginEvent is dispatched correctly.
     */
    public function test_user_login_event_dispatches()
    {
        $event = new UserLoginEvent(
            userId: 1,
            ipAddress: '192.168.1.1',
            userAgent: 'Mozilla/5.0'
        );

        $this->dispatcher->dispatch($event);

        Event::assertDispatched(UserLoginEvent::class);
    }

    /**
     * Test that QuizAttemptedEvent contains correct payload.
     */
    public function test_quiz_attempted_event_payload()
    {
        $event = new QuizAttemptedEvent(
            userId: 1,
            quizId: 10,
            quizTitle: 'JavaScript Basics',
            score: 85.5,
            correctAnswers: 17,
            totalQuestions: 20,
            timeSpentSeconds: 1800,
            courseId: 5,
            courseTitle: 'Web Development'
        );

        $payload = $event->getPayload();

        $this->assertEquals(10, $payload['quiz_id']);
        $this->assertEquals('JavaScript Basics', $payload['quiz_title']);
        $this->assertEquals(85.5, $payload['score']);
        $this->assertEquals(17, $payload['correct_answers']);
        $this->assertEquals(20, $payload['total_questions']);
        $this->assertEquals(1800, $payload['time_spent_seconds']);
        $this->assertEquals(5, $payload['course_id']);
        $this->assertEquals('Web Development', $payload['course_title']);
    }

    /**
     * Test that CourseCompletedEvent is created with all fields.
     */
    public function test_course_completed_event_complete()
    {
        $event = new CourseCompletedEvent(
            userId: 2,
            courseId: 5,
            courseTitle: 'Advanced PHP',
            completionPercentage: 100,
            timeSpentSeconds: 7200,
            totalLessons: 12,
            completedLessons: 12,
            categoryId: 3,
            categoryName: 'Backend Development',
            score: 92.0,
            certificateIssued: true
        );

        $this->assertEquals(2, $event->getUserId());
        $this->assertEquals('course_completed', $event->getEventType());
        $this->assertInstanceOf(Carbon::class, $event->getOccurredAt());

        $payload = $event->getPayload();
        $this->assertEquals(5, $payload['course_id']);
        $this->assertEquals(100, $payload['completion_percentage']);
        $this->assertEquals(92.0, $payload['score']);
        $this->assertTrue($payload['certificate_issued']);
    }

    /**
     * Test that LessonCompletedEvent is created correctly.
     */
    public function test_lesson_completed_event()
    {
        $event = new LessonCompletedEvent(
            userId: 3,
            lessonId: 50,
            lessonTitle: 'Introduction to Databases',
            courseId: 5,
            courseTitle: 'Advanced PHP',
            position: 2,
            timeSpentSeconds: 1200,
            score: 88.0
        );

        $this->assertEquals(3, $event->getUserId());
        $this->assertEquals('lesson_completed', $event->getEventType());

        $payload = $event->getPayload();
        $this->assertEquals(50, $payload['lesson_id']);
        $this->assertEquals('Introduction to Databases', $payload['lesson_title']);
        $this->assertEquals(5, $payload['course_id']);
        $this->assertEquals(2, $payload['position']);
    }

    /**
     * Test that AssignmentCompletedEvent is created with status.
     */
    public function test_assignment_completed_event()
    {
        $event = new AssignmentCompletedEvent(
            userId: 4,
            assignmentId: 20,
            assignmentTitle: 'Build a REST API',
            courseId: 5,
            courseTitle: 'Advanced PHP',
            status: AssignmentCompletedEvent::STATUS_GRADED,
            score: 95.0,
            timeToCompleteSeconds: 3600,
            submissionDate: Carbon::now()->subHours(2)->toIso8601String(),
            gradeDate: Carbon::now()->toIso8601String()
        );

        $this->assertEquals(4, $event->getUserId());
        $this->assertEquals('assignment_completed', $event->getEventType());

        $payload = $event->getPayload();
        $this->assertEquals(20, $payload['assignment_id']);
        $this->assertEquals(AssignmentCompletedEvent::STATUS_GRADED, $payload['status']);
        $this->assertEquals(95.0, $payload['score']);
    }

    /**
     * Test that event timestamps are normalized correctly.
     */
    public function test_event_occurred_at_normalization()
    {
        // Test with Carbon instance
        $carbon = Carbon::now()->subDay();
        $event1 = new UserLoginEvent(1, null, null, $carbon);
        $this->assertTrue($event1->getOccurredAt()->equalTo($carbon));

        // Test with ISO 8601 string
        $isoString = '2024-04-08T10:30:00Z';
        $event2 = new UserLoginEvent(1, null, null, $isoString);
        $this->assertInstanceOf(Carbon::class, $event2->getOccurredAt());

        // Test with default (now)
        $event3 = new UserLoginEvent(1, null, null, null);
        $this->assertInstanceOf(Carbon::class, $event3->getOccurredAt());
        $this->assertTrue($event3->getOccurredAt()->diffInSeconds(Carbon::now()) < 5);
    }

    /**
     * Test event toArray() conversion.
     */
    public function test_event_to_array_conversion()
    {
        $event = new UserLoginEvent(
            userId: 1,
            ipAddress: '192.168.1.1',
            userAgent: 'Mozilla/5.0',
            occurredAt: '2024-04-08T10:00:00'
        );

        $array = $event->toArray();

        $this->assertIsArray($array);
        $this->assertEquals(1, $array['user_id']);
        $this->assertEquals('user_login', $array['event_type']);
        $this->assertIsString($array['occurred_at']);
        $this->assertIsArray($array['payload']);
    }

    /**
     * Test that dispatcher can enable/disable logging.
     */
    public function test_dispatcher_logging_control()
    {
        $this->assertFalse($this->dispatcher->isLoggingEnabled());

        $this->dispatcher->enableLogging();
        $this->assertTrue($this->dispatcher->isLoggingEnabled());

        $this->dispatcher->disableLogging();
        $this->assertFalse($this->dispatcher->isLoggingEnabled());
    }

    /**
     * Test that multiple events can be dispatched in sequence.
     */
    public function test_multiple_events_dispatch()
    {
        $loginEvent = new UserLoginEvent(1, '192.168.1.1');
        $quizEvent = new QuizAttemptedEvent(1, 10, 'Quiz', 80, 16, 20, 1800);
        $courseEvent = new CourseCompletedEvent(1, 5, 'Course', 100, 7200, 12, 12);

        $this->dispatcher->dispatch($loginEvent);
        $this->dispatcher->dispatch($quizEvent);
        $this->dispatcher->dispatch($courseEvent);

        Event::assertDispatched(UserLoginEvent::class);
        Event::assertDispatched(QuizAttemptedEvent::class);
        Event::assertDispatched(CourseCompletedEvent::class);
    }

    /**
     * Test that events with minimal payload work correctly.
     */
    public function test_events_with_minimal_payload()
    {
        // UserLoginEvent with only userId
        $event1 = new UserLoginEvent(userId: 1);
        $this->assertEquals(1, $event1->getUserId());
        $this->assertEmpty($event1->getPayload());

        // CourseCompletedEvent with only required fields
        $event2 = new CourseCompletedEvent(
            userId: 2,
            courseId: 5,
            courseTitle: 'Course',
            completionPercentage: 100,
            timeSpentSeconds: 3600,
            totalLessons: 5,
            completedLessons: 5
        );
        $payload = $event2->getPayload();
        $this->assertArrayNotHasKey('category_id', $payload);
        $this->assertArrayNotHasKey('score', $payload);
    }
}
