<?php

namespace App\Events\Kpi;

/**
 * Event fired when a user completes a course.
 *
 * Payload structure:
 * - course_id (int): Course ID
 * - course_title (string): Course title
 * - category_id (int|null): Course category ID
 * - category_name (string|null): Course category name
 * - completion_percentage (float): Completion percentage (0-100)
 * - time_spent_seconds (int): Total time spent on course in seconds
 * - total_lessons (int): Total lessons in course
 * - completed_lessons (int): Number of lessons completed
 * - score (float|null): Final course score if applicable
 * - certificate_issued (bool|null): Whether a certificate was issued
 *
 * Used for: Completion tracking, performance metrics, certificate tracking
 */
class CourseCompletedEvent extends AbstractKpiEvent
{
    protected string $eventType = 'course_completed';

    /**
     * Create a new course completed event.
     *
     * @param int $userId
     * @param int $courseId
     * @param string $courseTitle
     * @param float $completionPercentage
     * @param int $timeSpentSeconds
     * @param int $totalLessons
     * @param int $completedLessons
     * @param int|null $categoryId
     * @param string|null $categoryName
     * @param float|null $score
     * @param bool|null $certificateIssued
     * @param mixed $occurredAt
     */
    public function __construct(
        int $userId,
        int $courseId,
        string $courseTitle,
        float $completionPercentage,
        int $timeSpentSeconds,
        int $totalLessons,
        int $completedLessons,
        ?int $categoryId = null,
        ?string $categoryName = null,
        ?float $score = null,
        ?bool $certificateIssued = null,
        $occurredAt = null
    ) {
        $payload = [
            'course_id' => $courseId,
            'course_title' => $courseTitle,
            'completion_percentage' => $completionPercentage,
            'time_spent_seconds' => $timeSpentSeconds,
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
        ];

        if ($categoryId) {
            $payload['category_id'] = $categoryId;
        }

        if ($categoryName) {
            $payload['category_name'] = $categoryName;
        }

        if ($score !== null) {
            $payload['score'] = $score;
        }

        if ($certificateIssued !== null) {
            $payload['certificate_issued'] = $certificateIssued;
        }

        parent::__construct($userId, $payload, $occurredAt);
    }
}
