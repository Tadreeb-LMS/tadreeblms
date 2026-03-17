<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

/**
 * Class CourseFeedback
 *
 * @package App
 * @property text $question
 * @property string $question_image
 * @property integer $score
 */
class UserFeedback extends Model
{
    protected $table = 'user_feedback';
    protected $fillable = ['course_id', 'user_id', 'feedback_id', 'feedback'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function courseFeedback()
    {
        return $this->belongsTo(CourseFeedback::class, 'course_id', 'course_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function feedbackQuestion()
    {
        return $this->belongsTo(FeedbackQuestion::class, 'feedback_id', 'id');
    }

    public function getQuestionAnswersAttribute()
    {
        $trs = '';

        foreach ($this->getQuestionAnswerRows() as $row) {
            $question = e($row['question']);
            $answer = e($row['answer']);

            $trs .= "
            <tr>
                <td>$question</td>
                <td>$answer</td>
            </tr>
            ";
        }

        $html = "
        <table class='table'>
            <tr>
                <th>Question</th>
                <th>Answer</th>
            </tr>
            $trs
        </table>
        ";

        return $html;
    }

    public function getQuestionAnswersTextAttribute()
    {
        $lines = [];

        foreach ($this->getQuestionAnswerRows() as $row) {
            $question = trim((string) $row['question']);
            $answer = trim((string) $row['answer']);

            $lines[] = $question !== '' ? $question . ': ' . $answer : $answer;
        }

        return implode(PHP_EOL, $lines);
    }

    protected function getQuestionAnswerRows(): array
    {
        $rows = [];

        $feedbackEntries = static::where('course_id', $this->course_id)
            ->where('user_id', $this->user_id)
            ->orderBy('id', 'desc')
            ->get()
            ->unique('feedback_id')
            ->values();

        foreach ($feedbackEntries as $feedbackEntry) {
            $rows[] = [
                'question' => optional($feedbackEntry->feedbackQuestion)->question ?? '',
                'answer' => $this->resolveFeedbackAnswerText($feedbackEntry),
            ];
        }

        return $rows;
    }

    protected function resolveFeedbackAnswerText(self $feedbackEntry): string
    {
        if (in_array($feedbackEntry->feedback_questions_type, [1, 2])) {
            if (str_contains($feedbackEntry->feedback, '[')) {
                $optionTexts = FeedbackOption::whereIn('id', json_decode($feedbackEntry->feedback, true) ?? [])
                    ->pluck('option_text')
                    ->toArray();

                return implode(', ', $optionTexts);
            }

            return FeedbackOption::firstWhere('id', json_decode($feedbackEntry->feedback))->option_text ?? '';
        }

        return (string) $feedbackEntry->feedback;
    }
}
