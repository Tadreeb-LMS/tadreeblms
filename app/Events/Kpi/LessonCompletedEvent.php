<?php

namespace App\Events\Kpi;

/**
 * Event fired when a user completes a lesson.
 *
 * Payload structure:
 * - lesson_id (int): Lesson ID
 * - lesson_title (string): Lesson title
 * - course_id (int): Associated course ID
 * - course_title (string): Associated course title
 * - position (int): Lesson position/order in course
 * - time_spent_seconds (int): Time spent on this lesson in seconds
 * - score (float|null): Lesson score if applicable
 *
 * Used for: Progress tracking, engagement metrics, time-on-task analysis
 */
class LessonCompletedEvent extends AbstractKpiEvent
{
    protected string $eventType = 'lesson_completed';

    /**
     * Create a new lesson completed event.
     *
     * @param int $userId
     * @param int $lessonId
     * @param string $lessonTitle
     * @param int $courseId
     * @param string $courseTitle
     * @param int $position
     * @param int $timeSpentSeconds
     * @param float|null $score
     * @param mixed $occurredAt
     */
    public function __construct(
        int $userId,
        int $lessonId,
        string $lessonTitle,
        int $courseId,
        string $courseTitle,
        int $position,
        int $timeSpentSeconds,
        ?float $score = null,
        $occurredAt = null
    ) {
        $payload = [
            'lesson_id' => $lessonId,
            'lesson_title' => $lessonTitle,
            'course_id' => $courseId,
            'course_title' => $courseTitle,
            'position' => $position,
            'time_spent_seconds' => $timeSpentSeconds,
        ];

        if ($score !== null) {
            $payload['score'] = $score;
        }

        parent::__construct($userId, $payload, $occurredAt);
    }
}
