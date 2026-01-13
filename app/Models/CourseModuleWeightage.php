<?php

namespace App\Models;

use App\Helpers\CustomHelper;
use App\Http\Controllers\LessonsController;
use App\Models\Auth\User;
use App\Models\Stripe\SubscribeCourse;
//use App\Models\stripe\UserCourses;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Models\Stripe\UserCourses;
use DB;
use Illuminate\Support\Facades\Storage;

class CourseModuleWeightage extends Model
{
    protected $fillable = [
        'course_id',
        'minimun_qualify_marks',
        'weightage',
        'module_included',
        'last_module'
    ];

    protected $casts = [
        'weightage' => 'array',
        'module_included' => 'array',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function getNormalizedWeightageAttribute()
    {
        return [
            'LessonModule'   => (int) ($this->weightage['LessonModule'] ?? 0),
            'QuestionModule' => (int) ($this->weightage['QuestionModule'] ?? 0),
            'FeedbackModule' => (int) ($this->weightage['FeedbackModule'] ?? 0),
        ];
    }
}
