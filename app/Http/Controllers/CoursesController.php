<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\{EmployeeProfile, Course, courseAssignment, CourseAssignmentToUser, LearningPathwayCourse, LiveSession, LiveSessionAttendance};
use App\Models\Auth\{User};
use App\Models\Review;
use App\Models\Stripe\SubscribeCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Customer;
use Cart;
// use App\Models\stripe\{Subscription,SubscribeCourse};
use DB;
use Carbon\Carbon;
use Auth;
use CustomHelper;
use App\Models\UserLearningPathway;
use Yajra\DataTables\Facades\DataTables;

class CoursesController extends Controller
{

    private $path;

    public function __construct()
    {
        $path = 'frontend';
        if (session()->has('display_type')) {
            if (session('display_type') == 'rtl') {
                $path = 'frontend-rtl';
            } else {
                $path = 'frontend';
            }
        } else if (config('app.display_type') == 'rtl') {
            $path = 'frontend-rtl';
        }
        $this->path = $path;
    }

    /**
     * Generate certificate for completed course
     */
    public function generateCertificate(Request $request)
    {
        //dd($request->all());
        $user_id = 80;
        $course_id = 82;
        //dd($user_id);

        $course = Course::where('id', '=', $course_id);
        $course = $course->first();

        if (1) {
            // $certificate = Certificate::firstOrCreate([
            //     'user_id' => auth()->user()->id,
            //     'course_id' => $request->course_id
            // ]);

            $data = [
                'name' => 'Anup kumar bhakta',
                'course_name' => $course->title,
                'date' => Carbon::now()->format('d M, Y'),
            ];
            $certificate_name = 'Certificate-' . $course->id . '-' . $user_id . '.pdf';

            //return view('certificate.index', compact('data'));
            $pdf = \PDF::loadView('certificate.index', compact('data'))->setPaper('', 'landscape');;
            $pdf->save(public_path('storage/certificates/' . $certificate_name));
            return response()->file(public_path('storage/certificates/' . $certificate_name));
            //return back()->withFlashSuccess(trans('alerts.frontend.course.completed'));
        }
    }

    public function all()
    {
        $perPage = 9;
        if (request('type') == 'popular') {
            $courses = Course::withoutGlobalScope('filter')->canDisableCourse()->where('published', 1)->where('popular', '=', 1)->orderBy('id', 'desc')->paginate($perPage);
        } else if (request('type') == 'trending') {
            $courses = Course::withoutGlobalScope('filter')->canDisableCourse()->where('published', 1)->where('trending', '=', 1)->orderBy('id', 'desc')->paginate($perPage);
        } else if (request('type') == 'featured') {
            $courses = Course::withoutGlobalScope('filter')->canDisableCourse()->where('published', 1)->where('featured', '=', 1)->orderBy('id', 'desc')->paginate($perPage);
        } else {
            $courses = Course::withoutGlobalScope('filter')->canDisableCourse()->where('published', 1)->orderBy('id', 'desc')->paginate($perPage);
        }
        $purchased_courses = NULL;
        $purchased_bundles = NULL;
        $categories = Category::where('status', '=', 1)->get();

        $recent_news = Blog::orderBy('created_at', 'desc')->take(2)->get();

        if (\Auth::check()) {
            //If student is internal user or external user
            $logged_in_user_id = \Auth::id();

            $purchased_courses = Course::withoutGlobalScope('filter')->canDisableCourse()
                ->whereHas('students', function ($query) use($logged_in_user_id) {
                    $query->where('id', $logged_in_user_id);
                })
                ->with('lessons')
                ->orderBy('id', 'desc')
                ->get();

            if(auth()->user()->employee_type == 'internal') {
                $featured_courses = [];
                $courses = [];
                $learning_courses = UserLearningPathway::where('user_id', auth()->id())->with('learningPathway.learningPathwayCoursesOrdered.course')->groupBy('pathway_id')->get();
                //dd(auth()->id());
                if($learning_courses) {
                    foreach($learning_courses as $course) {
                        foreach($course->learningPathway->learningPathwayCoursesOrdered as $c) {
                            if($c->course->id) {
                                $courses[] = $c->course->id;
                            }
                            
                        }
                        
                    }
                }
                $courses = Course::whereIn('id',$courses)->paginate($perPage);
                //dd($courses);
                
    
                return view($this->path . '.courses.index', compact('courses', 'purchased_courses', 'recent_news', 'featured_courses', 'categories'));
            }
            
        }
        $featured_courses = Course::withoutGlobalScope('filter')->canDisableCourse()->where('published', '=', 1)
            ->where('featured', '=', 1)->take(8)->get();

        return view($this->path . '.courses.index', compact('courses', 'purchased_courses', 'recent_news', 'featured_courses', 'categories'));
    }

    public function allCme()
    {
        // dd('hi');
        if (request('type') == 'popular') {
            $courses = Course::withoutGlobalScope('filter')->canDisableCourse()->where('cms', 1)->orderBy('id', 'desc')->paginate(9);
        } else if (request('type') == 'trending') {
            $courses = Course::withoutGlobalScope('filter')->canDisableCourse()->where('cms', 1)->orderBy('id', 'desc')->paginate(9);
        } else if (request('type') == 'featured') {
            $courses = Course::withoutGlobalScope('filter')->canDisableCourse()->where('cms', 1)->orderBy('id', 'desc')->paginate(9);
        } else {
            $courses = Course::withoutGlobalScope('filter')->canDisableCourse()->where('cms', 1)->orderBy('id', 'desc')->paginate(9);
        }
        $purchased_courses = NULL;
        $purchased_bundles = NULL;
        $categories = Category::where('status', '=', 1)->get();

        if (\Auth::check()) {
            $purchased_courses = Course::withoutGlobalScope('filter')->canDisableCourse()->whereHas('students', function ($query) {
                $query->where('id', \Auth::id());
            })
                ->with('lessons')
                ->orderBy('id', 'desc')
                ->get();
        }
        $featured_courses = Course::withoutGlobalScope('filter')->canDisableCourse()->where('published', '=', 1)
            ->where('featured', '=', 1)->where('cms', 1)->take(8)->get();

        $recent_news = Blog::orderBy('created_at', 'desc')->take(2)->get();
        return view($this->path . '.courses.cme-index', compact('courses', 'purchased_courses', 'recent_news', 'featured_courses', 'categories'));
    }

    public function show_($course_slug)
    {
        
        $user = \Auth::user();
        $course = Course::withoutGlobalScope('filter')->where('slug', $course_slug)->with('publishedLessons')->first();



        //dd($course->publishedLessons[0]->slug);

        if (!$user) {
            return redirect('request-course/' . $course->slug);
        }

        $countries = DB::table('master_countries')->get();
        $continue_course = NULL;

        $recent_news = Blog::orderBy('created_at', 'desc')->take(2)->get();


        
        // checking if course is offline
        if (!$course->is_online_course) { 

            
            $course_id = $course->id;

            $assessment_link = "";

            $lessonController = new LessonsController;
    
            $logged_in_user_id = auth()->user()->id;
            $isAssignmentTaken = $lessonController->isAssignmentTaken($logged_in_user_id, $course_id);
            $hasAssessmentLink = $lessonController->hasAssessmentLink($course_id, $logged_in_user_id);
            $feedbackLink = $lessonController->courseFeedbackLink($course_id);
            $courseFeedbackLink = '';
            $notGivenFeedback = true;

            // updating course progress
            $progress = CustomHelper::progress($course_id);

            if ($hasAssessmentLink) {
                $assessment_link = $lessonController->assessmentLink($logged_in_user_id, $course_id);
            }
            if ($isAssignmentTaken && $feedbackLink) {
                $courseFeedbackLink = $feedbackLink;
            }

            //dd( $courseFeedbackLink, $hasAssessmentLink );

            //dd($this->path . '.courses.offline-course');

            return view($this->path . '.courses.offline-course', compact('course', 'assessment_link', 'courseFeedbackLink', 'hasAssessmentLink', 'isAssignmentTaken'));
        }
        
        $purchased_course = \Auth::check() && $course->students()->where('user_id', \Auth::id())->count() > 0;
        
        if (isset($user->id)) {
            $course_assignment = \DB::table('course_assignment')->where('course_id', $course->id)->where('assign_to', $user->id)->first();
        }


        $course_rating = 0;
        $total_ratings = 0;
        $completed_lessons = "";
        $is_reviewed = false;
        if (\Auth::check()) {

            $completed_lessons = \Auth::user()->chapters()
                    ->where('course_id', $course->id)
                    ->get()
                    ->pluck('model_id')
                    ->toArray();

            $course_lessons = $course->lessons->pluck('id')->toArray();
            //dd($course_lessons);

            $continue_course = $course->courseTimeline()
                ->whereIn('model_id', $course_lessons)
                ->orderby('sequence', 'asc')
                ->whereNotIn('model_id', $completed_lessons)
                ->first();
            if ($continue_course == null) {
                $continue_course = $course->courseTimeline()
                    ->whereIn('model_id', $course_lessons)
                    ->orderby('sequence', 'asc')->first();
            }
            $checkSubcribePlan = '';
        }
        
        if (auth()->check() && $course->reviews()->where('user_id', '=', auth()->user()->id)->first()) {
            $is_reviewed = true;
        }

        if ($course->reviews->count() > 0) {
            $course_rating = $course->reviews->avg('rating');
            $total_ratings = $course->reviews()->where('rating', '!=', "")->get()->count();
        }
        if (!empty($course_lessons)) {
            $lessons = $course->courseTimeline()->whereIn('model_id', $course_lessons)->orderby('id', 'asc')->get();
        } else {
            $lessons = $course->courseTimeline()->orderby('id', 'asc')->get();
        }
        $checkSubcribePlan = [];

        $lessonCount = $course->publishedLessons($course->id)->count();
        //dd($lessonCount);

        $courseInPlan = courseOrBundlePlanExits($course->id, '');
        //dd($courseInPlan);

        // Upcoming live sessions for this course
        $liveSessions = LiveSession::where('course_id', $course->id)
            ->where('session_date', '>=', Carbon::today())
            ->orderBy('session_date')
            ->orderBy('session_time')
            ->get();

        $isHostRole = \Auth::check() && (\Auth::user()->isAdmin() || \Auth::user()->hasRole('teacher'));

        return view($this->path . '.courses.course', compact('lessonCount', 'course', 'purchased_course', 'recent_news', 'course_rating', 'completed_lessons', 'total_ratings', 'is_reviewed', 'lessons', 'continue_course', 'checkSubcribePlan', 'courseInPlan', 'countries', 'liveSessions', 'isHostRole'));
    }

    public function show($course_slug)
    {
        //dd($course_slug);
        $user = \Auth::user();
       
        $isGrantCertificate = false;

        //dd($course);

        //dd($course->publishedLessons[0]->slug);

        

        $course = Course::withoutGlobalScope('filter')->where('slug', $course_slug)->with('publishedLessons')->first();

        //dd($course);

        if (!$user) {
            return redirect('request-course/' . $course->slug);
        }

        $countries = DB::table('master_countries')->get();
        $continue_course = NULL;

        $feedback_given = false;

        $recent_news = Blog::orderBy('created_at', 'desc')->take(2)->get();

        $course_id = $course->id;

        $logged_in_user_id = auth()->user()->id;
       
        $subscribe_data = SubscribeCourse::where('user_id',$logged_in_user_id)
    ->where('course_id', $course_id)
    ->first();

// E-Learning courses do not depend on assignment due dates.
if (!$this->isLiveCourse($course) && $subscribe_data) {
    $subscribe_data->due_date = null;
}



        $has_subscribtion = $subscribe_data ? 1 : 0;
            
            $isAssignmentTaken = $subscribe_data ? ($subscribe_data->assesment_taken ?? 0) : 0;
$hasAssessmentLink = $subscribe_data ? ($subscribe_data->has_assesment ?? 0) : 0;
$hasFeedBack       = $subscribe_data ? ($subscribe_data->has_feedback ?? 0) : 0;
            $courseFeedbackLink = '';
            $lessonController = new LessonsController;

            $isGrantCertificate = $subscribe_data ? ($subscribe_data->grant_certificate ?? false) : false;

            $courseFeedbackLink = $lessonController->courseFeedbackLink($course_id);
            $feedbackLink = $courseFeedbackLink;
            //dd($feedbackLink);
            $feedback_given = ($hasFeedBack == 1 && $subscribe_data) ? ($subscribe_data->feedback_given ?? false) : false;
        
        // checking if course is offline
        if ($course->is_online == 'Offline' || $course->is_online == 'Live-Classroom') { 

            
            $course_id = $course->id;

            //dd($course_id);

            $assessment_link = "";

            // --- Attendance marking ---
            $isScheduledCourse = in_array($course->schedule_type, ['daily', 'weekly', 'custom']);

            if ($isScheduledCourse && $subscribe_data && request()->has('joined')) {
                // Scheduled course: mark per-session attendance (only within time window)
                $joinedSessionId = request()->get('session_id');
                if ($joinedSessionId) {
                    $joinedSession = LiveSession::find($joinedSessionId);
                    if ($joinedSession) {
                        $sessionStart = Carbon::parse($joinedSession->session_date->format('Y-m-d') . ' ' . $joinedSession->session_time);
                        $sessionEnd = $sessionStart->copy()->addMinutes((int)($joinedSession->duration ?? 60));
                        $windowStart = $sessionStart->copy()->subMinutes(15);
                        $now = Carbon::now();

                        if ($now->between($windowStart, $sessionEnd)) {
                            LiveSessionAttendance::firstOrCreate(
                                ['live_session_id' => $joinedSessionId, 'user_id' => $logged_in_user_id],
                                ['attended_at' => Carbon::now()]
                            );
                        }
                    }
                }

                // Check if this is the last session (no future sessions after today)
                $futureSessionsCount = LiveSession::where('course_id', $course->id)
                    ->where('session_date', '>', Carbon::today())
                    ->count();

                if ($futureSessionsCount == 0 && !$subscribe_data->is_attended) {
                    // Last session day — mark course-level attendance to trigger post-attendance flow
                    DB::table('subscribe_courses')->where('id', $subscribe_data->id)->update(['is_attended' => 1]);
                    $subscribe_data->is_attended = 1;
                }
            } elseif (!$isScheduledCourse && $subscribe_data && !$subscribe_data->is_attended && request()->has('joined')) {
                // Single meeting: existing flow — mark attended immediately
                DB::table('subscribe_courses')->where('id', $subscribe_data->id)->update(['is_attended' => 1]);
                $subscribe_data->is_attended = 1;
            }

            // updating course progress
            $progress = CustomHelper::progress($course_id, $logged_in_user_id);

            if ($hasAssessmentLink) {
                $assessment_link = $lessonController->assessmentLink($logged_in_user_id, $course_id);
            }

            if($hasFeedBack) {
              $feedbackLink = $feedbackLink;
            }

            if ($isAssignmentTaken && $feedbackLink) {
                $courseFeedbackLink = $feedbackLink;
            }


            //dd($isAssignmentTaken, $feedbackLink, $hasFeedBack, $feedback_given);


            //dd($logged_in_user_id, $course_id);

            $is_attended = $subscribe_data ? ($subscribe_data->is_attended ?? false) : false;

            //dd($is_attended);

$now = Carbon::now();

if ($this->isLiveCourse($course) && $subscribe_data && $subscribe_data->due_date) {
    $due_date_time = Carbon::parse($subscribe_data->due_date);
} else {
    $due_date_time = null;
}

            $course_assign_data = CourseAssignmentToUser::query()
                                ->with('assignment')
                                ->where('user_id', $logged_in_user_id)
                                ->where('course_id', $course_id)
                                ->first();
            //($course_assign_data);

            $end_meeting_attend_time = isset($course_assign_data->assignment->meeting_end_datetime)
    ? $course_assign_data->assignment->meeting_end_datetime
    : null;


            if ($end_meeting_attend_time) {
                $end_meeting_attend_time = Carbon::parse($end_meeting_attend_time);
                $buffer_minutes = $now->diffInMinutes($end_meeting_attend_time, false);
            } else {
                $buffer_minutes = null;
            }

            //$due_date_time = Carbon::parse('2025-10-13 19:00:00');
            //$end_meeting_attend_time = Carbon::parse('2025-10-13 20:05:00');

            if ($this->isLiveCourse($course) && $due_date_time && $end_meeting_attend_time) {
    $is_course_started   = $now->greaterThan($due_date_time);
    $is_course_completed = $now->lessThan($end_meeting_attend_time);
} else {
    // E-Learning or missing schedule → always accessible
    $is_course_started   = true;
    $is_course_completed = false;
}




            //dd($is_course_started, $is_course_completed);

            $first_lesson_slug = null;
            $lessonCount = $course->publishedLessons()->count();

            //dd($lessonCount);
            if($lessonCount) {
                $completed_lessons = \Auth::user()->chapters()
                    ->where('course_id', $course->id)
                    ->get()
                    ->pluck('model_id')
                    ->toArray();

                $course_lessons = $course->lessons->pluck('id')->toArray();
                //dd($course_lessons);

                $continue_course = $course->courseTimeline()
                    ->whereIn('model_id', $course_lessons)
                    ->orderby('sequence', 'asc')
                    ->whereNotIn('model_id', $completed_lessons)
                    ->first();
                if ($continue_course == null) {
                    $continue_course = $course->courseTimeline()
                        ->whereIn('model_id', $course_lessons)
                        ->orderby('sequence', 'asc')->first();
                }
                if(isset($course->publishedLessons[0])) {
                    $first_lesson_slug = $course->publishedLessons[0]->slug;
                }

                $purchased_course = \Auth::check() && $course->students()->where('user_id', \Auth::id())->count() > 0;
        
                if (isset($user->id)) {
                    $course_assignment = \DB::table('course_assignment')->where('course_id', $course->id)->where('assign_to', $user->id)->first();
                }
                $total_ratings = 0;
                $course_rating = null;
                $is_reviewed = null;

                if (auth()->check() && $course->reviews()->where('user_id', '=', auth()->user()->id)->first()) {
                    $is_reviewed = true;
                }

                if ($course->reviews->count() > 0) {
                    $course_rating = $course->reviews->avg('rating');
                    $total_ratings = $course->reviews()->where('rating', '!=', "")->get()->count();
                }
                if (!empty($course_lessons)) {
                    $lessons = $course->courseTimeline()->whereIn('model_id', $course_lessons)->orderby('id', 'asc')->get();
                } else {
                    $lessons = $course->courseTimeline()->orderby('id', 'asc')->get();
                }
                $checkSubcribePlan = [];

                $lessonCount = $course->publishedLessons()->count();
                //dd($lessonCount);

                $courseInPlan = courseOrBundlePlanExits($course->id, '');

                //dd($isAssignmentTaken, $hasFeedBack, $feedbackLink, "hhhh");
                $nextTasks = CustomHelper::getNextTask($subscribe_data, $course_id);
                //dd($nextTasks);

                // Upcoming live sessions for this course
                $liveSessions = LiveSession::where('course_id', $course->id)
                    ->where('session_date', '>=', Carbon::today())
                    ->orderBy('session_date')
                    ->orderBy('session_time')
                    ->get();

                $isHostRole = \Auth::check() && (\Auth::user()->isAdmin() || \Auth::user()->hasRole('teacher'));

                return view($this->path . '.courses.course', compact('nextTasks','has_subscribtion','isGrantCertificate','hasFeedBack','feedback_given','is_course_started','is_course_completed','end_meeting_attend_time','lessonCount', 'course', 'purchased_course', 'recent_news', 'course_rating', 'completed_lessons', 'total_ratings', 'is_reviewed', 'lessons', 'continue_course', 'checkSubcribePlan', 'courseInPlan', 'countries', 'liveSessions', 'isHostRole'));

            }
            
            
            $is_after_endtime = false;
            $is_within_buffer = false;
            $is_after_due = false;
            $is_before = false;
            $both_pass = false;

            $isGrantCertificate = $subscribe_data ? $subscribe_data->grant_certificate : false;
            //$is_attended = $subscribe_data->is_attended;
            //dd($hasFeedBack, $progress, $is_attended);
            $course_is_ready = 1;
            $nextTasks = $subscribe_data ? CustomHelper::getNextTask($subscribe_data, $course_id) : [
                'download_certificate' => false,
                'open_assesment' => false,
                'open_feedback' => false,
                'reattempt_assesment' => false,
                'completed_assesment' => false,
                'failed_in_assesment_all_attempts' => false,
            ];
            //dd($nextTasks);


            // Today's live session for this course
            $todaySession = LiveSession::where('course_id', $course->id)
                ->whereDate('session_date', Carbon::today())
                ->orderBy('session_time')
                ->first();

            // Upcoming live sessions
            $liveSessions = LiveSession::where('course_id', $course->id)
                ->where('session_date', '>=', Carbon::today())
                ->orderBy('session_date')
                ->orderBy('session_time')
                ->get();

            // All sessions for this course
            $allSessions = LiveSession::where('course_id', $course->id)
                ->orderBy('session_date')
                ->orderBy('session_time')
                ->get();

            $isHostRole = \Auth::check() && (\Auth::user()->isAdmin() || \Auth::user()->hasRole('teacher'));

            // Scheduled course stats
            $totalSessionsCount = $allSessions->count();
            $attendedSessionsCount = 0;
            $attendedSessionIds = [];
            if ($isScheduledCourse && $totalSessionsCount > 0) {
                $attendedSessionIds = LiveSessionAttendance::where('user_id', $logged_in_user_id)
                    ->whereIn('live_session_id', $allSessions->pluck('id'))
                    ->pluck('live_session_id')
                    ->toArray();
                $attendedSessionsCount = count($attendedSessionIds);
            }

            // Next session (for when there's no session today)
            $nextSession = $liveSessions->where('session_date', '>', Carbon::today())->first();

            return view($this->path . '.courses.offline-course', compact('nextTasks','course_is_ready','has_subscribtion','hasFeedBack','feedback_given','is_course_started','is_course_completed','lessonCount', 'is_after_endtime','end_meeting_attend_time','due_date_time','is_attended', 'course', 'assessment_link', 'courseFeedbackLink', 'is_within_buffer', 'is_after_due','is_before', 'subscribe_data', 'hasAssessmentLink', 'isAssignmentTaken','both_pass', 'both_pass', 'first_lesson_slug', 'now','isGrantCertificate', 'todaySession', 'liveSessions', 'allSessions', 'isHostRole', 'isScheduledCourse', 'totalSessionsCount', 'attendedSessionsCount', 'attendedSessionIds', 'nextSession'));
        }
        
        $purchased_course = \Auth::check() && $course->students()->where('user_id', \Auth::id())->count() > 0;
        
        if (isset($user->id)) {
            $course_assignment = \DB::table('course_assignment')->where('course_id', $course->id)->where('assign_to', $user->id)->first();
        }


        $course_rating = 0;
        $total_ratings = 0;
        $completed_lessons = "";
        $is_reviewed = false;
        if (\Auth::check()) {

            $completed_lessons = \Auth::user()->chapters()
                    ->where('course_id', $course->id)
                    ->get()
                    ->pluck('model_id')
                    ->toArray();

            $course_lessons = $course->lessons->pluck('id')->toArray();
            //dd($course_lessons);

            $continue_course = $course->courseTimeline()
                ->whereIn('model_id', $course_lessons)
                ->orderby('sequence', 'asc')
                ->whereNotIn('model_id', $completed_lessons)
                ->first();
            if ($continue_course == null) {
                $continue_course = $course->courseTimeline()
                    ->whereIn('model_id', $course_lessons)
                    ->orderby('sequence', 'asc')->first();
            }
            $checkSubcribePlan = '';
        }
        
        if (auth()->check() && $course->reviews()->where('user_id', '=', auth()->user()->id)->first()) {
            $is_reviewed = true;
        }

        if ($course->reviews->count() > 0) {
            $course_rating = $course->reviews->avg('rating');
            $total_ratings = $course->reviews()->where('rating', '!=', "")->get()->count();
        }
        if (!empty($course_lessons)) {
            $lessons = $course->courseTimeline()->whereIn('model_id', $course_lessons)->orderby('id', 'asc')->get();
        } else {
            $lessons = $course->courseTimeline()->orderby('id', 'asc')->get();
        }
        $checkSubcribePlan = [];

        $lessonCount = $course->publishedLessons($course->id)->count();
        //dd($lessonCount);

        $courseInPlan = courseOrBundlePlanExits($course->id, '');
        //dd($courseInPlan);

        $end_meeting_attend_time = null;

        //dd("fdf");

        // Upcoming live sessions for this course
        $liveSessions = LiveSession::where('course_id', $course->id)
            ->where('session_date', '>=', Carbon::today())
            ->orderBy('session_date')
            ->orderBy('session_time')
            ->get();

        $isHostRole = \Auth::check() && (\Auth::user()->isAdmin() || \Auth::user()->hasRole('teacher'));

        return view($this->path . '.courses.course', compact('hasFeedBack','feedback_given','end_meeting_attend_time','lessonCount', 'course', 'purchased_course', 'recent_news', 'course_rating', 'completed_lessons', 'total_ratings', 'is_reviewed', 'lessons', 'continue_course', 'checkSubcribePlan', 'courseInPlan', 'countries', 'liveSessions', 'isHostRole'));
    }

    public function coursePreview($course_slug)
    {
        //dd($course_slug);
        $user = \Auth::user();
        

        $course = Course::withoutGlobalScope('filter')->where('slug', $course_slug)->first();

        //dd($course->publishedLessons[0]->slug);

        $is_admin = null;

        $isGrantCertificate = null;

        if (!$user) {
            return view($this->path . '.courses.course-preview', compact(
                'course',
                'is_admin'
            ));
        }

        $is_admin = false;
        
        if($user->isAdmin()) {
            $is_admin = true;
            $now = Carbon::now();
            $admin_course_assignment_data = courseAssignment::query()
                            ->where('course_id',$course->id )
                            ->first();
            $start_datetime = null;
            $end_meeting_attend_time = null;

            


            if($admin_course_assignment_data) {
                $end_meeting_attend_time = $admin_course_assignment_data->meeting_end_datetime ? $admin_course_assignment_data->meeting_end_datetime : null;

                $start_datetime = $admin_course_assignment_data->due_date ? $admin_course_assignment_data->due_date : null;

                if ($end_meeting_attend_time) {
                    $end_meeting_attend_time = Carbon::parse($end_meeting_attend_time);
                    $buffer_minutes = $now->diffInMinutes($end_meeting_attend_time, false); 
                } else {
                    $buffer_minutes = null;
                }
            }

            
            //$nextTasks = CustomHelper::getNextTask($subscribe_data, $course_id);
            

            return view($this->path . '.courses.course-preview', compact(
                'course',
                'is_admin',
                'start_datetime',
                'end_meeting_attend_time'
            ));
        }

        $course = Course::withoutGlobalScope('filter')->where('slug', $course_slug)->with('publishedLessons')->first();

        $countries = DB::table('master_countries')->get();
        $continue_course = NULL;

        $recent_news = Blog::orderBy('created_at', 'desc')->take(2)->get();


        //dd($course, $course_slug);
        $assignmentScoreValue = 0;
        
        // checking if course is offline
        if ($course->is_online == 'Offline' || $course->is_online == 'Live-Classroom') { 

            
            $course_id = $course->id;

            //dd($course_id);

            $assessment_link = "";

            $lessonController = new LessonsController;
    
            $logged_in_user_id = auth()->user()->id;
            $isAssignmentTaken = $lessonController->isAssignmentTaken($logged_in_user_id, $course_id);
            $hasAssessmentLink = $lessonController->hasAssessmentLink($course_id, $logged_in_user_id);
            $feedbackLink = $lessonController->courseFeedbackLink($course_id);
            $courseFeedbackLink = '';
            $notGivenFeedback = true;

            // updating course progress
            $progress = CustomHelper::progress($course_id);

            if ($hasAssessmentLink) {
                $assessment_link = $lessonController->assessmentLink($logged_in_user_id, $course_id);
            } else {
              $courseFeedbackLink = $feedbackLink;
            }

            if ($isAssignmentTaken && $feedbackLink) {
                $courseFeedbackLink = $feedbackLink;
            }

            $subscribe_data = SubscribeCourse::where('user_id',$logged_in_user_id)
                            ->where('course_id', $course_id)
                            ->first();

            if(!$subscribe_data) {
                $has_subscribtion = 0;
                $has_any_subscribe_data = SubscribeCourse::query()
                            ->where('course_id', $course_id)
                            ->latest()
                            ->first();
                if($has_any_subscribe_data) {
                    $subscribe_data = $has_any_subscribe_data;
                    $course_is_ready = 1;
                } else {
                    $course_is_ready = 0;
                }
                $isGrantCertificate = false;
                $is_attended = false;
            } else {
                $has_subscribtion = 1;
                $course_is_ready = 1;

                // TEMP: Mark attendance when student clicks Join (TODO: move to teacher flow later)
                if (!$subscribe_data->is_attended && request()->has('joined')) {
                    DB::table('subscribe_courses')->where('id', $subscribe_data->id)->update(['is_attended' => 1]);
                    $subscribe_data->is_attended = 1;
                }

                $isGrantCertificate = $subscribe_data->grant_certificate;
                $is_attended = $subscribe_data ? ($subscribe_data->is_attended ?? false) : false;
            }

            
            //dd($course_id, $logged_in_user_id);

            

            if ($this->isLiveCourse($course) && $subscribe_data && !empty($subscribe_data->due_date)) {
    $due_date_time = Carbon::parse($subscribe_data->due_date);
} else {
    $due_date_time = null;   // E-Learning
}

            
            $now = Carbon::now();
            //dd($now);

            $course_assign_data = CourseAssignmentToUser::query()
                                ->with('assignment')
                                ->where('user_id', $logged_in_user_id)
                                ->where('course_id', $course_id)
                                ->first();

            $end_meeting_attend_time = isset($course_assign_data->assignment->meeting_end_datetime) && $course_assign_data->assignment->meeting_end_datetime ? $course_assign_data->assignment->meeting_end_datetime : null;

            if ($end_meeting_attend_time) {
                $end_meeting_attend_time = Carbon::parse($end_meeting_attend_time);
                $buffer_minutes = $now->diffInMinutes($end_meeting_attend_time, false); 
            } else {
                $buffer_minutes = null;
            }

            if ($this->isLiveCourse($course) && $due_date_time) {
    $is_after_due = $now->greaterThan($due_date_time);
    $is_within_buffer = $now->lessThanOrEqualTo(
        $due_date_time->copy()->addMinutes($buffer_minutes ?? 0)
    );
    $is_before = $now->lessThanOrEqualTo($due_date_time);
    $both_pass = $is_after_due && $is_within_buffer;
} else {
    // E-Learning → no time restriction
    $is_after_due = false;
    $is_within_buffer = true;
    $is_before = false;
    $both_pass = true;
}


            if ($this->isLiveCourse($course) && $due_date_time) {
    $is_course_started = $now->greaterThan($due_date_time);
    $is_course_completed = $now->lessThan($end_meeting_attend_time);
} else {
    // E-Learning → always accessible
    $is_course_started = true;
    $is_course_completed = false;
}


            //dd($due_date_time, $now, $is_before, $is_after_due,  $is_within_buffer, $both_pass);

            $first_lesson_slug = null;
            $lessonCount = $course->publishedLessons($course->id)->count();
            if($lessonCount) {
                $completed_lessons = \Auth::user()->chapters()
                    ->where('course_id', $course->id)
                    ->get()
                    ->pluck('model_id')
                    ->toArray();

                $course_lessons = $course->lessons->pluck('id')->toArray();
                //dd($course_lessons);

                $continue_course = $course->courseTimeline()
                    ->whereIn('model_id', $course_lessons)
                    ->orderby('sequence', 'asc')
                    ->whereNotIn('model_id', $completed_lessons)
                    ->first();
                if ($continue_course == null) {
                    $continue_course = $course->courseTimeline()
                        ->whereIn('model_id', $course_lessons)
                        ->orderby('sequence', 'asc')->first();
                }
                if(isset($course->publishedLessons[0])) {
                    $first_lesson_slug = $course->publishedLessons[0]->slug;
                }
            }
            
            
            $nextTasks = CustomHelper::getNextTask($subscribe_data, $course_id);

            //dd($nextTasks);

            return view($this->path . '.courses.offline-course', compact('nextTasks','course_is_ready','has_subscribtion','assignmentScoreValue','now','end_meeting_attend_time','is_course_completed','is_course_started','is_admin','due_date_time','is_attended', 'course', 'assessment_link', 'courseFeedbackLink', 'is_within_buffer', 'is_after_due','is_before', 'subscribe_data', 'hasAssessmentLink', 'isAssignmentTaken','both_pass', 'both_pass', 'first_lesson_slug','isGrantCertificate'));
        }
        
        $purchased_course = \Auth::check() && $course->students()->where('user_id', \Auth::id())->count() > 0;
        
        if (isset($user->id)) {
            $course_assignment = \DB::table('course_assignment')->where('course_id', $course->id)->where('assign_to', $user->id)->first();
        }


        $course_rating = 0;
        $total_ratings = 0;
        $completed_lessons = "";
        $is_reviewed = false;
        if (\Auth::check()) {

            $completed_lessons = \Auth::user()->chapters()
                    ->where('course_id', $course->id)
                    ->get()
                    ->pluck('model_id')
                    ->toArray();

            $course_lessons = $course->lessons->pluck('id')->toArray();
            //dd($course_lessons);

            $continue_course = $course->courseTimeline()
                ->whereIn('model_id', $course_lessons)
                ->orderby('sequence', 'asc')
                ->whereNotIn('model_id', $completed_lessons)
                ->first();
            if ($continue_course == null) {
                $continue_course = $course->courseTimeline()
                    ->whereIn('model_id', $course_lessons)
                    ->orderby('sequence', 'asc')->first();
            }
            $checkSubcribePlan = '';
        }
        
        if (auth()->check() && $course->reviews()->where('user_id', '=', auth()->user()->id)->first()) {
            $is_reviewed = true;
        }

        if ($course->reviews->count() > 0) {
            $course_rating = $course->reviews->avg('rating');
            $total_ratings = $course->reviews()->where('rating', '!=', "")->get()->count();
        }
        if (!empty($course_lessons)) {
            $lessons = $course->courseTimeline()->whereIn('model_id', $course_lessons)->orderby('id', 'asc')->get();
        } else {
            $lessons = $course->courseTimeline()->orderby('id', 'asc')->get();
        }
        $checkSubcribePlan = [];

        $lessonCount = $course->publishedLessons($course->id)->count();
        //dd($lessonCount);

        $courseInPlan = courseOrBundlePlanExits($course->id, '');

        $subscribe_data = SubscribeCourse::where('user_id',auth()->user()->id)
                            ->where('course_id', $course->id)
                            ->first() ?? null;

        //dd($courseInPlan);
        $nextTasks = CustomHelper::getNextTask($subscribe_data, $course->id);

        //dd($nextTasks);

        // Upcoming live sessions for this course
        $liveSessions = LiveSession::where('course_id', $course->id)
            ->where('session_date', '>=', Carbon::today())
            ->orderBy('session_date')
            ->orderBy('session_time')
            ->get();

        $isHostRole = \Auth::check() && (\Auth::user()->isAdmin() || \Auth::user()->hasRole('teacher'));

        return view($this->path . '.courses.course', compact('nextTasks','is_course_completed','is_course_started','is_admin','lessonCount', 'course', 'purchased_course', 'recent_news', 'course_rating', 'completed_lessons', 'total_ratings', 'is_reviewed', 'lessons', 'continue_course', 'checkSubcribePlan', 'courseInPlan', 'countries', 'liveSessions', 'isHostRole'));
    }

    public function register_course(Request $request, $course_id)
    {
        $course_list = Course::with('publishedLessons')->where('id', $course_id)->first();
        $countries = DB::table('master_countries')->get();
        return view('delta_academy.register.course', compact('countries', 'course_list'));
    }

    public function save_register_course(Request $request)
    {
        $course_id = $request->course_id;
        $user = User::create(
            [
                'email' => $request->email,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'password' => $request->password,
                'specialization' => $request->specialization,
                'employee_type' => 'external'
                //'email' => $request->email,
                //'email' => $request->email
            ]
        );
        $user->assignRole('student');
        //dd($user->id);
        if ($user) {
            SubscribeCourse::create(
                [
                    'course_id' => $course_id,
                    'user_id' => $user->id,
                    'status' => 0
                ]
            );
            DB::table('course_user')->insert(
                [
                    'course_id' => $course_id,
                    'user_id' => $user->id,
                ]
            );
            DB::table('course_student')->insert(
                [
                    'course_id' => $course_id,
                    'user_id' => $user->id,
                ]
            );
        }

        $data = [
            'user_id' => Auth::user()->id,
            'course_id' =>  $request->course_id,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        DB::table('subscribe_courses')->insert($data);

        return redirect()->back()->with('success', 'You have successfully register to this course.');
    }

    public function rating($course_id, Request $request)
    {
        $course = Course::findOrFail($course_id);
        $course->students()->updateExistingPivot(\Auth::id(), ['rating' => $request->get('rating')]);

        return redirect()->back()->with('success', 'Thank you for rating.');
    }

    public function getByCategory(Request $request)
    {
        $category = Category::where('slug', '=', $request->category)
            ->where('status', '=', 1)
            ->first();
        $categories = Category::where('status', '=', 1)->get();

        if ($category != "") {
            $recent_news = Blog::orderBy('created_at', 'desc')->take(2)->get();
            $featured_courses = Course::where('published', '=', 1)
                ->where('featured', '=', 1)->take(8)->get();

            if (request('type') == 'popular') {
                $courses = $category->courses()->withoutGlobalScope('filter')->where('published', 1)->where('popular', '=', 1)->orderBy('id', 'desc')->paginate(9);
            } else if (request('type') == 'trending') {
                $courses = $category->courses()->withoutGlobalScope('filter')->where('published', 1)->where('trending', '=', 1)->orderBy('id', 'desc')->paginate(9);
            } else if (request('type') == 'featured') {
                $courses = $category->courses()->withoutGlobalScope('filter')->where('published', 1)->where('featured', '=', 1)->orderBy('id', 'desc')->paginate(9);
            } else {
                $courses = $category->courses()->withoutGlobalScope('filter')->where('published', 1)->orderBy('id', 'desc')->paginate(9);
            }


            return view($this->path . '.courses.index', compact('courses', 'category', 'recent_news', 'featured_courses', 'categories'));
        }
        return abort(404);
    }

    public function addReview(Request $request)
    {
        $this->validate($request, [
            'review' => 'required'
        ]);
        $course = Course::findORFail($request->id);
        $review = new Review();
        $review->user_id = auth()->user()->id;
        $review->reviewable_id = $course->id;
        $review->reviewable_type = Course::class;
        $review->rating = $request->rating;
        $review->content = $request->review;
        $review->save();

        return back();
    }

    public function editReview(Request $request)
    {
        $review = Review::where('id', '=', $request->id)->where('user_id', '=', auth()->user()->id)->first();
        if ($review) {
            $course = $review->reviewable;
            $recent_news = Blog::orderBy('created_at', 'desc')->take(2)->get();
            $purchased_course = \Auth::check() && $course->students()->where('user_id', \Auth::id())->count() > 0;
            $course_rating = 0;
            $total_ratings = 0;
            $lessons = $course->courseTimeline()->orderby('sequence', 'asc')->get();

            if ($course->reviews->count() > 0) {
                $course_rating = $course->reviews->avg('rating');
                $total_ratings = $course->reviews()->where('rating', '!=', "")->get()->count();
            }
            if (\Auth::check()) {

                $completed_lessons = \Auth::user()->chapters()->where('course_id', $course->id)->get()->pluck('model_id')->toArray();
                $continue_course = $course->courseTimeline()->orderby('sequence', 'asc')->whereNotIn('model_id', $completed_lessons)->first();
                if ($continue_course == "") {
                    $continue_course = $course->courseTimeline()->orderby('sequence', 'asc')->first();
                }
            }
            return view($this->path . '.courses.course', compact('course', 'purchased_course', 'recent_news', 'completed_lessons', 'continue_course', 'course_rating', 'total_ratings', 'lessons', 'review'));
        }
        return abort(404);
    }


    public function updateReview(Request $request)
    {
        $review = Review::where('id', '=', $request->id)->where('user_id', '=', auth()->user()->id)->first();
        if ($review) {
            $review->rating = $request->rating;
            $review->content = $request->review;
            $review->save();

            return redirect()->route('courses.show', ['slug' => $review->reviewable->slug]);
        }
        return abort(404);
    }

    public function deleteReview(Request $request)
    {
        $review = Review::where('id', '=', $request->id)->where('user_id', '=', auth()->user()->id)->first();
        if ($review) {
            $slug = $review->reviewable->slug;
            $review->delete();
            return redirect()->route('courses.show', ['slug' => $slug]);
        }
        return abort(404);
    }

    public function mycourses(Request $request)
    {
        $course_status = $request->input('course_status') ?? null;
        $by_due_date = $request->input('by_due_date') ?? null;
        $course_name = $request->input('course_name') ?? null;
        

        //dd($course_status, $by_due_date);

        $subscribe_courses = null;
        $learning_pathways = null;
        if (\Auth::check()) {

            $subscribe_courses = auth()->user()->subscribeCourses(auth()->id(), $course_status, $by_due_date, $course_name);


            //dd($subscribe_courses);
           
            if($course_status == 'Completed') {
                $course_status = 2;
            }
            if($course_status == 'InProgress') {
                $course_status = 1;
            }
            if($course_status == 'NotStarted') {
                $course_status = 0;
            }

            //dd($course_name, $course_status);

            

            $learning_pathways = UserLearningPathway::where('user_id', auth()->id())
                ->with(['learningPathwayCoursesOrdered' => function ($q) use ($course_status, $by_due_date) {

                    // Filter by related subscribedCourse fields
                   $q->when(isset($course_status) || isset($by_due_date), function ($query) use ($course_status, $by_due_date) {
    $query->whereHas('subscribedCourse', function ($sub) use ($course_status, $by_due_date) {

        // Status filter (unchanged)
        if (isset($course_status)) {
            // $sub->where('course_progress_status', $course_status);
        }

        // Due date filter — E-Learning MUST be included
        if (isset($by_due_date)) {
            if ($by_due_date === 'late') {
                $sub->where(function ($q) {
                    $q->where('due_date', '<', Carbon::now())
                      ->orWhereNull('due_date'); // E-Learning
                });
            }

            if ($by_due_date === 'soon') {
                $sub->where(function ($q) {
                    $q->whereBetween('due_date', [Carbon::now(), Carbon::now()->addDays(7)])
                      ->orWhereNull('due_date'); // E-Learning
                });
            }
        }
    });
});

                    // eager load subscribedCourse itself
                $q->with('subscribedCourse')->orderBy('position');
                }, 'learningPathway'])
                ->when(!empty($course_name), function ($q) use ($course_name) {
                    $q->whereHas('learningPathway', function ($query) use ($course_name) {
                        if(!empty($course_name)) {
                             $query->where('title', 'LIKE', "%{$course_name}%");
                        }
                       
                    });
                })
                ->groupBy('pathway_id')
                ->get();
                

            //dd($learning_pathways);    
            


            return view('backend.mycourses.index',[
                'subscribe_courses' => $subscribe_courses,
                'learning_pathways' => $learning_pathways
            ]);
        }
    }

    public function getMyCoursesData(Request $request)
    {
        
    }

    public function mypathwaycourses(Request $request)
    {
        return view('backend.mycourses.pathwaycourses');
    }

    public function getMyPathWayCoursesData(Request $request)
    {
        $has_view = false;
        $has_delete = false;
        $has_edit = false;
        $courses = "";

        
        // $learning_pathways = UserLearningPathway::where('user_id', auth()->id())
        //                             ->with('learningPathwayCoursesOrdered')
        //                             ->groupBy('pathway_id')
        //                             ->get();

        $learning_pathways = LearningPathwayCourse::with('subscribedCourse','course','pathway','user_pathway')
                                ->whereHas('user_pathway', function ($q) {
                                    $q->where('user_id', auth()->id());
                                });
                                //->get();
        //dd($learning_pathways);

        return DataTables::of($learning_pathways)
            ->addIndexColumn()
            ->addColumn('title', function ($q) {

                if(app()->getLocale() == 'ar') {
                  return $q->course->title ? $q->course->title : $q->course->title;
                } else {
                   return $q->course->title ? $q->course->title : $q->course->title;
                }

            })
            ->addColumn('category', function ($q) {
                return $q->course->category ? $q->course->category->name : '';
            })
            ->addColumn('pathway', function ($q) {
                return $q->pathway->title ? $q->pathway->title : '';
            })
            ->addColumn('lebel', function ($q) {
                return $q->position;
            })
            ->addColumn('duration', function ($q) {
                return $q->course->lessons() ? $q->course->lessons()->count() : '';
            })
            ->addColumn('lessons', function ($q) {
                return $q->course->courseAllLessonDuration ? $q->course->courseAllLessonDuration : '';
            })
            ->addColumn('due_date', function ($q) {
    if (!isset($q->subscribedCourse) || !$q->subscribedCourse->due_date) {
        return 'Self-Paced';
    }

    return date('d/m/Y', strtotime($q->subscribedCourse->due_date));
})


            ->addColumn('progress', function ($q) {
    if (!isset($q->subscribedCourse)) {
        return '0%';
    }

    return $q->subscribedCourse->assignment_progress
        ? $q->subscribedCourse->assignment_progress . '%'
        : '0%';
})

            ->addColumn('download_certificate', function ($q) {
                $download_certificate = route('admin.certificates.generate', ['course_id' => $q->course->id, 'user_id' => auth()->id()]);
                return $q->subscribedCourse->grant_certificate ? "<a class='btn btn-success' download
                                                            href=" . $download_certificate . "> " . trans('course.btn.download_certificate') . "
                                                            </a>" : '-';
            })
            ->addColumn('actions', function ($q) {

                $course_route = route('courses.show', [$q->course->slug]);

                return '<a href=" '. $course_route . ' " target="_blank" class="btn btn-sm btn-primary">Continue</a>';
            })
            ->rawColumns(['download_certificate','actions'])
            ->make();
    }

    private function isLiveCourse($course)
{
    return in_array($course->is_online, ['Live-Online', 'Live-Classroom', 'Offline']);
}

}


