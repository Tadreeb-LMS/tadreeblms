<?php

namespace App\Events\Kpi;

/**
 * Event fired when a user completes a quiz/test attempt.
 *
 * Payload structure:
 * - quiz_id (int): Quiz/Test ID
 * - quiz_title (string): Quiz title
 * - score (int|float): Score achieved (0-100 typically)
 * - correct_answers (int): Number of correct answers
 * - total_questions (int): Total number of questions
 * - time_spent_seconds (int): Time spent on quiz in seconds
 * - course_id (int|null): Associated course ID if applicable
 * - course_title (string|null): Associated course title
 *
 * Used for: Performance metrics, learning progress, time-on-task analysis
 */
class QuizAttemptedEvent extends AbstractKpiEvent
{
    protected string $eventType = 'quiz_attempt';

    /**
     * Create a new quiz attempted event.
     *
     * @param int $userId
     * @param int $quizId
     * @param string $quizTitle
     * @param float $score
     * @param int $correctAnswers
     * @param int $totalQuestions
     * @param int $timeSpentSeconds
     * @param int|null $courseId
     * @param string|null $courseTitle
     * @param mixed $occurredAt
     */
    public function __construct(
        int $userId,
        int $quizId,
        string $quizTitle,
        float $score,
        int $correctAnswers,
        int $totalQuestions,
        int $timeSpentSeconds,
        ?int $courseId = null,
        ?string $courseTitle = null,
        $occurredAt = null
    ) {
        $payload = [
            'quiz_id' => $quizId,
            'quiz_title' => $quizTitle,
            'score' => $score,
            'correct_answers' => $correctAnswers,
            'total_questions' => $totalQuestions,
            'time_spent_seconds' => $timeSpentSeconds,
        ];

        if ($courseId) {
            $payload['course_id'] = $courseId;
        }

        if ($courseTitle) {
            $payload['course_title'] = $courseTitle;
        }

        parent::__construct($userId, $payload, $occurredAt);
    }
}
