<?php

namespace App\Http\Controllers;

use App\Helpers\Auth\Auth;
use App\Mail\Frontend\LiveLesson\StudentMeetingSlotMail;
use App\Models\{Assignment, Lesson, AttendanceStudent, Course, CourseFeedback, EmployeeCourseProgress, UserFeedback};
use App\Models\Stripe\SubscribeCourse;
use App\Models\Auth\User;
use App\Models\LessonSlotBooking;
use App\Models\LiveLessonSlot;
use App\Models\Media;
use App\Models\{Question, TestQuestion};
use App\Models\QuestionsOption;
use App\Models\Test;
use App\Models\TestsResult;
use App\Models\VideoProgress;
use Illuminate\Http\Request;
use App\Helpers\CustomHelper;
use App\Models\EmployeeProfile;
use App\Models\courseAssignment;
use Yajra\DataTables\DataTables;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Str;


class LessonsController extends Controller
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

    public function isAssignmentTaken($logged_in_user_id, $course_id)
    {



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

        return $assignment_taken;
    }

    public function hasAssessmentLink($course_id, $logged_in_user_id)
    {

        $agmt = Assignment::where('assignments.course_id', $course_id)
            ->join('courses', 'courses.id', '=', 'assignments.course_id')
            ->join('course_assignment', 'course_assignment.course_id', '=', 'courses.id')
            ->join('tests', 'tests.id', '=', 'assignments.test_id')
            ->join('test_questions', 'test_questions.test_id', '=', 'tests.id')
            ->whereRaw('FIND_IN_SET(?, `assign_to`) > 0', $logged_in_user_id)
            ->exists();
        if ($agmt) {
            return true;
        }

        return false;
    }

    public function courseFeedbackLink($course_id)
    {
        $cf = CourseFeedback::firstWhere('course_id', $course_id);
        if ($cf) {
            $uf = UserFeedback::where("user_id", auth()->id())->where('course_id', $course_id)->first();
            if (!$uf) {
                return route('course-feedback', ['course_id' =>  $course_id]);
            }
        }

        return route('course-feedback', ['course_id' =>  $course_id]);
    }

    public function courseHasFeedbackLink($course_id)
    {
        $cf = CourseFeedback::firstWhere('course_id', $course_id);
        if ($cf) {
            return true;
        }

        return false;
    }

    public function assessmentLink($logged_in_user_id, $course_id)
    {
       
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
        
        //dd($assignment);

        // If no assignment found, fallback
        if (!isset($assignment)) {

            CourseAssignment::create(
                [
                    'course_id' => $course_id,
                    'assign_by' => $logged_in_user_id,
                    'assign_to' => $logged_in_user_id
                ]
            );


            $assignment = CourseAssignment::with(['assessment', 'assessment.course'])
                ->where('course_assignment.course_id', $course_id)
                ->where('assign_to', $logged_in_user_id)
                ->latest('course_assignment.id')
                ->first();

            //dd( $assignment );
            
        }

        //dd( $assignment );

        // If still nothing
        if (!$assignment) {
            return '';
        }

        $assessment = $assignment->assessment;
        if (!$assessment) {
            $assessment = $this->resolveOrCreateCourseAssessment((int) $course_id, (int) $logged_in_user_id);
        }

        // Build the assessment URL
        if ($assessment) {

            $test_taken = CustomHelper::assignmentAttempts($assessment->id, $logged_in_user_id);

            //dd($test_taken);

            $allowedAssignmentRetake = 2;
            //dd($allowedAssignmentRetake, $test_taken);

            if ($test_taken < $allowedAssignmentRetake) {
                return route('online_assessment', [
                    'assignment'     => $assessment->url_code,
                    'verify_code'    => $assessment->verify_code,
                    'id'             => $assignment->id,
                    'assessment_id'  => $assessment->id,
                    'course_id'      => $course_id
                ]);
            }
        }

        return '';
    }

    private function resolveCourseFinalAssessmentTest(int $course_id): ?Test
    {
        $finalTests = Test::where('course_id', $course_id)
            ->whereNull('lesson_id')
            ->orderBy('id', 'desc')
            ->get();

        foreach ($finalTests as $test) {
            $questionsCount = DB::table('test_questions')
                ->where('test_id', $test->id)
                ->where('is_deleted', 0)
                ->whereNull('deleted_at')
                ->count();

            if ($questionsCount > 0) {
                return $test;
            }
        }

        return null;
    }

    private function resolveOrCreateCourseAssessment(int $course_id, int $logged_in_user_id): ?Assignment
    {
        $finalTest = $this->resolveCourseFinalAssessmentTest($course_id);
        if (!$finalTest) {
            return null;
        }

        $questionCount = DB::table('test_questions')
            ->where('test_id', $finalTest->id)
            ->where('is_deleted', 0)
            ->whereNull('deleted_at')
            ->count();

        if ($questionCount <= 0) {
            return null;
        }

        $assessment = Assignment::where('course_id', (string) $course_id)
            ->where('test_id', $finalTest->id)
            ->whereNull('deleted_at')
            ->latest('id')
            ->first();

        if ($assessment) {
            $dirty = false;
            if (empty($assessment->url_code)) {
                $assessment->url_code = Str::lower(Str::random(20));
                $dirty = true;
            }
            if (empty($assessment->verify_code)) {
                $assessment->verify_code = strtoupper(Str::random(6));
                $dirty = true;
            }
            if (empty($assessment->total_question) || (int) $assessment->total_question < 1) {
                $assessment->total_question = $questionCount;
                $dirty = true;
            }
            if ($dirty) {
                $assessment->save();
            }

            return $assessment;
        }

        $urlCode = Str::lower(Str::random(20));
        while (Assignment::where('url_code', $urlCode)->exists()) {
            $urlCode = Str::lower(Str::random(20));
        }

        $verifyCode = strtoupper(Str::random(6));

        $assessmentId = DB::table('assignments')->insertGetId([
            'test_id' => $finalTest->id,
            'user_id' => (string) $logged_in_user_id,
            'course_id' => (string) $course_id,
            'title' => $finalTest->title ?: 'Final Assessment',
            'duration' => 60,
            'verify_code' => $verifyCode,
            'url_code' => $urlCode,
            'total_question' => $questionCount,
            'due_date' => now()->addYears(5),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Assignment::find($assessmentId);
    }


    public function assessmentLink_($logged_in_user_id, $course_id)
    {



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

        if ($assignments->count() == 0) {
            $assignments = courseAssignment::with(['assessment', 'assessment.course'])
                //->where('assign_to', $logged_in_user_id)
                ->where('course_assignment.course_id', $course_id)
                ->orderBy('course_assignment.id', 'desc')
                ->get();
            //dd( $assignments );
        }




        // $test_taken = CustomHelper::is_test_taken($assignments->assessment->id,$logged_in_user_id);

        $dd = DataTables::of($assignments)->addColumn('assesment_url', function ($q) use ($logged_in_user_id, $course_id) {
            if ($q->assessment) {
                $test_taken = CustomHelper::assignmentAttempts($q->assessment->id, $logged_in_user_id);
                //echo '<pre>';   print_r([$test_taken]);die;

                $allowedAssignmentRetake = 2;

                if ($test_taken < $allowedAssignmentRetake) {
                    $test_link = route('online_assessment', ['assignment' => $q->assessment->url_code, 'verify_code' => $q->assessment->verify_code, 'id' => $q->id, 'assessment_id' => $q->assessment->id, 'course_id' => $course_id]);
                    return  $test_link;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        })->make();

        return isset($dd->original['data'][0]['assesment_url']) ? $dd->original['data'][0]['assesment_url'] : '';
    }

    /**
     * Resolve a published lesson for a course by tolerant slug matching.
     */
    private function resolvePublishedLessonBySlug(int $course_id, ?string $normalized_slug): ?Lesson
    {
        if (empty($normalized_slug)) {
            return null;
        }

        $baseQuery = Lesson::with(['downloadableMedia', 'mediaVideo', 'mediaPDF'])
            ->where('course_id', $course_id)
            ->where('published', 1);

        $lesson = (clone $baseQuery)->where('slug', $normalized_slug)->first();
        if ($lesson) {
            return $lesson;
        }

        // Old lesson links may include a random unique prefix before the slug.
        $slug_without_prefix = preg_replace('/^[0-9a-f]{8,}(?=[a-z])/i', '', $normalized_slug);
        if (!empty($slug_without_prefix) && $slug_without_prefix !== $normalized_slug) {
            $lesson = (clone $baseQuery)->where('slug', $slug_without_prefix)->first();
            if ($lesson) {
                return $lesson;
            }
        }

        $lesson = (clone $baseQuery)->where('slug', 'like', $normalized_slug . '%')->first();
        if ($lesson) {
            return $lesson;
        }

        $lesson = (clone $baseQuery)
            ->whereRaw('? LIKE CONCAT("%", slug)', [$normalized_slug])
            ->first();
        if ($lesson) {
            return $lesson;
        }

        return (clone $baseQuery)
            ->where('slug', 'like', '%' . $normalized_slug . '%')
            ->first();
    }

    /**
     * Whether the given lesson has an active lesson-level quiz.
     */
    private function lessonHasQuiz(Lesson $lesson): bool
    {
        $lesson_test = $lesson->test;
        if (!$lesson_test) {
            return false;
        }

        return $lesson_test->test_questions()->exists();
    }

    /**
     * Whether the current user has passed the lesson-level quiz.
     */
    private function hasUserPassedLessonQuiz(Lesson $lesson, int $user_id): bool
    {
        $lesson_test = $lesson->test;
        if (!$lesson_test) {
            return true;
        }

        $question_count = $lesson_test->test_questions()->count();
        if ($question_count === 0) {
            return true;
        }

        $latest_result = TestsResult::where('test_id', $lesson_test->id)
            ->where('user_id', $user_id)
            ->orderBy('id', 'desc')
            ->first();

        if (!$latest_result) {
            return false;
        }

        $percentage = ($latest_result->test_result / $question_count) * 100;

    // Lesson progression requires all answers to be correct.
    return $percentage >= 100;
    }

    /**
     * Enforce sequential lesson progression when previous lessons have quizzes.
     */
    private function canUserOpenLesson(Course $course, Lesson $target_lesson, int $user_id): array
    {
        $ordered_lessons = Lesson::where('course_id', $course->id)
            ->where('published', 1)
            ->orderBy('position', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $target_index = $ordered_lessons->search(function ($lesson) use ($target_lesson) {
            return (int) $lesson->id === (int) $target_lesson->id;
        });

        if ($target_index === false || $target_index === 0) {
            return ['allowed' => true, 'blocked_lesson' => null];
        }

        foreach ($ordered_lessons->slice(0, $target_index) as $previous_lesson) {
            if ($this->lessonHasQuiz($previous_lesson) && !$this->hasUserPassedLessonQuiz($previous_lesson, $user_id)) {
                return ['allowed' => false, 'blocked_lesson' => $previous_lesson];
            }
        }

        return ['allowed' => true, 'blocked_lesson' => null];
    }

    /**
     * Build quiz state for a lesson-level quiz page/section.
     */
    private function getLessonQuizData(Lesson $lesson): array
    {
        $lesson_quiz = null;
        $lesson_quiz_questions = collect();
        $lesson_quiz_result = null;
        $lesson_quiz_pass = null;
        $lesson_quiz_percentage = 0;

        if ($lesson->test) {
            $lesson_test = $lesson->test;
            $lesson_quiz_questions = $lesson_test->test_active_questions();

            if ($lesson_quiz_questions->isNotEmpty()) {
                $lesson_quiz = $lesson_test;

                foreach ($lesson_quiz_questions as $question) {
                    if (empty($question->options)) {
                        $question->options = DB::table('test_question_options')
                            ->where('question_id', $question->id)
                            ->get();
                    }
                }

                $lesson_quiz_result = TestsResult::where('test_id', $lesson_test->id)
                    ->where('user_id', auth()->id())
                    ->orderBy('id', 'desc')
                    ->first();

                if ($lesson_quiz_result) {
                    $question_count = $lesson_quiz_questions->count();
                    $lesson_quiz_percentage = $question_count > 0
                        ? ($lesson_quiz_result->test_result / $question_count) * 100
                        : 0;
                    $lesson_quiz_pass = ($lesson_quiz_percentage >= 100) ? 'Pass' : 'Failed';
                }
            }
        }

        return [
            'lesson_quiz' => $lesson_quiz,
            'lesson_quiz_questions' => $lesson_quiz_questions,
            'lesson_quiz_result' => $lesson_quiz_result,
            'lesson_quiz_pass' => $lesson_quiz_pass,
            'lesson_quiz_percentage' => $lesson_quiz_percentage,
        ];
    }

    public function show($course_id, $lesson_slug = null)
    {
        //dd("hi");
        $test_result = "";
        $completed_lessons = "";

        $test_pass = "";
        $total_questions = "";
        $percentage = "";
        $assessment_link = "";

        $logged_in_user_id = auth()->user()->id;

        $sc = SubscribeCourse::where('course_id', $course_id)
            ->where('user_id', $logged_in_user_id)
            ->first();

        $isAssignmentTaken = $sc
            ? ($sc->assesment_taken ?? false)
            : $this->isAssignmentTaken($logged_in_user_id, $course_id);

        $assessment_link = $this->assessmentLink($logged_in_user_id, $course_id);
        $hasAssessmentLinkByUrl = !empty($assessment_link);
        $hasAssessmentLink = $sc
            ? ((bool) ($sc->has_assesment ?? 0) || $hasAssessmentLinkByUrl)
            : ($hasAssessmentLinkByUrl || (bool) $this->hasAssessmentLink($course_id, $logged_in_user_id));

        if ($sc && $hasAssessmentLinkByUrl && !(bool) ($sc->has_assesment ?? 0)) {
            $sc->has_assesment = 1;
            $sc->save();
        }

        //dd($hasAssessmentLink);

        $completed_at = ($sc && $sc->is_completed) ? $sc->completed_at : null;

        $is_certificate_download = false;
        $is_assesment_taken = false;
        $is_feedback_taken = false;
        $has_feedback = false;
        $has_assesment = false;

        $is_attended = $sc ? ($sc->is_attended ?? 0) : 0;

        //dd($logged_in_user_id, $isAssignmentTaken, $hasAssessmentLink);

        $courseFeedbackLink = '';
        //dd($this->assessmentLink($logged_in_user_id, $course_id), $hasAssessmentLink,  "hhh");

        //dd($isAssignmentTaken, $hasAssessmentLink, $assessment_link);

        if (!$hasAssessmentLink && CustomHelper::courseProgress($course_id, $logged_in_user_id) == 100) {
            $courseFeedbackLink = $this->courseFeedbackLink($course_id);
        }


        $assignment_status = ($sc && $sc->course)
            ? $sc->course->assignmentStatus($logged_in_user_id)
            : null;

        //dd($sc);

        if ($sc) {
            $is_certificate_download = $sc->course_progress_status == 2 ? true : false;
            $is_assesment_taken = $sc->assesment_taken ?? false;
            $is_feedback_taken = $sc->feedback_given ?? false;;
            $has_feedback = $sc->has_feedback ?? false;
            $has_assesment = $sc->has_assesment ?? false;;
        }

        $normalized_slug = is_string($lesson_slug)
            ? trim(rawurldecode($lesson_slug))
            : $lesson_slug;

        $lesson = $this->resolvePublishedLessonBySlug($course_id, $normalized_slug);

        if ($lesson == "") {
            $lesson = Test::where('slug', $normalized_slug)
                ->where('course_id', $course_id)
                ->where('published', '=', 1)
                ->first();

            if (!$lesson) {
                $fallback_lesson = Lesson::where('course_id', $course_id)
                    ->where('published', 1)
                    ->orderBy('id', 'asc')
                    ->first();

                if ($fallback_lesson) {
                    return redirect()->route('lessons.show', [$course_id, $fallback_lesson->slug]);
                }

                abort(404);
            }

            $lesson->full_text = $lesson->description;
            $test_result = TestsResult::where('test_id', $lesson->id)
                ->where('user_id', \Auth::id())
                ->first();
            //dd($test_result);
            if ($lesson && $test_result) {

                // get the tes't score

                $total_questions = $lesson->questions->count();
                $percentage = $test_result->test_result / $total_questions * 100;
                $test_pass = ($percentage < $lesson->passing_score) ? "Failed" : "Pass";
            }
        }

        if ($lesson instanceof Lesson) {
            $lesson_access = $this->canUserOpenLesson($lesson->course, $lesson, $logged_in_user_id);
            if (!$lesson_access['allowed'] && !empty($lesson_access['blocked_lesson'])) {
                return redirect()
                    ->route('lessons.show', [$course_id, $lesson_access['blocked_lesson']->slug])
                    ->with('flash_warning', 'To continue, you must complete and pass the quiz for lesson: ' . $lesson_access['blocked_lesson']->title . '.');
            }
        }


        //dd($lesson, $lesson->mediaVideo()->exists());
        if ($lesson instanceof Lesson && !$lesson->mediaVideo()->exists()) {

            $custom_helper = new CustomHelper();
            $custom_helper->updateUserProgress($logged_in_user_id, $course_id);
        }



        if ((int)config('lesson_timer') == 0) {
            if (!$lesson->live_lesson && !$lesson->mediaVideo) {
                if ($lesson->chapterStudents()->where('user_id', \Auth::id())->count() == 0) {
                    $lesson->chapterStudents()->create([
                        'model_type' => get_class($lesson),
                        'model_id' => $lesson->id,
                        'user_id' => auth()->user()->id,
                        'course_id' => $lesson->course->id
                    ]);

                    // Save the attendance
                    $data = [
                        'student_id' => \Auth::id(),
                        'course_id' => $lesson->course->id,
                        'lesson_id' => $lesson->id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    AttendanceStudent::create($data);
                }
            }
        }

        //dd("hh");

        $course_lessons = $lesson->course->lessons->pluck('id')->toArray();
        $course_lessons_arr = $lesson->course->lessons;
        $lessonCount = count($course_lessons);
        $course_tests = ($lesson->course->tests) ? $lesson->course->tests->pluck('id')->toArray() : [];
        $timeline = $lesson->courseTimeline()->customScope($course_id)->first();
        $sequence = $timeline->sequence ?? 0;
        $previous_lesson = $lesson->course->courseTimeline()
            ->where('sequence', '<', $sequence)
            ->whereIn('model_id', $course_lessons)
            ->orderBy('sequence', 'desc')
            ->first();

        $next_lesson = $lesson->course->courseTimeline()
            ->whereIn('model_id', $course_lessons)
            ->where('sequence', '>', $sequence)
            ->orderBy('sequence', 'asc')
            ->first();


        $lessons = $lesson->course->courseTimeline()
            ->whereIn('model_id', $course_lessons)
            ->orderby('id', 'asc')
            ->get();


        $purchased_course = $lesson->course->students()->where('user_id', \Auth::id())->count() > 0;
        $test_exists = FALSE;

        //dd("hh");

        if (!empty($course_lessons)) {

            $lesson->course->courseTimeline()->whereIn('model_id', $course_lessons)->orderby('id', 'asc')->get();
            //dd($l);
            //dd($lesson->course->courseTimeline()->orderBy('id')->get());
        } else {
        }

        //dd($lesson->course->courseTimeline()->orderBy('id')->get());

        //dd($lesson->course->progress());
        //dd($lesson->course->lessonProgress());
        if (get_class($lesson) == 'App\Models\Test') {
            $test_exists = TRUE;
        }
        //dd($lesson->media);
        $lesson_media = $lesson->media;

        $mediavideo = (isset($lesson_media[0])) ? $lesson_media[0] : '';

        $completed_lessons = \Auth::user()->chapters()
            ->where('course_id', $lesson->course->id)
            ->get()
            ->pluck('model_id')
            ->toArray();

        //dd("hhd");

        $is_course_completed = SubscribeCourse::where('course_id', $course_id)
            ->where('user_id', auth()->user()->id)
            ->where('is_completed', 1)
            ->count() ?? 0;

        $course_detail = Course::query()
            ->where('id',  $course_id)
            ->first();

        $is_offline_course = false;

        if ($course_detail->is_online == 'Offline') {
            $is_offline_course = true;
        }

        //dd($is_offline_course);
        //dd("99");



        if ($is_course_completed) {
            //dd("jj");
            $course_lessons  = Lesson::where('course_id', $course_id)
                ->when(!empty($completed_at), function ($q) use ($completed_at) {
                    return $q->where('created_at', '<', $completed_at);
                })
                ->where('published', 1)
                ->get();
            $lessonCount = count($completed_lessons);
        } else {
            //dd("jj2");
            $course_lessons  = Lesson::where('course_id', $course_id)
                ->where('published', 1)
                ->get();
            $lessonCount = $course_lessons->count();
        }

        //dd("kk");

        $lessons = $course_lessons;

        $course_lessons_arr = $course_lessons;

        //dd($has_assesment, $is_assesment_taken, $has_feedback, $is_feedback_taken,  $is_offline_course, $is_certificate_download, $is_course_completed);

        $is_certificate_download = $sc ? ($sc->grant_certificate ?? 0) : 0;

        //dd($lessonCount, );
        $lessonCompletedCount = count($completed_lessons);
        //dd($lessonCompletedCount, $course_lessons);

        $nextTasks = $sc
            ? CustomHelper::getNextTask($sc, $course_id)
            : [
                'failed_in_assesment_all_attempts' => false,
                'reattempt_assesment' => false,
                'completed_assesment' => false,
                'download_certificate' => false,
                'open_assesment' => false,
                'open_feedback' => false,
            ];
        //dd($nextTasks, $assessment_link);

        // ── Lesson-level quiz detection ───────────────────────────────────────
        $lesson_quiz = null;
        $lesson_quiz_pass = null;
        $lesson_quiz_url = null;
        $requires_lesson_quiz_pass_for_next = false;
        $can_access_next_lesson = true;

        if ($lesson instanceof Lesson) {
            $quiz_data = $this->getLessonQuizData($lesson);
            $lesson_quiz = $quiz_data['lesson_quiz'];
            $lesson_quiz_pass = $quiz_data['lesson_quiz_pass'];

            if ($lesson_quiz) {
                $lesson_quiz_url = route('lessons.lesson_quiz.show', [$course_id, $lesson->slug]);
            }
        }

        if ($next_lesson && get_class($lesson) === 'App\Models\Lesson' && $lesson_quiz) {
            $requires_lesson_quiz_pass_for_next = true;
            $can_access_next_lesson = ($lesson_quiz_pass === 'Pass');
        }
        // ─────────────────────────────────────────────────────────────────────

        return view($this->path . '.courses.lesson', compact(
            'is_certificate_download',
            'is_assesment_taken',
            'is_feedback_taken',
            'has_feedback',
            'has_assesment',
            'nextTasks',
            'lessonCompletedCount',
            'is_attended',
            'is_offline_course',
            'is_course_completed',
            'mediavideo',
            'assessment_link',
            'course_lessons',
            'lessonCount',
            'lesson',
            'previous_lesson',
            'next_lesson',
            'test_result',
            'purchased_course',
            'test_exists',
            'lessons',
            'completed_lessons',
            'test_pass',
            'percentage',
            'total_questions',
            'course_id',
            'isAssignmentTaken',
            'courseFeedbackLink',
            'assignment_status',
            'course_lessons_arr',
            'lesson_quiz',
            'lesson_quiz_pass',
            'lesson_quiz_url',
            'requires_lesson_quiz_pass_for_next',
            'can_access_next_lesson'
        ));
    }

    /**
     * Display the dedicated quiz section for a lesson.
     */
    public function showLessonQuiz($course_id, $lesson_slug)
    {
        $logged_in_user_id = auth()->id();
        $normalized_slug = is_string($lesson_slug)
            ? trim(rawurldecode($lesson_slug))
            : $lesson_slug;

        $lesson = $this->resolvePublishedLessonBySlug($course_id, $normalized_slug);
        if (!$lesson) {
            abort(404);
        }

        if (!$lesson->isCompleted()) {
            return redirect()
                ->route('lessons.show', [$course_id, $lesson->slug])
                ->with('flash_warning', 'Complete this lesson first to unlock its quiz section.');
        }

        $lesson_access = $this->canUserOpenLesson($lesson->course, $lesson, $logged_in_user_id);
        if (!$lesson_access['allowed'] && !empty($lesson_access['blocked_lesson'])) {
            return redirect()
                ->route('lessons.show', [$course_id, $lesson_access['blocked_lesson']->slug])
                ->with('flash_warning', 'To continue, you must complete and pass the quiz for lesson: ' . $lesson_access['blocked_lesson']->title . '.');
        }

        $quiz_data = $this->getLessonQuizData($lesson);
        if (!$quiz_data['lesson_quiz']) {
            return redirect()
                ->route('lessons.show', [$course_id, $lesson->slug])
                ->with('flash_warning', 'No quiz is configured for this lesson.');
        }

        $course_lessons = $lesson->course->lessons->pluck('id')->toArray();
        $timeline = $lesson->courseTimeline()->customScope($course_id)->first();
        $sequence = $timeline->sequence ?? 0;
        $next_lesson = $lesson->course->courseTimeline()
            ->whereIn('model_id', $course_lessons)
            ->where('sequence', '>', $sequence)
            ->orderBy('sequence', 'asc')
            ->first();

        $can_access_next_lesson = ($quiz_data['lesson_quiz_pass'] === 'Pass');

        return view($this->path . '.courses.lesson-quiz', [
            'course_id' => $course_id,
            'lesson' => $lesson,
            'next_lesson' => $next_lesson,
            'can_access_next_lesson' => $can_access_next_lesson,
            'lesson_quiz' => $quiz_data['lesson_quiz'],
            'lesson_quiz_questions' => $quiz_data['lesson_quiz_questions'],
            'lesson_quiz_result' => $quiz_data['lesson_quiz_result'],
            'lesson_quiz_pass' => $quiz_data['lesson_quiz_pass'],
            'lesson_quiz_percentage' => $quiz_data['lesson_quiz_percentage'],
        ]);
    }

    /**
     * Handle submission of a lesson-level quiz (TestQuestion-based).
     */
    public function submitLessonQuiz(Request $request, $lesson_id)
    {
        $lesson = Lesson::findOrFail($lesson_id);
        $lessonTest = $lesson->test;

        if (!$lesson->isCompleted()) {
            return redirect()
                ->route('lessons.show', [$lesson->course_id, $lesson->slug])
                ->with('flash_warning', 'Complete this lesson first to unlock its quiz section.');
        }

        if (!$lessonTest) {
            return back()->with('flash_warning', 'No quiz found for this lesson.');
        }

        if ($request->boolean('retest')) {
            TestsResult::where('test_id', $lessonTest->id)
                ->where('user_id', auth()->id())
                ->delete();

            return back();
        }

        $submitted = $request->input('lesson_quiz_questions', []);

        if (empty($submitted)) {
            return back()->with('flash_warning', 'Please select an answer for each question before submitting.');
        }

        $correct = 0;
        foreach ($submitted as $question_id => $selected_option_id) {
            $isRight = DB::table('test_question_options')
                ->where('id', (int) $selected_option_id)
                ->where('question_id', (int) $question_id)
                ->where('is_right', 1)
                ->exists();
            if ($isRight) {
                $correct++;
            }
        }

        // Replace any previous attempt so the student always sees their latest score.
        TestsResult::where('test_id', $lessonTest->id)
            ->where('user_id', auth()->id())
            ->delete();

        TestsResult::create([
            'test_id'     => $lessonTest->id,
            'user_id'     => auth()->id(),
            'test_result' => $correct,
        ]);

        return back();
    }

    public function test($lesson_slug, Request $request)
    {
        //dd($request->all());
        $test = Test::where('slug', $lesson_slug)->firstOrFail();
        //dd($request->get('questions'));
        $answers = [];
        $test_score = 0;
        $total_score = 0;
        if (!$request->get('questions')) {

            return back()->with(['flash_warning' => 'No options selected']);
        }
        foreach ($request->get('questions') as $question_id => $answer_id) {
            $question = Question::find($question_id);
            $correct = QuestionsOption::where('question_id', $question_id)
                ->where('id', $answer_id)
                ->where('correct', 1)->count() > 0;
            $answers[] = [
                'question_id' => $question_id,
                'option_id' => $answer_id,
                'correct' => $correct
            ];
            if ($correct) {
                if ($question->score) {
                    $test_score += $question->score;
                }
            }
            /*
             * Save the answer
             * Check if it is correct and then add points
             * Save all test result and show the points
             */
        }
        $test_result = TestsResult::create([
            'test_id' => $test->id,
            'user_id' => \Auth::id(),
            'test_result' => $test_score,
            'test_score' => $total_score,
            'course_id' => $test->course_id,
        ]);
        $test_result->answers()->createMany($answers);

        // get the test score

        $total_questions = count($request->get('questions'));
        $percentage = $test_score / $total_questions * 100;
        $test_pass = ($percentage < $test->passing_score) ? "Failed" : "Pass";

        if ($test->chapterStudents()->where('user_id', \Auth::id())->get()->count() == 0) {
            $test->chapterStudents()->create([
                'model_type' => $test->model_type,
                'model_id' => $test->id,
                'user_id' => auth()->user()->id,
                'course_id' => $test->course->id
            ]);
        }

        return back()->with([
            'message' => 'Test score: ' . $test_score,
            'result' => $test_result,
            'test_percentage' => $percentage,
            'test_pass' => $test_pass,
            'total_score' => $total_score
        ]);
    }

    public function retest(Request $request)
    {
        $test = TestsResult::where('id', '=', $request->result_id)
            ->where('user_id', '=', auth()->user()->id)
            ->first();
        $test->delete();
        return back();
    }

    public function videoProgress(Request $request)
    {
        //dd( $request->all(), "rrrr" );
        $user = auth()->user();
        $video = Media::findOrFail($request->video);
        $video_progress = VideoProgress::where('user_id', '=', $user->id)
            ->where('media_id', '=', $video->id)->first() ?: new VideoProgress();
        $video_progress->media_id = $video->id;
        $video_progress->user_id = $user->id;
        $video_progress->duration = $video_progress->duration ?: round($request->duration, 2);
        $video_progress->progress = round($request->progress, 2);
        if ($video_progress->duration - $video_progress->progress < 5) {
            $video_progress->progress = $video_progress->duration;
            $video_progress->complete = 1;
        }
        $video_progress->save();

        /**
         * check if video has been watched upto certain percentage in order to mark lesson as completed
         */
        $percentageCompleted = $video->getProgressPercentage($user->id);
        $percentageToMarkLessonAsCompleted = 90;
        if ($percentageCompleted >= $percentageToMarkLessonAsCompleted) {
            // mark lesson as completed
            $lesson = Lesson::find($video->model_id);
            if ($lesson->chapterStudents()->where('user_id', \Auth::id())->count() == 0) {
                $lesson->chapterStudents()->create([
                    'model_type' => get_class($lesson),
                    'model_id' => $lesson->id,
                    'user_id' => auth()->user()->id,
                    'course_id' => $lesson->course->id
                ]);

                // Save the attendance
                $data = [
                    'student_id' => \Auth::id(),
                    'course_id' => $lesson->course->id,
                    'lesson_id' => $lesson->id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                AttendanceStudent::create($data);
            }
        }
        return $video_progress->progress;
    }

    public function videoProgressUpdates(Request $request)
    {





        $user = auth()->user();
        $video = Media::findOrFail($request->vedio_id);

        $lesson = Lesson::find($video->model_id);
        $course_id = $lesson->course->id ?? null;

        if ($course_id && $user) {
            $already_completed = CustomHelper::isCourseAlreadyCompleted($user->id, $course_id);
            if ($already_completed) {
                return;
            }
        }

        //dd($already_completed);

        if ($already_completed == 0) {
            $video_progress = VideoProgress::where('user_id', '=', $user->id)->where('media_id', '=', $video->id)->first() ?: new VideoProgress();

            $video_progress->media_id = $video->id;
            $video_progress->user_id = $user->id;
            $video_progress->progress_per = $request->watchPoint;
            if ($video_progress->progress_per == '100') {
                $video_progress->complete = 1;
            }

            $video_progress->duration = $video_progress->duration ?: round($request->duration, 2);
            $video_progress->progress = round($request->progress, 2);
            $video_progress->save();

            // progress update for user
            $video = Media::findOrFail($request->vedio_id);

            $course_id = null;
            /**
             * check if video has been watched upto certain percentage in order to mark lesson as completed
             */
            $percentageCompleted = $video->getProgressPercentage($user->id);
            //dd($percentageCompleted);
            $percentageToMarkLessonAsCompleted = 90;



            //if ($percentageCompleted >= $percentageToMarkLessonAsCompleted) {
            if ($percentageCompleted >= $percentageToMarkLessonAsCompleted) {


                // mark lesson as completed
                $lesson = Lesson::find($video->model_id);

                $course_id = $lesson->course->id ?? null;

                //dd($course_id);

                //dd($lesson->chapterStudents()->get());
                if ($lesson->chapterStudents()->where('user_id', \Auth::id())->count() == 0) {
                    $lesson->chapterStudents()->create([
                        'model_type' => get_class($lesson),
                        'model_id' => $lesson->id,
                        'user_id' => auth()->user()->id,
                        'course_id' => $lesson->course->id
                    ]);

                    // Save the attendance
                    $data = [
                        'student_id' => \Auth::id(),
                        'course_id' => $lesson->course->id,
                        'lesson_id' => $lesson->id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    AttendanceStudent::create($data);

                    //dd($lesson->course->id);

                    if ($lesson->course->id) {

                        //dd($lesson->course->id, \Auth::id());
                        $progressdata = CustomHelper::updateUserProgress(\Auth::id(), $lesson->course->id);
                    }
                }
            }


            $lesson = Lesson::find($video->model_id);
            $course_id = $lesson->course->id ?? null;

            if ($course_id) {

                //CustomHelper::updateUserProgress(\Auth::id(), $course_id);

                EmployeeCourseProgress::updateOrCreate(
                    [
                        'user_id' => \Auth::id(),
                        'course_id' => $course_id
                    ],
                    [
                        'is_cron_run' => 0,
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s')
                    ]
                );

                $sub_data = SubscribeCourse::where('course_id', $course_id)
                    ->where('user_id', \Auth::id())
                    ->first();

                if ($sub_data && $sub_data->course_progress_status == 0 && $percentageCompleted >= $percentageToMarkLessonAsCompleted) {
                    $sub_data->update([
                        'course_progress_status' => 1
                    ]);
                }


                CustomHelper::updateGrantCertificate($course_id, \Auth::id());

            }


            return response()->json([
                'progress_per' => $video_progress->progress_per,
                'lesson_completed' => $percentageCompleted >= $percentageToMarkLessonAsCompleted,
            ]);
        }
    }


    public function courseProgress(Request $request)
    {
        //dd($request->all());
        if (\Auth::check()) {
            $lesson = Lesson::find($request->model_id);
            if ($lesson != null) {
                if ($lesson->chapterStudents()->where('user_id', \Auth::id())->get()->count() == 0) {
                    $lesson->chapterStudents()->create([
                        'model_type' => $request->model_type,
                        'model_id' => $request->model_id,
                        'user_id' => auth()->user()->id,
                        'course_id' => $lesson->course->id
                    ]);
                    return true;
                }
            }
        }
        return false;
    }

    public function bookSlot(Request $request)
    {
        $lesson_slot = LiveLessonSlot::find($request->live_lesson_slot_id);
        $lesson = $lesson_slot->lesson;

        if ((int)config('lesson_timer') == 0) {
            if ($lesson->chapterStudents()->where('user_id', \Auth::id())->count() == 0) {
                $lesson->chapterStudents()->create([
                    'model_type' => get_class($lesson),
                    'model_id' => $lesson->id,
                    'user_id' => auth()->user()->id,
                    'course_id' => $lesson->course->id
                ]);
            }
        }

        if (LessonSlotBooking::where('lesson_id', $request->lesson_id)->where('user_id', auth()->user()->id)->count() == 0) {
            LessonSlotBooking::create(
                ['lesson_id' => $request->lesson_id, 'live_lesson_slot_id' => $request->live_lesson_slot_id, 'user_id' => auth()->user()->id]
            );
            \Mail::to(auth()->user()->email)->send(new StudentMeetingSlotMail($lesson_slot));
        }
        return back()->with(['success' => __('alerts.frontend.course.slot_booking')]);
    }

    public function attendance_lesson(Request $request, $course_id, $lesson_id)
    {
        $lessons_list = Lesson::with('course')->where('course_id', $course_id)->where('id', $lesson_id)->first();
        return view('delta_academy.attendance.attendance', compact('lessons_list'));
    }

    public function save_attendance_lesson(Request $request)
    {
        //dd( $request->all() );
        $course_id = $request->course_id;
        $lesson_id = $request->lesson_id;
        $user = User::where('email', $request->email)->first();
        if ($user) {
            //dd($course_id);
            $has_course_subscribed = DB::table('course_student')->where('course_id', $course_id)->where('user_id', $user->id);
            $has_course_subscribed = $has_course_subscribed->first();
            if ($has_course_subscribed) {
                $has_already_mark_present = AttendanceStudent::where('student_id', $user->id)
                    ->where('course_id', $course_id)
                    ->where('lesson_id', $lesson_id)
                    ->first();
                if ($has_already_mark_present) {
                    return redirect()->route('attendance.attendance.lesson', [$request->course_id, $request->lesson_id])->with('success', __('alerts.backend.general.already_mark_present'));
                } else {
                    $lesson_detail = Lesson::where('course_id', $course_id)->where('id', $lesson_id)->first();
                    $lesson_present_time = strtotime($lesson_detail->lesson_start_date . " +10 minutes");
                    //dd(date('Y-m-d H:i:s'));
                    //dd(date('Y-m-d H:i:s',$lesson_present_time));
                    if (strtotime(date('Y-m-d H:i:s')) < strtotime($lesson_detail->lesson_start_date . " -10 minutes")) {
                        if ($lesson_present_time >= strtotime(date('Y-m-d H:i:s')) && strtotime(date('Y-m-d H:i:s')) >= strtotime($lesson_detail->lesson_start_date . " -10 minutes")) {
                            $data = [
                                'student_id' => $user->id,
                                'course_id' => $course_id,
                                'lesson_id' => $lesson_id,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ];
                            AttendanceStudent::create($data);
                            return redirect()->route('attendance.attendance.lesson', [$request->course_id, $request->lesson_id])->with('success', __('alerts.backend.general.mark_attendance'));
                        } else {
                            return redirect()->route('attendance.attendance.lesson', [$request->course_id, $request->lesson_id])->with('success', __('alerts.backend.general.not_yet_started_lesson'));
                        }
                    } else {
                        return redirect()->route('attendance.attendance.lesson', [$request->course_id, $request->lesson_id])->with('success', __('alerts.backend.general.you_are_late_for_this_lesson'));
                    }
                }
            } else {
                return redirect()->route('attendance.attendance.lesson', [$request->course_id, $request->lesson_id])->with('error', __('alerts.backend.general.couse_not_subscribed'));
            }
        } else {
            return redirect()->route('attendance.attendance.lesson', [$request->course_id, $request->lesson_id])->with('error', __('alerts.backend.general.couse_not_subscribed'));
        }
    }
}
