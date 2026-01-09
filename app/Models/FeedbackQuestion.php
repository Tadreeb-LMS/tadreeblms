<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class FeedbackQuestion
 *
 * @package App
 * @property text $question
 * @property string $question_image
 * @property integer $score
 */
class FeedbackQuestion extends Model
{
    use SoftDeletes;
    protected $fillable = ['temp_id','question', 'created_by', 'question_type', 'solution', 'option_json'];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        if (auth()->check()) {
            if (auth()->user()->hasRole('teacher')) {
                static::addGlobalScope('filter', function (Builder $builder) {
                    $courses = auth()->user()->courses->pluck('id');
                    $builder->whereHas('tests', function ($q) use ($courses) {
                        $q->whereIn('tests.course_id', $courses);
                    });
                });
            }
        }

        static::deleting(function ($question) { // before delete() method call this
            if ($question->isForceDeleting()) {
                if (File::exists(public_path('/storage/uploads/' . $question->question_image))) {
                    File::delete(public_path('/storage/uploads/' . $question->question_image));
                }
            }
        });
    }

    public function courses()
    {
        return $this->hasMany(CourseFeedback::class, 'feedback_question_id');
    }

    public function tests()
    {
        return $this->hasMany(CourseFeedback::class, 'feedback_question_id');
    }
}
