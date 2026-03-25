<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\LearningPathwayAssignment;
use App\Models\UserLearningPathway;
use App\Models\Stripe\SubscribeCourse;
use App\Helpers\CustomHelper;
use App\Models\Assignment;
use App\Models\Lesson;
use Illuminate\Support\Facades\DB;
use App\Models\ChapterStudent;
use App\Models\courseAssignment;
use App\Models\CourseFeedback;
use App\Models\EmployeeProfile;
use App\Models\FeedbackQuestion;
use App\Models\Media;
use App\Models\UserFeedback;
use App\Models\VideoProgress;

class UpdateHasAssesmentSubscribeCourses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-has-assignment-subscribe-courses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {


        try {
            SubscribeCourse::with(['user', 'course'])
                ->whereHas('course')
                ->whereHas('user', function ($query) {
                    $query->where('employee_type', 'internal'); 
                    //$query->where('id','4939'); // Filter on the 'user' model's status
                })
                ->whereHas('course')
                ->whereHas('course', function ($query) {
                    //$query->where('id','421');
                    //$query->where('is_online','Online');
                })
                ->orderBy('id', 'desc')
                ->chunk(100, function ($rows) {
                    foreach ($rows as $row) {

                        $course_progress_status = 0;
                        $has_feedback = false;
                        $feedback_given = false;

                        $has_assesment = false;
                        $course_id = $row->course_id;
                        $logged_in_user_id = $row->user_id;


                        //for feedback
                        $has_feedback = CourseFeedback::query()
                                    ->where('course_id', $course_id)
                                    ->count();

                        //dd($has_feedback);

                        $feedback_given = UserFeedback::query()
                                    ->where('user_id', $logged_in_user_id)
                                    ->where('course_id',$course_id)
                                    ->count();


                        $agmt = Assignment::where('assignments.course_id', $course_id)
                                    ->join('courses', 'courses.id', '=', 'assignments.course_id')
                                    ->join('course_assignment', 'course_assignment.course_id', '=', 'courses.id')
                                    ->join('tests', 'tests.id', '=', 'assignments.test_id')
                                    ->join('test_questions', 'test_questions.test_id', '=', 'tests.id')
                                    ->whereRaw('FIND_IN_SET(?, `assign_to`) > 0', $logged_in_user_id)
                                    ->exists();
                        if ($agmt) {
                            $has_assesment = true;
                        }

                        $employee_profile = EmployeeProfile::where('user_id', $logged_in_user_id)->first();
                        $logged_in_department_id = $employee_profile ? $employee_profile->department : null;
                        if (!empty($employee_profile) && !empty($logged_in_department_id)) {
                            $assignments = courseAssignment::with(['assessment', 'assessment.course'])
                                ->whereRaw('FIND_IN_SET(?, `assign_to`) > 0', $logged_in_user_id)
                                ->where('course_assignment.course_id', $course_id)
                                ->whereNotNull('course_id')
                                ->get();
                        } else {
                            $assignments = courseAssignment::with(['assessment', 'assessment.course'])
                                ->where('assign_to', $logged_in_user_id)
                                ->where('course_assignment.course_id', $course_id)
                                ->get();
                        }

                        $assignment_taken = false;
                        foreach ($assignments as $q) {
                            $assignment_taken = false;
                            $test_taken = CustomHelper::is_test_taken(@$q->assessment->id, $logged_in_user_id);
                            if ($test_taken) {
                                $assignment_taken = true;
                            }
                        }


                        $is_completed = $row->is_completed;


                        if($row->course->is_online == 'Offline') {
                            if($row->is_attended == 1 && $row->is_completed == 0) {
                                $course_progress_status = 1;
                            }
                            if($row->is_attended == 1 && $row->is_completed == 1) {
                                if($has_feedback && $has_assesment) {
                                    if($feedback_given && $assignment_taken) {
                                        $course_progress_status = 2;
                                    } else {
                                        $course_progress_status = 1;
                                    }
                                } else {
                                    $course_progress_status = 2;
                                }
                            }
                        } elseif ($row->course->is_online == 'Live-Classroom') {
                            if($row->is_attended == 1 && $row->is_completed == 0) {
                                $course_progress_status = 1;
                            }
                            if($row->is_attended == 1 && $row->is_completed == 1) {
                                if($has_feedback && $has_assesment) {
                                    if($feedback_given && $assignment_taken) {
                                        $course_progress_status = 2;
                                    } else {
                                        $course_progress_status = 1;
                                    }
                                } else {
                                    $course_progress_status = 2;
                                }
                            }
                        } else { // Online course

                            

                            

                                

                                if($has_assesment && !$has_feedback) {
                                    if($assignment_taken) {
                                        $course_progress_status = 2;
                                        $is_completed = 1;
                                        //dd("hfhfh");
                                    } else {
                                        $course_progress_status = 1;
                                        $is_completed = 0;
                                        //dd("44444");
                                    }
                                }
                                if(!$has_assesment && $has_feedback) {
                                    if($feedback_given) {
                                        $course_progress_status = 2;
                                        $is_completed = 1;
                                        //dd("hfhfh");
                                    } else {
                                        $course_progress_status = 1;
                                        $is_completed = 0;
                                        //dd("44444");
                                    }
                                }
                                if($has_feedback && $has_assesment) {
                                    if($feedback_given && $assignment_taken) {
                                        $course_progress_status = 2;
                                        $is_completed = 1;
                                    } else {
                                        $course_progress_status = 1;
                                        $is_completed = 0;
                                    }
                                } 
                            
                            

                            //dd($course_progress_status);

                            if(1) {//if($row->is_completed == 0) {

                                $total_lessons = Lesson::where('course_id', $course_id)
                                    ->where('published', 1)
                                    ->pluck('id')
                                    ->toArray() ?? [];

                                $total_media_ids = Media::whereIn('model_id', $total_lessons)
                                    ->pluck('id')
                                    ->toArray() ?? [];
                                
                                $use_has_started = VideoProgress::whereIn('media_id', $total_media_ids)
                                            ->where('user_id', $logged_in_user_id)
                                            ->where('progress_per', '>' , 90)
                                            ->count();

                                if($use_has_started)  {
                                    $row->update([
                                        'course_progress_status' => 1
                                    ]);
                                } else {
                                    $row->update([
                                        'course_progress_status' => 0
                                    ]);
                                }          
                                
                            }
                            
                        }

                        if($row->assignment_status == 'Passed' || $row->assignment_status == 'Failed') {
                            $has_assesment = 1;
                            $assignment_taken = 1;
                        }

                        $row->update(
                            [
                                'has_feedback' => $has_feedback > 0 ? 1 : 0,
                                'feedback_given' => $feedback_given > 0 ? 1 : 0,
                                'has_assesment' => $has_assesment,
                                'assesment_taken' => $has_assesment == true ? $assignment_taken : 0,
                                'course_progress_status' => $course_progress_status,
                            ]
                        );

                        if ($row->course && $row->course->is_online == 'Online') {
                            CustomHelper::updateGrantCertificate($course_id, $logged_in_user_id);
                        }
                    }
                });
        } catch (\Exception $e) {
        }
    }
}
