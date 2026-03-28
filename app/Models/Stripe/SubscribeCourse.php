<?php

namespace App\Models\Stripe;

use App\Helpers\CustomHelper;
use App\Models\AssignmentQuestion;
use App\Models\Auth\User;
use App\Models\Course;
use App\Models\Category;
use App\Models\courseAssignment;
use App\Models\Lesson;
use App\Models\CourseTimeline;
use App\Models\EmployeeProfile;
use App\Models\Test;
use App\Models\TestQuestion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\LearningPathwayCourse;
use App\Models\Assignment;
use App\Models\stripe\CourseUser;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscribeCourse extends Model
{
    use SoftDeletes;
    protected $casts = [
        'completed_at' => 'datetime',
    ];
    
    protected $fillable = [
        'stripe_id',
        'user_id', 
        'status', 
        'course_id', 
        'assign_date', 
        'due_date', 
        'course_trainer_name',
        'assignment_progress',
        'assignment_status',
        'assignment_score',
        'grant_certificate',
        'created_at', 
        'updated_at',
        'is_completed',
        'course_progress_status',
        'completed_at', 
        'is_pathway',
        'by_invitation',
        'is_attended',
        'has_assesment',
        'assesment_taken',
        'has_feedback',
        'feedback_given'
    ];

    public function course()
    {
        return $this->hasOne(Course::class, 'id', 'course_id')->with('category');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function courseTrainer()
    {
        return $this->hasMany(CourseUser::class, 'course_id', 'course_id');
    }

    public function courseAssignment()
    {
        
        $cas = courseAssignment::where("course_id", $this->course_id)->get();

        foreach ($cas as $ca) {
            
            if ($ca->assign_to == $this->user_id) {
                return $ca;
            }
            $usersAssigned = explode(',', $ca->assign_to);
            if (in_array($this->user_id, $usersAssigned)) {
                return $ca;
            }
            if ($ca->department_id && empty($ca->assign_to)) {
                $ep = EmployeeProfile::where('user_id', $this->user_id)->first();
                if ($ca->department_id == $ep->department) {
                    return $ca;
                }
            }
        }
        
        
    }

    public function courseAssigmentByPathway()
    {
        $cas = LearningPathwayCourse::with('learningPathwayAssignment')->where("course_id", $this->course_id)->get();
        //dd($cas);
        if($cas) {
            foreach($cas as $row) {
                $pathway_id = $row->pathway_id;
                if($row->learningPathwayAssignment) {
                    foreach($row->learningPathwayAssignment as $r) {
                        $user_assigned = !empty($r->assigned_to) ? json_decode($r->assigned_to) : [];
                
                        if(in_array($this->user_id, $user_assigned)) {
                            return $r;
                        }
                    }
                }
                
            }
        }

        return null;
    }

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class, 'user_id', 'user_id');
    }

    public function isUserCertified()
    {
        $status = false;
        $certified = auth()->user()->certificates()->where('course_id', '=', $this->course_id)->first();
        if ($certified != null) {
            $status = true;
        }
        return $status;
    }

    public function assignmentScoreWithHtml($user_id = null)
    {
        if(!empty($user_id)) {
            $completed_at = isset($this->completed_at) ? $this->completed_at->format('Y-m-d H:i:s') : null;
            $is_completed = $this->is_completed ?? 0;
            $course_test_ids = [];

            if($this->course_id) {
                $course_test_ids = Test::where('course_id', $this->course_id)
                    ->where(function ($q) {
                        $q->whereNull('lesson_id')->orWhere('lesson_id', 0);
                    })
                    ->pluck('id')
                    ->toArray();
            }
            
            
            $max_marks = TestQuestion::whereIn('test_id', $course_test_ids)
                        ->when($is_completed && $completed_at, function ($q) use($completed_at) {
                            $q->where('created_at', '<', $completed_at);
                        })
                        ->sum('marks');
            $action_url = url("user/assessement-answers/$user_id/$this->course_id");

            $action = '';
            if ($max_marks) {
                $action = '<a href=' . $action_url . ' target="_blank" class=""><i class="fa fa-eye ml-2"></i></a>';
            }

            $score = $this->assignmentScore($user_id);

            //dd($score);

            return "$score$action";
        } else {
            return "-";
        }
        
    }

    public function assignmentScore($user_id)
    {

        $completed_at = isset($this->completed_at) ? $this->completed_at->format('Y-m-d H:i:s') : null;
        $is_completed = $this->is_completed ?? 0;
        $course_test_ids = [];
        $secured_marks = 0;
        $course_test_ids = Test::where('course_id', $this->course_id)
            ->where(function ($q) {
                $q->whereNull('lesson_id')->orWhere('lesson_id', 0);
            })
            ->pluck('id')
            ->toArray();
        
        $test_questions = TestQuestion::whereIn('test_id', $course_test_ids)
                            ->when($is_completed && $completed_at, function ($q) use($completed_at) {
                                $q->where('created_at', '<', $completed_at);
                            })
                            ->pluck('id')->toArray();
        $all_test_ids = Assignment::whereIn('test_id',$course_test_ids)->pluck('id')->toArray();

        
        $marks_sum = 0;

        $latest_attempts = AssignmentQuestion::selectRaw('question_id, MAX(attempt) as max_attempt')
        ->join('test_questions', 'test_questions.id', '=', 'assignment_questions.question_id')
        ->whereIn('test_questions.id', $test_questions)
        ->whereIn('assessment_test_id', $all_test_ids)
        ->where('assessment_account_id', $user_id)
        ->when($is_completed && $completed_at, function ($q) use ($completed_at) {
            $q->where('test_questions.created_at', '<', $completed_at);
        })
        ->groupBy('question_id');

        $aqs = AssignmentQuestion::join('test_questions', 'test_questions.id', '=',     'assignment_questions.question_id')
            ->joinSub($latest_attempts, 'latest', function ($join) {
                $join->on('assignment_questions.question_id', '=', 'latest.question_id')
                    ->on('assignment_questions.attempt', '=', 'latest.max_attempt');
            })
            ->whereIn('test_questions.id', $test_questions)
            ->whereIn('assessment_test_id', $all_test_ids)
            ->where('assessment_account_id', $user_id)
            ->orderBy('assignment_questions.id', 'desc')
            ->groupBy('assignment_questions.question_id')
            ->get();
 

        foreach ($aqs as $aq) {
            if ($aq->is_correct===1) {
                $marks_sum += $aq->marks;
            }
        }

        //dd($aqs, $course_test_ids);

        $secured_marks = $marks_sum;
        $max_marks = TestQuestion::whereIn('test_id', $course_test_ids)
                        ->when($is_completed && $completed_at, function ($q) use($completed_at) {
                            $q->where('created_at', '<', $completed_at);
                        })
                        ->sum('marks');
        //dd($secured_marks, $marks_sum);

        //return $secured_marks . '/' . $max_marks;
        $optain_marks = $max_marks > 0 ? round(($secured_marks / $max_marks) * 100,2) : 0;
        // if($optain_marks > 100) {
        //     $optain_marks = 100;
        // }

        $optain_marks = $max_marks > 0 ? $optain_marks .'%' : '0%';
        
        

        return $optain_marks;
    }

    public function assignmentRawScore($user_id)
    {

        $completed_at = isset($this->completed_at) ? $this->completed_at->format('Y-m-d H:i:s') : null;
        $is_completed = $this->is_completed ?? 0;
        $course_test_ids = [];
        $secured_marks = 0;
        $course_test_ids = Test::where('course_id', $this->course_id)
            ->where(function ($q) {
                $q->whereNull('lesson_id')->orWhere('lesson_id', 0);
            })
            ->pluck('id')
            ->toArray();
        
        $test_questions = TestQuestion::whereIn('test_id', $course_test_ids)
                            ->when($is_completed && $completed_at, function ($q) use($completed_at) {
                                $q->where('created_at', '<', $completed_at);
                            })
                            ->pluck('id')->toArray();
        $all_test_ids = Assignment::whereIn('test_id',$course_test_ids)->pluck('id')->toArray();

        
        $marks_sum = 0;

        $latest_attempts = AssignmentQuestion::selectRaw('question_id, MAX(attempt) as max_attempt')
        ->join('test_questions', 'test_questions.id', '=', 'assignment_questions.question_id')
        ->whereIn('test_questions.id', $test_questions)
        ->whereIn('assessment_test_id', $all_test_ids)
        ->where('assessment_account_id', $user_id)
        ->when($is_completed && $completed_at, function ($q) use ($completed_at) {
            $q->where('test_questions.created_at', '<', $completed_at);
        })
        ->groupBy('question_id');

        $aqs = AssignmentQuestion::join('test_questions', 'test_questions.id', '=',     'assignment_questions.question_id')
            ->joinSub($latest_attempts, 'latest', function ($join) {
                $join->on('assignment_questions.question_id', '=', 'latest.question_id')
                    ->on('assignment_questions.attempt', '=', 'latest.max_attempt');
            })
            ->whereIn('test_questions.id', $test_questions)
            ->whereIn('assessment_test_id', $all_test_ids)
            ->where('assessment_account_id', $user_id)
            ->orderBy('assignment_questions.id', 'desc')
            ->groupBy('assignment_questions.question_id')
            ->get();
 

        foreach ($aqs as $aq) {
            if ($aq->is_correct===1) {
                $marks_sum += $aq->marks;
            }
        }

        //dd($aqs, $course_test_ids);

        $secured_marks = $marks_sum;
        $max_marks = TestQuestion::whereIn('test_id', $course_test_ids)
                        ->when($is_completed && $completed_at, function ($q) use($completed_at) {
                            $q->where('created_at', '<', $completed_at);
                        })
                        ->sum('marks');
        //dd($secured_marks, $marks_sum);

        //return $secured_marks . '/' . $max_marks;
        $optain_marks = $max_marks > 0 ? round(($secured_marks / $max_marks) * 100,2) : 0;
        // if($optain_marks > 100) {
        //     $optain_marks = 100;
        // }

        $optain_marks = $max_marks > 0 ? $optain_marks : 0;
        
        

        return $optain_marks;
    }

    public function assignmentScoreValue($user_id)
    {
        $completed_at = isset($this->completed_at) ? $this->completed_at->format('Y-m-d H:i:s') : null;
        $is_completed = $this->is_completed ?? 0;
        $secured_marks = 0;
        $course_test_ids = Test::where('course_id', $this->course_id)
            ->where(function ($q) {
                $q->whereNull('lesson_id')->orWhere('lesson_id', 0);
            })
            ->pluck('id')
            ->toArray();
        $test_questions = TestQuestion::whereIn('test_id', $course_test_ids)
                            ->when($is_completed && $completed_at, function ($q) use($completed_at) {
                                $q->where('created_at', '<', $completed_at);
                            })
                            ->pluck('id')->toArray();


        $marks_sum = 0;

        $aqs = AssignmentQuestion::join('test_questions', 'test_questions.id', '=', 'assignment_questions.question_id')
            ->whereIn('test_questions.id', $test_questions)
            ->where('assessment_account_id', $user_id)
            ->when($is_completed && $completed_at, function ($q) use($completed_at) {
                $q->where('test_questions.created_at', '<', $completed_at);
            })
            ->orderBy('attempt','desc')
            ->orderBy('assignment_questions.id', 'desc')
            ->groupBy('assignment_questions.question_id', 'desc')
            ->limit(count($test_questions))
            ->get();

        foreach ($aqs as $aq) {
            if ($aq->is_correct===1) {
                $marks_sum += $aq->marks;
            }
        }

        $secured_marks = $marks_sum;
        $max_marks = TestQuestion::whereIn('test_id', $course_test_ids)
                            ->when($is_completed && $completed_at, function ($q) use($completed_at) {
                                $q->where('created_at', '<', $completed_at);
                            })
                            ->sum('marks');

        //return $secured_marks . '/' . $max_marks;
        return $max_marks > 0 ? (($secured_marks / $max_marks) * 100)  : 0;
    }

    public function getAssignmentScorePercentageAttribute()
    {
        $assignments = $this->course->courseAssignments;

        $correct_ans_count = 0;
        $total_ans_count = 0;

        foreach ($assignments as $assignment) {
            $correct_ans_count += $assignment->assignmentQuestions->where('is_correct', 1)->count();
            $total_ans_count += $assignment->assignmentQuestions->count();
        }
        if ($total_ans_count > 0) {
            return ($correct_ans_count / $total_ans_count) * 100;
        }

        return 0;
    }

    public function scopePathway($q)
    {
        return $q->where('is_pathway', true);
    }
    public function scopeNotPathway($q)
    {
        return $q->where('is_pathway', false);
    }
}
