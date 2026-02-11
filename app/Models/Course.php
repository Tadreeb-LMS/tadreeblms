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

/**
 * Class Course
 *
 * @package App
 * @property string $title
 * @property string $qr_code
 * @property string $slug
 * @property text $description
 * @property decimal $price
 * @property string $course_image
 * @property string $start_date
 * @property tinyInteger $published
 */
class Course extends Model
{
    use SoftDeletes;

    protected $fillable = ['temp_id','category_id', 'title', 'slug', 'qr_code', 'description', 'department_id', 'price', 'course_image', 'course_video', 'start_date', 'published', 'free', 'featured', 'trending', 'popular', 'meta_title', 'meta_description', 'meta_keywords', 'expire_at', 'strike', 'marks_required', 'course_code', 'arabic_title','course_lang','is_online','current_step'];

    protected $appends = ['image'];

    //    protected $dates = ['expire_at'];


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
                    $builder->whereHas('teachers', function ($q) {
                        $q->where('course_user.user_id', '=', auth()->user()->id);
                    });
                });
            }
        }

        static::deleting(function ($course) { // before delete() method call this
            if ($course->isForceDeleting()) {
                if (File::exists(public_path('/storage/uploads/' . $course->course_image))) {
                    File::delete(public_path('/storage/uploads/' . $course->course_image));
                    File::delete(public_path('/storage/uploads/thumb/' . $course->course_image));
                }
            }
        });
    }



    public function latestModuleWeightage()
    {
        return $this->hasOne(CourseModuleWeightage::class)->latestOfMany();
    }


    
    public function getCourseImageAttribute($value)
    {
       
        // If null or empty, return null
        if (!$value) {
            return null;
        }

        $storage = config('filesystems.default');
        if( $storage == 'local') {
            return asset('storage/uploads/' . $value);
        } else {
            return Storage::disk('s3')->temporaryUrl(
                $value,
                now()->addMinutes(60)
            );
        }
        

        //return $value;
        
        
    }

    


    public function subscribe_detail()
    {
        return $this->hasOne(SubscribeCourse::class,'course_id');
    }


    public function getImageAttribute()
    {
        if ($this->course_image != null) {
            return url('storage/uploads/' . $this->course_image);
        }
        return NULL;
    }

    // public function getAssesmentLinkAttribute()
    // {
        
    //     if($this->latest_assesment->count()) {
    //         return '<a target="_blank" class="btn mb-1 btn-warning text-white" href="' . route('online_assessment', 'assignment=' . $this->latest_assesment[0]->url_code) . '" > <i class="fa fa-arrow-circle-right"></i></a>
    //         <a href="/user/tests/create?course_id='.$this->id.'&new_test" class="btn btn-success mb-1"><i class="fa fa-plus-circle"></i>   </a>
    //         ';
    //     } else {
    //         return '<a href="' . route('admin.assessment_accounts.assignment_create', ['course_id' => $this->id]) . '" class="btn btn-success mb-1"><i class="fa fa-plus-circle"></i>   </a>';
    //     }
        
    // }
    public function getAssesmentLinkAttribute()
{
     $buttons = '';

    if ($this->latest_assesment->count()) {
        $buttons .= '<div><a target="_blank" class="btn1" href="' . route('online_assessment', 'assignment=' . $this->latest_assesment[0]->url_code) . '">
       Create Test 
                   <!--   <i class="fa fa-folder-open" style="font-size:20px" aria-hidden="true"></i> -->
                     </a></div>';
        $buttons .= '<div><a class="btn2" href="/user/tests/create?course_id=' . $this->id . '&new_test"> Online Assignment
                      <!-- <i class="fa fa-plus-circle" aria-hidden="true" style="font-size:20px;margin-bottom:-3px"></i>  -->
                     </a></div>';
    } else {
        $buttons .= '<div><a class="btn2" href="' . route('admin.assessment_accounts.assignment_create', ['course_id' => $this->id]) . '">Online Assignment
                     <!-- <i class="fa fa-plus-circle " aria-hidden="true" style="font-size:20px;margin-bottom:-3px"></i> 
                     </a></div>';
    }

    return $buttons;
}

    public function latest_assesment()
    {
        return $this->hasMany(Assignment::class,'course_id')->latest();
    }

   

    public function getCourseAllLessonDurationAttribute()
    {
        // Use the loaded relationship or fallback to lazy load
        $lessons = $this->publishedCourseLessons();

        $minutes = $lessons->sum('duration');

        if ($minutes > 0) {
            $time = Carbon::createFromTime(0, 0)->addMinutes($minutes);
            return $time->format('G:i');
        } else {
            return 0;
        }
    }


    public function getPriceAttribute()
    {
        if (($this->attributes['price'] == null)) {
            return round(0.00);
        }
        return $this->attributes['price'];
    }


    /**
     * Set attribute to money format
     * @param $input
     */
    public function setPriceAttribute($input)
    {
        $this->attributes['price'] = $input ? $input : null;
    }

    /**
     * Set attribute to date format
     * @param $input
     */
    public function setStartDateAttribute($input)
    {
        if ($input != null && $input != '') {
            $this->attributes['start_date'] = Carbon::createFromFormat(config('app.date_format'), $input)->format('Y-m-d');
        } else {
            $this->attributes['start_date'] = null;
        }
    }

    /**
     * Get attribute from date format
     * @param $input
     *
     * @return string
     */
    public function getStartDateAttribute($input)
    {
        $zeroDate = str_replace(['Y', 'm', 'd'], ['0000', '00', '00'], config('app.date_format'));

        if ($input != $zeroDate && $input != null) {
            return Carbon::createFromFormat('Y-m-d', $input)->format(config('app.date_format'));
        } else {
            return '';
        }
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'course_user')->withPivot('user_id');
    }

    

    public function students()
    {
        return $this->belongsToMany(User::class, 'course_student')->withTimestamps()->withPivot(['rating']);
    }

    public function publishedCourseLessons()
    {
        return $this->hasMany(Lesson::class)->where('published','1')->orderBy('position');
    }

    public function lessons()
    {
        $user_id = Auth::user()->id;

        $sub_data = SubscribeCourse::query()
                ->where('user_id', $user_id)
                ->where('course_id', $this->id)
                ->first();
        if($this->id && $sub_data) {
            $completed_at = !empty($sub_data->completed_at) ? $sub_data->completed_at->format('Y-m-d H:i:s') : null;

            if($sub_data->is_completed == 1 && !empty($completed_at))  {
                
                return $this->hasMany(Lesson::class)
                ->where('published', 1)
                ->where('created_at','<',$completed_at)
                ->orderBy('position');
            }
        } 

        return $this->hasMany(Lesson::class)->orderBy('position');
    }

    public function publishedLessons($course_id = null)
    {
        //dd($this->id);
        $user_id = Auth::user()->id;

        $sub_data = SubscribeCourse::query()
                ->where('user_id', $user_id)
                ->where('course_id', $course_id)
                ->first();
        if($course_id && $sub_data) {
            $completed_at = !empty($sub_data->completed_at) ? $sub_data->completed_at->format('Y-m-d H:i:s') : null;

            if($sub_data->is_completed == 1 && !empty($completed_at)) {
                
                return $this->hasMany(Lesson::class)
                ->where('published', 1)
                ->where('created_at','<',$completed_at);
            }
        } 

        return $this->hasMany(Lesson::class)
                ->where('published', 1);
    }

    public function coursePublishedLessons()
    {
        
        return $this->hasMany(Lesson::class)
                ->where('published', 1);
    }

    public function scopeOfTeacher($query)
    {
        if (!Auth::user()->isAdmin()) {
            return $query->whereHas('teachers', function ($q) {
                $q->where('user_id', Auth::user()->id);
            });
        }
        return $query;
    }

    public function getRatingAttribute()
    {
        return $this->reviews->avg('rating');
    }

    public function orderItem()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tests()
    {
        return $this->hasMany('App\Models\Test');
    }

    public function total_tests_published()
    {
        return $this->hasMany('App\Models\Test')->where('published', 1);
    }

    public function courseTimeline()
    {
        return $this->hasMany(CourseTimeline::class);
    }

    public function getIsAddedToCart()
    {
        if (auth()->check() && (auth()->user()->hasRole('student')) && (\Cart::session(auth()->user()->id)->get($this->id))) {
            return true;
        }
        return false;
    }


    public function reviews()
    {
        return $this->morphMany('App\Models\Review', 'reviewable');
    }

    public function lessonProgress()
    {
        //$main_chapter_timeline = $this->lessons()->where('published',1)->pluck('id')->merge($this->tests()->pluck('id'));
        $main_chapter_timeline = $this->lessons()->where('published', 1)->pluck('id');

        $completed_lessons = auth()->user()->chapters()->where('course_id', $this->id)->distinct('model_id')->pluck('model_id');


        if ($completed_lessons->count() > 0) {
            return $main_chapter_timeline->count() > 0 
                ? intval($completed_lessons->count() / $main_chapter_timeline->count() * 100) 
                : 0;
        } else {
            return 0;
        }
    }
    public function progress()
    {
        $main_chapter_timeline = $this->lessons()->where('published', 1)->pluck('id')->merge($this->tests()->pluck('id'));

        $completed_lessons = auth()->user()->chapters()->where('course_id', $this->id)->pluck('model_id');

        if ($completed_lessons->count() > 0) {
            return intval($completed_lessons->count() / $main_chapter_timeline->count() * 100);
        } else {
            return 0;
        }
    }

    public function isUserCertified()
    {
        $status = false;
        $certified = auth()->user()->certificates()->where('course_id', '=', $this->id)->first();
        if ($certified != null) {
            $status = true;
        }
        return $status;
    }

    public function item()
    {
        return $this->morphMany(OrderItem::class, 'item');
    }

    public function bundles()
    {
        return $this->belongsToMany(Bundle::class, 'bundle_courses');
    }

    public function chapterCount()
    {
        $timeline = $this->courseTimeline;
        $chapters = 0;
        foreach ($timeline as $item) {
            if (isset($item->model) && ($item->model->published == 1)) {
                $chapters++;
            }
        }
        return $chapters;
    }

    public function mediaVideo()
    {
        $types = ['youtube', 'vimeo', 'upload', 'embed'];
        return $this->morphOne(Media::class, 'model')
            ->whereIn('type', $types);
    }

    // scope for disable course if course expire date is less than tomorrow date
    public function scopeCanDisableCourse($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expire_at')->orWhereDate('expire_at', '>=', Carbon::now()->format('Y-m-d'));
        });
    }

    public function getStrikePriceAttribute()
    {
        if ($this->strike) {
            return '<strike class="text-secondary">' . getCurrency(config('app.currency'))['symbol'] . ' ' . $this->strike . '</strike>';
        }
        return;
    }

    public function getCoursePageStrikePriceAttribute()
    {
        if ($this->strike) {
            return '<div class="h6">' . trans('labels.frontend.course.original_price') . '<span> ' . $this->strikePrice . '</span></div>';
        }
        return;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wishlist()
    {
        return $this->hasMany(WishList::class);
    }

    public function courseUser()
    {
        return $this->hasOne(UserCourses::class, 'course_id', 'id')->where('user_id', \Auth::id());
    }

    public function getDepartment($department_id)
    {
        $department = DB::table('department')
            ->select('department.title')
            //->leftJoin('courses','courses.department_id','department.id')
            ->where('department.id', '=', $department_id)
            //->where('employee_profiles.status','1')
            ->first();
        //dd($subscriptionList);
        if (!empty($department)) {
            return $department->title;
        }
        return '';
    }


    public function getFeedbackQuestionsAttribute()
    {
        $questions = CourseFeedback::where('course_id', $this->id)->pluck('feedback_question_id')->toArray();
        $fq =  FeedbackQuestion::whereIn('id', $questions)->pluck('question')->toArray();
        return implode('<br/>', $fq);
        // return $this->hasMany(FeedbackQuestion::class)->where('id', )
    }

    public function courseFeedback()
    {
        return $this->hasMany(CourseFeedback::class);
    }

    public function userCourseFeedback($user_id)
    {
        return $this->hasMany(UserFeedback::class)->where('user_id', $user_id);
    }

    public function courseAssignments()
    {
        return $this->hasMany(Assignment::class)->whereHas('assignmentQuestions');
    }

    public function courseHasAssignment()
    {
        return $this->hasMany(Assignment::class);
    }

    public function assignmentScorePercentage($user_id)
    {
        $course_test_ids = Test::where('course_id', $this->id)->pluck('id')->toArray();
        
        $test_questions = TestQuestion::whereIn('test_id', $course_test_ids)->pluck('id')->toArray();
        //dd($course_test_ids, $test_questions);
        $correct_ans_count = 0;
        $total_ans_count = 0;


        $total_ans_count = AssignmentQuestion::join('test_questions', 'test_questions.id', '=', 'assignment_questions.question_id')
        ->whereIn('test_questions.id', $test_questions)
        ->where('assessment_account_id', $user_id)
        ->orderBy('attempt','desc')
        ->orderBy('assignment_questions.id', 'desc')
        ->limit(count($test_questions))
        ->count();

        


        $correct_ans_count = AssignmentQuestion::join('test_questions', 'test_questions.id', '=', 'assignment_questions.question_id')
        ->whereIn('test_questions.id', $test_questions)
        ->where('assessment_account_id', $user_id)
        ->where('is_correct', 1)
        ->orderBy('attempt','desc')
        ->orderBy('assignment_questions.id', 'desc')
        ->limit(count($test_questions))
        ->count();

        //dd($correct_ans_count);

        if ($total_ans_count > 0) {
            return ($correct_ans_count / $total_ans_count) * 100;
        }

        return 0;
    }

    public function assignmentScoreValue($user_id, $progress = null)
    {
        //dd($user_id, $progress);
        $completed_at = null;
        $is_completed = 0;

        $course_id = $this->id;
        $sub_data = SubscribeCourse::query()
                ->where('user_id', $user_id)
                ->where('course_id', $course_id)
                //->where('is_completed', 1)
                ->first();

        //Log::info('Step 2: after sub_data query', ['sub_data' => $sub_data]);
        if($sub_data) {
            $completed_at = isset($sub_data) && $sub_data->completed_at != null ? $sub_data->completed_at->format('Y-m-d H:i:s') : null;
            $is_completed = isset($sub_data) ? $sub_data->is_completed : 0;
        }
        

        $course_test_ids = Test::where('course_id', $this->id)->pluck('id')->toArray();

        //dd( $course_test_ids , $this->id );

        $test_questions = TestQuestion::whereIn('test_id', $course_test_ids)
                            ->when($is_completed, function ($q) use($completed_at) {
                                if($completed_at) {
                                    //$q->where('created_at', '<', $completed_at)
                                       //->whereNotNull('created_at');
                                }
                            })
                            ->pluck('id')->toArray();
        
        //dd($test_questions);
        
        //Log::info('Step 4: after sub_data query', ['sub_data' => $sub_data]);

        if($progress != 100) {
            $progress = CustomHelper::progress($course_id, $user_id) ?? 0;
        }

        $progress = $progress;


        //dd($progress);
        // check if the user has given or not the assinment
        $hasGivenAssinment = false;
        if(isset($sub_data) && $sub_data->has_assesment) {
            $hasGivenAssinment = $sub_data->assesment_taken;
        }

        //dd($user_id, $course_test_ids, $test_questions);
        $hasGivenAssinmentQuery = Assignment::query()
                        ->with('assignmentQuestions')
                        ->whereHas('assignmentQuestions', function($q) use($user_id, $course_test_ids) {
                            $q->where('assessment_account_id', $user_id);
                        })
                        ->where('course_id', $course_id)
                        ->first();

        //dd($hasGivenAssinment);

        
        $hasGivenAssinmentCount = 0;
        if($hasGivenAssinment) {
            $hasGivenAssinmentCount = $hasGivenAssinment; 
        }

        //dd($hasGivenAssinmentCount, $sub_data);

        if($sub_data) {
            if($sub_data->has_assesment == 0) {
                return "Not Applied";
            } 
            if($sub_data->assesment_taken == 0 && $sub_data->has_assesment == 1) {
                return "Not Started";
            }
        }
       
        // if($hasGivenAssinmentCount == 0 && $progress == 0) {
        //     return "Not Started";
        // }
        // if($hasGivenAssinmentCount == 0 && $progress > 0) {
        //     return "Not Applied";
        // }

        $secured_marks = 0;
        
        $marks_sum = 0;

        $aqs = AssignmentQuestion::select('assignment_questions.*')
                ->join('test_questions', 'test_questions.id', '=', 'assignment_questions.question_id')
                ->whereIn('test_questions.id', $test_questions)
                ->where('assessment_account_id', $user_id)
                ->when($is_completed && $completed_at, function ($q) use ($completed_at) {
                    $q->where('test_questions.created_at', '<', $completed_at);
                })
                ->whereIn('assignment_questions.id', function ($sub) use ($user_id) {
                    $sub->selectRaw('MAX(id)')
                        ->from('assignment_questions as aq2')
                        ->where('assessment_account_id', $user_id)
                        ->groupBy('aq2.question_id');
                })
                ->orderBy('assignment_questions.id', 'desc')
                ->get();

        
        

        foreach ($aqs as $aq) {
            if ($aq->is_correct===1) {
                $marks_sum += $aq->marks;
            }
        }

        //dd($aqs, $test_questions, $marks_sum, $completed_at);
        //dd($marks_sum);

        $secured_marks = $marks_sum;
        $max_marks = TestQuestion::whereIn('test_id', $course_test_ids)
                                    ->when($is_completed, function ($q) use($completed_at) {
                                        if($completed_at) {
                                            $q->where('created_at', '<', $completed_at);
                                        }
                                        
                                    })
                                    ->sum('marks');

        //dd($secured_marks, $max_marks);                         
        //return $secured_marks . '/' . $max_marks;
        return $max_marks > 0 ? (($secured_marks / $max_marks) * 100)  : 0;
    }

    public function assignmentStatus($user_id, $progress = null)
    {
        $assignmentScoreValue = 0;
        try {
            $assignmentScoreValue = $this->assignmentScoreValue($user_id, $progress);

            //dd($assignmentScoreValue);

        } catch (\Throwable $e) {
            //dd('Error occurred:', $e->getMessage(), $e->getTraceAsString());
        }
        //dd($assignmentScoreValue, $user_id, $this->id);
        if(is_string($assignmentScoreValue)) {
            return $assignmentScoreValue;
        }

        //dd($this->assignmentScoreValue($user_id, $progress));

        $percent_required = $this->marks_required ?? 70;
        return $this->assignmentScoreValue($user_id, $progress) >= $percent_required ? 'Passed' : 'Failed';
    }

    public function getGrantCertificateAttribute()
    {
        $allow = false;
        $total_lessons = Lesson::where('course_id', $this->id)->where('published', 1)->count();
        
        $courseController = new LessonsController;
        $hasAssignment = $courseController->hasAssessmentLink($this->id, auth()->id());

        $progress = CustomHelper::progress($this->id, auth()->id());
        $courseProgressStatus = $this->assignmentStatus(auth()->id(), $progress);

        // if course has a assignment
        if ($hasAssignment && $courseProgressStatus == 'Passed' && $this->userCourseFeedback(auth()->id())->count() > 0) {
            $allow = true;
        }


        // if course has no assignment but a feedback
        if (!$hasAssignment && $this->courseFeedback->count() > 0) {
            // if course has no assignment
            if (CustomHelper::courseProgress($this->id) == 100 && $this->userCourseFeedback(auth()->id())->count() > 0) {
                $allow = true;
            }
            
        }

        // if course has assignment but no feedback
        if ($hasAssignment && $courseProgressStatus == 'Passed' && $this->courseFeedback->count() == 0) {
            $allow = true;
        }
        


        // if course has no assignnment and no feedback
        if (!$hasAssignment && $this->courseFeedback->count() == 0 && $total_lessons == 0) {
            $allow = true;
           
        }

        return $allow;
    }

    public function grantCertificate($user_id)
    {
       
        $allow = false;
        $total_lessons = Lesson::where('course_id', $this->id)->where('published', 1)->count();
        $progress = null;
        $progress = CustomHelper::progress($this->id, $user_id);
        $courseProgressStatus = $this->assignmentStatus($user_id, $progress);
        // if course has a assignment
        //dd($this->courseAssignments->count(), $this->assignmentStatus($user_id));
        if ($this->courseAssignments->count() > 0 && $courseProgressStatus == 'Passed' && $this->userCourseFeedback($user_id)->count() > 0) {
            $allow = true;
        }

        // if course has no assignment but a feedback
        if ($this->courseAssignments->count() == 0 && $this->courseFeedback->count() > 0) {
            // if course has no assignment
            if (CustomHelper::courseProgress($this->id, $user_id) == 100 && $this->userCourseFeedback($user_id)->count() > 0) {
                $allow = true;
            }
        }

        // if course has assignment but no feedback
        if ($this->courseAssignments->count() > 0 && $courseProgressStatus == 'Passed' && $this->courseFeedback->count() == 0) {
            $allow = true;
        }

        // if course has no assignnment and no feedback
        if ($this->courseAssignments->count() == 0 && $this->courseFeedback->count() == 0 && $total_lessons == 0) {
            $allow = true;
        }

        return $allow;
    }

    public function scopePublished($query)
    {
        return $query->where('published', 1);
    }

    public function getIsOnlineCourseAttribute(){

        //dd($this->is_online);

        if(empty($this->is_online)) {
            $lesson_ids = Lesson::where('course_id', $this->id)->pluck('id')->toArray();
            $courseHasMedia = Media::where('model_type', '=', 'App\Models\Course')->where('model_id', '=', $this->id)->exists();
            $LessonHasMedia = Media::where('model_type', '=', 'App\Models\Lesson')->whereIn('model_id', $lesson_ids)->exists();
    
            return $courseHasMedia || $LessonHasMedia ? true : false;
        } else {
            return $this->is_online == 'Online' ? true : false;
        }
        
    }
}
