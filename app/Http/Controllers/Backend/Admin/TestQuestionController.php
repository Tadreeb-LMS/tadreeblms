<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\FeedbackQuestion;
use App\Models\Lesson;
use App\Models\Test;
use App\Models\TestQuestionOption;
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
        if ($request->test_id) {
            $test_questions = DB::table('test_questions')
                ->select('test_questions.*', 'tests.title', 'courses.title as course_title')
                ->leftjoin('tests', 'tests.id', '=', 'test_questions.test_id')
                ->leftjoin('courses', 'courses.id', '=', 'tests.course_id')
                ->where('test_questions.is_deleted', '=', 0)
                ->where('test_questions.test_id', '=', $request->test_id);
        } else {
            $test_questions = DB::table('test_questions')
                ->select('test_questions.*', 'tests.title', 'courses.title as course_title')
                ->leftjoin('tests', 'tests.id', '=', 'test_questions.test_id')
                ->leftjoin('courses', 'courses.id', '=', 'tests.course_id')
                ->where('test_questions.is_deleted', '=', 0);
        }


        if ($request->course_id != "") {
            $test_questions = $test_questions->where('tests.course_id', (int)$request->course_id);
        }

        $test_questions = $test_questions->get();

        return view('backend.test_questions.index', compact('test_questions'));
    }

    public function create(Request $request, $course_id = null, $temp_id = null)
    {
        $course_id = $course_id ?? $request->course_id;
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
            ? Lesson::where('course_id', $course_id)
                ->where('published', 1)
                ->orderBy('position')
                ->orderBy('id')
                ->get(['id', 'title'])
            : collect();

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

    if ($marks < 1 || $marks > 999) {
        return response()->json([
            'success' => false,
            'message' => 'Marks must be between 1 and 999.',
            'errors' => 'Marks must be between 1 and 999.',
        ], 422);
    }

    if ($request->question_type == 1) {
        $options = isset($request->options) ? json_decode($request->options) : [];
        if (isset($request->options) && count($options) == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide the options',
                'errors' => 'Please provide the options',
            ], 422);
        }
    } elseif ($request->question_type == 2) {
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

    $status = $this->checkOptionValidation($options);
    // if (!$status) {
    //     return response()->json([
    //         'success' => false,
    //         'message' => 'At least one option must be selected.',
    //     ], 422);
    // }

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
        'question_type' => $request->question_type,
        'question_text' => $request->question,
        'solution'      => $request->solution,
        'marks'         => $marks, // use canonical column from feature branch
        'comment'       => $request->comment,
        'option_json'   => $request->question_type != 3 ? $request->options : null,
        'created_at'    => date('Y-m-d H:i:s'),
        'updated_at'    => date('Y-m-d H:i:s'),
    ]);

    foreach ($options as $key => $value) {
        DB::table('test_question_options')->insert([
            'temp_id'     => $request->temp_id ?? null,
            'question_id' => $question_id,
            'option_text' => $value[0],
            'is_right'    => $value[1],
        ]);
    }

    if ($request->action_btn == 'save_and_add_more') {
        if ($legacy_test_id > 0 && !$lesson_id) {
            $params = ['test_id=' . $legacy_test_id];
            if ($course_id) {
                $params[] = 'course_id=' . $course_id;
            }
            if (!empty($request->temp_id)) {
                $params[] = 'uuid=' . urlencode($request->temp_id);
            }
            if ($lesson_id) {
                $params[] = 'lesson_id=' . $lesson_id;
            }
            $redirect_url = route('admin.test_questions.create') . '?' . implode('&', $params);
        } else {
            $redirect_url = route('admin.test_questions.create', [$course_id, $request->temp_id]);
            if ($lesson_id) {
                $redirect_url .= '?lesson_id=' . $lesson_id;
            }
        }
    }

    Course::where('id', $course_id)->update([
        'current_step' => 'question-added'
    ]);

    if (isset($request->temp_id) && $request->action_btn == 'Next') {
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
        return view('backend.test_questions.edit', compact('question', 'tests'));
    }

    public function update(Request $request)
    {

        return json_encode(array(
            'code' => 200,
            'message' => 'Question Updated is not option'
        ));
        
        if (empty($request->marks) || $request->marks <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide valid marks for the question',
                'errors' => 'Please provide valid marks for the question',
            ], 422);
        }

        if($request->question_type ==1) {
            $options = isset($request->options) ? json_decode($request->options) : [];
            if(isset($request->options) && count($options) == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide the options',
                    'errors' => "Please provide the options",
                ], 422);
            }
        } else if($request->question_type ==2) {

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

        $status = $this->checkOptionValidation($options);
        if(!$status ) {
            return response()->json([
                'success' => false,
                'message' => 'At least one option must be selected.',
            ], 422);
        }
        

        $question_id =  DB::table('test_questions')->where('id', $request->id)->where('is_deleted', 0)->first();

        if ($request->options) {
            $options = json_decode($request->options) ?? [];
        }


        DB::table('test_questions')->where('id', $request->id)->where('is_deleted', 0)->update([
            'test_id' => $request->test_id,
            'question_type' => $request->question_type,
            'question_text' => $request->question,
            'solution' => $request->solution,
            'score' => $request->score,
            'comment' => $request->comment,
            'option_json' => $request->question_type != 3 ? $request->options : NULL
        ]);

        TestQuestionOption::where('question_id', $question_id->id)->delete();
        
        foreach ($options as $key => $value) {

            
            TestQuestionOption::insert([
                'question_id' => $question_id->id,
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

        

        return json_encode(array(
            'code' => 200,
            'message' => 'Question Updated'
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
}
