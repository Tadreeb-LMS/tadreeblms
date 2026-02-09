<?php


namespace App\Helpers;

use App\Http\Controllers\LessonsController;
use App\Models\Auth\User;
use App\Models\Course;
use App\Models\Category;
use App\Models\courseAssignment;
use App\Models\AssignmentQuestion;
use App\Models\{Assignment, Lesson, AttendanceStudent, ChapterStudent, StudentCourseFeedback, Certificate, Config, CourseModuleWeightage, EmployeeProfile, Test, TestQuestion, UserCourseDetail};
use App\Models\Stripe\SubscribeCourse;
use Auth;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class CustomHelper
{

    public static function getCaptcha()
    {
        $a = rand(1, 9);
        $b = rand(1, 9);

        session(['captcha_answer' => $a + $b]);

        return "$a + $b = ?";
    }

    public static function redirect_based_on_setting()
    {
        return Config::where('key', 'landing_page_toggle')->value('value') ?? 1;
        //return true;
    }


    public static function isCourseAlreadyCompleted($user_id, $course_id)
    {
        return SubscribeCourse::query()
            //->with(['course'])
            ->orderBy('id', 'Desc')
            ->where('course_id', $course_id)
            ->where('user_id', $user_id)
            ->where('grant_certificate', '1')
            ->where('is_completed', '1')
            ->where('assignment_progress', '100')
            ->count();
    }

    public static function updateGrantCertificate($course_id, $user_id)
    {
        $helper = new self();
        
        $row = SubscribeCourse::query()
            ->with(['course'])
            ->orderBy('id', 'Desc')
            ->where('course_id', $course_id)
            ->where('user_id', $user_id)
            ->first();
        if ($row) {
            $progress = $helper->getCourseProgress($row->course, $row, $row->user_id);
            $assesmentStatus = null;
            if ($row->course) {
                $assesmentStatus = @$row->course->assignmentStatus(@$row->user_id, $progress);
            }

            //dd($progress, $row->course->id, $assesmentStatus);


            if ($progress == 100) {
                $assessment_ok = !$row->has_assesment || $assesmentStatus === 'Passed';
                $feedback_ok   = !$row->has_feedback || $row->feedback_given;

                // If course has no assessment and no feedback, or both conditions are satisfied
                if ($assessment_ok && $feedback_ok) {
                    $row->grant_certificate = 1;
                    $row->is_completed = 1;
                } else {
                    $row->grant_certificate = 0;
                    $row->is_completed = 0;
                }
            } else {
                $row->grant_certificate = 0;
                $row->is_completed = 0;
            }

            $row->assignment_progress = $progress;

            $row->save();
        }
    }

    public static function updateToAllUserAssignedToCourse($course_id)
    {
        SubscribeCourse::query()
            ->with('user', 'course')
            ->where('course_id', $course_id)
            ->orderBy('id', 'desc')
            ->chunk(100, function ($rows) use ($course_id) {
                foreach ($rows as $row) {
                    $progress = self::getCourseProgress($row->course,  $row, $row->user_id);

                    $course_weightage = CourseModuleWeightage::where('course_id',$course_id)->first();

                    $min_passing_marks = $course_weightage->minimun_qualify_marks ?? 70;

                    if ($progress > 0 && $progress < $min_passing_marks) {
                        $progress_status =  'In progress';
                    } elseif ($progress >= $min_passing_marks) {
                        $progress_status =  'Completed';
                    }

                    if ($row->grant_certificate != 1) {
                        $row->update(
                            [
                                'assignment_progress' => $progress,
                            ]
                        );
                    }
                }
            });
    }

    public static function updateUserProgress($user_id, $course_id)
    {
        $helper = new self();
        //dd($user_id, $course_id);
        $sub_course = SubscribeCourse::query()
            ->with('user', 'course')
            ->where('user_id', $user_id)
            ->where('course_id', $course_id)
            ->first();

        if ($sub_course) {

            $progress =  $helper->getCourseProgress($sub_course->course,  $sub_course, $user_id);

            $course_weightage = CourseModuleWeightage::where('course_id',$course_id)->first();

            $min_passing_marks = $course_weightage->minimun_qualify_marks ?? 70;

            if ($progress > 0 && $progress < $min_passing_marks) {
                $progress_status =  'In progress';
            } elseif ($progress >= $min_passing_marks) {
                $progress_status =  'Completed';
            }

            //dd($progress, "update");
            $srore = null;
            $status = null;
            if ($sub_course->has_assesment == 1) {
                if (isset($user_id)) {
                    $srore = (string)$sub_course->assignmentScore($user_id);
                }
                $status = @$sub_course->course->assignmentStatus($user_id, $progress);
            }


            //dd($status, $srore);

            $sub_course->update(
                [
                    'assignment_status' => $status,
                    'assignment_progress' => $progress,
                    'assignment_score' => $srore,
                ]
            );

            self::updateGrantCertificate($course_id, $user_id);
        }
    }


    public  function getCourseProgress($course, $sub_data, $user_id)
    {
        if (!$course || !$sub_data) {
            return 100;
        }

        // 1️⃣ Certificate granted → full completion
        if ($sub_data->grant_certificate == 1) {
            return 100;
        }

        // 2️⃣ Flags
        $is_offline = $course->is_online != 'Online';
        $attended = $sub_data->is_attended == 1;

        $has_feedback = $sub_data->has_feedback;
        $feedback_given = $sub_data->feedback_given > 0;

        $has_assessment = $sub_data->has_assessment ?? $sub_data->has_assesment;

        $score = @$user_id ? @$sub_data->assignmentRawScore($user_id) : 0;

        $course_weightage = CourseModuleWeightage::where('course_id',$course->id)->first();

        $min_passing_marks = $course_weightage->minimun_qualify_marks ?? 70;


        if ($score >= $min_passing_marks) {
            $assignment_status = 'Passed';
        } else {
            $assignment_status = 'Failed';
        }

        //dd($assignment_status, $sub_data->assignment_status);

        $assessment_done =
            $has_assessment &&
            ($sub_data->assessment_taken ?? $sub_data->assesment_taken) > 0 &&
            $assignment_status === 'Passed';

        // 3️⃣ Lesson weight rules
        if ($has_assessment && $has_feedback) {
            $lesson_weight = $course_weightage->weightage['LessonModule'] ?? 75;
        } elseif ($has_assessment && !$has_feedback) {
            $lesson_weight = $course_weightage->weightage['LessonModule'] ?? 85;
        } elseif (!$has_assessment && $has_feedback) {
            $lesson_weight = $course_weightage->weightage['LessonModule'] ?? 90;
        } else {
            $lesson_weight = 100;  // no assessment + no feedback
        }

        $assessment_weight = $has_assessment ? $course_weightage->weightage['QuestionModule'] : 0;
        $feedback_weight   = $has_feedback ? $course_weightage->weightage['FeedbackModule'] : 0;

        // 4️⃣ Fetch lessons
        $lessons = Lesson::where('course_id', $course->id)
            ->where('published', 1)
            ->pluck('id');

        $total_lessons = count($lessons);
        $progress = 0;

        // 5️⃣ If no lessons → give FULL lesson weight
        if ($total_lessons == 0) {
            $progress = $lesson_weight;
        } else {
            // Otherwise calculate lesson % done
            $completed_lessons = 0;

            foreach ($lessons as $lessonId) {
                $status = self::lessonStatusEmployee(0, $lessonId, $course->id, $user_id);
                if ($status === 'Completed') {
                    $completed_lessons++;
                }
            }

            $progress += ($completed_lessons / $total_lessons) * $lesson_weight;
        }

        // 6️⃣ Add assessment & feedback
        if ($assessment_done) {
            $progress += $assessment_weight;
        }

        if ($has_feedback && $feedback_given) {
            $progress += $feedback_weight;
        }

        // 7️⃣ Offline attendance logic
        if ($is_offline && $attended) {
            if ($has_assessment && !$assessment_done) {
                $progress = min($progress, $lesson_weight);
            }

            if ($has_feedback && !$feedback_given) {
                $progress = min($progress, $lesson_weight + $assessment_weight);
            }
        }

        // 8️⃣ Cap at 100
        return min(100, round($progress));
    }








    public static function getCourseTrainerName($course_id)
    {


        $trainer_name = '';

        $courseUser = DB::table('course_user')->where('course_id', $course_id)->first();
        if ($courseUser) {
            $user = DB::table('users')->where('id', $courseUser->user_id)->first();
            $trainer_name = $user ? @$user->first_name . ' ' . @$user->last_name : '';
        }

        return  $trainer_name;
    }

    public static function progress($course_id, $user_id = 0)
    {
        $helper = new self(); // create instance

        if ($user_id == 0) {
            $user_id = Auth::user()->id;
        }


        $is_attended = 0;
        $progress_per = 0;
        $total_lessons = [];

        $sub_data = SubscribeCourse::query()
            ->with('user', 'course')
            ->where('user_id', $user_id)
            ->where('course_id', $course_id)
            ->first();
        //dd($sub_data);
        if ($course_id && $sub_data) {

            $is_attended = $sub_data->is_attended;

            $completed_at = isset($sub_data->completed_at) ? $sub_data->completed_at->format('Y-m-d H:i:s') : null;

            if ($sub_data->is_completed == 1 && !empty($completed_at)) {

                $total_lessons = Lesson::where('course_id', $course_id)
                    ->where('published', 1)
                    ->where('created_at', '<', $completed_at)
                    ->pluck('id');
                //dd($completed_at, $sub_data->is_completed, $total_lessons);

            } else {
                $total_lessons = Lesson::where('course_id', $course_id)
                    ->where('published', 1)
                    ->pluck('id');
            }
        }

        $courseController = new LessonsController;
        $hasAssessmentLink = $courseController->hasAssessmentLink($course_id, $user_id);
        $courseFeedbackLink = $courseController->courseFeedbackLink($course_id);

        $courseHasFeedbackLink = $courseController->courseHasFeedbackLink($course_id);


        $user_feedback = DB::table('user_feedback')
            ->where('course_id', $course_id)
            ->where('user_id', $user_id)
            ->count();
        if (!empty($user_feedback)) {
            $user_feedback = 1;
        } else {
            $user_feedback = 0;
        }


        if (isset($sub_data) && $sub_data->grant_certificate == 1) {
            return 100;
        }


        $course = Course::where('id', $course_id)->first();

        $total_lessons_count = count($total_lessons);
        // dd( $total_lessons_count, $completed_at );

        $by_invitation = $sub_data->by_invitation ?? 0;

        if ($total_lessons_count == 0 && $course->is_online == 'Online' && $by_invitation == 0) {
            return $total_plus = 0;
        }


        // if the course is added by invitation
        if ($by_invitation && $is_attended == 0 && $total_lessons_count == 0) {
            return 0;
        }



        if ($is_attended == 1 && $is_attended == 1 && $course->is_online == 'Offline') {

            //$progress_value = self::getCourseProgress($course, $sub_data, $user_id);
            $progress_value = $helper->getCourseProgress($course, $sub_data, $user_id);

            //dd( $progress_value );
            return $progress_value;
            //dd($progress_value);


        }


        $total_lessons_completes = 0;
        foreach ($total_lessons as $lesson) {
            $lessonStatusEmployee = self::lessonStatusEmployee(0, $lesson, $course_id, $user_id);
            //dd($lessonStatusEmployee);
            if ($lessonStatusEmployee == 'Completed') {
                $total_lessons_completes++;
            }
        }

        //dd($total_lessons_completes, $total_lessons);



        //dd( $user_feedback ); 1

        $lessonController = new LessonsController;
        if ($lessonController->isAssignmentTaken($user_id, $course_id)) {
            $test_count = 1;
        } else {
            $test_count = 0;
        }

        //dd($test_count); 1

        $total_plus = 0;



        $lesson_weight = 75;
        $assignment_weight = $hasAssessmentLink ? 15 : 0;
        $feedback_weight = $courseFeedbackLink ? 10 : 0;

        $total_weight = $lesson_weight + $assignment_weight + $feedback_weight;

        $lesson_weight = ($lesson_weight / $total_weight) * 100;
        $assignment_weight = ($assignment_weight / $total_weight) * 100;
        $feedback_weight = ($feedback_weight / $total_weight) * 100;

        //dd($hasAssessmentLink,  $courseFeedbackLink); true & link
        /**
         * checking if course has assessement / feedback question - managing total score accordingly
         */
        if ($total_lessons_count > 0) {
            $total_plus += ($total_lessons_completes / $total_lessons_count) * $lesson_weight;
        }

        // Assignment progress
        if ($hasAssessmentLink) {
            $total_plus += ($test_count > 0 ? $assignment_weight : 0);
        }

        // Feedback progress
        if ($courseFeedbackLink) {
            $total_plus += ($user_feedback > 0 ? $feedback_weight : 0);
        }

        $total_plus = intval($total_plus);

        if (!$hasAssessmentLink && !$courseFeedbackLink && $total_lessons_count == 0) {
            $total_plus = 100;
        }

        //$total_plus = self::getCourseProgress($course, $sub_data, $user_id);
       
        $total_plus = $helper->getCourseProgress($course, $sub_data, $user_id);

        if ($total_plus == 100) {
            if ($sub_data) {
                if ($sub_data->is_completed == 0) {

                    //update the progress as well
                    //dd($total_plus);

                    if ($sub_data->course_trainer_name == null) {
                        $trainer_name = '';

                        $courseUser = DB::table('course_user')->where('course_id', $course_id)->first();
                        if ($courseUser) {
                            $user = DB::table('users')->where('id', $user_id)->first();
                            $trainer_name = $user ? @$user->first_name . ' ' . @$user->last_name : '';
                        }
                    } else {
                        $trainer_name = $sub_data->course_trainer_name;
                    }
                    $progress = $total_plus;


                    //dd($trainer_name);

                    $status = null;
                    $srore = null;
                    if (isset($user_id) && $sub_data) {
                        $srore = (string)$sub_data->assignmentScore($user_id);
                        //dd($srore);
                        $status = $course->assignmentStatus($user_id, $total_plus);
                        //dd($status);
                    }

                    //dd($status, $srore, $trainer_name);

                    DB::table('subscribe_courses')->where('course_id', $course_id)
                        ->where('user_id', $user_id)
                        ->update(
                            [
                                'course_progress_status' => 2,
                                'course_trainer_name' => $trainer_name,
                                'assignment_progress' => $progress,
                                'assignment_status' => $status,
                                'assignment_score' => $srore,
                                'is_completed' => 1,
                                'completed_at' => Carbon::now()->format('Y-m-d H:i:s')
                            ]
                        );


                    // DB::table('subscribe_courses')->where('course_id', $course_id)
                    //         ->where('user_id', $user_id)
                    //         ->update(
                    //             [
                    //                 'is_completed' => 1,
                    //                 'completed_at' => Carbon::now()->format('Y-m-d H:i:s')
                    //             ]);
                }
            }
        }

        return $total_plus;
    }

    public static function courseProgress($course_id, $user_id = 0)
    {

        if ($user_id == 0) {
            $user_id = Auth::user()->id;
        }

        $total_lessons = Lesson::where('course_id', $course_id)->where('published', 1)->pluck('id');
        $total_lessons_count = count($total_lessons);
        $total_lessons_completes = 0;
        foreach ($total_lessons as $lesson) {
            $lessonStatusEmployee = self::lessonStatusEmployee(0, $lesson, $course_id, $user_id);
            if ($lessonStatusEmployee == 'Completed') {
                $total_lessons_completes++;
            }
        }

        $total_plus = 0;
        if ($total_lessons_count > 0) {
            $total_plus = intval(($total_lessons_completes) / ($total_lessons_count) * 100);
        }

        $courseController = new LessonsController;
        $hasAssessmentLink = $courseController->hasAssessmentLink($course_id, $user_id);
        $courseFeedbackLink = $courseController->courseFeedbackLink($course_id);
        if (!$hasAssessmentLink && !$courseFeedbackLink && $total_lessons_count == 0) {
            $total_plus = 100;
        }

        if ($total_plus == 100) {
            DB::table('subscribe_courses')->where('course_id', $course_id)
                ->where('user_id', $user_id)->update(['is_completed' => 1]);
        }

        return $total_plus;
    }

    public static function is_course_completed($student_id, $course_id)
    {
        $course_completed = UserCourseDetail::where('user_id', $student_id)
            ->where('course_id', $course_id)
            //->where('status','completed')
            ->first();
        //$total_lessons = Lesson::where('course_id',$course_id)->where('published',1)->count();
        if ($course_completed) {
            return $course_completed->status == 'completed' ? true : false;
        } else {
            return false;
        }
    }

    public static function is_course_completed_status($student_id, $course_id)
    {
        $course_completed = UserCourseDetail::where('user_id', $student_id)
            ->where('course_id', $course_id)
            //->where('status','completed')
            ->first();
        //$total_lessons = Lesson::where('course_id',$course_id)->where('published',1)->count();
        if ($course_completed) {
            return $course_completed->status == 'completed' ? '<span class="pill-publish">Completed</span>' : '<span class="badge badge-info">Inprogress</span>';
        } else {
            return '<span class="pill-unpublish">Not Started</span>';
        }
    }

    public static function is_user_course_has_feedback($student_id, $course_id)
    {
        $has_feedback = StudentCourseFeedback::where('course_id', $course_id)
            ->where('user_id', $student_id)
            ->count();
        if (isset($has_feedback) && $has_feedback > 0) {
            return true;
        } else {
            return false;
        }
    }


    public static function is_user_course_has_issed_certificate($student_id, $course_id)
    {
        $user_course = UserCourseDetail::where('course_id', $course_id)
            ->where('user_id', $student_id)
            ->first();
        if ($user_course) {
            return $user_course->issue_certificate === 'yes' ? $user_course : false;
        } else {
            return false;
        }
    }


    public static function totalEnrolled($course_id)
    {
        $total_enrolled = 0;
        $result = SubscribeCourse::where('course_id', $course_id)
            ->where('status', 1);
        // $query = str_replace(array('?'), array('\'%s\''), $result->toSql());
        // $query = vsprintf($query, $result->getBindings());
        // dump($query);
        // die;
        $result = $result->count();
        if ($result) {
            return $total_enrolled = $result;
        } else {
            return $total_enrolled;
        }
    }

    public static function get_student_attendance($lesson_id)
    {
        $count =  AttendanceStudent::where('lesson_id', $lesson_id)->count();
        return $count;
    }

    public static function ck_attendance_lesson($course_id, $lesson_id, $employee_id)
    {

        $count =  AttendanceStudent::where('lesson_id', $lesson_id)
            ->where('student_id', $employee_id)
            ->where('course_id', $course_id)
            ->count();

        if (isset($count) && $count > 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function courseStatus($course_id)
    {

        if (Auth::user()) {
            $is_course_assigned = courseAssignment::where('course_id', $course_id)->whereRaw("FIND_IN_SET(?, assign_to)", [Auth::user()->id])->count();
            //dd($is_course_assigned);
            if ($is_course_assigned > 0) {
                return 1;
            } else {
                $result = SubscribeCourse::where('course_id', $course_id)->where('user_id', Auth::user()->id)->first();

                //dd($result, Auth::user()->id,  $course_id);

                if ($result) {
                    return $result->status;
                } else {
                    return 3;
                }
            }
        } else {
            return 3;
        }
    }


    public static function getCategoryName($cat_id)
    {
        //dd(Auth::user()->id);
        $result = Category::where('id', $cat_id)->first();
        if ($result) {
            return $result;
        } else {
            return '';
        }
    }

    public static function getCourseName($course_id)
    {
        $result = Course::where('id', $course_id)->first();
        if ($result) {
            return $result['title'];
        } else {
            return '';
        }
    }




    public static function getUserName($user_id)
    {
        $result = User::where('id', $user_id)->first();
        if ($result) {
            return $result['first_name'];
        } else {
            return '';
        }
    }

    public static function getLessonDetail($lesson_id, $course_id, $employee_id)
    {
        $data = DB::table('chapter_students')
            ->where('model_id', $lesson_id)
            ->where('course_id', $course_id)
            ->where('user_id', $employee_id)
            ->first();
        return $data;
    }

    public static function isLessonTimeCompleted($duration = 0, $lesson_id, $course_id, $employee_id)
    {
        //dd($lesson_id);
        $data = CustomHelper::getLessonDetail($lesson_id, $course_id, $employee_id);
        if (isset($data)) {
            //echo $data->created_at.'<br>';
            $next_lesson_start_time = strtotime($data->created_at . " + " . $duration . " minute");
            //echo $next_lesson_start_time.'<br>';
            //echo strtotime(date('Y-m-d H:i:s'));
            // if(strtotime(date('Y-m-d H:i:s'))>=$next_lesson_start_time) {
            //     return true;
            // } else{
            //     return false;
            // }

            return true;


            // return true;


        }
    }

    public static function nextLessonActiveTime($duration = 0, $lesson_id, $course_id, $employee_id)
    {
        $data = CustomHelper::getLessonDetail($lesson_id, $course_id, $employee_id);
        if (isset($data)) {
            //echo $data->created_at.'<br>';
            $next_lesson_start_time = strtotime($data->created_at . " + " . $duration . " minute");

            $time_left = strtotime(date('Y-m-d H:i:s')) - $next_lesson_start_time;
            $time_left_minutes =  round(abs($time_left / 60), 2);
            return trans('lesson.time_left_next_lesson', ['attribute' => $time_left_minutes]);
        }
    }

    public static function get_course_completed_status($student_id, $course_id)
    {
        return 'Completed';
    }

    public static function totalLessons($course_id)
    {
        $user_id = Auth::user()->id;

        $sub_data = SubscribeCourse::query()
            ->where('user_id', $user_id)
            ->where('course_id', $course_id)
            ->first();
        if ($course_id && $sub_data) {
            $completed_at = isset($sub_data->completed_at) ? $sub_data->completed_at->format('Y-m-d H:i:s') : null;

            if ($sub_data->is_completed == 1 && !empty($completed_at)) {

                $total_lessons = Lesson::where('course_id', $course_id)
                    ->where('published', 1)
                    ->where('updated_at', '<', $completed_at)
                    ->count();
                //dd($completed_at, $sub_data->is_completed, $total_lessons);
                return $total_lessons;
            }
        }


        return Lesson::where('course_id', $course_id)
            ->where('published', 1)
            ->count();
    }

    public static function lessonStatusEmployee($duration = 0, $lesson_id, $course_id, $employee_id)
    {

        $completed_lessons = ChapterStudent::query()
            ->where('course_id', $course_id)
            ->where('user_id', $employee_id)
            ->get()
            ->pluck('model_id')
            ->toArray() ?? [];

        if (in_array($lesson_id, $completed_lessons)) {
            return 'Completed';
        } else {
            return 'In progress';
        }

        /*
        $data = DB::table('chapter_students')->select('*')
            ->where('model_id', $lesson_id)
            ->where('course_id', $course_id)
            ->where('user_id', $employee_id)
            ->first();

        //dd($data, $employee_id, $course_id, $lesson_id);

        if (isset($data)) {
            $next_lesson_start_time = strtotime($data->created_at . " + " . $duration . " minute");
            //dd($lesson_id, $course_id, $employee_id, $next_lesson_start_time);
            if (strtotime(date('Y-m-d H:i:s')) >= $next_lesson_start_time) {
                return 'Completed';
            } else {
                return 'In progress';
            }
        } else {
            return 'Pending';
        }
        */
    }

    public static function getAllEmployeeEmailsByDepartment($department_id)
    {
        $data = DB::table('users')->select('employee_profiles.user_id', 'users.email')
            ->leftJoin('employee_profiles', 'employee_profiles.user_id', 'users.id')
            ->where('employee_profiles.department', $department_id)
            ->whereNull('users.deleted_at')
            ->get();
        if (isset($data)) {
            return $data;
        } else {
            return false;
        }
    }


    public static function updateAutoSubscribe($department_employees, $course_id)
    {
        if (isset($department_employees) && count($department_employees) > 0) {
            foreach ($department_employees as $employee) {
                $data = [
                    'user_id' => $employee->user_id,
                    'course_id' =>  $course_id,
                    'status' => 1
                ];
                SubscribeCourse::updateOrCreate($data);
            }
        }
    }

    public static function is_test_taken($assessment_id, $logged_in_id)
    {
        $is_test_taken = AssignmentQuestion::where('assessment_test_id', $assessment_id)->where('assessment_account_id', $logged_in_id)->first();
        //dd($assessment_id, $logged_in_id);
        if ($is_test_taken) {
            return true;
        }
        return false;
    }

    public static function assignmentAttempts($assessment_id, $logged_in_id)
    {
        return AssignmentQuestion::where('assessment_test_id', $assessment_id)->where('assessment_account_id', $logged_in_id)->groupBy('assignment_id', 'assessment_account_id', 'attempt')->get()->count();
    }

    public static function toSnakeCase($string)
    {
        // Convert spaces and hyphens to underscores
        $string = preg_replace('/[ -]+/', '_', $string);

        // Remove non-alphanumeric characters except underscores
        $string = preg_replace('/[^A-Za-z0-9_]/', '', $string);

        // Convert to lowercase
        return strtolower($string);
    }

    public static function convertTimeZone($dateTimeString, $toTimeZone)
    {
        $fromTimeZone = config('app.timezone');
        // Create a Carbon instance from the given datetime string and time zone
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $dateTimeString, $fromTimeZone);

        // Convert to the desired time zone
        $convertedDate = $date->setTimezone($toTimeZone);

        return $convertedDate->format('d M Y, h:i A');
    }

    public static function syncCourseAssignmentAndSubscribeCourseData()
    {
        $cas = courseAssignment::get();

        foreach ($cas as $ca) {
            if (strpos($ca->assign_to, ',') !== false) {
                $usersAssigned = explode(',', $ca->assign_to);

                foreach ($usersAssigned as $value) {
                    $sc = SubscribeCourse::where('user_id', $value)->where('course_id', $ca->course_id)->exists();
                    if (!$sc) {
                        SubscribeCourse::create([
                            'user_id' => $value,
                            'course_id' => $ca->course_id,
                            'status' => 1,
                            'created_at' => $ca->assign_date,
                            'updated_at' => $ca->assign_date,
                        ]);
                    }
                }
            } else {
                $sc = SubscribeCourse::where('user_id', $ca->assign_to)->where('course_id', $ca->course_id)->exists();
                if (!$sc) {
                    SubscribeCourse::create([
                        'user_id' => $ca->assign_to,
                        'course_id' => $ca->course_id,
                        'status' => 1,
                        'created_at' => $ca->assign_date,
                        'updated_at' => $ca->assign_date,
                    ]);
                }
            }
        }
    }

    public static function completeCourseForUser($course_id, $user_id)
    {
        $lessons = Lesson::where('course_id', $course_id)->where('published', '=', 1)->get();

        foreach ($lessons as $lesson) {
            $lesson->chapterStudents()->create([
                'model_type' => get_class($lesson),
                'model_id' => $lesson->id,
                'user_id' => $user_id,
                'course_id' => $course_id
            ]);

            // Save the attendance
            $data = [
                'student_id' => $user_id,
                'course_id' => $course_id,
                'lesson_id' => $lesson->id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            AttendanceStudent::create($data);
        }
    }

    public static function emailTemplates($type, $lang, $variables)
    {
        $lang = $lang ? $lang : 'english';
        $templates = [
            'user_added' => [
                'arabic' => [
                    'email_content' => '<table>
                        <tr>
                            <td>
                                <h3> مرحباً {User_Name}</h3>
                                <p> أهلاً بك في أكاديمية دلتا</p>                                
                                <h4>يمكنك الدخول لحسابك عن طريق المعلومات التالية:</h4>
                            </td>
                        </tr>
                        <table>
                            <tr>
                                <td>
                                    <a href="{Academy_Website_Link}" style="display: inline-block; background-color: #3c4085;  color: white; padding: 15px 35px; text-decoration: none; border-radius: 30px; margin: 20px 0; font-weight: bold; font-size: 16px; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">انقر هنا</a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    
                                    الاسم {User_Name} 
                                    
                                </td>
                            </tr>
                            <tr>
                                <td>
                                   
                                        البريد الإلكتروني {User_Email}
                                    
                                </td>
                            </tr>   
                            <tr>
                                <td>
                                   
                                        كلمة المرور {Password}
                                   
                                </td>
                            </tr>  
                            <tr>
                                <td>
                                   
                                        نتمنى لك رحلة عظيمة معنا،
                                    
                                </td>
                            </tr>   
                            <tr>
                                <td>
                                    
                                        أكاديمية دلتا
                                    
                                </td>
                            </tr>
                        </table>
                    </table>',
                    'subject' => 'أهلاً بك في أكاديمية دلتا'
                ],
                'english' => [
                    'email_content' => '<table>
                        <tr>
                            <td>
                                <h3># Hello {User_Name},</h3>
                                <p>Welcome to Delta Academy.</p>
                                <h4>Please find your account credentials below:</h4>
                            </td>
                        </tr>
                        <table>
                            <tr>
                                <td>
                                    <a href="{Academy_Website_Link}" style="display: inline-block;  background-color: #3c4085; color: white; padding: 15px 30px; text-decoration: none; border-radius: 30px; margin: 20px 0; font-weight: bold; font-size: 14px; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">Click Here</a>
                                </td>
                            </tr>
                            <tr>
                                <td>Name: {User_Name}</td>
                            </tr>
                            <tr>
                                <td>Email: {User_Email}</td>
                            </tr>
                            <tr>
                                <td>Password: {Password}</td>
                            </tr>
                            <tr>
                                <td>We wish you an awesome journey with us</td>
                            </tr>
                            <tr>
                                <td>Delta Academy</td>
                            </tr>
                        </table>
                    </table>',
                    'subject' => 'Welcome to Delta Academy'
                ],
            ],
            'assignment_reminder' => [
                'arabic' => [
                    'email_content' => '<table>
                        <tr>
                            <td>
                                <h3>
                                    مرحباً {User_Name}
                                </h3>
                                <p>
                                    نذكرك بإكمال الدورة التدريبية المضافة لك قبل انتهاء الموعد المحدد لها ب {Due_Date}
                                </p>
                                <p>
                                    يمكنك الوصول للدورة مباشرة عن طريق الرابط التالي 
                                </p>
                                <a href={Course_Link} style="display: inline-block;  background-color: #3c4085;  color: white; padding: 15px 35px; text-decoration: none; border-radius: 30px; margin: 20px 0; font-weight: bold; font-size: 16px; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">رابط الدورة</a>
                                <p>
                                    لا تفوت الفرصة لتتعلم وتطور مهاراتك!
                                </p>
                            </td>
                        </tr>
                    </table>',
                    'subject' => 'تذكير بحضور الدورة التدريبية'
                ],
                'english' => [
                    'email_content' => '<table>
                        <tr>
                            <td>
                                <h3>Hello {User_Name},</h3>
                                <p>This is to kindly remind you to complete your assigned course before {Due_Date}</p>
                                <p>You can follow this link to lead you directly to the course </p>
                                <a href={Course_Link} style="display: inline-block;  background-color: #3c4085;color: white; padding: 15px 35px; text-decoration: none; border-radius: 30px; margin: 20px 0; font-weight: bold; font-size: 16px; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">Course Link</a>
                                <p>Don\'t miss this opportunity to learn & improve your skills!</p>
                            </td>
                        </tr>
                    </table>',
                    'subject' => 'Course Completion Reminder'
                ],
            ],
            'course_assignment' => [
                'arabic' => [
                    'email_content' => '<table>
                        <tr>
                            <td>
                                <h3>
                                    مرحباً {User_Name},
                                </h3>
                                <p>
                                    هل أنت مستعد لبدء رحلة جديدة من التعلم؟
                                </p>
                                <p>
                                    تمت إضافة لك دورة تدريبية جديدة تحت عنوان {Course_Name}
                                </p>
                                <p>                                    
                                    لطفاً اتبع الرابط التالي لبدء دورتك التدريبية 
                                </p>
                                <a href={Course_Link} style="display: inline-block; background-color: #3c4085;  color: white; padding: 15px 35px; text-decoration: none; border-radius: 30px; margin: 20px 0; font-weight: bold; font-size: 16px; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">رابط الدورة</a>
                                <p>
                                    نتمنى لك وقت ممتع.,
                                </p>
                            </td>
                        </tr>
                    </table>',
                    'subject' => 'دورة تدريبية جديدة'
                ],
                'english' => [
                    'email_content' => '<table>
                        <tr>
                            <td>
                                <h3>Hello {User_Name},</h3>
                                <p>Ready to start a new journey of learning ?</p>
                                <p>A new course is assigned to you under the name of {Course_Name}</p>
                                <p>Kindly follow the link to start your course </p>
                                <a href={Course_Link} style="display: inline-block;  background-color: #3c4085; color: white; padding: 15px 35px; text-decoration: none; border-radius: 30px; margin: 20px 0; font-weight: bold; font-size: 16px; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">Course Link</a>
                                <p>We wish you an enjoyable time.</p>
                            </td>
                        </tr>
                    </table>',
                    'subject' => 'New Assignment'
                ],
            ],
            'reset_password' => [
                'arabic' => [
                    'email_content' => '<table>
                        <tr>
                            <td>
                                <h3>
                                    مرحباً {User_Name}
                                </h3>
                                <p>
                                    لقد وصلنا طلبك بتغيير كلمة المرور لحسابك. بحال لم تقم بهذا الطلب من فضلك تجاهل هذه الرسالة.
                                </p>
                                <p>
                                    لإعادة تعيين كلمة المرور، اضغط على الرابط التالي:
                                </p>
                                <a href={Link} style="display: inline-block;  background-color: #3c4085;  color: white; padding: 15px 35px; text-decoration: none; border-radius: 30px; margin: 20px 0; font-weight: bold; font-size: 16px; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">إعادة تعيين كلمة المرور</a>
                                <p>                                    
                                    بحال تعثر عليك الوصول إلى الرابط، يمكنك نسخ الرابط التالي إلي المتصفح
                                </p>
                            </td>
                        </tr>
                    </table>,
                    {Link}',
                    'subject' => 'تم طلب إعادة تعيين كلمة المرور'
                ],
                'english' => [
                    'email_content' => '<table>
                        <tr>
                            <td>
                                <h3>Hello {User_Name}</h3>
                                <p>We received a request to reset your password. If you didn`t make this request, please ignore this email.</p>
                                <p>To reset your password, click the link below:</p>
                                <a href={Link} style="display: inline-block;  background-color: #3c4085;  color: white; padding: 15px 35px; text-decoration: none; border-radius: 30px; margin: 20px 0; font-weight: bold; font-size: 16px; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">Reset Password</a>
                                <p>If the button above doesn`t work, copy and paste the following link into your browser: {Link}</p>
                            </td>
                        </tr>
                    </table>',
                    'subject' => 'Password reset requested'
                ],
            ],
            'pathway_assignment' => [
                'arabic' => [
                    'email_content' => '<table>
                        <tr>
                            <td>
                                <h3>
                                    مرحباً {User_Name}
                                </h3>
                                <p>
                                    هل أنت مستعد لبدء رحلة جديدة من التعلم؟
                                </p>
                                <p>تمت إضافة لك مسار تدريبي جديد تحت عنوان #{Course_Title}</p>
                                <p>لطفاً اتبع الرابط التالي لبدء دورتك التدريبية </p>
                                <a href="{Link}" style="display: inline-block;  background-color: #3c4085; color: white; padding: 15px 35px; text-decoration: none; border-radius: 30px; margin: 20px 0; font-weight: bold; font-size: 16px; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">انقر هنا</a>
                                <p>نتمنى لك وقت ممتع.</p>
                            </td>
                        </tr>
                    </table>',
                    'subject' => 'دورة تدريبية جديدة | Delta Academy'
                ],
                'english' => [
                    'email_content' => '<table>
                        <tr>
                            <td>
                                <h3>Hello {User_Name}</h3>
                                <p>Ready to start a new journey of learning ?</p>
                                <p>A new pathway is assigned to you under the name of {Course_Title}</p>
                                <p>Kindly follow the link to start your course </p>
                                <a href="{Link}" style="display: inline-block;  background-color: #3c4085; color: white; padding: 15px 35px; text-decoration: none; border-radius: 30px; margin: 20px 0; font-weight: bold; font-size: 16px; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">Click Here</a>
                                <p>We wish you an enjoyable time.</p>
                            </td>
                        </tr>
                    </table>',
                    'subject' => 'New Assignment | Delta Academy'
                ],
            ],
            'invitation_assignment' => [
                'arabic' => [
                    'email_content' => '<table>
                        <tr>
                            <td>
                                <h3>عزيزي {User_Name}</h3>
                                <p>أتمنى أن تكون بصحة جيدة،</p>
                                <p>أنت مدعو لحضور الدورة التدريبية القادمة: {Course_Name}</p>
                                <p>التاريخ: {Course_Date}</p>
                                <p>الوقت: {Course_Time}</p>
                                <p>لطفاً انضم لنا بالتوقيت المحدد عبر الرابط التالي: {Meet_Link}</p>
                                <br>
                                <p>ننتظر حضورك ومشاركتك،<br> تحياتنا الطيبة،</p>
                            </td>
                        </tr>
                    </table>',
                    'subject' => 'دعوة لحضور {Course_Name}'
                ],
                'english' => [
                    'email_content' => '<table>
                        <tr>
                            <td>
                                <h3>Dear {User_Name}</h3>
                                <p>I hope you\'re doing well</p>
                                <p>You\'re invited to attend our upcoming course: {Course_Name}</p>
                                <p>Date: {Course_Date}</p>
                                <p>Time: {Course_Time}</p>
                                <p>Please join us on time {location_link}: {Meet_Link}</p>
                                <br>
                                <p>Looking forward to your participation.<br> Best regards</p>
                            </td>
                        </tr>
                    </table>',
                    'subject' => 'Invitation to {Course_Name}'
                ],
            ],
        ];

        $template = $templates[$type][$lang];

        // top headers
        $template['email_heading'] = $lang == 'english' ? 'Welcome to Delta Academy' : 'Welcome to Delta Academy';

        $template['sub_heading'] = $lang == 'english' ? 'A journey of learning and development' : 'A journey of learning and development';

        $template['subject'] = $template['subject'];

        foreach ($variables as $key => $value) {
            $template['email_content'] = str_replace($key, $value, $template['email_content']);
            $template['subject'] = str_replace($key, $value, $template['subject']);
        }

        //dd($template);

        return $template;
    }


    public static function uploadToS3($file, $filename, $folder = 'staging')
    {
        // Ensure folder ends with exactly one '/'
        $folder = $folder ? rtrim($folder, '/') . '/' : '';

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Detect MIME type
        if ($extension === 'mp4') {
            $mimeType = 'video/mp4';
        } elseif ($extension === 'mp3') {
            $mimeType = 'audio/mpeg';
        } elseif ($extension === 'mov') {
            $mimeType = 'video/quicktime';
        } elseif ($extension === 'm4a') {
            $mimeType = 'audio/mp4';
        } elseif ($extension === 'pdf') {
            $mimeType = 'application/pdf';
        } elseif ($extension === 'jpg' || $extension === 'jpeg') {
            $mimeType = 'image/jpeg';
        } elseif ($extension === 'png') {
            $mimeType = 'image/png';
        } else {
            $mimeType = 'application/octet-stream';
        }

        // Generate unique file name
        $fileName = time() . '-' . uniqid() . '.' . $extension;
        $s3Path = $folder . $fileName;

        // Get file content
        if ($file instanceof \Illuminate\Http\UploadedFile) {
            $fileStream = file_get_contents($file->getRealPath());
        } elseif (is_string($file) && file_exists($file)) {
            $fileStream = file_get_contents($file);
        } elseif (is_string($file)) {
            $fileStream = $file; // raw string content
        } else {
            throw new \Exception("Invalid file input.");
        }

        // Upload to S3
        Storage::disk('s3')->put($s3Path, $fileStream, [
            'visibility'   => 'private',
            'ContentType'  => $mimeType,
        ]);

        return $s3Path;

        // Return Temporary URL (1 hour)
        // return Storage::disk('s3')->temporaryUrl(
        //     $s3Path,
        //     now()->addMinutes(60)
        // );
    }


    public static function getNextTask($sc, $course_id)
    {
        $helper = new self();
        $failed_in_assesment_all_attempts = false;
        $open_assesment = false;
        $open_feedback = false;
        $completed_assesment = false;
        $reattempt_assesment = false;

        $course_detail = Course::query()
            ->where('id',  $course_id)
            ->first();

        //self::getCompletedLessons($sc, $course_id);


        if ($course_detail->is_online == 'Online') {
            //dd("ON");
            $isAllLessonsCommpleted = $helper->getIsAllLessonsCompleted($sc, $course_id);
            //dd($isAllLessonsCommpleted);
            if ($sc->is_completed == 0 && $isAllLessonsCommpleted) {
                //dd("hh");
                return $helper->openAssesmentOrFeedback($sc, $course_id, $open_assesment, $open_feedback);
            }
            if ($isAllLessonsCommpleted == 0) {
                return [
                    'failed_in_assesment_all_attempts' => $failed_in_assesment_all_attempts,
                    'reattempt_assesment' => false,
                    'completed_assesment' => false,
                    'download_certificate' => false,
                    'open_assesment' => false,
                    'open_feedback' => false,
                ];
            }
            return $helper->openAssesmentOrFeedback($sc, $course_id, $open_assesment, $open_feedback);
        } else { //Offline or ClassRoom
            //dd("OF");
            $course_lessons  = Lesson::where('course_id', $course_id)
                ->where('published', 1)
                ->get();
            $lessonCount = isset($course_lessons) ? $course_lessons->count() : 0;

            if ($lessonCount == 0) { // no lessons
                //dd("hh");
                if ($sc->is_attended && $sc->is_completed == 0) {
                    return $helper->openAssesmentOrFeedback($sc, $course_id, $open_assesment, $open_feedback);
                }
                //dd("asad");
            } else { //has lessons now
                //dd("hh3");
                if ($sc->is_attended && $sc->is_completed == 0) {

                    $isAllLessonsCommpleted = $helper->getIsAllLessonsCompleted($sc, $course_id);

                    if ($sc->is_completed == 0 && $isAllLessonsCommpleted) {
                        return $helper->openAssesmentOrFeedback($sc, $course_id, $open_assesment, $open_feedback);
                    }
                }
            }
        }

        $download_certificate = $sc->grant_certificate ?? false;
        $completed_assesment = $sc->assignment_status == 'Passed' && $sc->assesment_taken ? true : false;

        $reattempt_assesment_count = $helper->getAttemptToAssesment($sc, $course_id);

        if ($reattempt_assesment_count > 0) {
            $open_assesment = false;
        }

        if ($reattempt_assesment_count > 0 && !$completed_assesment) {
            $open_assesment = false;
            $reattempt_assesment = true;
        }

        if ($reattempt_assesment_count > 1) {
            $completed_assesment = $sc->assignment_status == 'Passed' && $sc->assesment_taken ? true : false;
            if ($completed_assesment) {
                $reattempt_assesment = false;
            }

            if ($sc->assignment_status == 'Failed') {
                $reattempt_assesment = false;
                $failed_in_assesment_all_attempts = true;
            }
        }

        return [
            'failed_in_assesment_all_attempts' => $failed_in_assesment_all_attempts,
            'reattempt_assesment' => $reattempt_assesment,
            'completed_assesment' => $completed_assesment,
            'download_certificate' => $download_certificate,
            'open_assesment' => $open_assesment,
            'open_feedback' => $open_feedback,
        ];
    }

    public function openAssesmentOrFeedback($sc, $course_id, $open_assesment, $open_feedback)
    {

        $helper = new self();

        $failed_in_assesment_all_attempts = false;
        $reattempt_assesment = false;
        $download_certificate = $sc->grant_certificate ?? false;

        $completed_assesment = $sc->assignment_status == 'Passed' && $sc->assesment_taken ? true : false;

        $open_assesment = $sc->has_assesment ? true : false;



        if ($sc->has_assesment) {
            if ($sc->assesment_taken && $sc->assignment_status == 'Passed') {
                $open_feedback = $sc->has_feedback ? true : false;
            }
        } else { // No Assesment
            $open_feedback = $sc->has_feedback ? true : false;
        }

        //dd($open_assesment, $open_feedback);

        $reattempt_assesment_count = $helper->getAttemptToAssesment($sc, $course_id);
        //dd($reattempt_assesment_count);

        if ($reattempt_assesment_count > 0) {
            $open_assesment = false;
        }

        if ($reattempt_assesment_count > 0 && !$completed_assesment) {
            $open_assesment = false;
            $reattempt_assesment = true;
        }

        if ($reattempt_assesment_count > 1) {
            $completed_assesment = $sc->assignment_status == 'Passed' && $sc->assesment_taken ? true : false;
            if ($completed_assesment) {
                $reattempt_assesment = false;
            }

            if ($sc->assignment_status == 'Failed') {
                $reattempt_assesment = false;
                $failed_in_assesment_all_attempts = true;
            }
        }



        if ($sc->assignment_progress == 90 && $sc->has_feedback && $download_certificate == 0) {
            $open_feedback = true;
        }

        if ($download_certificate == 1 && $sc->assignment_progress == 100) {
            $open_feedback = false;
        }

        if ($completed_assesment == 1) {
            $open_assesment = false;
        }


        return [
            'failed_in_assesment_all_attempts' => $failed_in_assesment_all_attempts,
            'reattempt_assesment' => $reattempt_assesment,
            'completed_assesment' => $completed_assesment,
            'download_certificate' => $download_certificate,
            'open_assesment' => $open_assesment,
            'open_feedback' => $open_feedback,
        ];
    }

    public function getAttemptToAssesment($sc, $course_id)
    {
        $logged_in_user_id = $sc->user_id ?? null;

        if ($logged_in_user_id) {
            $employee_profile = EmployeeProfile::where('user_id', $logged_in_user_id)->first();
            $logged_in_department_id = $employee_profile ? $employee_profile->department : null;



            if (!empty($employee_profile) && !empty($logged_in_department_id)) {

                $assignment = CourseAssignment::with(['assessment', 'assessment.course'])
                    ->whereRaw('FIND_IN_SET(?, assign_to) > 0', $logged_in_user_id)
                    ->where('course_assignment.course_id', $course_id)
                    ->whereNotNull('course_id')
                    ->latest('course_assignment.id')
                    ->first();
                //dd($assignment);
            }



            if (!isset($assignment)) {
                $assignment = CourseAssignment::with(['assessment', 'assessment.course'])
                    ->where('assign_to', $logged_in_user_id)
                    ->where('course_assignment.course_id', $course_id)
                    ->latest('course_assignment.id')
                    ->first();
            }

            //dd( $assignment);        

            if ($assignment) {
                $test_taken = CustomHelper::assignmentAttempts($assignment->assessment->id, $sc->user_id);
                return $test_taken;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    public function getIsAllLessonsCompleted($sc, $course_id)
    {

        $course_lessons  = Lesson::where('course_id', $course_id)
            ->where('published', 1)
            ->get();
        $lessonCount = $course_lessons->count();

        $completed_lessons = \Auth::user()->chapters()
            ->where('course_id', $course_id)
            ->get()
            ->pluck('model_id')
            ->count();
        //dd($lessonCount, $completed_lessons);

        if ($lessonCount >  $completed_lessons) {
            return 0;
        }
        if ($lessonCount == $completed_lessons) {
            return 1;
        }
    }
}
