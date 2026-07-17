<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\FeedbackQuestion;
use App\Models\Lesson;
use App\Models\Test;
use App\Models\TestQuestionOption;
use App\Models\TestsResult;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TestQuestionController extends Controller
{

    public function test_questions_delete($id)
    {

        DB::table('test_questions')->where('id', $id)->delete();
        return back()->withFlashSuccess(trans('alerts.backend.general.deleted'));
        print_r($id);
        die;
    }



    public function index(Request $request)
    {
        $test_questions = DB::table('test_questions')
            ->select('test_questions.*', 'tests.title', 'courses.title as course_title')
            ->leftjoin('tests', 'tests.id', '=', 'test_questions.test_id')
            ->leftjoin('courses', 'courses.id', '=', 'tests.course_id')
            ->where('test_questions.is_deleted', '=', 0);

        if ($request->test_id) {
            $test_questions = $test_questions->where('test_questions.test_id', '=', $request->test_id);
        }

        $this->scopeQuestionsToAssignedCoursesForNonAdmins($test_questions);

        if ($request->course_id != "") {
            $test_questions = $test_questions->where('tests.course_id', (int)$request->course_id);
        }

        $test_questions = $test_questions->get();

        return view('backend.test_questions.index', compact('test_questions'));
    }

    public function create(Request $request, $course_id = null, $temp_id = null)
    {
        $course_id = $request->input('course_id')
            ?? $course_id
            ?? optional(Test::find($request->test_id))->course_id
            ?? optional(Test::find($request->input('test_id')))->course_id;
        $temp_id = $temp_id ?? $request->uuid; 
        $legacy_test_id = (int) $request->input('test_id');
        $selected_test = $legacy_test_id > 0 ? Test::find($legacy_test_id) : null;

        if (!$course_id && $selected_test && $selected_test->course_id) {
            $course_id = (int) $selected_test->course_id;
        }

        if (!$course_id && (!$selected_test || !$selected_test->course_id)) {
            return redirect()
                ->route('admin.test_questions.index')
                ->withFlashDanger('Please select a course before adding a new question.');
        }

        $lessons = $course_id
            ? Lesson::query()
                ->where('course_id', $course_id)
                ->where(function ($q) {
                     $q->where('published', 1)
                        ->orWhereNull('published'); // fallback for old data
                })
                ->withCount([
                    'questions as questions_count' => function ($query) {
                        $query->whereNull('deleted_at');
                    }
                ])
                ->orderBy('position')
                ->orderBy('id')
                ->get([
                    'id',
                    'title',
                    'course_id'
                ])
            : collect([])->values();
        $lesson_id_preselect = $request->input('lesson_id');
        $lock_lesson_selection = $request->filled('lesson_id');

        if (!$lesson_id_preselect && $selected_test && $selected_test->lesson_id) {
            $lesson_id_preselect = (int) $selected_test->lesson_id;
        }

        if ($lesson_id_preselect && $lessons->isNotEmpty() && !$lessons->contains('id', (int) $lesson_id_preselect)) {
            $lesson_id_preselect = null;
        }

        $last_lesson_id = $lessons->isNotEmpty() ? (int) optional($lessons->last())->id : null;
        $selected_lesson_preselect = null;
        $is_last_lesson_preselect = false;

        if ($lesson_id_preselect && $lessons->isNotEmpty()) {
            $selected_lesson_preselect = $lessons->firstWhere('id', (int) $lesson_id_preselect);
            $is_last_lesson_preselect = $selected_lesson_preselect
                ? ((int) $selected_lesson_preselect->id === (int) $last_lesson_id)
                : false;
        }

        return view('backend.test_questions.create', compact(
            'course_id',
            'temp_id',
            'lessons',
            'lesson_id_preselect',
            'lock_lesson_selection',
            'last_lesson_id',
            'selected_lesson_preselect',
            'is_last_lesson_preselect',
            'legacy_test_id'
        ));
    }

    public function store(Request $request)
{
    $options = [];

    // Accept both "marks" and legacy "score", but normalize to marks
    $marksInput = $request->input('marks', $request->input('score'));

    if ($marksInput === null || $marksInput === '') {
        return response()->json([
            'success' => false,
            'message' => 'Please provide marks for the question.',
            'errors' => 'Please provide marks for the question.',
        ], 422);
    }

    if (!is_numeric($marksInput)) {
        return response()->json([
            'success' => false,
            'message' => 'Marks must be a valid number.',
            'errors' => 'Marks must be a valid number.',
        ], 422);
    }

    $marks = (int) $marksInput;
    $questionType = (int) $request->question_type;

    if ($questionType == 1) {
        $options = isset($request->options) ? json_decode($request->options) : [];
        if (isset($request->options) && count($options) == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide the options',
                'errors' => 'Please provide the options',
            ], 422);
        }
    } elseif ($questionType == 2) {
        $options = isset($request->options) ? json_decode($request->options) : [];
        if (isset($request->options) && count($options) == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide the options',
                'errors' => 'Please provide the options',
            ], 422);
        }
    } else { // 3 short answer
        if (empty($request->solution)) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide the answer',
                'errors' => 'Please provide the answer',
            ], 422);
        }
    }

    if (in_array($questionType, [1, 2], true) && !$this->checkOptionValidation($options)) {
        return response()->json([
            'success' => false,
            'message' => 'At least one option must be selected.',
        ], 422);
    }

    if ($request->options) {
        $decodedOptions = json_decode($request->options);
        $options = is_array($decodedOptions) ? $decodedOptions : [];
    }

    // Legacy compatibility: test_id may be present, but explicit lesson selection must win.
    $legacy_test_id = (int) $request->input('test_id');

    // Questions can be lesson-level (lesson_id set) or course-level final assessment (lesson_id NULL)
    $lesson_id = (int) $request->input('lesson_id');
    $lesson_id = $lesson_id > 0 ? $lesson_id : null;
    $course_id = (int) $request->input('course_id');

    $lesson = null;
    if ($lesson_id) {
        // Lesson-level question always uses the selected lesson quiz test.
        $lesson = Lesson::find($lesson_id);
        if (!$lesson) {
            return response()->json([
                'success' => false,
                'message' => 'The selected lesson is invalid.',
                'errors' => 'The selected lesson is invalid.',
            ], 422);
        }

        if ($course_id && (int) $lesson->course_id !== $course_id) {
            return response()->json([
                'success' => false,
                'message' => 'The selected lesson does not belong to the selected course.',
                'errors' => 'The selected lesson does not belong to the selected course.',
            ], 422);
        }

        $course_id = (int) $lesson->course_id;

        $lessonTest = Test::firstOrCreate(
            ['lesson_id' => $lesson->id],
            [
                'course_id'     => $course_id,
                'title'         => ($lesson->title ?? 'Lesson') . ' - Quiz',
                'description'   => ($lesson->title ?? 'Lesson') . ' - Quiz',
                'passing_score' => 100,
                'published'     => 1,
            ]
        );

        if ((int) $lessonTest->course_id !== $course_id || is_null($lessonTest->passing_score)) {
            $lessonTest->course_id = $course_id;
            if (is_null($lessonTest->passing_score)) {
                $lessonTest->passing_score = 100;
            }
            $lessonTest->save();
        }

        $resolved_test_id = $lessonTest->id;
    } elseif ($legacy_test_id > 0) {
        $legacyTest = Test::find($legacy_test_id);
        if (!$legacyTest) {
            return response()->json([
                'success' => false,
                'message' => 'The selected test is invalid.',
                'errors' => 'The selected test is invalid.',
            ], 422);
        }

        if ($course_id && $legacyTest->course_id && (int) $legacyTest->course_id !== $course_id) {
            return response()->json([
                'success' => false,
                'message' => 'The selected test does not belong to the selected course.',
                'errors' => 'The selected test does not belong to the selected course.',
            ], 422);
        }

        $course_id = $course_id ?: (int) $legacyTest->course_id;
        $lesson_id = $legacyTest->lesson_id ? (int) $legacyTest->lesson_id : null;
        $resolved_test_id = $legacyTest->id;
    } else {
        // Final assessment question (no lesson)
        if (!$course_id) {
            return response()->json([
                'success' => false,
                'message' => 'Please select either a lesson or provide the course ID for final assessment.',
                'errors' => 'Course or lesson is required.',
            ], 422);
        }

        $finalAssessmentTest = Test::firstOrCreate(
            ['course_id' => $course_id, 'lesson_id' => null],
            [
                'title'         => 'Final Assessment',
                'description'   => 'Final Assessment - ' . now()->year,
                'passing_score' => 70,
                'published'     => 1,
            ]
        );

        $resolved_test_id = $finalAssessmentTest->id;
    }

    $question_id = DB::table('test_questions')->insertGetId([
        'temp_id'       => $request->temp_id ?? null,
        'test_id'       => $resolved_test_id,
        'lesson_id'     => $lesson_id ?? null,
        'question_type' => $questionType,
        'question_text' => $request->question,
        'solution'      => $request->solution,
        'marks'         => $marks, // use canonical column from feature branch
        'comment'       => $request->comment,
        'option_json'   => $questionType != 3 ? $request->options : null,
        'created_at'    => date('Y-m-d H:i:s'),
        'updated_at'    => date('Y-m-d H:i:s'),
    ]);

    if ($questionType != 3) {
        foreach ($options as $key => $value) {
            DB::table('test_question_options')->insert([
                'temp_id'     => $request->temp_id ?? null,
                'question_id' => $question_id,
                'option_text' => $value[0],
                'is_right'    => $value[1],
            ]);
        }
    }

if ($request->action_btn == 'save_and_add_more') {

    $params = [
        'course_id=' . $course_id,
    ];

    if ($lesson_id !== null) {
        $params[] = 'lesson_id=' . ($lesson_id ?? 0);
    }

    if (!empty($request->temp_id)) {
        $params[] = 'uuid=' . urlencode($request->temp_id);
    }

    if ($legacy_test_id > 0) {
        $params[] = 'test_id=' . $legacy_test_id;
    }

    $redirect_url = route('admin.test_questions.create') . '?' . implode('&', $params);
} 

    // Capture the course's wizard state BEFORE it gets overwritten below.
    // This is the only reliable signal to tell apart two flows that hit this
    // same store() action with an identical request shape (course_id set,
    // temp_id blank, action_btn=Next):
    //   1) The multi-step Create Course wizard (Course -> Lesson -> Questions
    //      -> Feedback), where current_step is still 'course-added' or
    //      'lesson-added' at this point.
    //   2) An ad-hoc question added to an already-existing course via the
    //      standalone Tests Management / Question Bank screens, where
    //      current_step is already past that (e.g. 'question-added',
    //      'feedback-added') or null for legacy courses.
    // Only flow (1) should auto-advance into the Feedback wizard step.
    $courseWizardStep = Course::where('id', $course_id)->value('current_step');
    $isCourseCreationWizard = in_array($courseWizardStep, ['course-added', 'lesson-added'], true);

    Course::where('id', $course_id)->update([
        'current_step' => 'question-added'
    ]);

    if ($isCourseCreationWizard && $request->action_btn == 'Next') {
        if ($course_id) {
            $has_feeback = FeedbackQuestion::query()
                ->where('course_id', $course_id)
                ->where('temp_id', $request->temp_id)
                ->count();

            if ($has_feeback == 0) {
                $redirect_url = route('admin.feedback.create_course_feedback', ['course_id' => $course_id]);
            }
        }
    }

    if ($request->action_btn == 'Save As Draft') {
        $redirect_url = route('admin.courses.index');
    }

    if ($request->action_btn == 'Next') {
        // Lesson quiz questions are independent from course-level assignments.
    }

    return json_encode([
        'code' => 200,
        'message' => 'Question Inserted',
        'redirect_url' => $redirect_url ?? route('admin.test_questions.index')
    ]);
}

    public function upload_ck_image(Request $request): JsonResponse
    {
        if ($request->hasFile('upload')) {
            $originName = $request->file('upload')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $extension = $request->file('upload')->getClientOriginalExtension();
            $fileName = $fileName . '_' . time() . '.' . $extension;

            $request->file('upload')->move(public_path('assets/img/ckeditor'), $fileName);

            $url = asset('assets/img/ckeditor/' . $fileName);

            return response()->json(['fileName' => $fileName, 'uploaded' => 1, 'url' => $url]);
        }
    }

    public function edit(Request $request, $id)
    {
        $test_id = $request->test_id;
        if ($test_id != NULL) {
            $tests = DB::table('tests')->where('id', $test_id)->get();
        } else {
            $tests = DB::table('tests')->where('deleted_at', '=', NULL)->get();
        }
        $question = DB::table('test_questions')->where('id', $id)->where('is_deleted', 0)->first();
        if ($question && (int) $question->question_type !== 3) {
            $optionRows = TestQuestionOption::where('question_id', $question->id)
                ->orderBy('id')
                ->get(['option_text', 'is_right']);

            if ($optionRows->isNotEmpty()) {
                $question->option_json = $optionRows
                    ->map(function ($option) {
                        return [
                            $option->option_text,
                            (int) $option->is_right,
                        ];
                    })
                    ->values()
                    ->toJson();
            }
        }
        return view('backend.test_questions.edit', compact('question', 'tests'));
    }

    public function update(Request $request)
    {
        $marksInput = $request->input('marks', $request->input('score'));

        if ($marksInput === null || $marksInput === '' || !is_numeric($marksInput) || (int) $marksInput <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide valid marks for the question',
                'errors' => 'Please provide valid marks for the question',
            ], 422);
        }

        $marks = (int) $marksInput;
        $questionType = (int) $request->question_type;
        $options = [];

        if($questionType == 1) {
            $options = isset($request->options) ? json_decode($request->options) : [];
            if(isset($request->options) && count($options) == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide the options',
                    'errors' => "Please provide the options",
                ], 422);
            }
        } else if($questionType == 2) {

            $options = isset($request->options) ? json_decode($request->options) : [];
            if(isset($request->options) && count($options) == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide the options',
                    'errors' => "Please provide the options",
                ], 422);
            }
        } else { // 3 short answer
            if(empty($request->solution)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide the answer',
                    'errors' => "Please provide the answer",
                ], 422);
            }
        }

        if (in_array($questionType, [1, 2], true) && !$this->checkOptionValidation($options)) {
            return response()->json([
                'success' => false,
                'message' => 'At least one option must be selected.',
            ], 422);
        }
        

        $question =  DB::table('test_questions')->where('id', $request->id)->where('is_deleted', 0)->first();

        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Question not found.',
            ], 404);
        }

        if ($request->options) {
            $options = json_decode($request->options) ?? [];
        }


        DB::table('test_questions')->where('id', $request->id)->where('is_deleted', 0)->update([
            'test_id' => $request->test_id,
            'question_type' => $questionType,
            'question_text' => $request->question,
            'solution' => $request->solution,
            'marks' => $marks,
            'comment' => $request->comment,
            'option_json' => $questionType != 3 ? $request->options : NULL
        ]);

        TestQuestionOption::where('question_id', $question->id)->delete();
        
        if ($questionType != 3) {
            foreach ($options as $key => $value) {

            
                TestQuestionOption::insert([
                    'question_id' => $question->id,
                    'option_text' => $value[0],
                    'is_right' => $value[1],
                ]);
            

            /*
            TestQuestionOption::updateOrInsert(
                ['question_id' => $question_id->id, 'option_text' => $value[0]],
                ['question_id' => $question_id->id, 'option_text' =>$value[0], 'is_right' =>  $value[1]]
            );
            */
            }
        }

        

        session()->flash('flash_success', 'Question updated successfully.');

        return response()->json(array(
            'code' => 200,
            'message' => 'Question updated successfully.'
        ));
    }


    protected function checkOptionValidation($options)
    {
        //dd($options);
        $hasAtLeastOneSelected = false;
        foreach ($options as $option) {
            if (is_array($option) && isset($option[1]) && $option[1] == 1) {
                $hasAtLeastOneSelected = true;
                break;
            }
        }
        
        return $hasAtLeastOneSelected;
        
    }

    private function scopeQuestionsToAssignedCoursesForNonAdmins($query): void
    {
        if (!$this->shouldScopeQuestionsToAssignedCourses()) {
            return;
        }

        $query->whereExists(function ($subQuery) {
            $subQuery->select(DB::raw(1))
                ->from('course_user')
                ->whereColumn('course_user.course_id', 'tests.course_id')
                ->where('course_user.user_id', auth()->id());
        });
    }

    private function shouldScopeQuestionsToAssignedCourses(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return !auth()->user()->isAdmin();
    }


    public function question_setup(Request $request)
    {
        $question_type = $request->question_type;
        // dd($question_type);
        if ($question_type == 1) {
            $view = view('backend/test_question_setup/single_choice');
            echo $view;
        } else if ($question_type == 2) {
            $view = view('backend/test_question_setup/multiple_choice');
            echo $view;
        } else {
            $view = view('backend/test_question_setup/short_answer');
            echo $view;
        }
    }

    public function question_setup_feedback(Request $request)
    {
        $question_type = $request->question_type;
        $feedbackQuestion = FeedbackQuestion::find($request->id);

        if ($question_type == 1) {
            $view = view('backend/feedback_question_setup/single_choice', compact('feedbackQuestion'));
            echo $view;
        } else if ($question_type == 2) {
            $view = view('backend/feedback_question_setup/multiple_choice', compact('feedbackQuestion'));
            echo $view;
        } else {
            $view = view('backend/feedback_question_setup/short_answer', compact('feedbackQuestion'));
            echo $view;
        }
    }

    public function lessonQuizShortAnswers(Request $request)
    {
        $status = $request->input('status') === 'reviewed' ? 'reviewed' : 'pending';

        $answers = DB::table('lesson_quiz_answers')
            ->join('test_questions', 'test_questions.id', '=', 'lesson_quiz_answers.question_id')
            ->join('tests_results', 'tests_results.id', '=', 'lesson_quiz_answers.tests_result_id')
            ->leftJoin('tests', 'tests.id', '=', 'tests_results.test_id')
            ->leftJoin('lessons', 'lessons.id', '=', 'tests.lesson_id')
            ->leftJoin('courses', 'courses.id', '=', 'tests.course_id')
            ->leftJoin('users', 'users.id', '=', 'lesson_quiz_answers.user_id')
            ->where('test_questions.question_type', 3)
            ->when(!$this->currentUserCanReviewAllCourses(), function ($query) {
                $query->whereExists(function ($subQuery) {
                    $subQuery->select(DB::raw(1))
                        ->from('course_user')
                        ->whereColumn('course_user.course_id', 'tests.course_id')
                        ->where('course_user.user_id', auth()->id());
                });
            })
            ->when($status === 'reviewed', function ($query) {
                $query->whereNotNull('lesson_quiz_answers.is_correct');
            }, function ($query) {
                $query->whereNull('lesson_quiz_answers.is_correct');
            })
            ->select(
                'lesson_quiz_answers.*',
                'test_questions.question_text',
                'test_questions.solution',
                'tests_results.test_result',
                'tests_results.test_id',
                'lessons.title as lesson_title',
                'courses.title as course_title',
                'users.first_name',
                'users.last_name',
                'users.email'
            )
            ->orderBy('lesson_quiz_answers.created_at', 'desc')
            ->paginate(25);

        $assessmentAnswers = DB::table('assignment_questions')
            ->join('test_questions', 'test_questions.id', '=', 'assignment_questions.question_id')
            ->leftJoin('assignments', 'assignments.id', '=', 'assignment_questions.assignment_id')
            ->leftJoin('courses', 'courses.id', '=', 'assignments.course_id')
            ->leftJoin('users', 'users.id', '=', 'assignment_questions.assessment_account_id')
            ->where('test_questions.question_type', 3)
            ->when(!$this->currentUserCanReviewAllCourses(), function ($query) {
                $query->whereExists(function ($subQuery) {
                    $subQuery->select(DB::raw(1))
                        ->from('course_user')
                        ->whereColumn('course_user.course_id', 'assignments.course_id')
                        ->where('course_user.user_id', auth()->id());
                });
            })
            ->when($status === 'reviewed', function ($query) {
                $query->whereIn('assignment_questions.is_correct', [1, 2]);
            }, function ($query) {
                $query->where('assignment_questions.is_correct', 0);
            })
            ->select(
                'assignment_questions.*',
                'test_questions.question_text',
                'test_questions.solution',
                'test_questions.marks as question_marks',
                'courses.title as course_title',
                'assignments.course_id',
                'users.first_name',
                'users.last_name',
                'users.email'
            )
            ->orderBy('assignment_questions.id', 'desc')
            ->paginate(25, ['*'], 'assessment_page');

        return view('backend.test_questions.lesson-quiz-short-answers', compact('answers', 'assessmentAnswers'));
    }

    public function reviewLessonQuizShortAnswer(Request $request, $id)
    {
        $request->validate([
            'is_correct' => 'required|in:0,1',
        ]);

        $answer = DB::table('lesson_quiz_answers')->where('id', $id)->first();

        if (!$answer) {
            return back()->withFlashDanger('Short answer submission not found.');
        }

        if (!$this->canCurrentUserReviewLessonQuizAnswer((int) $id)) {
            abort(403);
        }

        DB::table('lesson_quiz_answers')
            ->where('id', $id)
            ->update([
                'is_correct' => (int) $request->input('is_correct'),
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'updated_at' => now(),
            ]);

        $score = DB::table('lesson_quiz_answers')
            ->where('tests_result_id', $answer->tests_result_id)
            ->where('is_correct', 1)
            ->count();

        TestsResult::where('id', $answer->tests_result_id)->update([
            'test_result' => $score,
        ]);

        return back()->withFlashSuccess('Short answer review saved.');
    }

    public function reviewAssessmentShortAnswer(Request $request, $id)
    {
        $request->validate([
            'is_correct' => 'required|in:1,2',
        ]);

        $answer = DB::table('assignment_questions')->where('id', $id)->first();

        if (!$answer) {
            return back()->withFlashDanger('Short answer submission not found.');
        }

        if (!$this->canCurrentUserReviewAssessmentAnswer((int) $id)) {
            abort(403);
        }

        $testQuestion = DB::table('test_questions')->where('id', $answer->question_id)->first();

        DB::table('assignment_questions')
            ->where('id', $id)
            ->update([
                'is_correct' => (int) $request->input('is_correct'),
                'marks' => (int) $request->input('is_correct') === 1 ? ($testQuestion->marks ?? 0) : 0,
            ]);

        $assignment = DB::table('assignments')->where('id', $answer->assignment_id)->first();
        if ($assignment && $assignment->course_id && $answer->assessment_account_id) {
            \CustomHelper::updateUserProgress($answer->assessment_account_id, $assignment->course_id);
        }

        return back()->withFlashSuccess('Short answer review saved.');
    }

    private function currentUserCanReviewAllCourses(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    private function canCurrentUserReviewLessonQuizAnswer(int $answerId): bool
    {
        if ($this->currentUserCanReviewAllCourses()) {
            return true;
        }

        return DB::table('lesson_quiz_answers')
            ->join('tests_results', 'tests_results.id', '=', 'lesson_quiz_answers.tests_result_id')
            ->join('tests', 'tests.id', '=', 'tests_results.test_id')
            ->join('course_user', 'course_user.course_id', '=', 'tests.course_id')
            ->where('lesson_quiz_answers.id', $answerId)
            ->where('course_user.user_id', auth()->id())
            ->exists();
    }

    private function canCurrentUserReviewAssessmentAnswer(int $answerId): bool
    {
        if ($this->currentUserCanReviewAllCourses()) {
            return true;
        }

        return DB::table('assignment_questions')
            ->join('assignments', 'assignments.id', '=', 'assignment_questions.assignment_id')
            ->join('course_user', 'course_user.course_id', '=', 'assignments.course_id')
            ->where('assignment_questions.id', $answerId)
            ->where('course_user.user_id', auth()->id())
            ->exists();
    }
}
