<?php

namespace App\Events\Kpi;

/**
 * Event fired when a user submits or completes an assignment.
 *
 * Payload structure:
 * - assignment_id (int): Assignment ID
 * - assignment_title (string): Assignment title
 * - course_id (int): Associated course ID
 * - course_title (string): Associated course title
 * - status (string): Status (submitted, graded, approved, rejected)
 * - score (float|null): Score awarded
 * - submission_date (string|null): When submission was made (ISO 8601)
 * - grade_date (string|null): When assignment was graded (ISO 8601)
 * - time_to_complete_seconds (int|null): Time spent working on assignment
 *
 * Used for: Project/assignment tracking, grading metrics, time-on-task
 */
class AssignmentCompletedEvent extends AbstractKpiEvent
{
    protected string $eventType = 'assignment_completed';

    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_GRADED = 'graded';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Create a new assignment completed event.
     *
     * @param int $userId
     * @param int $assignmentId
     * @param string $assignmentTitle
     * @param int $courseId
     * @param string $courseTitle
     * @param string $status
     * @param float|null $score
     * @param int|null $timeToCompleteSeconds
     * @param string|null $submissionDate
     * @param string|null $gradeDate
     * @param mixed $occurredAt
     */
    public function __construct(
        int $userId,
        int $assignmentId,
        string $assignmentTitle,
        int $courseId,
        string $courseTitle,
        string $status = self::STATUS_SUBMITTED,
        ?float $score = null,
        ?int $timeToCompleteSeconds = null,
        ?string $submissionDate = null,
        ?string $gradeDate = null,
        $occurredAt = null
    ) {
        $payload = [
            'assignment_id' => $assignmentId,
            'assignment_title' => $assignmentTitle,
            'course_id' => $courseId,
            'course_title' => $courseTitle,
            'status' => $status,
        ];

        if ($score !== null) {
            $payload['score'] = $score;
        }

        if ($timeToCompleteSeconds !== null) {
            $payload['time_to_complete_seconds'] = $timeToCompleteSeconds;
        }

        if ($submissionDate) {
            $payload['submission_date'] = $submissionDate;
        }

        if ($gradeDate) {
            $payload['grade_date'] = $gradeDate;
        }

        parent::__construct($userId, $payload, $occurredAt);
    }
}
